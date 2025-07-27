<?php
// signup.php
require_once "../Project-root/db_connect.php";

// ✅ Process only on POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Get POST data safely
    $full_name  = trim($_POST['full_name']);
    $email      = trim($_POST['email']);
    $username   = trim($_POST['username']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $region     = trim($_POST['region']);
    $address    = trim($_POST['address']);
    $gender     = trim($_POST['gender']);
    $contact    = trim($_POST['contact']);
    $user_type  = strtolower(trim($_POST['user_type']));

    // ✅ Basic validation
    if (empty($full_name) || empty($email) || empty($username) || empty($_POST['password']) || empty($user_type)) {
        echo "<script>alert('❌ Please fill all required fields!'); window.history.back();</script>";
        exit;
    }

    // ✅ Check if username already exists
    $check = $conn->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo "<script>alert('❌ Username already exists! Please choose another.'); window.history.back();</script>";
        exit;
    }
    $check->close();

    // ✅ Insert into DB
    $stmt = $conn->prepare("
        INSERT INTO users (full_name, email, username, password, region, address, gender, contact, user_type) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssssss", $full_name, $email, $username, $password, $region, $address, $gender, $contact, $user_type);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Registration successful! You can now login.'); window.location.href='Login page.html';</script>";
    } else {
        echo "<script>alert('❌ Database error: " . $conn->error . "'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: Sign up.html"); // No direct access
    exit;
}
?>
