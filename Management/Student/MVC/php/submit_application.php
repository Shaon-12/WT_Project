<?php
session_start();
include '../db/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    echo json_encode(array('error' => 'Not authorized'));
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_SESSION['user_id'];
    $app_type = $_POST['app-type'];
    $program = $_POST['program'];
    $country = $_POST['country'];
    $intake = $_POST['intake'];
    $personal_statement = $_POST['personal-statement'];
    $academic_background = $_POST['academic-background'];
    $gpa = $_POST['gpa'];
    $test_scores = $_POST['test-scores'];
    $status = isset($_POST['save_draft']) ? 'draft' : 'pending';

    // Validation
    if (empty($app_type) || empty($program) || empty($country) || empty($intake)) {
        header("Location: ../html/student-dashboard.html?error=Required fields missing");
        exit();
    }

    // Sanitize inputs
    $app_type = mysqli_real_escape_string($conn, $app_type);
    $program = mysqli_real_escape_string($conn, $program);
    $country = mysqli_real_escape_string($conn, $country);
    $intake = mysqli_real_escape_string($conn, $intake);
    $personal_statement = mysqli_real_escape_string($conn, $personal_statement);
    $academic_background = mysqli_real_escape_string($conn, $academic_background);
    $gpa = mysqli_real_escape_string($conn, $gpa);
    $test_scores = mysqli_real_escape_string($conn, $test_scores);

    // Insert application
    $sql = "INSERT INTO student_applications 
            (student_id, application_type, program_name, country, intake, personal_statement, academic_background, gpa, test_scores, status) 
            VALUES ('$student_id', '$app_type', '$program', '$country', '$intake', '$personal_statement', '$academic_background', '$gpa', '$test_scores', '$status')";

    if (mysqli_query($conn, $sql)) {
        $app_id = mysqli_insert_id($conn);
        
        // Add to history
        $action = ($status == 'draft') ? 'Application saved as draft' : 'Application submitted';
        $hist_sql = "INSERT INTO application_history (application_id, action, details) 
                     VALUES ('$app_id', '$action', 'Student submitted application for $program')";
        mysqli_query($conn, $hist_sql);
        
        header("Location: ../html/student-dashboard.html?success=Application submitted");
    } else {
        header("Location: ../html/student-dashboard.html?error=Submission failed");
    }
} else {
    header("Location: ../html/student-dashboard.html");
}
?>
