<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';
$sku = $_GET['sku'] ?? '';
if (!$sku) { echo json_encode([]); exit; }

// Return ordered time series (date + retail_price)
$stmt = $conn->prepare("SELECT date_recorded AS date, retail_price FROM processed_price_history WHERE sku=? ORDER BY date_recorded ASC");
$stmt->bind_param("s",$sku);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($r=$res->fetch_assoc()) $out[]=$r;
echo json_encode($out);
$stmt->close(); $conn->close();
