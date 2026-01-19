<?php
session_start();
include '../db/database.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(array('error' => 'Not logged in'));
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle POST (Add Program)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['program_name'];
    $dept = $_POST['department'];
    
    // Validation
    if (empty($name) || empty($dept)) {
        header("Location: ../html/programs.html?error=Empty fields");
        exit();
    }
    
    $name = mysqli_real_escape_string($conn, $name);
    $dept = mysqli_real_escape_string($conn, $dept);
    
    $sql = "INSERT INTO programs (user_id, program_name, department) VALUES ('$user_id', '$name', '$dept')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: ../html/programs.html?success=Added");
    } else {
        header("Location: ../html/programs.html?error=Error");
    }

} else {
    // Handle GET (List Programs)
    $sql = "SELECT * FROM programs WHERE user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    
    $programs = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $programs[] = $row;
    }
    
    $response = array(
        'username' => $_SESSION['username'],
        'programs' => $programs
    );
    
    echo json_encode($response);
}
?>
