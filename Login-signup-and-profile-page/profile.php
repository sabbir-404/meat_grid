<?php
session_start();
require_once "../Project-root/db_connect.php";

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login page.html");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT full_name, email, gender, address, contact, profile_pic FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Default dummy avatar if no profile picture
if (empty($user['profile_pic'])) {
    $user['profile_pic'] = "https://cdn-icons-png.flaticon.com/512/847/847969.png";
}

// Handle profile updates
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email   = $_POST['email'];
    $phone   = $_POST['phone'];
    $gender  = $_POST['gender'];
    $address = $_POST['address'];

    $profile_pic_path = $user['profile_pic'];

    // Handle profile picture upload
    if (!empty($_FILES['profile_pic']['name'])) {
        $uploadDir = "../uploads/profile_pics/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $newName = "user_" . $user_id . "_" . time() . "." . pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $uploadFile = $uploadDir . $newName;

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadFile)) {
            $profile_pic_path = $uploadFile;
        }
    }

    // Update user data
    $update = $conn->prepare("UPDATE users SET email=?, contact=?, gender=?, address=?, profile_pic=? WHERE id=?");
    $update->bind_param("sssssi", $email, $phone, $gender, $address, $profile_pic_path, $user_id);
    $update->execute();
    $update->close();

    echo "<script>alert('Profile updated successfully!'); window.location.href='profile.php';</script>";
    exit;
}

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
    body { background-color: #f4f6f9; }
    .profile-header { display: flex; align-items: center; gap: 30px; padding: 40px 60px 20px 60px; background-color: #fff; border-bottom: 1px solid #ddd; }
    .profile-img { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 4px solid #ddd; }
    .info-section { padding: 30px 60px; background-color: white; }
    .info-group { margin-bottom: 20px; }
    .hidden { display: none; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
  <div class="container-fluid d-flex justify-content-between align-items-center">
    <a href="../Home-page/front page.html" class="navbar-brand d-flex align-items-center">
      <i class="fas fa-arrow-left me-2"></i> Home
    </a>
    <a class="navbar-brand" href="#">
      <img src="../Logo/Logo white.png" alt="Meat Grid Logo" height="40" />
    </a>
    <a class="nav-link text-white" href="logout.php">Logout</a>
  </div>
</nav>

<!-- Profile Header -->
<div class="profile-header">
  <div class="profile-pic-container">
    <img id="profileImage" class="profile-img" src="<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile Picture">
  </div>
  <div class="user-info">
    <h3 id="nameView"><?= htmlspecialchars($user['full_name']) ?></h3>
    <p class="text-muted mb-1"><?= htmlspecialchars($user['email']) ?></p>
    <p class="text-muted"><?= htmlspecialchars($user['gender']) ?></p>
  </div>
</div>

<!-- Profile Info Section -->
<div class="info-section">
  <form method="POST" enctype="multipart/form-data">
    <div class="row">
      <div class="col-md-6 info-group">
        <label>Email</label>
        <p class="info-value"><?= htmlspecialchars($user['email']) ?></p>
        <input type="email" class="form-control hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
      </div>
      <div class="col-md-6 info-group">
        <label>Phone Number</label>
        <p class="info-value"><?= htmlspecialchars($user['contact']) ?></p>
        <input type="text" class="form-control hidden" name="phone" value="<?= htmlspecialchars($user['contact']) ?>">
      </div>
      <div class="col-md-6 info-group">
        <label>Gender</label>
        <p class="info-value"><?= htmlspecialchars($user['gender']) ?></p>
        <select class="form-select hidden" name="gender">
          <option <?= $user['gender']=="Male"?"selected":"" ?>>Male</option>
          <option <?= $user['gender']=="Female"?"selected":"" ?>>Female</option>
          <option <?= $user['gender']=="Other"?"selected":"" ?>>Other</option>
        </select>
      </div>
      <div class="col-md-6 info-group">
        <label>Address</label>
        <p class="info-value"><?= htmlspecialchars($user['address']) ?></p>
        <input type="text" class="form-control hidden" name="address" value="<?= htmlspecialchars($user['address']) ?>">
      </div>
      <div class="col-md-6 info-group hidden" id="picUploadDiv">
        <label>Change Profile Picture</label>
        <input type="file" name="profile_pic" class="form-control">
      </div>
    </div>
    <div class="text-center">
      <button type="button" id="editBtn" class="btn btn-primary" onclick="enableEdit()">Edit Profile</button>
      <button type="submit" id="saveBtn" class="btn btn-success hidden">Save Changes</button>
    </div>
  </form>
</div>

<script>
function enableEdit() {
  document.querySelectorAll(".info-value").forEach(el => el.classList.add("hidden"));
  document.querySelectorAll(".form-control, .form-select").forEach(el => el.classList.remove("hidden"));
  document.getElementById("picUploadDiv").classList.remove("hidden");
  document.getElementById("editBtn").classList.add("hidden");
  document.getElementById("saveBtn").classList.remove("hidden");
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
