<?php
// api/fetch_student_documents.php

session_start();
require_once('config.php');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Access denied or Invalid Request.'];

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode($response);
    exit;
}

if (!isset($_GET['student_id']) || !is_numeric($_GET['student_id'])) {
    $response['message'] = 'Missing or invalid Student ID.';
    echo json_encode($response);
    exit;
}

$studentID = (int) $_GET['student_id'];

if ($_SESSION['role'] === 'student') {
    $verify_sql = "SELECT StudentID FROM STUDENT WHERE UserID = ?";
    if ($stmt = mysqli_prepare($link, $verify_sql)) {
        mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $allowed_student_id);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        
        if ($allowed_student_id != $studentID) {
            $response['message'] = 'Access denied.';
            http_response_code(403);
            echo json_encode($response);
            exit;
        }
    }
}

$sql = "SELECT 
            DocumentID, 
            FileName, 
            FileType, 
            DATE_FORMAT(UploadDate, '%Y-%m-%d %H:%i') AS UploadDateFormatted,
            ReviewStatus,
            ReviewNotes
        FROM 
            DOCUMENT
        WHERE 
            StudentID = ?
        ORDER BY 
            UploadDate DESC";

$documents = [];

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $studentID);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $documents[] = $row;
        }
        
        $response['success'] = true;
        $response['data'] = $documents;
        $response['count'] = count($documents);
        $response['message'] = 'Student documents fetched successfully.';
        mysqli_free_result($result);
    } else {
        $response['message'] = 'Database execute error: ' . mysqli_error($link);
    }
    mysqli_stmt_close($stmt);
} else {
    $response['message'] = 'Database prepare error: Could not prepare query.';
}

mysqli_close($link);
echo json_encode($response);
?>
