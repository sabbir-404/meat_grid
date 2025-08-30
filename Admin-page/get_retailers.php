<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . "/../Project-root/db_connect.php";

$sql = "SELECT retailer_ID, name, address FROM retailer ORDER BY retailer_ID DESC";
$res = $conn->query($sql);
if (!$res) {
    echo json_encode([]);
    exit;
}
$out = [];
while ($r = $res->fetch_assoc()) $out[] = $r;
echo json_encode($out);

