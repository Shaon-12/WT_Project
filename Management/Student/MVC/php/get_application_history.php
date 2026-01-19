<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    echo json_encode(array('error' => 'Not authorized'));
    exit();
}

$student_id = $_SESSION['user_id'];

// Sample history data (in production, query from database)
$history = array(
    array(
        'date' => '2026-01-15',
        'action' => 'Application Review Started',
        'program' => "Master's in Computer Science - MIT",
        'details' => 'The admissions committee has started reviewing your application. You can expect a decision within 2-3 weeks.'
    ),
    array(
        'date' => '2026-01-10',
        'action' => 'Application Submitted',
        'program' => "Master's in Computer Science - MIT",
        'details' => 'Successfully submitted application with all required documents.'
    ),
    array(
        'date' => '2026-01-08',
        'action' => 'Application Approved',
        'program' => 'PhD in Data Science - Oxford',
        'details' => 'Congratulations! Your application has been approved. You will receive an acceptance letter via email within 48 hours.'
    ),
    array(
        'date' => '2026-01-05',
        'action' => 'Application Submitted',
        'program' => 'Credit Transfer - Stanford University',
        'details' => 'Application submitted for credit transfer review.'
    ),
    array(
        'date' => '2025-12-28',
        'action' => 'Application Submitted',
        'program' => 'PhD in Data Science - Oxford',
        'details' => 'Initial application submitted with transcripts and recommendation letters.'
    ),
    array(
        'date' => '2025-12-20',
        'action' => 'Application Updated',
        'program' => 'PhD in Data Science - Oxford',
        'details' => 'Updated personal statement and added research proposal.'
    )
);

echo json_encode($history);
?>
