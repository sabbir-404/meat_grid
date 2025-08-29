<?php
// Admin-page/admin_fetch_guidelines.php
require_once __DIR__ . '/../Project-root/db_connect.php';
header('Content-Type: application/json');

$sql = "SELECT id, guideline_code, title, issue_date, nutritional_intake, issued_by FROM health_guidelines ORDER BY issue_date DESC, id DESC";
if ($res = $conn->query($sql)) {
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    echo json_encode(['success'=>true, 'rows'=>$rows]);
} else {
    echo json_encode(['success'=>false, 'message'=>$conn->error]);
}
