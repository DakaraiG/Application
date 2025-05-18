<?php
require_once 'config.php';
checkAuth();

// Handle goal creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_goal'])) {
    $goal_name = $conn->real_escape_string($_POST['goal_name']);
    $target_amount = (float)$_POST['target_amount'];
    $deadline = $conn->real_escape_string($_POST['deadline']);

    $stmt = $conn->prepare("INSERT INTO Goals (user_id, goal_name, target_amount, deadline) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $_SESSION['user_id'], $goal_name, $target_amount, $deadline);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Goal created successfully!";
    } else {
        $_SESSION['error'] = "Error creating goal: " . $conn->error;
    }
    $stmt->close();
}

// Handle savings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_savings'])) {
    $goal_id = (int)$_POST['goal_id'];
    $amount = (float)$_POST['amount'];
    $account_id = (int)$_POST['account_id'];

    $conn->begin_transaction();
    try {
        // Check account balance
        $balance_result = $conn->query("SELECT balance FROM Accounts 
                                      WHERE account_id = $account_id 
                                      AND user_id = {$_SESSION['user_id']}");
        $balance = $balance_result->fetch_assoc()['balance'];
        
        if ($balance < $amount) {
            throw new Exception("Insufficient funds in selected account");
        }

        // Update account balance
        $conn->query("UPDATE Accounts SET balance = balance - $amount 
                     WHERE account_id = $account_id");

        // Update goal savings
        $conn->query("UPDATE Goals SET saved_amount = saved_amount + $amount 
                     WHERE goal_id = $goal_id");

        // Record transaction
        $conn->query("INSERT INTO Transactions (account_id, amount, category, type, date)
                     VALUES ($account_id, $amount, 'Savings Transfer', 'expense', NOW())");

        $conn->commit();
        $_SESSION['message'] = "£" . number_format($amount, 2) . " added to savings!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
}

// Get user goals
$goals = $conn->prepare("SELECT * FROM Goals WHERE user_id = ? ORDER BY deadline ASC");
$goals->bind_param("i", $_SESSION['user_id']);
$goals->execute();
$goal_result = $goals->get_result();

// Get user accounts
$accounts = $conn->query("SELECT account_id, account_name, balance 
                        FROM Accounts WHERE user_id = {$_SESSION['user_id']}");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Inter&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="main.css">
    <title>Savings Goals</title>
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
                <span class="dashboard-title">Savings Goals</span>
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

                <div class="goal-form-section">
                    <h3>Create New Goal</h3>
                    <form method="POST">
                        <div class="form-columns">
                            <div class="form-group">
                                <label>Goal Name</label>
                                <input type="text" name="goal_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Target Amount (£)</label>
                                <input type="number" step="0.01" name="target_amount" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Deadline</label>
                                <input type="date" name="deadline" required>
                            </div>
                        </div>
                        <button type="submit" name="add_goal" class="submit-btn">Create Goal</button>
                    </form>
                </div>

                <div class="goals-list">
                    <h3>Your Goals</h3>
                    <?php if($goal_result->num_rows > 0): ?>
                        <?php while($goal = $goal_result->fetch_assoc()): 
                            $progress = ($goal['saved_amount'] / $goal['target_amount']) * 100;
                            $days_left = ceil((strtotime($goal['deadline']) - time()) / 86400);
                        ?>
                            <div class="goal-item">
                                <div class="goal-header">
                                    <h4><?= htmlspecialchars($goal['goal_name']) ?></h4>
                                    <div class="goal-meta">
                                        <span class="target">Target: £<?= number_format($goal['target_amount'], 2) ?></span>
                                        <span class="deadline <?= $days_left < 0 ? 'expired' : '' ?>">
                                            <?= $days_left > 0 ? 
                                                $days_left . " days left" : 
                                                "Expired " . date('M d, Y', strtotime($goal['deadline'])) 
                                            ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="progress-container">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= min($progress, 100) ?>%"></div>
                                    </div>
                                    <div class="progress-label">
                                        <span>Saved: £<?= number_format($goal['saved_amount'], 2) ?></span>
                                        <span><?= number_format($progress, 1) ?>%</span>
                                    </div>
                                </div>

                                <form method="POST" class="update-savings-form">
                                    <input type="hidden" name="goal_id" value="<?= $goal['goal_id'] ?>">
                                    <div class="form-group">
                                        <label>Transfer From:</label>
                                        <select name="account_id" required>
                                            <?php while($account = $accounts->fetch_assoc()): ?>
                                                <option value="<?= $account['account_id'] ?>">
                                                    <?= htmlspecialchars($account['account_name']) ?> 
                                                    (£<?= number_format($account['balance'], 2) ?>)
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Amount (£)</label>
                                        <input type="number" step="0.01" name="amount" required>
                                    </div>
                                    <button type="submit" name="update_savings" class="submit-btn-small">Add Funds</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">No savings goals created yet</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <?php $conn->close(); ?>
</body>
</html>