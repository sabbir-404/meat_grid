<?php
session_start();
require_once "../Project-root/db_connect.php"; // DB connection

// ✅ If already logged in, redirect to profile
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}

// ✅ Process login when form submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // ✅ Fetch user by username
    $stmt = $conn->prepare("SELECT id, username, password, user_type FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // ✅ Verify password
        if (password_verify($password, $row['password'])) {
            // ✅ Set session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_type'] = $row['user_type'];

            // ✅ Redirect based on user type
            switch ($row['user_type']) {
                case "farmer":
                    header("Location: ../Farmer/Dashboard.html");
                    break;
                case "retailer":
                    header("Location: ../Retailer/Rdashboard.html");
                    break;
                case "wholesale":
                    header("Location: ../Wholesale/dashboard.html");
                    break;
                case "slaughterhouse":
                    header("Location: ../Slaughterhouse/Dashboard.html");
                    break;
                case "policy_maker":
                    header("Location: ../Policy-Maker/Dashboard.html");
                    break;
                case "health_authority":
                    header("Location: ../Health-Authority/Dashboard.html");
                    break;
                case "manager":
                    header("Location: ../Admin-page/admin-dashboard.html");
                    break;
                default:
                    // If no dashboard → show profile page
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
