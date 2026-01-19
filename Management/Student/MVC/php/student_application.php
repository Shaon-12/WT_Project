<?php
session_start();
include '../db/database.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    echo json_encode(array('error' => 'Access denied. Student login required.'));
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle different actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle POST requests (submit new application)
    handlePostApplication($conn, $user_id);
} else {
    // Handle GET requests (get applications)
    if ($action == 'get_all') {
        getAllApplications($conn, $user_id);
    } elseif ($action == 'get_single') {
        $app_id = isset($_GET['id']) ? $_GET['id'] : 0;
        getSingleApplication($conn, $user_id, $app_id);
    } else {
        // Default: get all applications
        getAllApplications($conn, $user_id);
    }
}

// Function to handle POST (submit new application)
function handlePostApplication($conn, $user_id) {
    // Get form data
    $application_type = isset($_POST['application_type']) ? mysqli_real_escape_string($conn, $_POST['application_type']) : '';
    $university_id = isset($_POST['university_id']) ? intval($_POST['university_id']) : 0;
    $program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;
    $start_semester = isset($_POST['start_semester']) ? mysqli_real_escape_string($conn, $_POST['start_semester']) : '';
    $statement_of_purpose = isset($_POST['statement_of_purpose']) ? mysqli_real_escape_string($conn, $_POST['statement_of_purpose']) : '';
    $additional_notes = isset($_POST['additional_notes']) ? mysqli_real_escape_string($conn, $_POST['additional_notes']) : '';
    
    // Check if saving as draft
    $save_draft = isset($_POST['save_draft']) ? true : false;
    $status = $save_draft ? 'Draft' : 'Pending';
    
    // Basic validation
    if (empty($application_type) || $university_id == 0 || $program_id == 0) {
        echo json_encode(array('error' => 'Required fields are missing'));
        exit();
    }
    
    // Get university name
    $uni_sql = "SELECT university_name, university_country FROM users WHERE id = '$university_id' AND role = 'university'";
    $uni_result = mysqli_query($conn, $uni_sql);
    if (mysqli_num_rows($uni_result) == 0) {
        echo json_encode(array('error' => 'University not found'));
        exit();
    }
    $uni_row = mysqli_fetch_assoc($conn, $uni_result);
    $university_name = $uni_row['university_name'];
    $university_country = $uni_row['university_country'];
    
    // Get program name
    $prog_sql = "SELECT program_name, department FROM programs WHERE id = '$program_id'";
    $prog_result = mysqli_query($conn, $prog_sql);
    if (mysqli_num_rows($prog_result) == 0) {
        echo json_encode(array('error' => 'Program not found'));
        exit();
    }
    $prog_row = mysqli_fetch_assoc($conn, $prog_result);
    $program_name = $prog_row['program_name'];
    $department = $prog_row['department'];
    
    // Insert application
    $sql = "INSERT INTO student_applications (student_id, university_id, program_id, application_type, 
            university_name, program_name, department, start_semester, statement_of_purpose, 
            additional_notes, status, application_date) 
            VALUES ('$user_id', '$university_id', '$program_id', '$application_type', 
            '$university_name', '$program_name', '$department', '$start_semester', 
            '$statement_of_purpose', '$additional_notes', '$status', NOW())";
    
    if (mysqli_query($conn, $sql)) {
        $application_id = mysqli_insert_id($conn);
        
        // Handle file uploads if not draft
        if (!$save_draft) {
            handleFileUploads($conn, $application_id);
        }
        
        echo json_encode(array(
            'success' => true,
            'message' => $save_draft ? 'Application saved as draft' : 'Application submitted successfully',
            'application_id' => $application_id
        ));
    } else {
        echo json_encode(array('error' => 'Database error: ' . mysqli_error($conn)));
    }
}

// Function to handle file uploads
function handleFileUploads($conn, $application_id) {
    // Define allowed file types
    $allowed_extensions = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
    
    // File fields to check
    $file_fields = array('transcript', 'passport', 'cv', 'recommendation_letter');
    
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
                    // Save to database
                    $doc_sql = "INSERT INTO application_documents (application_id, document_type, file_name, uploaded_date) 
                               VALUES ('$application_id', '$field', '$new_filename', NOW())";
                    mysqli_query($conn, $doc_sql);
                }
            }
        }
    }
}

// Function to get all applications for a student
function getAllApplications($conn, $user_id) {
    $sql = "SELECT sa.*, 
                   (SELECT COUNT(*) FROM application_documents WHERE application_id = sa.id) as documents_count
            FROM student_applications sa
            WHERE sa.student_id = '$user_id'
            ORDER BY sa.application_date DESC";
    
    $result = mysqli_query($conn, $sql);
    
    $applications = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $applications[] = $row;
    }
    
    $response = array(
        'username' => $_SESSION['username'],
        'applications' => $applications
    );
    
    echo json_encode($response);
}

// Function to get single application
function getSingleApplication($conn, $user_id, $app_id) {
    $sql = "SELECT sa.* FROM student_applications sa
            WHERE sa.id = '$app_id' AND sa.student_id = '$user_id'";
    
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $application = mysqli_fetch_assoc($result);
        
        // Get documents for this application
        $doc_sql = "SELECT * FROM application_documents WHERE application_id = '$app_id'";
        $doc_result = mysqli_query($conn, $doc_sql);
        $documents = array();
        while ($doc_row = mysqli_fetch_assoc($doc_result)) {
            $documents[] = $doc_row;
        }
        $application['documents'] = $documents;
        
        echo json_encode($application);
    } else {
        echo json_encode(array('error' => 'Application not found'));
    }
}

// Close connection
mysqli_close($conn);
?>