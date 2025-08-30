<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . "/../Project-root/db_connect.php";

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!$data) $data = $_POST;

$retailer_ID = isset($data['retailer_ID']) ? (int)$data['retailer_ID'] : null;
if (!$retailer_ID) { echo json_encode(['success'=>false,'message'=>'retailer_ID required']); exit; }

$stmt = $conn->prepare("DELETE FROM retailer WHERE retailer_ID=?");
$stmt->bind_param("i", $retailer_ID);
if ($stmt->execute()) echo json_encode(['success'=>true,'message'=>'Deleted']);
else echo json_encode(['success'=>false,'message'=>$stmt->error]);

$stmt->close();
$conn->close();
