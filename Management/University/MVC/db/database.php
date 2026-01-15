<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "study_abroad_db";

// Connect to database using procedural style (easier for beginners)
$conn = mysqli_connect($servername, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if it checks out doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (mysqli_query($conn, $sql)) {
    // Database created or exists
} else {
    echo "Error creating database: " . mysqli_error($conn);
}

// Select the database
mysqli_select_db($conn, $dbname);

// Table: users
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'university') NOT NULL,
    university_name VARCHAR(100),
    university_ranking INT,
    university_country VARCHAR(50),
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

mysqli_query($conn, $sql_users);

// Table: programs
$sql_programs = "CREATE TABLE IF NOT EXISTS programs (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED,
    program_name VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

mysqli_query($conn, $sql_programs);
?>
