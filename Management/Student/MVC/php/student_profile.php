<?php
session_start();
include '../db/database.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    echo json_encode(array('error' => 'Access denied. Student login required.'));
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if student_profile table exists, if not create it
$check_table = "CREATE TABLE IF NOT EXISTS student_profile (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED NOT NULL UNIQUE,
    full_name VARCHAR(100),
    date_of_birth DATE,
    nationality VARCHAR(50),
    phone VARCHAR(20),
    current_institution VARCHAR(100),
    current_program VARCHAR(100),
    current_gpa VARCHAR(10),
    graduation_date DATE,
    address TEXT,
    city VARCHAR(50),
    country VARCHAR(50),
    postal_code VARCHAR(20),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

mysqli_query($conn, $check_table);

// Handle POST (update profile)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $full_name = isset($_POST['full_name']) ? mysqli_real_escape_string($conn, $_POST['full_name']) : '';
    $date_of_birth = isset($_POST['date_of_birth']) ? mysqli_real_escape_string($conn, $_POST['date_of_birth']) : '';
    $nationality = isset($_POST['nationality']) ? mysqli_real_escape_string($conn, $_POST['nationality']) : '';
    $phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, $_POST['phone']) : '';
    $current_institution = isset($_POST['current_institution']) ? mysqli_real_escape_string($conn, $_POST['current_institution']) : '';
    $current_program = isset($_POST['current_program']) ? mysqli_real_escape_string($conn, $_POST['current_program']) : '';
    $current_gpa = isset($_POST['current_gpa']) ? mysqli_real_escape_string($conn, $_POST['current_gpa']) : '';
    $graduation_date = isset($_POST['graduation_date']) ? mysqli_real_escape_string($conn, $_POST['graduation_date']) : '';
    $address = isset($_POST['address']) ? mysqli_real_escape_string($conn, $_POST['address']) : '';
    $city = isset($_POST['city']) ? mysqli_real_escape_string($conn, $_POST['city']) : '';
    $country = isset($_POST['country']) ? mysqli_real_escape_string($conn, $_POST['country']) : '';
    $postal_code = isset($_POST['postal_code']) ? mysqli_real_escape_string($conn, $_POST['postal_code']) : '';
    
    // Check if profile already exists
    $check_sql = "SELECT id FROM student_profile WHERE user_id = '$user_id'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Update existing profile
        $sql = "UPDATE student_profile SET 
                full_name = '$full_name',
                date_of_birth = " . ($date_of_birth ? "'$date_of_birth'" : "NULL") . ",
                nationality = '$nationality',
                phone = '$phone',
                current_institution = '$current_institution',
                current_program = '$current_program',
                current_gpa = '$current_gpa',
                graduation_date = " . ($graduation_date ? "'$graduation_date'" : "NULL") . ",
                address = '$address',
                city = '$city',
                country = '$country',
                postal_code = '$postal_code'
                WHERE user_id = '$user_id'";
    } else {
        // Insert new profile
        $sql = "INSERT INTO student_profile (user_id, full_name, date_of_birth, nationality, phone, 
                current_institution, current_program, current_gpa, graduation_date, 
                address, city, country, postal_code) 
                VALUES ('$user_id', '$full_name', " . ($date_of_birth ? "'$date_of_birth'" : "NULL") . ", 
                '$nationality', '$phone', '$current_institution', '$current_program', 
                '$current_gpa', " . ($graduation_date ? "'$graduation_date'" : "NULL") . ", 
                '$address', '$city', '$country', '$postal_code')";
    }
    
    if (mysqli_query($conn, $sql)) {
        echo json_encode(array(
            'success' => true,
            'message' => 'Profile updated successfully'
        ));
    } else {
        echo json_encode(array('error' => 'Database error: ' . mysqli_error($conn)));
    }
    
} else {
    // Handle GET (get profile data)
    // First get basic user info
    $user_sql = "SELECT username, email FROM users WHERE id = '$user_id'";
    $user_result = mysqli_query($conn, $user_sql);
    $user_data = mysqli_fetch_assoc($conn, $user_result);
    
    // Then get profile data if exists
    $profile_sql = "SELECT * FROM student_profile WHERE user_id = '$user_id'";
    $profile_result = mysqli_query($conn, $profile_sql);
    
    $response = array(
        'username' => $user_data['username'],
        'email' => $user_data['email']
    );
    
    if (mysqli_num_rows($profile_result) > 0) {
        $profile_data = mysqli_fetch_assoc($conn, $profile_result);
        $response = array_merge($response, $profile_data);
    }
    
    echo json_encode($response);
}

mysqli_close($conn);
?>