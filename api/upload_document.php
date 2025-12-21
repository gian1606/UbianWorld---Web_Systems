<?php
// api/upload_document.php

session_start();
require_once('config.php');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Upload failed.'];

// Check if user is logged in and is a student
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'student') {
    http_response_code(401);
    $response['message'] = 'Unauthorized. Students only.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Get student ID from session
$sql = "SELECT StudentID FROM STUDENT WHERE UserID = ?";
$studentID = null;

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $studentID);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    
    if (!$studentID) {
        $response['message'] = 'Student record not found.';
        echo json_encode($response);
        exit;
    }
}

// Validate form inputs
if (empty($_POST['document_type'])) {
    $response['message'] = 'Document type is required.';
    echo json_encode($response);
    exit;
}

if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
    $response['message'] = 'File upload error. Please try again.';
    echo json_encode($response);
    exit;
}

$documentType = trim($_POST['document_type']);
$file = $_FILES['document_file'];

// Validate file type (PDF and JPG only)
$allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

if (!in_array($file['type'], $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
    $response['message'] = 'Invalid file type. Only PDF, JPG, and PNG files are allowed.';
    echo json_encode($response);
    exit;
}

// Validate file size (max 5MB)
$maxFileSize = 5 * 1024 * 1024; // 5MB in bytes
if ($file['size'] > $maxFileSize) {
    $response['message'] = 'File size exceeds 5MB limit.';
    echo json_encode($response);
    exit;
}

$uploadDir = '../uploads/documents/';

// Create directory if it doesn't exist
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate unique filename
$uniqueFileName = 'student_' . $studentID . '_' . time() . '_' . basename($file['name']);
$uploadPath = $uploadDir . $uniqueFileName;
$dbFilePath = 'uploads/documents/' . $uniqueFileName;

// Try to move uploaded file
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    $response['message'] = 'Failed to save file. Please make sure the uploads/documents folder has write permissions (chmod 777).';
    echo json_encode($response);
    exit;
}

// Insert document record into database
$insertSql = "INSERT INTO DOCUMENT (StudentID, FileName, FileType, FilePath, ReviewStatus) 
              VALUES (?, ?, ?, ?, 'Pending')";

if ($stmt = mysqli_prepare($link, $insertSql)) {
    mysqli_stmt_bind_param($stmt, "isss", $studentID, $documentType, $fileExtension, $dbFilePath);
    
    if (mysqli_stmt_execute($stmt)) {
        $response['success'] = true;
        $response['message'] = 'Document uploaded successfully and pending review.';
        $response['document_id'] = mysqli_insert_id($link);
    } else {
        // If database insert fails, delete the uploaded file
        if (file_exists($uploadPath)) {
            unlink($uploadPath);
        }
        $response['message'] = 'Database error: ' . mysqli_error($link);
    }
    mysqli_stmt_close($stmt);
} else {
    // If prepare fails, delete the uploaded file
    if (file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    $response['message'] = 'Database prepare error: ' . mysqli_error($link);
}

mysqli_close($link);
echo json_encode($response);
?>
