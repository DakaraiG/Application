<?php
header('Content-Type: application/json');
require 'config.php';

$chart = $_GET['chart'] ?? '';
$type = $_GET['type'] ?? '';

if ($chart === 'category' && ($type === 'income' || $type === 'expense')) {
    // Get sum of amount grouped by category, filtered by type
    $stmt = $conn->prepare("SELECT category AS label, SUM(amount) AS value FROM transactions WHERE type = ? GROUP BY category");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data);

    $stmt->close();
} else {
    echo json_encode(["error" => "Invalid parameters"]);
}

$conn->close();
?>