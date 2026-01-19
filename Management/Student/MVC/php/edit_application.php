<?php
session_start();
include '../db/database.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    echo json_encode(array('error' => 'Access denied. Student login required.'));
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if POST request
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(array('error' => 'Invalid request method'));
    exit();
}

// Get form data
$application_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;
$application_type = isset($_POST['application_type']) ? mysqli_real_escape_string($conn, $_POST['application_type']) : '';
$start_semester = isset($_POST['start_semester']) ? mysqli_real_escape_string($conn, $_POST['start_semester']) : '';
$statement_of_purpose = isset($_POST['statement_of_purpose']) ? mysqli_real_escape_string($conn, $_POST['statement_of_purpose']) : '';
$additional_notes = isset($_POST['additional_notes']) ? mysqli_real_escape_string($conn, $_POST['additional_notes']) : '';

// Validate application ID
if ($application_id == 0) {
    echo json_encode(array('error' => 'Invalid application ID'));
    exit();
}

// Check if application belongs to this student and is editable
$check_sql = "SELECT status FROM student_applications 
              WHERE id = '$application_id' AND student_id = '$user_id'";
$check_result = mysqli_query($conn, $check_sql);

if (mysqli_num_rows($check_result) == 0) {
    echo json_encode(array('error' => 'Application not found or access denied'));
    exit();
}

$app_data = mysqli_fetch_assoc($conn, $check_result);
$current_status = $app_data['status'];

// Check if application can be edited
if ($current_status != 'Draft' && $current_status != 'Pending') {
    echo json_encode(array('error' => 'Application cannot be edited. Current status: ' . $current_status));
    exit();
}

// Update application
$sql = "UPDATE student_applications SET 
        application_type = '$application_type',
        start_semester = '$start_semester',
        statement_of_purpose = '$statement_of_purpose',
        additional_notes = '$additional_notes',
        updated_date = NOW()
        WHERE id = '$application_id' AND student_id = '$user_id'";

if (mysqli_query($conn, $sql)) {
    // Handle file uploads if any
    handleFileUploads($conn, $application_id);
    
    echo json_encode(array(
        'success' => true,
        'message' => 'Application updated successfully',
        'application_id' => $application_id
    ));
} else {
    echo json_encode(array('error' => 'Database error: ' . mysqli_error($conn)));
}

// Function to handle file uploads
function handleFileUploads($conn, $application_id) {
    // Define allowed file types
    $allowed_extensions = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
    
    // File fields to check
    $file_fields = array('new_transcript', 'additional_document');
    
    foreach ($file_fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $file_name = $_FILES[$field]['name'];
            $file_tmp = $_FILES[$field]['tmp_name'];
            $file_size = $_FILES[$field]['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            // Check file extension
            if (in_array($file_ext, $allowed_extensions)) {
                // Generate unique filename
                $new_filename = 'app_' . $application_id . '_' . $field . '_' . time() . '.' . $file_ext;
                $upload_path = '../uploads/' . $new_filename;
                
                // Move uploaded file
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    // Determine document type
                    $doc_type = ($field == 'new_transcript') ? 'updated_transcript' : 'additional_document';
                    
                    // Save to database
                    $doc_sql = "INSERT INTO application_documents (application_id, document_type, file_name, uploaded_date) 
                               VALUES ('$application_id', '$doc_type', '$new_filename', NOW())";
                    mysqli_query($conn, $doc_sql);
                }
            }
        }
    }
}

mysqli_close($conn);
?>