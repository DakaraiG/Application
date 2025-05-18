<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)$_POST['amount'];
    $category = $conn->real_escape_string($_POST['category']);
    $type = $conn->real_escape_string($_POST['type']);
    $date = $conn->real_escape_string($_POST['date']);
    $account_id = (int)$_POST['account_id'];
    $note = $conn->real_escape_string($_POST['note']);

    $stmt = $conn->prepare("INSERT INTO Transactions (account_id, amount, category, type, date, note) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("idssss", $account_id, $amount, $category, $type, $date, $note);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Transaction added successfully!";
    } else {
        $_SESSION['error'] = "Error adding transaction: " . $conn->error;
    }
    $stmt->close();
}

// Get user's accounts
$accounts_query = $conn->prepare("SELECT account_id, account_name FROM Accounts WHERE user_id = ?");
$accounts_query->bind_param("i", $_SESSION['user_id']);
$accounts_query->execute();
$accounts_result = $accounts_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Inter&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="main.css">
    <title>Expenses & Income</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="dashboard-grid">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="sidebar-inner">
                <a href="dashboard.php">Home</a>
                <a href="profile.php">Profile</a>
                <a href="expense_and_income.php">Expenses</a>
                <a href="goals.php">Goals</a>
                <a href="inbox.php">Inbox</a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="content-area">
            <div class="dashboard-header">
                <span class="dashboard-title">Expenses & Income</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
            
            <div class="content-white">
                <?php if(isset($_SESSION['message'])): ?>
                    <div class="success-message"><?= $_SESSION['message'] ?></div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="error-message"><?= $_SESSION['error'] ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Transaction Form -->
                <div class="transaction-form-section">
                    <h3>Add New Transaction</h3>
                    <form method="POST">
                        <div class="form-columns">
                            <div class="form-group">
                                <label>Type</label>
                                <select name="type" required>
                                    <option value="expense">Expense</option>
                                    <option value="income">Income</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Amount</label>
                                <input type="number" step="0.01" name="amount" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Category</label>
                                <input type="text" name="category" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Date</label>
                                <input type="date" name="date" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Account</label>
                                <select name="account_id" required>
                                    <?php if($accounts_result->num_rows > 0): ?>
                                        <?php while($account = $accounts_result->fetch_assoc()): ?>
                                            <option value="<?= $account['account_id'] ?>">
                                                <?= htmlspecialchars($account['account_name']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <option disabled>No accounts found</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Note</label>
                                <textarea name="note" rows="2"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="submit-btn">Add Transaction</button>
                    </form>
                </div>

                <!-- Charts Section -->
                <div class="charts-container">
                    <div class="chart-box">
                        <canvas id="incomeChart"></canvas>
                    </div>
                    <div class="chart-box">
                        <canvas id="expenseChart"></canvas>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div class="recent-transactions">
                    <h3>Recent Transactions</h3>
                    <div class="transactions-list">
                        <?php
                        $transactions_query = $conn->prepare("
                            SELECT t.*, a.account_name 
                            FROM Transactions t
                            JOIN Accounts a ON t.account_id = a.account_id
                            WHERE a.user_id = ?
                            ORDER BY t.date DESC LIMIT 10
                        ");
                        $transactions_query->bind_param("i", $_SESSION['user_id']);
                        $transactions_query->execute();
                        $transactions = $transactions_query->get_result();
                        
                        if ($transactions->num_rows > 0):
                            while($transaction = $transactions->fetch_assoc()):
                        ?>
                            <div class="transaction-item <?= $transaction['type'] ?>">
                                <div class="transaction-header">
                                    <span><?= date('M d, Y', strtotime($transaction['date'])) ?></span>
                                    <span class="account-name"><?= htmlspecialchars($transaction['account_name']) ?></span>
                                </div>
                                <div class="transaction-details">
                                    <span class="category"><?= htmlspecialchars($transaction['category']) ?></span>
                                    <span class="amount">£<?= number_format($transaction['amount'], 2) ?></span>
                                </div>
                                <?php if(!empty($transaction['note'])): ?>
                                    <div class="transaction-note"><?= htmlspecialchars($transaction['note']) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; else: ?>
                            <div class="empty-state">No transactions found</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    // Enhanced Chart Script
    document.addEventListener('DOMContentLoaded', function() {
        const chartConfig = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ` £${context.parsed.toFixed(2)} (${Math.round(context.percent)}%)`;
                        }
                    }
                }
            }
        };

        function loadChart(canvasId, type) {
            fetch(`data.php?chart=category&type=${type}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.length) {
                        document.getElementById(canvasId).parentElement.innerHTML = 
                            `<div class="empty-state">No ${type} data available</div>`;
                        return;
                    }

                    new Chart(document.getElementById(canvasId), {
                        type: 'doughnut',
                        data: {
                            labels: data.map(item => item.label),
                            datasets: [{
                                data: data.map(item => item.value),
                                backgroundColor: [
                                    '#FF6384', '#36A2EB', '#FFCE56', 
                                    '#4BC0C0', '#9966FF', '#FF9F40'
                                ],
                                borderWidth: 2
                            }]
                        },
                        options: chartConfig
                    });
                })
                .catch(error => console.error('Error loading chart:', error));
        }

        loadChart('incomeChart', 'income');
        loadChart('expenseChart', 'expense');
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>