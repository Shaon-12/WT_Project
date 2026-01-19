<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    echo json_encode(array('error' => 'Not authorized'));
    exit();
}

$student_id = $_SESSION['user_id'];

// For demo purposes, we'll use JSON file
// In production, use database query
$json_file = '../db/student_applications.json';

if (file_exists($json_file)) {
    $applications = json_decode(file_get_contents($json_file), true);
    
    // Filter by student_id (in real app, this comes from database)
    $student_apps = array();
    foreach ($applications as $app) {
        if ($app['student_id'] == $student_id) {
            $student_apps[] = $app;
        }
    }
    
    echo json_encode($student_apps);
} else {
    echo json_encode(array());
}
?>
