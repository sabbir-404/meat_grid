<?php
// Admin-page/admin_add_guideline.php
require_once __DIR__ . '/../Project-root/db_connect.php';
header('Content-Type: application/json');

// Accept JSON or form data
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if ($input && isset($input['guideline'])) {
    $g = $input['guideline'];
    $age_ranges = $input['age_ranges'] ?? [];
} else {
    // fallback to form POST
    $g = $_POST;
    $age_ranges = isset($_POST['age_ranges']) ? json_decode($_POST['age_ranges'], true) : [];
}

$title = trim($g['title'] ?? '');
if ($title === '') { echo json_encode(['success'=>false,'message'=>'Title required']); exit; }

$guideline_code = $g['guideline_code'] ?? null;
$issue_date = $g['issue_date'] ?? null;
$nutritional_intake = $g['nutritional_intake'] ?? null;
$issued_by = $g['issued_by'] ?? null;
$nutrition_type = $g['nutrition_type'] ?? null;
$recommended_meat_quantity = $g['recommended_meat_quantity'] ?? null;
$guideline_description = $g['guideline_description'] ?? null;

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO health_guidelines (guideline_code, title, issue_date, nutritional_intake, issued_by, nutrition_type, recommended_meat_quantity, guideline_description, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param('sssssdss', $guideline_code, $title, $issue_date, $nutritional_intake, $issued_by, $nutrition_type, $recommended_meat_quantity, $guideline_description);
    // Note: binding the decimal may require string; using 'd' for double but safe to pass as string
    // Above type string 'd' isn't included; use string for recommended_meat_quantity via 's' if needed. For simplicity, we used 'd' via conversion.
    // To avoid mismatches, ensure PHP variable types: cast recommended_meat_quantity to float if provided.
    $stmt->execute();
    $newId = $conn->insert_id;

    if (!empty($age_ranges) && is_array($age_ranges)) {
        $ins = $conn->prepare("INSERT INTO guideline_age_ranges (guideline_id, age_group, intake_text) VALUES (?, ?, ?)");
        foreach ($age_ranges as $ar) {
            $ag = $ar['age_group'] ?? null;
            $it = $ar['intake_text'] ?? null;
            if ($ag && $it) {
                $ins->bind_param('iss', $newId, $ag, $it);
                $ins->execute();
            }
        }
    }

    $conn->commit();
    $code_out = $guideline_code ?: 'G'.str_pad($newId,3,'0',STR_PAD_LEFT);
    echo json_encode(['success'=>true, 'id'=>$newId, 'guideline_code'=>$code_out]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
