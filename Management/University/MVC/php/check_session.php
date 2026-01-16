<?php
session_start();
header('Content-Type: application/json');

$response = array();

if (isset($_SESSION['user_id'])) {
    $response['authenticated'] = true;
    $response['username'] = $_SESSION['username'];
    $response['role'] = $_SESSION['role'];
} else {
    $response['authenticated'] = false;
}

echo json_encode($response);
?>
