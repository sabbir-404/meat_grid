<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';
$id = $_GET['wholesale_ID'] ?? null;
if (!$id) { echo json_encode([]); exit; }
$stmt = $conn->prepare("SELECT wholesale_ID, name, address, stock_capacity FROM wholesale WHERE wholesale_ID = ?");
$stmt->bind_param("s",$id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc() ?: [];
echo json_encode($row);
$stmt->close(); $conn->close();
