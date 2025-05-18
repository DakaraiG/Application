<?php
require_once 'config.php';
checkAuth();

header('Content-Type: application/json');

if (!isset($_GET['chart'])) {
    die(json_encode(['error' => 'Invalid request']));
}

$user_id = $_SESSION['user_id'];
$type = isset($_GET['type']) ? $conn->real_escape_string($_GET['type']) : 'expense';

try {
    $stmt = $conn->prepare("
        SELECT category AS label, SUM(amount) AS value 
        FROM Transactions t
        JOIN Accounts a ON t.account_id = a.account_id
        WHERE a.user_id = ? AND t.type = ?
        GROUP BY category
    ");
    $stmt->bind_param("is", $user_id, $type);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'label' => $row['label'],
            'value' => (float)$row['value']
        ];
    }
    
    echo json_encode($data);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>