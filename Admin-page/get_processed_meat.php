<?php
// Admin-page/get_processed_meat.php
require_once __DIR__ . '/../Project-root/db_connect.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $sql = "
      SELECT 
        pm.id,
        pm.meat_type,
        pm.breed,
        pm.weight,
        pm.processed_date,
        s.name AS slaughterhouse_name
      FROM processed_meat pm
      JOIN slaughterhouses s 
        ON pm.slaughterhouse_id = s.slaughterhouse_id
      ORDER BY pm.processed_date DESC
    ";

    $res = $conn->query($sql);
    $data = [];

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
    }

    echo json_encode(['success'=>true, 'processed_meat'=>$data], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['success'=>false, 'error'=>$e->getMessage()]);
}

