<?php
// ADMIN-PAGE/admin_save_processed_product.php
session_start();
require_once "../Project-root/db_connect.php";
header('Content-Type: application/json; charset=utf-8');

// require login (you can add role-check if needed)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Not logged in']);
    exit;
}

// read JSON body (fall back to form-data)
$payload = json_decode(file_get_contents('php://input'), true);
if (!$payload) $payload = $_POST ?? null;
if (!$payload || !is_array($payload)) {
    echo json_encode(['success'=>false,'error'=>'Invalid JSON or POST data']);
    exit;
}

// validate minimal required
if (empty($payload['name'])) {
    echo json_encode(['success'=>false,'error'=>'Missing name']);
    exit;
}

// normalize / optional fields
$sku = trim($payload['sku'] ?? '');
$name = trim($payload['name'] ?? '');
$category = trim($payload['category'] ?? null);
$base_meat = trim($payload['base_meat'] ?? null);
$breed = trim($payload['breed'] ?? null);
$avg_slaughter_weight = isset($payload['avg_slaughter_weight']) && $payload['avg_slaughter_weight'] !== '' ? (float)$payload['avg_slaughter_weight'] : null;
$fcr = isset($payload['fcr']) && $payload['fcr'] !== '' ? (float)$payload['fcr'] : null;
$rearing_days = isset($payload['rearing_days']) && $payload['rearing_days'] !== '' ? (int)$payload['rearing_days'] : null;
$packaging = trim($payload['packaging'] ?? null);
$avg_unit_weight_g = isset($payload['avg_unit_weight_g']) && $payload['avg_unit_weight_g'] !== '' ? (float)$payload['avg_unit_weight_g'] : null;
$shelf_life_days = isset($payload['shelf_life_days']) && $payload['shelf_life_days'] !== '' ? (int)$payload['shelf_life_days'] : null;
$cold_chain_required = (isset($payload['cold_chain_required']) && ($payload['cold_chain_required']==1 || $payload['cold_chain_required']==='1' || $payload['cold_chain_required']===true)) ? 1 : 0;
$price_per_kg = isset($payload['price_per_kg']) && $payload['price_per_kg'] !== '' ? (float)$payload['price_per_kg'] : null;
$protein_per_100g = isset($payload['protein_per_100g']) && $payload['protein_per_100g'] !== '' ? (float)$payload['protein_per_100g'] : null;
$fat_per_100g = isset($payload['fat_per_100g']) && $payload['fat_per_100g'] !== '' ? (float)$payload['fat_per_100g'] : null;
$notes = trim($payload['notes'] ?? null);

// helper to generate a unique SKU
function make_unique_sku($conn) {
    for ($i=0;$i<6;$i++){
        $cand = 'PP'.strtoupper(substr(md5(uniqid('', true)), 0, 8));
        $chk = $conn->prepare("SELECT sku FROM processed_product WHERE sku = ?");
        if (!$chk) continue;
        $chk->bind_param("s",$cand);
        $chk->execute();
        $res = $chk->get_result();
        if (!$res->fetch_assoc()) {
            return $cand;
        }
    }
    // fallback longer uniqid
    return 'PP'.strtoupper(uniqid());
}

// if sku empty -> create new SKU
$creating = false;
if ($sku === '') {
    $creating = true;
    $sku = make_unique_sku($conn);
} else {
    // sanitize provided sku
    $sku = $conn->real_escape_string($sku);
    // check if sku exists
    $chk = $conn->prepare("SELECT sku FROM processed_product WHERE sku = ?");
    if ($chk) {
        $chk->bind_param("s",$sku);
        $chk->execute();
        $res = $chk->get_result();
        $exists = (bool)$res->fetch_assoc();
        $creating = !$exists;
    } else {
        echo json_encode(['success'=>false,'error'=>'DB prepare failed: '.$conn->error]);
        exit;
    }
}

// Use prepared statements. For simplicity we bind all as strings (MySQL will coerce numeric strings).
if ($creating) {
    $sql = "INSERT INTO processed_product
      (sku,name,category,base_meat,breed,avg_slaughter_weight,fcr,rearing_days,packaging,avg_unit_weight_g,shelf_life_days,cold_chain_required,price_per_kg,protein_per_100g,fat_per_100g,notes)
      VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo json_encode(['success'=>false,'error'=>'Prepare failed: '.$conn->error]); exit; }
    $types = str_repeat('s', 16);
    $vals = [
        $sku, $name, $category, $base_meat, $breed,
        $avg_slaughter_weight === null ? null : (string)$avg_slaughter_weight,
        $fcr === null ? null : (string)$fcr,
        $rearing_days === null ? null : (string)$rearing_days,
        $packaging,
        $avg_unit_weight_g === null ? null : (string)$avg_unit_weight_g,
        $shelf_life_days === null ? null : (string)$shelf_life_days,
        (string)$cold_chain_required,
        $price_per_kg === null ? null : (string)$price_per_kg,
        $protein_per_100g === null ? null : (string)$protein_per_100g,
        $fat_per_100g === null ? null : (string)$fat_per_100g,
        $notes
    ];
    // bind params dynamically
    $stmt->bind_param($types, ...$vals);
    if ($stmt->execute()) {
        echo json_encode(['success'=>true,'sku'=>$sku,'action'=>'insert']);
        exit;
    } else {
        echo json_encode(['success'=>false,'error'=>'DB error: '.$stmt->error]);
        exit;
    }
} else {
    // update existing SKU
    $sql = "UPDATE processed_product SET
      name=?, category=?, base_meat=?, breed=?, avg_slaughter_weight=?, fcr=?, rearing_days=?, packaging=?, avg_unit_weight_g=?, shelf_life_days=?, cold_chain_required=?, price_per_kg=?, protein_per_100g=?, fat_per_100g=?, notes=?
      WHERE sku = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) { echo json_encode(['success'=>false,'error'=>'Prepare failed: '.$conn->error]); exit; }
    $types = str_repeat('s', 16); // 15 fields + sku = 16
    $vals = [
        $name, $category, $base_meat, $breed,
        $avg_slaughter_weight === null ? null : (string)$avg_slaughter_weight,
        $fcr === null ? null : (string)$fcr,
        $rearing_days === null ? null : (string)$rearing_days,
        $packaging,
        $avg_unit_weight_g === null ? null : (string)$avg_unit_weight_g,
        $shelf_life_days === null ? null : (string)$shelf_life_days,
        (string)$cold_chain_required,
        $price_per_kg === null ? null : (string)$price_per_kg,
        $protein_per_100g === null ? null : (string)$protein_per_100g,
        $fat_per_100g === null ? null : (string)$fat_per_100g,
        $notes,
        $sku
    ];
    $stmt->bind_param($types, ...$vals);
    if ($stmt->execute()) {
        if ($stmt->affected_rows >= 0) {
            echo json_encode(['success'=>true,'sku'=>$sku,'action'=>'update']);
            exit;
        } else {
            echo json_encode(['success'=>false,'error'=>'No rows updated']);
            exit;
        }
    } else {
        echo json_encode(['success'=>false,'error'=>'DB error: '.$stmt->error]);
        exit;
    }
}
