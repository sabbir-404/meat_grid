<?php
// ADMIN-PAGE/fetch_processed_batches.php
require_once "../Project-root/db_connect.php";
header('Content-Type: application/json');

// Accept GET params: sku (required), from (optional, yyyy-mm-dd), to (optional)
$sku  = isset($_GET['sku']) ? trim($_GET['sku']) : '';
$from = isset($_GET['from']) ? trim($_GET['from']) : '';
$to   = isset($_GET['to']) ? trim($_GET['to']) : '';

if ($sku === '') {
    // return empty array instead of error so frontend can fallback
    echo json_encode([]);
    exit;
}

// Build query with optional date filters
$sql = "SELECT batch_id, sku, facility_id, facility_type, region, processing_date, raw_input_kg, batch_yield_kg, loss_pct, units_produced, expiry_date, notes, created_at
        FROM processed_batch
        WHERE sku = ?";
$params = [$sku];
$types  = "s";

if ($from !== '' && $to !== '') {
    $sql .= " AND processing_date BETWEEN ? AND ?";
    $params[] = $from;
    $params[] = $to;
    $types .= "ss";
} elseif ($from !== '') {
    $sql .= " AND processing_date >= ?";
    $params[] = $from;
    $types .= "s";
} elseif ($to !== '') {
    $sql .= " AND processing_date <= ?";
    $params[] = $to;
    $types .= "s";
}

$sql .= " ORDER BY processing_date DESC, created_at DESC LIMIT 1000";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([]);
    exit;
}

// bind params dynamically
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($r = $res->fetch_assoc()) $out[] = $r;

echo json_encode($out);
