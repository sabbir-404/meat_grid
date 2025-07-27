<?php
session_start();

// ✅ Include database connection
require_once "../Project-root/db_connect.php";  // adjust path if needed

// ✅ Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // ✅ Prepare query
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // ✅ Verify password
        if (password_verify($password, $row['password'])) {
            
            // ✅ Store session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_type'] = $row['user_type'];

            // ✅ Redirect based on user_type
            switch ($row['user_type']) {
                case 'farmer':
                    header("Location: ../Farmer/Dashboard.html");
                    break;
                case 'retailer':
                    header("Location: ../Retailer/Rdashboard.html");
                    break;
                case 'slaughterhouse':
                    header("Location: ../Slaughterhouse/Dashboard.html");
                    break;
                case 'wholesale':
                    header("Location: ../Wholesale/dashboard.html");
                    break;
                case 'policy_maker':
                    header("Location: ../Policy-Maker/Dashboard.html");
                    break;
                case 'health_authority':
                    header("Location: ../Health-Authority/Dashboard.html");
                    break;
                case 'manager':
                    header("Location: ../Admin-page/admin-dashboard.html");
                    break;
                case 'researcher':
                    header("Location: ../Analyst/Dashboard.html");
                    break;
                default:
                    // If unknown type, go to profile
                    header("Location: profile.php");
            }
            exit;
        } else {
            echo "<script>alert('❌ Incorrect password'); window.history.back();</script>";
        }

    } else {
        echo "<script>alert('❌ User not found'); window.history.back();</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('Invalid request!'); window.location.href='Login page.html';</script>";
}
?>
