<?php
require_once "../Project-root/db_connect.php";

header('Content-Type: application/json');

// Join feedback with consumer and processed_product
$sql = "
    SELECT
        f.feedback_ID,
        c.name AS consumer_name,
        p.name AS product_name,
        f.score,
        f.comment,
        f.submitted_date
    FROM feedback f
    LEFT JOIN consumer c ON f.consumer_ID = c.consumer_ID
    LEFT JOIN processed_product p ON f.product_ID = p.sku
    ORDER BY f.submitted_date DESC
";

$result = $conn->query($sql);

$feedbacks = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
    }
}

echo json_encode(["success" => true, "data" => $feedbacks]);

$conn->close();
?>



