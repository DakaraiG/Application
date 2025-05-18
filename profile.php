<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_account'])) {
        $account_name = $conn->real_escape_string($_POST['account_name']);
        $type = $conn->real_escape_string($_POST['type']);
        $balance = (float)$_POST['balance'];
        
        $stmt = $conn->prepare("INSERT INTO Accounts (user_id, account_name, balance, type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isds", $_SESSION['user_id'], $account_name, $balance, $type);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Account added successfully!";
        } else {
            $_SESSION['error'] = "Error adding account: " . $conn->error;
        }
        $stmt->close();
    }
}

// Get user data
$user_query = $conn->prepare("SELECT firstname, surname, email FROM Users WHERE user_id = ?");
$user_query->bind_param("i", $_SESSION['user_id']);
$user_query->execute();
$user_result = $user_query->get_result();
$user_data = $user_result->fetch_assoc();

// Get accounts
$accounts_query = $conn->prepare("SELECT account_name, balance, type FROM Accounts WHERE user_id = ?");
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
    <title>Profile</title>
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
                <span class="dashboard-title">User Profile</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
            
            <div class="content-white">
                <!-- User Information -->
                <div class="profile-section">
                    <h3>Personal Information</h3>
                    <div class="user-info">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($user_data['firstname'] . ' ' . htmlspecialchars($user_data['surname'])); ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($user_data['email']) ?></p>
                    </div>
                </div>

                <!-- Add Account Form -->
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

                    <form method="POST" class="account-form">
                        <div class="form-group">
                            <label>Account Name:</label>
                            <input type="text" name="account_name" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Account Type:</label>
                            <select name="type" required>
                                <option value="bank">Bank Account</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Initial Balance:</label>
                            <input type="number" step="0.01" name="balance" required>
                        </div>
                        
                        <button type="submit" name="add_account" class="submit-btn">Add Account</button>
                    </form>
                </div>

                <!-- Existing Accounts -->
                <div class="profile-section">
                    <h3>Your Accounts</h3>
                    <div class="accounts-list">
                        <?php if($accounts_result->num_rows > 0): ?>
                            <?php while($account = $accounts_result->fetch_assoc()): ?>
                                <div class="account-item">
                                    <div class="account-header">
                                        <span><?= htmlspecialchars($account['account_name']) ?></span>
                                        <span class="account-type"><?= ucfirst($account['type']) ?></span>
                                    </div>
                                    <div class="account-balance">
                                        £<?= number_format($account['balance'], 2) ?>
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
</body>
</html>
<?php $conn->close(); ?>