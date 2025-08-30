<?php
require_once "../Project-root/db_connect.php";

header("Content-Type: application/json");

// Average score per product
$avg_sql = "
    SELECT p.name AS product_name, ROUND(AVG(f.score),2) AS avg_score
    FROM feedback f
    JOIN processed_product p ON f.product_ID = p.sku
    GROUP BY f.product_ID
    ORDER BY avg_score DESC
";
$avg_res = $conn->query($avg_sql);
$avg_scores = [];
if ($avg_res) {
    while ($row = $avg_res->fetch_assoc()) $avg_scores[] = $row;
}

// Score distribution (1–5 counts)
$dist_sql = "
    SELECT score, COUNT(*) AS count
    FROM feedback
    GROUP BY score
    ORDER BY score
";
$dist_res = $conn->query($dist_sql);
$score_dist = [];
if ($dist_res) {
    while ($row = $dist_res->fetch_assoc()) $score_dist[] = $row;
}

echo json_encode([
    "success" => true,
    "avg_scores" => $avg_scores,
    "score_dist" => $score_dist
]);
?>

