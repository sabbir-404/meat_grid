<?php
include("../db_connect.php");

$user_id = intval($_GET['user_id'] ?? 0);

$sql = "SELECT user_id, name, gender, contact, email FROM farmer WHERE user_id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["error" => "Farmer not found"]);
}
?>
