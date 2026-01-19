<?php
session_start();
include '../db/database.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    echo json_encode(array('error' => 'Access denied. Student login required.'));
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle POST (upload document)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $document_type = isset($_POST['document_type']) ? mysqli_real_escape_string($conn, $_POST['document_type']) : '';
    $description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : '';
    
    // Check if file was uploaded
    if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] == 0) {
        $file_name = $_FILES['document_file']['name'];
        $file_tmp = $_FILES['document_file']['tmp_name'];
        $file_size = $_FILES['document_file']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed file types
        $allowed_extensions = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
        
        if (in_array($file_ext, $allowed_extensions)) {
            // Generate unique filename
            $new_filename = 'student_' . $user_id . '_' . $document_type . '_' . time() . '.' . $file_ext;
            $upload_path = '../uploads/' . $new_filename;
            
            // Move uploaded file
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Save to database
                $sql = "INSERT INTO student_documents (student_id, document_type, file_name, description, uploaded_date) 
                       VALUES ('$user_id', '$document_type', '$new_filename', '$description', NOW())";
                
                if (mysqli_query($conn, $sql)) {
                    echo json_encode(array(
                        'success' => true,
                        'message' => 'Document uploaded successfully',
                        'file_name' => $new_filename
                    ));
                } else {
                    echo json_encode(array('error' => 'Database error: ' . mysqli_error($conn)));
                }
            } else {
                echo json_encode(array('error' => 'File upload failed'));
            }
        } else {
            echo json_encode(array('error' => 'Invalid file type. Allowed: PDF, DOC, JPG, PNG'));
        }
    } else {
        echo json_encode(array('error' => 'No file uploaded or upload error'));
    }
} else {
    // Handle GET (list documents)
    $sql = "SELECT * FROM student_documents 
            WHERE student_id = '$user_id' 
            ORDER BY uploaded_date DESC";
    
    $result = mysqli_query($conn, $sql);
    
    $documents = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $documents[] = $row;
    }
    
    $response = array(
        'username' => $_SESSION['username'],
        'documents' => $documents
    );
    
    echo json_encode($response);
}

mysqli_close($conn);
?>