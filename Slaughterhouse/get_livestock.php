<?php
header('Content-Type: application/json');

// DB connection
$mysqli = new mysqli("localhost", "root", "", "meat_grid");

if ($mysqli->connect_error) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Query livestock with farmer details
$sql = "
    SELECT 
        le.product_id,
        le.animal_type,
        le.breed,
        le.quantity,
        f.user_id,
        f.farm_type,
        f.name,
        f.gender,
        f.contact_info,
        f.email
    FROM livestock_entries le
    INNER JOIN farmer f ON le.user_id = f.user_id
    ORDER BY le.entry_date DESC
";

$result = $mysqli->query($sql);

if (!$result) {
    echo json_encode(["error" => "Query failed: " . $mysqli->error]);
    exit();
}

$rows = [];
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);
$mysqli->close();
?>

