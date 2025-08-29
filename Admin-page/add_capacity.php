<?php
// add_capacity.php
session_start();
require_once __DIR__ . '/../Project-root/db_connect.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'error'=>'Not logged in']);
    exit;
}

// Accept form-data or JSON body
$input = $_POST;
if (empty($input)) {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (is_array($json)) $input = $json;
}

$slaughterhouse_ID = $input['slaughterhouse_ID'] ?? null;
$name = isset($input['name']) ? trim($input['name']) : null;
$slaughter_rate = isset($input['slaughter_rate']) && $input['slaughter_rate'] !== '' ? (int)$input['slaughter_rate'] : null;
$processing_capacity = isset($input['processing_capacity']) && $input['processing_capacity'] !== '' ? (int)$input['processing_capacity'] : null;
$average_weight = isset($input['average_weight']) && $input['average_weight'] !== '' ? (float)$input['average_weight'] : null;

if (!$name) {
    http_response_code(400);
    echo json_encode(['success'=>false,'error'=>'Missing required field: name']);
    exit;
}

// If ID provided and exists -> update
if ($slaughterhouse_ID) {
    $chk = $conn->prepare("SELECT COUNT(*) AS cnt FROM slaughterhouse WHERE slaughterhouse_ID = ?");
    if (!$chk) { http_response_code(500); echo json_encode(['success'=>false,'error'=>$conn->error]); exit; }
    $chk->bind_param('s', $slaughterhouse_ID);
    $chk->execute();
    $r = $chk->get_result()->fetch_assoc();
    $chk->close();
    if ($r && $r['cnt'] > 0) {
        // update
        $stmt = $conn->prepare("UPDATE slaughterhouse SET name = ?, slaughter_rate = ?, processing_capacity = ?, average_weight = ? WHERE slaughterhouse_ID = ?");
        if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>$conn->error]); exit; }
        // Use 0 if null to avoid binding problems; change if you prefer SQL NULL
        $sr = $slaughter_rate ?? 0;
        $pc = $processing_capacity ?? 0;
        $aw = $average_weight ?? 0;
        $stmt->bind_param('sddds', $name, $sr, $pc, $aw, $slaughterhouse_ID);
        if ($stmt->execute()) {
            echo json_encode(['success'=>true, 'action'=>'updated']);
        } else {
            http_response_code(500);
            echo json_encode(['success'=>false,'error'=>$stmt->error]);
        }
        $stmt->close();
        $conn->close();
        exit;
    }
}

// Insert new
$new_id = $slaughterhouse_ID ?: 'SH' . uniqid();
$stmt = $conn->prepare("INSERT INTO slaughterhouse (slaughterhouse_ID, name, slaughter_rate, processing_capacity, average_weight) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'error'=>$conn->error]); exit; }
$sr = $slaughter_rate ?? 0;
$pc = $processing_capacity ?? 0;
$aw = $average_weight ?? 0;
$stmt->bind_param('ssdds', $new_id, $name, $sr, $pc, $aw);
if ($stmt->execute()) {
    echo json_encode(['success'=>true, 'slaughterhouse_ID'=>$new_id]);
} else {
    http_response_code(500);
    echo json_encode(['success'=>false,'error'=>$stmt->error]);
}
$stmt->close();
$conn->close();

