<?php
// Admin-page/fetch_processed_price_history.php
require_once "../Project-root/db_connect.php";
header('Content-Type: application/json');

if (empty($_GET['sku'])) {
    echo json_encode([]);
    exit;
}

$sku = $conn->real_escape_string($_GET['sku']);
$from = isset($_GET['from']) ? $conn->real_escape_string($_GET['from']) : null;
$to   = isset($_GET['to']) ? $conn->real_escape_string($_GET['to']) : null;
$region = isset($_GET['region']) ? $conn->real_escape_string($_GET['region']) : null;

$sql = "SELECT * FROM processed_price_history WHERE sku = ?";
$params = [$sku];
$types = "s";

if ($from) { $sql .= " AND date_recorded >= ?"; $params[] = $from; $types .= "s"; }
if ($to)   { $sql .= " AND date_recorded <= ?"; $params[] = $to;   $types .= "s"; }
if ($region){ $sql .= " AND region = ?"; $params[] = $region; $types .= "s"; }

$sql .= " ORDER BY date_recorded ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($r = $res->fetch_assoc()) $out[] = $r;
echo json_encode($out);
