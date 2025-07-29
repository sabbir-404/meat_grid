<?php
session_start();
require_once "../Project-root/db_connect.php";
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo "[]"; exit;
}
$user = (int)$_SESSION['user_id'];
$stmt = $conn->prepare(
    "SELECT * FROM livestock_entries 
     WHERE user_id=? ORDER BY created_at DESC"
);
$stmt->bind_param("i",$user);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while($r = $res->fetch_assoc()) $out[] = $r;
echo json_encode($out);
