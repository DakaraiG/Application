<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css?family=Inter&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="main.css">
    <title>expenses_and_income</title>
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
                <span class="dashboard-title">Expenses and Income</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
                
                <div class="content-gray-bar-income-and-expenses">
                    <canvas class="chart-box" id="incomeChart"></canvas>
                    <canvas class="chart-box" id="expenseChart"></canvas>

                    <script>
                    const colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

                    function drawChart(canvasId, type) {
                        fetch(`data.php?chart=category&type=${type}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.error) {
                                alert(data.error);
                                return;
                            }
                            if (!data.length) {
                                alert('No data for ' + type);
                                return;
                            }

                            const labels = data.map(item => item.label);
                            const values = data.map(item => parseFloat(item.value));

                            new Chart(document.getElementById(canvasId), {
                                type: 'pie',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        data: values,
                                        backgroundColor: colors
                                    }]
                                }
                            });
                        });
                    }

                    drawChart('incomeChart', 'income');
                    drawChart('expenseChart', 'expense');
                    </script>

                </div>
            </div>
        </main>
    </div>
</body>
</html>