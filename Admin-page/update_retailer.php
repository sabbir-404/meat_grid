<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . "/../Project-root/db_connect.php";

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!$data) $data = $_POST;

$retailer_ID = isset($data['retailer_ID']) ? (int)$data['retailer_ID'] : null;
$name = $data['name'] ?? null;
$address = $data['address'] ?? null;

if (!$retailer_ID || !$name) {
    echo json_encode(['success'=>false,'message'=>'retailer_ID and name required']);
    exit;
}

$stmt = $conn->prepare("UPDATE retailer SET name=?, address=? WHERE retailer_ID=?");
$stmt->bind_param("ssi", $name, $address, $retailer_ID);
if ($stmt->execute()) echo json_encode(['success'=>true,'message'=>'Updated']);
else echo json_encode(['success'=>false,'message'=>$stmt->error]);
$stmt->close();
$conn->close();

