<?php
// fetch_admin_livestock.php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../Project-root/db_connect.php'; // adjust if your path differs

try {
    // If product_id is provided, return single entry
    if (isset($_GET['product_id']) && $_GET['product_id'] !== '') {
        $pid = $conn->real_escape_string($_GET['product_id']);
        $stmt = $conn->prepare("SELECT * FROM livestock_entries WHERE product_id = ?");
        if (!$stmt) throw new Exception($conn->error);
        $stmt->bind_param("s", $pid);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        // return all, most recent first
        $res = $conn->query("SELECT * FROM livestock_entries ORDER BY entry_date DESC, created_at DESC");
        if (!$res) throw new Exception($conn->error);
        $rows = $res->fetch_all(MYSQLI_ASSOC);
    }

    echo json_encode($rows);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error', 'message' => $e->getMessage()]);
}


