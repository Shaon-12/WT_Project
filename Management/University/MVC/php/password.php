<?php
session_start();
include '../db/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password != $confirm_password) {
        header("Location: ../html/password.html?error=New passwords do not match");
        exit();
    } 

    // Fetch user
    $sql = "SELECT password FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    // Check plain text
    if ($current_password == $user['password']) {
        
        // Update to new password (plain text)
        $new_password = mysqli_real_escape_string($conn, $new_password);
        
        $update_sql = "UPDATE users SET password = '$new_password' WHERE id = '$user_id'";
        
        if (mysqli_query($conn, $update_sql)) {
            header("Location: ../html/password.html?success=Password updated");
        } else {
            header("Location: ../html/password.html?error=Error updating");
        }
    } else {
        header("Location: ../html/password.html?error=Wrong password");
    }

} else {
    header("Location: ../html/password.html");
}
?>
