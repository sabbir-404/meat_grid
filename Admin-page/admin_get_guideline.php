<?php
// Admin-page/admin_get_guideline.php
require_once __DIR__ . '/../Project-root/db_connect.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { echo json_encode(['success'=>false,'message'=>'Missing id']); exit; }

$sql = "SELECT * FROM health_guidelines WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$g = $res->fetch_assoc();
if (!$g) { echo json_encode(['success'=>false,'message'=>'Not found']); exit; }

$ageSql = "SELECT age_group, intake_text FROM guideline_age_ranges WHERE guideline_id = ? ORDER BY id ASC";
$st2 = $conn->prepare($ageSql);
$st2->bind_param('i', $id);
$st2->execute();
$r2 = $st2->get_result();
$age_ranges = [];
while ($a = $r2->fetch_assoc()) $age_ranges[] = $a;

$g['age_ranges'] = $age_ranges;
echo json_encode(['success'=>true,'guideline'=>$g]);
