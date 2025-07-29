<?php
session_start();
require_once "../Project-root/db_connect.php";
header('Content-Type: application/json');

// ensure login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false,'error'=>'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success'=>false,'error'=>'Invalid JSON']);
    exit;
}

// validate
$req = ['animalType','breed','quantity','avgWeight','feed','health','entryDate'];
foreach($req as $f){
    if (empty($data[$f])) {
        echo json_encode(['success'=>false,'error'=>"Missing $f"]);
        exit;
    }
}

// generate product_id
$user = (int)$_SESSION['user_id'];
$res = $conn->query(
    "SELECT MAX(CAST(SUBSTRING(product_id,2) AS UNSIGNED)) AS m 
     FROM livestock_entries WHERE user_id=$user"
);
$row = $res->fetch_assoc();
$next = $row['m']? $row['m']+1:1;
$pid = 'L'.str_pad($next,3,'0',STR_PAD_LEFT);

// insert
$stmt = $conn->prepare(
    "INSERT INTO livestock_entries
     (product_id,user_id,animal_type,breed,quantity,avg_weight,feed_type,health_status,entry_date)
     VALUES(?,?,?,?,?,?,?,?,?)"
);
$stmt->bind_param(
    "sissidsss",
    $pid,$user,
    $data['animalType'],
    $data['breed'],
    $data['quantity'],
    $data['avgWeight'],
    $data['feed'],
    $data['health'],
    $data['entryDate']
);

if ($stmt->execute()) {
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false,'error'=>$stmt->error]);
}
