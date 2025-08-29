<?php
// Admin-page/get_meat_summary.php
session_start();
require_once __DIR__ . '/../Project-root/db_connect.php';
header('Content-Type: application/json; charset=utf-8');

// if you want to enforce login:
// if (!isset($_SESSION['user_id'])) {
//     echo json_encode(['success'=>false,'error'=>'Not logged in']);
//     exit;
// }

try {
    // total meat production by type
    $sql = "SELECT meat_type, SUM(weight) AS total_weight
            FROM processed_meat
            GROUP BY meat_type";
    $res = $conn->query($sql);

    $summary = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $summary[] = [
                'meat_type' => $row['meat_type'],
                'total_weight' => (float)$row['total_weight']
            ];
        }
    }

    echo json_encode(['success'=>true, 'summary'=>$summary], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
}

