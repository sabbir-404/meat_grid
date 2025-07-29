<?php
// fetch_admin_livestock.php
require_once "../Project-root/db_connect.php";
header('Content-Type: application/json');

if (isset($_GET['product_id'])) {
$pid = $conn->real_escape_string($_GET['product_id']);
$stmt = $conn->prepare(
    "SELECT le.*, u.username, u.user_type 
    FROM livestock_entries le 
    JOIN users u ON le.user_id=u.id 
    WHERE le.product_id=?"
);
$stmt->bind_param("s",$pid);
} else {
$stmt = $conn->prepare(
    "SELECT le.*, u.username, u.user_type 
    FROM livestock_entries le 
    JOIN users u ON le.user_id=u.id"
);
}
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while($r = $res->fetch_assoc()) $out[] = $r;
echo json_encode($out);
