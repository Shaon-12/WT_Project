<?php
session_start();
include '../db/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get form inputs
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    // University inputs (optional)
    $uni_name = "";
    $uni_rank = 0;
    $uni_country = "";
    
    if (isset($_POST['university_name'])) {
        $uni_name = $_POST['university_name'];
    }
    if (isset($_POST['university_ranking'])) {
        $uni_rank = $_POST['university_ranking'];
    }
    if (isset($_POST['university_country'])) {
        $uni_country = $_POST['university_country'];
    }

    // Basic Validation
    if ($password != $confirm_password) {
        header("Location: ../html/register.html?error=Passwords do not match");
        exit();
    }
    
    if (strlen($password) < 6) {
        header("Location: ../html/register.html?error=Password must be 6 chars");
        exit();
    }

    // Protect inputs
    $username = mysqli_real_escape_string($conn, $username);
    $email = mysqli_real_escape_string($conn, $email);
    $role = mysqli_real_escape_string($conn, $role);
    $uni_name = mysqli_real_escape_string($conn, $uni_name);
    $uni_country = mysqli_real_escape_string($conn, $uni_country);

    // Check if email already exists
    $check_query = "SELECT * FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        header("Location: ../html/register.html?error=Email already exists");
        exit();
    }

    // Insert new user
    // We store password as PLAIN TEXT as requested (basic code)
    $sql = "INSERT INTO users (username, email, password, role, university_name, university_ranking, university_country) 
            VALUES ('$username', '$email', '$password', '$role', '$uni_name', '$uni_rank', '$uni_country')";

    if (mysqli_query($conn, $sql)) {
        header("Location: ../html/register.html?success=Registration successful!");
        exit();
    } else {
        header("Location: ../html/register.html?error=Database Error");
        exit();
    }

} else {
    header("Location: ../html/register.html");
    exit();
}
?>
