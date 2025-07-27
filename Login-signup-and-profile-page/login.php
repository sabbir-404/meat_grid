<?php
session_start();
include("../Project-root/db_connect.php"); // adjust path if needed

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Query the database
    $stmt = $conn->prepare("SELECT id, username, password, user_type FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // ✅ Login successful, store session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];

            // ✅ Redirect based on user_type
            switch ($user['user_type']) {
                case 'admin':
                    header("Location: ../Admin-page/admin-dashboard.html");
                    break;

                case 'farmer':
                    header("Location: ../Farmer/Dashboard.html");
                    break;

                case 'slaughterhouse':
                    header("Location: ../Slaughterhouse/Dashboard.html");
                    break;

                case 'wholesale':
                    header("Location: ../Wholesale/dashboard.html");
                    break;

                case 'retailer':
                    header("Location: ../Retailer/Rdashboard.html");
                    break;

                case 'policy_maker':
                    header("Location: ../Policy-Maker/Dashboard.html");
                    break;

                case 'health_authority':
                    header("Location: ../Health-Authority/Dashboard.html");
                    break;

                case 'researcher':
                    header("Location: ../Analyst/Dashboard.html");
                    break;

                case 'manager':
                    header("Location: ../Analyst/Dashboard.html");
                    break;

                default:
                    // If user_type not recognized, send to a default page
                    header("Location: ../Home-page/front page.html");
                    break;
            }
            exit;
        } else {
            echo "<h3 style='color:red;text-align:center;'>❌ Incorrect password!</h3>";
        }
    } else {
        echo "<h3 style='color:red;text-align:center;'>❌ User not found!</h3>";
    }
}
?>
