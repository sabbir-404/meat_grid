<?php
// Slaughterhouse/delete_batch.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success'=>false,'error'=>'Method not allowed']);
    exit;
}

$batch_ID = $_POST['batch_ID'] ?? '';
$product_ID = $_POST['product_ID'] ?? null;
$remove_only = isset($_POST['remove_only']) && $_POST['remove_only'] == '1';

if ($batch_ID === '') {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Missing batch_ID']);
    exit;
}

if ($remove_only && $product_ID) {
    $stmt = $conn->prepare("DELETE FROM batch_quantity WHERE batch_ID = ? AND product_ID = ?");
    $stmt->bind_param("ss", $batch_ID, $product_ID);
    $ok = $stmt->execute();
    echo json_encode(['success'=>$ok,'deleted'=>$stmt->affected_rows]);
    $stmt->close();
    $conn->close();
    exit;
}

// delete whole batch
$stmt = $conn->prepare("DELETE FROM batch_quantity WHERE batch_ID = ?");
$stmt->bind_param("s", $batch_ID);
$ok = $stmt->execute();
echo json_encode(['success'=>$ok,'deleted'=>$stmt->affected_rows]);
$stmt->close();
$conn->close();

