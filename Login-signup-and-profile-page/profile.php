<?php
session_start();
require_once "../Project-root/db_connect.php";  // database connection

// ✅ If user is NOT logged in, redirect to login
if (!isset($_SESSION['user_id'])) {
header("Location: Login page.html");
exit;
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch user info from database
$stmt = $conn->prepare("SELECT full_name, email, username, region, address, gender, contact, user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// ✅ Update user profile if form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
$full_name = $_POST['full_name'];
$email = $_POST['email'];
$region = $_POST['region'];
$address = $_POST['address'];
$gender = $_POST['gender'];
$contact = $_POST['contact'];

$update = $conn->prepare("UPDATE users SET full_name=?, email=?, region=?, address=?, gender=?, contact=? WHERE id=?");
$update->bind_param("ssssssi", $full_name, $email, $region, $address, $gender, $contact, $user_id);

if ($update->execute()) {
    echo "<script>alert('✅ Profile updated successfully!'); window.location.href='profile.php';</script>";
} else {
    echo "<script>alert('❌ Error updating profile');</script>";
}
$update->close();
}

// ✅ Re-fetch updated info
$stmt = $conn->prepare("SELECT full_name, email, username, region, address, gender, contact, user_type FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f4f6f9; }
.profile-container {
    max-width: 700px;
    margin: 50px auto;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}
footer {
    text-align: center;
    padding: 10px;
    background: #343a40;
    color: white;
    margin-top: 20px;
}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark px-4">
<a class="navbar-brand" href="#">Meat Grid</a>
<a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
</nav>

<div class="profile-container">
<h2 class="mb-4 text-center">My Profile</h2>

<!-- ✅ User Profile Form -->
<form method="POST">
<div class="mb-3">
    <label class="form-label">Full Name</label>
    <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required>
</div>

<div class="mb-3">
    <label class="form-label">Username (cannot change)</label>
    <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
</div>

<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
</div>

<div class="mb-3">
    <label class="form-label">Contact</label>
    <input type="text" class="form-control" name="contact" value="<?= htmlspecialchars($user['contact']) ?>" required>
</div>

<div class="mb-3">
    <label class="form-label">Region</label>
    <select class="form-select" name="region" required>
    <?php
    $regions = ["Dhaka","Chattogram","Rajshahi","Khulna","Barisal","Sylhet","Rangpur","Mymensingh"];
    foreach ($regions as $r) {
        $selected = ($user['region'] === $r) ? "selected" : "";
        echo "<option $selected>$r</option>";
    }
    ?>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">Address</label>
    <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($user['address']) ?>" required>
</div>

<div class="mb-3">
    <label class="form-label">Gender</label>
    <select class="form-select" name="gender" required>
    <option <?= $user['gender']=="Male" ? "selected" : "" ?>>Male</option>
    <option <?= $user['gender']=="Female" ? "selected" : "" ?>>Female</option>
    <option <?= $user['gender']=="Other" ? "selected" : "" ?>>Other</option>
    </select>
</div>

<div class="mb-3">
    <label class="form-label">User Type</label>
    <input type="text" class="form-control" value="<?= htmlspecialchars($user['user_type']) ?>" disabled>
</div>

<button type="submit" class="btn btn-success w-100">Save Changes</button>
</form>
</div>

<footer>
&copy; 2025 Meat Grid. All rights reserved.
</footer>

</body>
</html>
