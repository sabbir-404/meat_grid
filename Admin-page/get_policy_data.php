<?php
require_once "../Project-root/db_connect.php";

$out = ["success"=>true];

// Supply
$res = $conn->query("SELECT division, SUM(total_yield) as total_yield FROM policy_supply GROUP BY division");
$out["supply"] = $res->fetch_all(MYSQLI_ASSOC);

// Prices
$res = $conn->query("SELECT product_name, AVG(avg_price) as avg_price FROM policy_prices GROUP BY product_ID, product_name");
$out["prices"] = $res->fetch_all(MYSQLI_ASSOC);

// Consumption
$res = $conn->query("SELECT product_ID, SUM(feedback_count) as feedback_count, AVG(avg_score) as avg_score FROM policy_consumption GROUP BY product_ID");
$out["consumption"] = $res->fetch_all(MYSQLI_ASSOC);

// Alt Protein
$res = $conn->query("SELECT category, AVG(avg_price) as avg_price, AVG(avg_qty) as avg_qty FROM policy_alt_protein GROUP BY category");
$out["altProtein"] = $res->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($out);
