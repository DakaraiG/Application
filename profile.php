<?php
require_once 'config.php';
checkAuth();

// Handle account creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_account'])) {
    $account_name = $conn->real_escape_string($_POST['account_name']);
    $type = $conn->real_escape_string($_POST['type']);
    $balance = (float)$_POST['balance'];

    $stmt = $conn->prepare("INSERT INTO Accounts (user_id, account_name, type, balance) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issd", $_SESSION['user_id'], $account_name, $type, $balance);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Account created successfully!";
    } else {
        $_SESSION['error'] = "Error creating account: " . $conn->error;
    }
    $stmt->close();
}

// Get user data
$user_stmt = $conn->prepare("SELECT firstname, surname, email FROM Users WHERE user_id = ?");
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Get accounts with transaction summary
$accounts = $conn->prepare("SELECT a.*, 
                          SUM(CASE WHEN t.type = 'income' THEN t.amount ELSE 0 END) AS total_income,
                          SUM(CASE WHEN t.type = 'expense' THEN t.amount ELSE 0 END) AS total_expenses
                          FROM Accounts a
                          LEFT JOIN Transactions t ON a.account_id = t.account_id
                          WHERE a.user_id = ?
                          GROUP BY a.account_id");
$accounts->bind_param("i", $_SESSION['user_id']);
$accounts->execute();
$account_result = $accounts->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Inter&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="main.css">
    <title>Profile</title>
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
                <span class="dashboard-title">Profile</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
            
            <div class="content-white">
                <div class="profile-section">
                    <h3>Personal Information</h3>
                    <div class="user-info">
                        <p><strong>Name:</strong> <?= htmlspecialchars($user_data['firstname'] . ' ' . $user_data['surname']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($user_data['email']) ?></p>
                    </div>
                </div>

                <div class="profile-section">
                    <h3>Add New Account</h3>
                    <?php if(isset($_SESSION['message'])): ?>
                        <div class="success-message"><?= $_SESSION['message'] ?></div>
                        <?php unset($_SESSION['message']); ?>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="error-message"><?= $_SESSION['error'] ?></div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="form-columns">
                            <div class="form-group">
                                <label>Account Name</label>
                                <input type="text" name="account_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Account Type</label>
                                <select name="type" required>
                                    <option value="bank">Bank Account</option>
                                    <option value="cash">Cash</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Initial Balance (£)</label>
                                <input type="number" step="0.01" name="balance" value="0.00" required>
                            </div>
                        </div>
                        <button type="submit" name="add_account" class="submit-btn">Create Account</button>
                    </form>
                </div>

                <div class="profile-section">
                    <h3>Your Accounts</h3>
                    <div class="accounts-list">
                        <?php if($account_result->num_rows > 0): ?>
                            <?php while($account = $account_result->fetch_assoc()): ?>
                                <div class="account-item">
                                    <div class="account-header">
                                        <h4><?= htmlspecialchars($account['account_name']) ?></h4>
                                        <span class="account-type"><?= ucfirst($account['type']) ?></span>
                                    </div>
                                    <div class="account-balance">
                                        £<?= number_format($account['balance'], 2) ?>
                                    </div>
                                    <div class="account-summary">
                                        <div class="summary-item income">
                                            <span>Income</span>
                                            <span>+£<?= number_format($account['total_income'], 2) ?></span>
                                        </div>
                                        <div class="summary-item expense">
                                            <span>Expenses</span>
                                            <span>-£<?= number_format($account['total_expenses'], 2) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">No accounts found</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <?php $conn->close(); ?>
</body>
</html>