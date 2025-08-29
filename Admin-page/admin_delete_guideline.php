<?php
// Admin-page/admin_delete_guideline.php
require_once __DIR__ . '/../Project-root/db_connect.php';
header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);
if ($input && isset($input['id'])) {
    $id = (int)$input['id'];
} else {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
}
if (!$id) { echo json_encode(['success'=>false,'message'=>'Missing id']); exit; }

try {
    $stmt = $conn->prepare("DELETE FROM health_guidelines WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo json_encode(['success'=>true]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
