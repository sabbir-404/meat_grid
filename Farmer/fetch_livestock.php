<?php
session_start();
require_once "../Project-root/db_connect.php";

$user_id = $_SESSION['user_id'] ?? 1;

$stmt = $conn->prepare("SELECT * FROM livestock_entries WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($data);
