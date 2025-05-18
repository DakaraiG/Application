<?php
require_once 'config.php';
checkAuth();

// Get user data
$user_id = $_SESSION['user_id'];

// Total balance calculation
$total_balance = 0.00;
$balance_query = $conn->prepare("SELECT SUM(balance) AS total_balance FROM Accounts WHERE user_id = ?");
$balance_query->bind_param("i", $user_id);
$balance_query->execute();
$balance_result = $balance_query->get_result();
if ($balance_result->num_rows > 0) {
    $balance_data = $balance_result->fetch_assoc();
    $total_balance = $balance_data['total_balance'] ?? 0.00;
}

// Recent transactions
$transactions_query = $conn->prepare("SELECT t.*, a.account_name 
                                    FROM Transactions t
                                    JOIN Accounts a ON t.account_id = a.account_id
                                    WHERE a.user_id = ?
                                    ORDER BY t.date DESC LIMIT 5");
$transactions_query->bind_param("i", $user_id);
$transactions_query->execute();
$transactions = $transactions_query->get_result();

// Current budgets
$budgets_query = $conn->prepare("SELECT category, `limit` FROM Budgets 
                               WHERE user_id = ? AND CURDATE() BETWEEN start_date AND end_date");
$budgets_query->bind_param("i", $user_id);
$budgets_query->execute();
$budgets = $budgets_query->get_result();

// Savings goals
$goals_query = $conn->prepare("SELECT * FROM Goals WHERE user_id = ? ORDER BY deadline ASC");
$goals_query->bind_param("i", $user_id);
$goals_query->execute();
$goals = $goals_query->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Inter&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="main.css">
    <title>Dashboard</title>
</head>
<body>
    <div class="dashboard-grid">
        <nav class="sidebar">
            <div class="sidebar-inner">
                <a href="dashboard.php">Home</a>
                <a href="profile.php">Profile</a>
                <a href="expense_and_income.php">Expenses</a>
                <a href="goals.php">Goals</a>
                <a href="inbox.php">Inbox</a>
            </div>
        </nav>

        <main class="content-area">
            <div class="dashboard-header">
                <span class="dashboard-title">Dashboard</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
            
            <div class="content-white">
                <div class="metrics-container">
                    <div class="metric-box">
                        <h3>Total Balance</h3>
                        <p>£<?= number_format($total_balance, 2) ?></p>
                        <?= ($total_balance == 0) ? handleEmpty('No accounts found') : '' ?>
                    </div>

                    <div class="metric-box">
                        <h3>Recent Transactions</h3>
                        <?php if($transactions->num_rows > 0): ?>
                            <?php while($transaction = $transactions->fetch_assoc()): ?>
                            <div class="transaction-item">
                                <span><?= date('d M', strtotime($transaction['date'])) ?></span>
                                <span class="<?= $transaction['type'] ?>">
                                    £<?= number_format($transaction['amount'], 2) ?>
                                </span>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <?= handleEmpty('No transactions found') ?>
                        <?php endif; ?>
                    </div>

                    <div class="metric-box">
                        <h3>Current Budgets</h3>
                        <?php if($budgets->num_rows > 0): ?>
                            <?php while($budget = $budgets->fetch_assoc()): ?>
                            <div class="budget-item">
                                <span><?= htmlspecialchars($budget['category']) ?></span>
                                <span>£<?= number_format($budget['limit'], 2) ?></span>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <?= handleEmpty('No active budgets') ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="content-gray-bar">
                    <h3>Savings Goals</h3>
                    <?php if($goals->num_rows > 0): ?>
                        <?php while($goal = $goals->fetch_assoc()): 
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
                        <?php endwhile; ?>
                    <?php else: ?>
                        <?= handleEmpty('No savings goals set') ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <?php $conn->close(); ?>
</body>
</html>