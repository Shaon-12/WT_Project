<?php
session_start();
include '../db/database.php';

if (!isset($_SESSION['user_id'])) {
    // If not logged in
    $response = array('error' => 'Not logged in');
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle POST (Update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $uni_name = $_POST['university_name'];
    $uni_country = $_POST['university_country'];
    
    // Basic checks
    if (empty($username) || empty($email)) {
        header("Location: ../html/profile.html?error=Empty fields");
        exit();
    }
    
    // Escape strings
    $username = mysqli_real_escape_string($conn, $username);
    $email = mysqli_real_escape_string($conn, $email);
    $uni_name = mysqli_real_escape_string($conn, $uni_name);
    $uni_country = mysqli_real_escape_string($conn, $uni_country);
    
    // Update query
    $sql = "UPDATE users SET username='$username', email='$email', university_name='$uni_name', university_country='$uni_country' WHERE id='$user_id'";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['username'] = $username;
        header("Location: ../html/profile.html?success=Updated");
    } else {
        header("Location: ../html/profile.html?error=Error");
    }

} else {
    // Handle GET (Fetch Data)
    $sql = "SELECT * FROM users WHERE id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    
    echo json_encode($row);
}
?>
