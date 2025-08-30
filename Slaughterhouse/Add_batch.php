<?php
// Slaughterhouse/add_batch.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../Project-root/db_connect.php';

function fail($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(['success'=>false,'error'=>$msg]);
    exit;
}

// only JSON POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Method not allowed', 405);
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) fail('Invalid JSON payload');

// required fields
$batch_ID = trim($data['batch_ID'] ?? '');
$production_date = $data['production_date'] ?? null;
$expiry_date = $data['expiry_date'] ?? null;
$meat_type = $data['meat_type'] ?? null;
$override_price = isset($data['price_per_kg']) && $data['price_per_kg'] !== '' ? (float)$data['price_per_kg'] : null;
$items = $data['items'] ?? [];

if ($batch_ID === '' || !$production_date || !is_array($items) || count($items) === 0) {
    fail('Missing batch_ID, production_date or items');
}

// ensure batch_quantity table has the optional columns price_per_kg and batch_price_total
$check = $conn->query("SHOW COLUMNS FROM `batch_quantity` LIKE 'price_per_kg'");
if ($check === false) fail('DB error when checking columns: ' . $conn->error, 500);
if ($check->num_rows === 0) {
    if (!$conn->query("ALTER TABLE `batch_quantity` ADD COLUMN `price_per_kg` DECIMAL(12,2) NULL AFTER `region`")) {
        fail('Failed to add column price_per_kg: ' . $conn->error, 500);
    }
}
$check2 = $conn->query("SHOW COLUMNS FROM `batch_quantity` LIKE 'batch_price_total'");
if ($check2 === false) fail('DB error when checking columns: ' . $conn->error, 500);
if ($check2->num_rows === 0) {
    if (!$conn->query("ALTER TABLE `batch_quantity` ADD COLUMN `batch_price_total` DECIMAL(14,2) NULL AFTER `price_per_kg`")) {
        fail('Failed to add column batch_price_total: ' . $conn->error, 500);
    }
}

// Build the base column list we will insert
$cols = [
    'batch_ID',         // s
    'product_ID',       // s
    'product_quantity', // i
    'production_date',  // s (DATE)
    'expiry_date',      // s (DATE) or NULL
    'raw_input_kg',     // d
    'batch_yield_kg',   // d
    'loss_pct',         // d
    'facility_id',      // s
    'region'            // s
];

// check whether the optional columns exist (they should after the ALTER above)
$has_price_col = (bool)$conn->query("SHOW COLUMNS FROM `batch_quantity` LIKE 'price_per_kg'")->num_rows;
$has_line_total_col = (bool)$conn->query("SHOW COLUMNS FROM `batch_quantity` LIKE 'batch_price_total'")->num_rows;
if ($has_price_col)  $cols[] = 'price_per_kg';       // d (nullable)
if ($has_line_total_col) $cols[] = 'batch_price_total'; // d (nullable)

// Build placeholder string and types mapping
$placeholders = implode(',', array_fill(0, count($cols), '?'));

// helper to build types and params for bind_param
function build_bind_arrays($cols, $values_row) {
    // map column => type
    $typesMap = [
        'batch_ID'=>'s','product_ID'=>'s','product_quantity'=>'i',
        'production_date'=>'s','expiry_date'=>'s','raw_input_kg'=>'d',
        'batch_yield_kg'=>'d','loss_pct'=>'d','facility_id'=>'s','region'=>'s',
        'price_per_kg'=>'d','batch_price_total'=>'d'
    ];
    $types = '';
    $params = [];
    foreach ($cols as $c) {
        $t = $typesMap[$c] ?? 's';
        $types .= $t;
        // Ensure every element is present in values_row (may be null)
        $val = array_key_exists($c, $values_row) ? $values_row[$c] : null;
        $params[] = $val;
    }
    return [$types, $params];
}

// Start transaction
$conn->begin_transaction();

$insert_sql = "INSERT INTO `batch_quantity` (" . implode(',', $cols) . ") VALUES ($placeholders)";
$stmt = $conn->prepare($insert_sql);
if (!$stmt) {
    $conn->rollback();
    fail('Prepare failed: ' . $conn->error, 500);
}

$inserted = 0;
$total_batch_price = 0.0;

// We'll use prepared SELECT to read product defaults from meat_product
$mpStmt = $conn->prepare("SELECT IFNULL(yield_weight,0) AS yield_weight, IFNULL(price_per_kg, NULL) AS price_per_kg FROM meat_product WHERE product_ID = ?");
if (!$mpStmt) {
    $conn->rollback();
    fail('Prepare failed (meat_product select): ' . $conn->error, 500);
}

foreach ($items as $it) {
    $product_ID = trim($it['product_ID'] ?? '');
    if ($product_ID === '') continue;

    // fetch default product info if needed
    $mpStmt->bind_param('s', $product_ID);
    if (!$mpStmt->execute()) {
        $mpStmt->close();
        $stmt->close();
        $conn->rollback();
        fail('DB error fetching product: ' . $mpStmt->error, 500);
    }
    $mpRes = $mpStmt->get_result();
    $mpRow = $mpRes->fetch_assoc() ?: ['yield_weight'=>0.0,'price_per_kg'=>null];

    $yield_weight = isset($it['yield_weight']) ? (float)$it['yield_weight'] : (float)$mpRow['yield_weight'];
    $product_price = isset($it['price_per_kg']) && $it['price_per_kg'] !== null ? (float)$it['price_per_kg'] : ($mpRow['price_per_kg'] !== null ? (float)$mpRow['price_per_kg'] : null);
    $use_price = $override_price !== null ? $override_price : $product_price;
    $line_total = ($use_price !== null) ? ($use_price * $yield_weight) : null;

    // prepare row values aligned to $cols
    $rowVals = [
        'batch_ID' => $batch_ID,
        'product_ID' => $product_ID,
        'product_quantity' => 1,
        'production_date' => $production_date,
        'expiry_date' => $expiry_date ?: null,
        'raw_input_kg' => $yield_weight,
        'batch_yield_kg' => $yield_weight,
        'loss_pct' => 0.0,
        'facility_id' => null,
        'region' => null
    ];
    if ($has_price_col) $rowVals['price_per_kg'] = $use_price;
    if ($has_line_total_col) $rowVals['batch_price_total'] = $line_total;

    // build types & params
    list($types, $params) = build_bind_arrays($cols, $rowVals);

    // mysqli::bind_param requires references
    $bind_names = [];
    $bind_names[] = $types;
    for ($i=0;$i<count($params);$i++){
        // convert null to null (will be bound)
        $bind_names[] = &$params[$i];
    }

    // call bind_param dynamically
    if (!call_user_func_array([$stmt, 'bind_param'], $bind_names)) {
        $mpStmt->close();
        $stmt->close();
        $conn->rollback();
        fail('bind_param failed: ' . $stmt->error, 500);
    }

    if (!$stmt->execute()) {
        $mpStmt->close();
        $stmt->close();
        $conn->rollback();
        fail('Insert failed: ' . $stmt->error, 500);
    }

    $inserted++;
    if ($line_total !== null) $total_batch_price += $line_total;
}

$mpStmt->close();
$stmt->close();

// commit
if (!$conn->commit()) {
    $conn->rollback();
    fail('Commit failed: ' . $conn->error, 500);
}

echo json_encode(['success'=>true,'inserted'=>$inserted,'total_price'=>round($total_batch_price,2)]);
$conn->close();

