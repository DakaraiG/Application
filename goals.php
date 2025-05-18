<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_goal'])) {
        $goal_name = $conn->real_escape_string($_POST['goal_name']);
        $target_amount = (float)$_POST['target_amount'];
        $deadline = $conn->real_escape_string($_POST['deadline']);

        $stmt = $conn->prepare("INSERT INTO Goals (user_id, goal_name, target_amount, deadline) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issd", $_SESSION['user_id'], $goal_name, $target_amount, $deadline);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Goal added successfully!";
        } else {
            $_SESSION['error'] = "Error adding goal: " . $conn->error;
        }
        $stmt->close();
    }

    if (isset($_POST['update_savings'])) {
        $goal_id = (int)$_POST['goal_id'];
        $amount = (float)$_POST['amount'];

        $stmt = $conn->prepare("UPDATE Goals SET saved_amount = saved_amount + ? WHERE goal_id = ? AND user_id = ?");
        $stmt->bind_param("dii", $amount, $goal_id, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Savings updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating savings: " . $conn->error;
        }
        $stmt->close();
    }
}

// Get user's goals
$goals_query = $conn->prepare("SELECT * FROM Goals WHERE user_id = ? ORDER BY deadline ASC");
$goals_query->bind_param("i", $_SESSION['user_id']);
$goals_query->execute();
$goals_result = $goals_query->get_result();
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

                <!-- Add Goal Form -->
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

                <!-- Goals List -->
                <div class="goals-list">
                    <h3>Your Savings Goals</h3>
                    <?php if($goals_result->num_rows > 0): ?>
                        <?php while($goal = $goals_result->fetch_assoc()): 
                            $progress = ($goal['saved_amount'] / $goal['target_amount']) * 100;
                            $days_remaining = ceil((strtotime($goal['deadline']) - time()) / (60 * 60 * 24));
                        ?>
                            <div class="goal-item">
                                <div class="goal-header">
                                    <h4><?= htmlspecialchars($goal['goal_name']) ?></h4>
                                    <span class="deadline">
                                        <?= date('M d, Y', strtotime($goal['deadline'])) ?>
                                        (<?= $days_remaining > 0 ? $days_remaining . ' days left' : 'Expired' ?>)
                                    </span>
                                </div>
                                
                                <div class="progress-container">
                                    <div class="progress-label">
                                        <span>Saved: £<?= number_format($goal['saved_amount'], 2) ?></span>
                                        <span>Target: £<?= number_format($goal['target_amount'], 2) ?></span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?= min($progress, 100) ?>%"></div>
                                    </div>
                                </div>

                                <form method="POST" class="update-savings-form">
                                    <input type="hidden" name="goal_id" value="<?= $goal['goal_id'] ?>">
                                    <div class="form-group">
                                        <label>Add to Savings (£)</label>
                                        <input type="number" step="0.01" name="amount" required>
                                    </div>
                                    <button type="submit" name="update_savings" class="submit-btn-small">Update</button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">No savings goals found</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php $conn->close(); ?>