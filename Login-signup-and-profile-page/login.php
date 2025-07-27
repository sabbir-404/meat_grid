<?php
session_start();
ini_set('session.cookie_lifetime', 60*60*24*30);
ini_set('session.gc_maxlifetime', 60*60*24*30);
require_once "../Project-root/db_connect.php";

// ✅ If already logged in → redirect to profile
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // ✅ Fetch user from DB
    $stmt = $conn->prepare("SELECT id, username, password, user_type FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // ✅ Verify password
        if (password_verify($password, $row['password'])) {

            // ✅ Set session
            $_SESSION['user_id']   = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_type'] = strtolower(trim($row['user_type'])); // force lowercase

            // ✅ Match case-insensitive user type
            $userType = $_SESSION['user_type'];

            if ($userType === "farmer") {
                header("Location: ../Farmer/Dashboard.html");
            } elseif ($userType === "retailer") {
                header("Location: ../Retailer/Rdashboard.html");
            } elseif ($userType === "wholesale") {
                header("Location: ../Wholesale/dashboard.html");
            } elseif ($userType === "slaughterhouse") {
                header("Location: ../Slaughterhouse/Dashboard.html");
            } elseif ($userType === "policy_maker") {
                header("Location: ../Policy-Maker/Dashboard.html");
            } elseif ($userType === "health_authority") {
                header("Location: ../Health-Authority/Dashboard.html");
            } elseif ($userType === "manager") {
                header("Location: ../Admin-page/admin-dashboard.html");
            } else {
                // Unknown user type → show profile
                header("Location: profile.php");
            }
            exit;

        } else {
            $error = "❌ Invalid password!";
        }
    } else {
        $error = "❌ Username not found!";
    }

    $stmt->close();
}
$conn->close();
?>
