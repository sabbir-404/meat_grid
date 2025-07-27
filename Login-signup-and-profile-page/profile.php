<?php
session_start();

// ✅ Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
header("Location: Login page.html");
exit();
}

// ✅ DB Connection
$conn = new mysqli("localhost", "root", "", "meat_grid");
if ($conn->connect_error) {
die("DB Connection Failed: " . $conn->connect_error);
}

// ✅ Fetch user info
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT full_name, email, username, gender, contact, address FROM users WHERE id = ?");
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
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>User Profile - Meat Grid</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
<style>
body { background-color: #f4f6f9; margin:0; }
.profile-header { display:flex; gap:30px; padding:30px; background:#fff; border-bottom:1px solid #ddd; }
.profile-img { width:150px; height:150px; border-radius:50%; object-fit:cover; border:4px solid #ddd; }
.info-section { background:white; padding:30px; }
.info-group { margin-bottom:20px; }
footer { background:#343a40; color:white; text-align:center; padding:1rem; margin-top:auto; }
</style>
</head>
<body>

<!-- ✅ Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
<div class="container-fluid d-flex justify-content-between align-items-center">
<a href="../Home-page/front page.html" class="navbar-brand d-flex align-items-center">
    <i class="fas fa-arrow-left me-2"></i> Back to Home
</a>
<a class="navbar-brand" href="#">
    <img src="../Logo/Logo white.png" alt="Meat Grid Logo" height="40" />
</a>
<a href="logout.php" class="btn btn-outline-light">Logout</a>
</div>
</nav>

<!-- ✅ Profile Header -->
<div class="profile-header">
<img id="profileImage" class="profile-img" src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="Profile Picture">
<div class="user-info">
<h3><?= htmlspecialchars($user['full_name']); ?></h3>
<p class="text-muted mb-1"><?= htmlspecialchars($user['email']); ?></p>
<p class="text-muted"><?= htmlspecialchars($user['gender']); ?></p>
</div>
</div>

<!-- ✅ Profile Info Section -->
<div class="info-section">
<div class="row">
<div class="col-md-6 info-group">
    <label>Email</label>
    <p class="info-value"><?= htmlspecialchars($user['email']); ?></p>
</div>
<div class="col-md-6 info-group">
    <label>Phone Number</label>
    <p class="info-value"><?= htmlspecialchars($user['contact']); ?></p>
</div>
<div class="col-md-6 info-group">
    <label>Gender</label>
    <p class="info-value"><?= htmlspecialchars($user['gender']); ?></p>
</div>
<div class="col-md-6 info-group">
    <label>Address</label>
    <p class="info-value"><?= htmlspecialchars($user['address']); ?></p>
</div>
<div class="col-md-6 info-group">
    <label>Username</label>
    <p class="info-value"><?= htmlspecialchars($user['username']); ?></p>
</div>
</div>

<!-- ✅ Edit button (optional future update) -->
<div class="text-center mt-3">
<a href="#" class="btn btn-primary">Edit Profile (coming soon)</a>
</div>
</div>

<footer>&copy; 2025 Meat Grid. All Rights Reserved.</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
