<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . "/../Project-root/db_connect.php";

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!$data) $data = $_POST;

$purchase_ID = $data['purchase_ID'] ?? null;
if (!$purchase_ID) { echo json_encode(['success'=>false,'message'=>'purchase_ID required']); exit; }

$stmt = $conn->prepare("DELETE FROM purchase WHERE purchase_ID = ?");
$stmt->bind_param("s", $purchase_ID);
if ($stmt->execute()) echo json_encode(['success'=>true,'message'=>'Deleted']);
else echo json_encode(['success'=>false,'message'=>$stmt->error]);

$stmt->close();
$conn->close();
