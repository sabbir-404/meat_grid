<?php
session_start();
require_once "../Project-root/db_connect.php";

// ✅ Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login page.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch user data
$stmt = $conn->prepare("SELECT full_name, email, gender, address, contact, profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// ✅ Handle update on POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email   = $_POST['email'];
    $phone   = $_POST['phone'];
    $gender  = $_POST['gender'];
    $address = $_POST['address'];

    // ✅ Default keep old profile picture
    $profile_pic_path = $user['profile_pic'];

    // ✅ If new profile pic uploaded
    if (!empty($_FILES['profile_pic']['name'])) {
        $uploadDir = "../uploads/profile_pics/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $newName = "user_" . $user_id . "_" . time() . "." . pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $uploadFile = $uploadDir . $newName;

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadFile)) {
            $profile_pic_path = $uploadFile;
        }
    }

    // ✅ Update DB
    $update = $conn->prepare("UPDATE users SET email=?, contact=?, gender=?, address=?, profile_pic=? WHERE id=?");
    $update->bind_param("sssssi", $email, $phone, $gender, $address, $profile_pic_path, $user_id);
    $update->execute();
    $update->close();

    echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
    exit;
}

// ✅ Reload latest info
$stmt = $conn->prepare("SELECT full_name, email, gender, address, contact, profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

// ✅ Set dummy avatar if no pic
if (empty($user['profile_pic'])) {
    $user['profile_pic'] = $user['gender'] === "Female" 
        ? "https://cdn-icons-png.flaticon.com/512/847/847969.png"
        : "https://cdn-icons-png.flaticon.com/512/847/847969.png";
}
?>
