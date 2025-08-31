<?php
require_once "../Project-root/db_connect.php";

$table = $_GET['table'] ?? '';
$id = intval($_GET['id'] ?? 0);
$allowed = ['policy_supply','policy_prices','policy_consumption','policy_alt_protein'];

if($id > 0 && in_array($table, $allowed)){
    $conn->query("DELETE FROM $table WHERE id=$id");
}
header("Location: admin-policy.php");
exit;
