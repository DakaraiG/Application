<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

// Function to handle empty results
function handleEmpty($message) {
    return '<div class="empty-state">'.$message.'</div>';
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Inter&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="main.css">
    <title>dashboard</title>
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
                <span class="dashboard-title">Dashboard</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
            
            <div class="content-white">
                <!-- Balance Summary -->
                <div class="metrics-container">
                    <?php
                    // Total Balance
                    $total_balance = 0.00;
                    $balance_query = "SELECT SUM(balance) AS total_balance FROM Accounts WHERE user_id = ?";
                    $stmt = $conn->prepare($balance_query);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $balance = $result->fetch_assoc();
                        $total_balance = $balance['total_balance'] ?? 0.00;
                    }
                    ?>
                    <div class="metric-box">
                        <h3>Total Balance</h3>
                        <p>£<?= number_format($total_balance, 2) ?></p>
                        <?php if($total_balance == 0): ?>
                            <div class="empty-state">No accounts found</div>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Transactions Summary -->
                    <div class="metric-box">
                        <h3>Recent Transactions</h3>
                        <?php
                        $transactions_query = "SELECT t.amount, t.type, t.date 
                                             FROM Transactions t
                                             JOIN Accounts a ON t.account_id = a.account_id
                                             WHERE a.user_id = ?
                                             ORDER BY t.date DESC LIMIT 5";
                        $stmt = $conn->prepare($transactions_query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0):
                            while ($transaction = $result->fetch_assoc()):
                        ?>
                            <div class="transaction-item">
                                <span><?= date('d M', strtotime($transaction['date'])) ?></span>
                                <span class="<?= $transaction['type'] ?>">
                                    £<?= number_format($transaction['amount'], 2) ?>
                                </span>
                            </div>
                        <?php 
                            endwhile;
                        else:
                            echo handleEmpty('No transactions found');
                        endif;
                        ?>
                    </div>

                    <!-- Budgets Summary -->
                    <div class="metric-box">
                        <h3>Current Budgets</h3>
                        <?php
                        $budgets_query = "SELECT category, `limit` FROM Budgets 
                                        WHERE user_id = ? 
                                        AND CURDATE() BETWEEN start_date AND end_date";
                        $stmt = $conn->prepare($budgets_query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0):
                            while ($budget = $result->fetch_assoc()):
                        ?>
                            <div class="budget-item">
                                <span><?= htmlspecialchars($budget['category']) ?></span>
                                <span>£<?= number_format($budget['limit'], 2) ?></span>
                            </div>
                        <?php 
                            endwhile;
                        else:
                            echo handleEmpty('No active budgets');
                        endif;
                        ?>
                    </div>
                </div>

                <!-- Goals Progress -->
                <div class="content-gray-bar">
                    <h3>Savings Goals</h3>
                    <?php
                    $goals_query = "SELECT goal_name, target_amount, saved_amount 
                                  FROM Goals WHERE user_id = ?";
                    $stmt = $conn->prepare($goals_query);
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0):
                        while ($goal = $result->fetch_assoc()):
                            $progress = ($goal['target_amount'] > 0) 
                                      ? ($goal['saved_amount'] / $goal['target_amount']) * 100 
                                      : 0;
                    ?>
                        <div class="goal-progress">
                            <div class="progress-label">
                                <?= htmlspecialchars($goal['goal_name']) ?>
                                <span><?= number_format($progress, 0) ?>%</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $progress ?>%"></div>
                            </div>
                        </div>
                    <?php 
                        endwhile;
                    else:
                        echo handleEmpty('No savings goals set');
                    endif;
                    $conn->close();
                    ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>