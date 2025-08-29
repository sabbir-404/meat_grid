<?php
// Admin-page/admin_edit_guideline.php
require_once __DIR__ . '/../Project-root/db_connect.php';
header('Content-Type: application/json');

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if ($input && isset($input['id']) && isset($input['guideline'])) {
    $id = (int)$input['id'];
    $g = $input['guideline'];
    $age_ranges = $input['age_ranges'] ?? null; // if null -> do not change, if array -> replace
} else {
    // fallback to form
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $g = $_POST;
    $age_ranges = isset($_POST['age_ranges']) ? json_decode($_POST['age_ranges'], true) : null;
}

if (!$id) { echo json_encode(['success'=>false,'message'=>'Missing id']); exit; }
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
    $stmt = $conn->prepare("UPDATE health_guidelines SET guideline_code = ?, title = ?, issue_date = ?, nutritional_intake = ?, issued_by = ?, nutrition_type = ?, recommended_meat_quantity = ?, guideline_description = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param('sssssdssi', $guideline_code, $title, $issue_date, $nutritional_intake, $issued_by, $nutrition_type, $recommended_meat_quantity, $guideline_description, $id);
    $stmt->execute();

    if (is_array($age_ranges)) {
        // Delete old ranges and re-insert
        $del = $conn->prepare("DELETE FROM guideline_age_ranges WHERE guideline_id = ?");
        $del->bind_param('i', $id);
        $del->execute();

        $ins = $conn->prepare("INSERT INTO guideline_age_ranges (guideline_id, age_group, intake_text) VALUES (?, ?, ?)");
        foreach ($age_ranges as $ar) {
            $ag = $ar['age_group'] ?? null;
            $it = $ar['intake_text'] ?? null;
            if ($ag && $it) {
                $ins->bind_param('iss', $id, $ag, $it);
                $ins->execute();
            }
        }
    }

    $conn->commit();
    $code_out = $guideline_code ?: 'G'.str_pad($id,3,'0',STR_PAD_LEFT);
    echo json_encode(['success'=>true, 'guideline_code'=>$code_out]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
