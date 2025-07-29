<?php
require_once "../Project-root/db_connect.php";
header('Content-Type: application/json');

// Read JSON
$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Validate
if (!isset($data['product_id'], $data['breed'], $data['quantity'], $data['avg_weight'])) {
    echo json_encode(['success' => false, 'error' => 'Missing fields']);
    exit;
}

// Prepare & execute
$stmt = $conn->prepare(
    "UPDATE livestock_entries
     SET breed = ?, quantity = ?, avg_weight = ?
     WHERE product_id = ?"
);
$stmt->bind_param(
    "sids",
    $data['breed'],
    $data['quantity'],
    $data['avg_weight'],
    $data['product_id']
);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
