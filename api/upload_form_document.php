<?php
// api/upload_form_document.php

session_start();
require_once('config.php');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Upload failed.'];

// Check if user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    $response['message'] = 'Unauthorized. Admin access required.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Validate file upload
if (!isset($_FILES['form_file']) || $_FILES['form_file']['error'] !== UPLOAD_ERR_OK) {
    $response['message'] = 'File upload error. Please try again.';
    echo json_encode($response);
    exit;
}

$file = $_FILES['form_file'];

// Validate file type (PDF, DOCX, XLSX)
$allowedTypes = [
    'application/pdf', 
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
];

$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];

if (!in_array($fileExtension, $allowedExtensions)) {
    $response['message'] = 'Invalid file type. Only PDF, DOCX, and XLSX files are allowed.';
    echo json_encode($response);
    exit;
}

// Validate file size (max 10MB)
$maxFileSize = 10 * 1024 * 1024; // 10MB in bytes
if ($file['size'] > $maxFileSize) {
    $response['message'] = 'File size exceeds 10MB limit.';
    echo json_encode($response);
    exit;
}

$uploadDir = '../uploads/forms/';

// Create directory if it doesn't exist
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate unique filename
$uniqueFileName = 'form_' . time() . '_' . basename($file['name']);
$uploadPath = $uploadDir . $uniqueFileName;
$dbFilePath = 'uploads/forms/' . $uniqueFileName;

// Try to move uploaded file
if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
    $response['message'] = 'Failed to save file. Please make sure the uploads/forms folder has write permissions.';
    echo json_encode($response);
    exit;
}

$response['success'] = true;
$response['message'] = 'File uploaded successfully.';
$response['file_path'] = $dbFilePath;
$response['file_name'] = $uniqueFileName;

echo json_encode($response);
mysqli_close($link);
?>
