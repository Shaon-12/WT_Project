<?php
session_start();
include '../db/database.php';

// Check if user is logged in (student or university)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(array('error' => 'Not logged in'));
    exit();
}

// Get all universities (users with role 'university')
$sql = "SELECT id, username as university_name, university_country, university_ranking 
        FROM users 
        WHERE role = 'university' 
        ORDER BY university_name";

$result = mysqli_query($conn, $sql);

$universities = array();
while ($row = mysqli_fetch_assoc($result)) {
    $universities[] = $row;
}

echo json_encode($universities);

mysqli_close($conn);
?>