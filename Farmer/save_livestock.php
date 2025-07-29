<?php
session_start();
require_once "../Project-root/db_connect.php";

// For testing, replace with your real user session id
$user_id = $_SESSION['user_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animalType = $_POST['animalType'] ?? '';
    $breed = $_POST['breed'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $avgWeight = $_POST['avgWeight'] ?? 0;
    $feed = $_POST['feed'] ?? '';
    $health = $_POST['health'] ?? '';
    $entryDate = $_POST['entryDate'] ?? '';

    if (!$animalType || !$breed || !$quantity || !$avgWeight || !$feed || !$health || !$entryDate) {
        die('All fields are required.');
    }

    // Generate product_id like L001, L002...
    $result = $conn->query("SELECT MAX(CAST(SUBSTRING(product_id, 2) AS UNSIGNED)) AS max_id FROM livestock_entries");
    if (!$result) {
        die("Query failed: " . $conn->error);
    }
    $row = $result->fetch_assoc();
    $max = $row['max_id'] ?? 0;
    $newId = 'L' . str_pad($max + 1, 3, '0', STR_PAD_LEFT);

    $sql = "INSERT INTO livestock_entries 
            (product_id, user_id, animal_type, breed, quantity, avg_weight, feed_type, health_status, entry_date)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param(
        "sisidssss",
        $newId,
        $user_id,
        $animalType,
        $breed,
        $quantity,
        $avgWeight,
        $feed,
        $health,
        $entryDate
    );

    $success = $stmt->execute();
    if ($success) {
        header("Location: farmer-livestock.html?msg=success");
        exit;
    } else {
        die("Failed to save entry: " . $stmt->error);
    }
} else {
    die("Invalid request method.");
}
