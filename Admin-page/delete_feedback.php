<?php
// Admin-page/delete_feedback.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) $data = $_POST;

$feedback_ID = $data['feedback_ID'] ?? null;
if (!$feedback_ID) {
    echo json_encode(['success' => false, 'message' => 'feedback_ID required']);
    exit;
}

// Decide bind type: integer if numeric, otherwise string
$bind_type = is_numeric($feedback_ID) ? 'i' : 's';

// prepare statement
$stmt = $conn->prepare("DELETE FROM feedback WHERE feedback_ID = ?");
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: '.$conn->error]);
    exit;
}

if ($bind_type === 'i') {
    $id_val = (int)$feedback_ID;
    $stmt->bind_param('i', $id_val);
} else {
    $stmt->bind_param('s', $feedback_ID);
}

if ($stmt->execute()) {
    // check affected rows
    if ($stmt->affected_rows > 0) echo json_encode(['success' => true]);
    else echo json_encode(['success' => false, 'message' => 'No row deleted']);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();

