<?php
// signup.php

// 1. Database connection
$host = "localhost";
$user = "root";          // default XAMPP username
$pass = "";              // default XAMPP password is empty
$db   = "meat_grid";     // your DB name

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 2. Get POST data safely
$full_name  = $_POST['full_name'];
$email      = $_POST['email'];
$username   = $_POST['username'];
$password   = password_hash($_POST['password'], PASSWORD_DEFAULT); // hash password
$region     = $_POST['region'];
$address    = $_POST['address'];
$gender     = $_POST['gender'];
$contact    = $_POST['contact'];
$user_type  = $_POST['user_type'];

// 3. Prepare SQL insert statement
$stmt = $conn->prepare("INSERT INTO users 
(full_name, email, username, password, region, address, gender, contact, user_type) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

// Bind parameters in the same order
$stmt->bind_param("sssssssss", 
    $full_name, 
    $email, 
    $username, 
    $password, 
    $region, 
    $address, 
    $gender, 
    $contact, 
    $user_type
);

// 4. Execute and check result
if ($stmt->execute()) {
    echo "<script>alert('✅ Registration successful! You can now login.'); window.location.href='Login page.html';</script>";
} else {
    echo "❌ Error: " . $stmt->error;
}

// Close connections
$stmt->close();
$conn->close();
?>
