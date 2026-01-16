<?php
session_start();
include '../db/database.php';

// Reset variables
$email = "";
$password = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Basic Validation
    if (empty($email)) {
        header("Location: ../html/login.html?error=Email is required");
        exit();
    }
    if (empty($password)) {
        header("Location: ../html/login.html?error=Password is required");
        exit();
    }

    // Security: Prevent SQL Injection for beginners
    $email = mysqli_real_escape_string($conn, $email);
    // Note: Password we check after fetching, but good habit to escape inputs used in queries
    
    // Check user in database
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    // If a user is found
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        
        // Simple password check (Equality)
        if ($password == $row['password']) {
            // Login Success
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            // Redirect based on role
            if ($row['role'] == 'university') {
                header("Location: ../html/dashboard.html");
            } else {
                header("Location: ../html/student_dashboard.html");
            }
            exit();
        } else {
            header("Location: ../html/login.html?error=Invalid Password");
            exit();
        }
    } else {
        header("Location: ../html/login.html?error=Email not found");
        exit();
    }
} else {
    // If someone tries to open this file directly
    header("Location: ../html/login.html");
    exit();
}
$conn->close();
?>
