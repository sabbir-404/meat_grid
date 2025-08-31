<?php
// get_policy_data.php
header('Content-Type: application/json');
require_once "../Project-root/db_connect.php"; // adjust path if needed

$response = [
    "success" => false,
    "supply" => [],
    "prices" => [],
    "consumption" => [],
    "altProtein" => []
];

try {
    // 1) Supply by division
    $sql = "SELECT division, SUM(total_yield) AS total_yield
            FROM policy_supply
            GROUP BY division";
    $stmt = $conn->query($sql);
    $response['supply'] = $stmt->fetch_all(MYSQLI_ASSOC);

    // 2) Average prices by product
    $sql = "SELECT product_ID, product_name, ROUND(AVG(avg_price),2) AS avg_price
            FROM policy_prices
            GROUP BY product_ID, product_name";
    $stmt = $conn->query($sql);
    $response['prices'] = $stmt->fetch_all(MYSQLI_ASSOC);

    // 3) Consumption/feedback proxy
    $sql = "SELECT product_ID, SUM(feedback_count) AS feedback_count, 
                   ROUND(AVG(avg_score),2) AS avg_score
            FROM policy_consumption
            GROUP BY product_ID";
    $stmt = $conn->query($sql);
    $response['consumption'] = $stmt->fetch_all(MYSQLI_ASSOC);

    // 4) Alternative proteins (avg price & qty)
    $sql = "SELECT category, ROUND(AVG(avg_price),2) AS avg_price,
                   ROUND(AVG(avg_qty),2) AS avg_qty
            FROM policy_alt_protein
            GROUP BY category";
    $stmt = $conn->query($sql);
    $response['altProtein'] = $stmt->fetch_all(MYSQLI_ASSOC);

    $response['success'] = true;

} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
