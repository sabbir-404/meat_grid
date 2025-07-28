<?php
session_start();
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);

require_once "../Project-root/db_connect.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, username, password, user_type FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_type'] = strtolower(trim($row['user_type']));

            $userType = $_SESSION['user_type'];
            switch ($userType) {
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
                    $error = "Unknown user type!";
            }
            exit;
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Username not found!";
    }
    $stmt->close();
}
$conn->close();

// Show error as alert
if (!empty($error)) {
    echo "<script>alert('". addslashes($error) ."'); window.history.back();</script>";
    exit;
}
?>
