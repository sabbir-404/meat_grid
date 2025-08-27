<?php
require_once "../Project-root/db_connect.php";
header('Content-Type: application/json');

// get JSON input
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success'=>false,'error'=>'Invalid JSON']);
    exit;
}

// required fields
$req = ['user_id','animal_type','breed','quantity','avg_weight','feed_type','health_status','entry_date','slaughter_weight','fcr','rearing_days'];
foreach($req as $f){
    if (!isset($data[$f])) {
        echo json_encode(['success'=>false,'error'=>"Missing $f"]);
        exit;
    }
}

// normalize fields
$product_id = isset($data['product_id']) ? $data['product_id'] : '';
$user = (int)$data['user_id'];
$animalType = $data['animal_type'];
$breed = $data['breed'];
$quantity = (int)$data['quantity'];
$avgWeight = (float)$data['avg_weight'];
$feed = $data['feed_type'];
$health = $data['health_status'];
$entryDate = $data['entry_date'];
$slaughterWeight = (float)$data['slaughter_weight'];
$fcr = (float)$data['fcr'];
$rearingDays = (int)$data['rearing_days'];

if ($product_id === '') {
    // INSERT
    $res = $conn->query(
        "SELECT MAX(CAST(SUBSTRING(product_id,2) AS UNSIGNED)) AS m 
         FROM livestock_entries"
    );
    $row = $res->fetch_assoc();
    $next = $row['m'] ? $row['m'] + 1 : 1;
    $product_id = 'L'.str_pad($next,3,'0',STR_PAD_LEFT);

    $stmt = $conn->prepare(
        "INSERT INTO livestock_entries
        (product_id,user_id,animal_type,breed,quantity,avg_weight,feed_type,health_status,entry_date,slaughter_weight,fcr,rearing_days)
        VALUES(?,?,?,?,?,?,?,?,?,?,?,?)"
    );
    $stmt->bind_param(
        "sissidsssddi",
        $product_id,
        $user,
        $animalType,
        $breed,
        $quantity,
        $avgWeight,
        $feed,
        $health,
        $entryDate,
        $slaughterWeight,
        $fcr,
        $rearingDays
    );

    if ($stmt->execute()) {
        echo json_encode(['success'=>true,'action'=>'insert','product_id'=>$product_id]);
    } else {
        echo json_encode(['success'=>false,'error'=>$stmt->error]);
    }
} else {
    // UPDATE
    $stmt = $conn->prepare(
        "UPDATE livestock_entries SET
            user_id=?, animal_type=?, breed=?, quantity=?, avg_weight=?, feed_type=?, health_status=?, entry_date=?, slaughter_weight=?, fcr=?, rearing_days=?
         WHERE product_id=?"
    );
    $stmt->bind_param(
        "issidsssddis",
        $user,
        $animalType,
        $breed,
        $quantity,
        $avgWeight,
        $feed,
        $health,
        $entryDate,
        $slaughterWeight,
        $fcr,
        $rearingDays,
        $product_id
    );

    if ($stmt->execute()) {
        echo json_encode(['success'=>true,'action'=>'update','product_id'=>$product_id,'rows_affected'=>$stmt->affected_rows]);
    } else {
        echo json_encode(['success'=>false,'error'=>$stmt->error]);
<<<<<<< Updated upstream
    }
}
=======
    }
}
>>>>>>> Stashed changes
