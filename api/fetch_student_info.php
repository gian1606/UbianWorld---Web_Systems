<?php
// api/fetch_student_info.php

session_start();
require_once('config.php');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Access denied or Invalid Request.'];

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'student') {
    http_response_code(401);
    echo json_encode($response);
    exit;
}

$sql = "SELECT StudentID, FirstName, LastName, Email, Nationality FROM STUDENT WHERE UserID = ?";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $response['success'] = true;
            $response['student_id'] = $row['StudentID'];
            $response['first_name'] = $row['FirstName'];
            $response['last_name'] = $row['LastName'];
            $response['email'] = $row['Email'];
            $response['nationality'] = $row['Nationality'];
            $response['message'] = 'Student information fetched successfully.';
        } else {
            $response['message'] = 'Student record not found.';
        }
        
        mysqli_free_result($result);
    } else {
        $response['message'] = 'Database execute error: ' . mysqli_error($link);
    }
    mysqli_stmt_close($stmt);
} else {
    $response['message'] = 'Database prepare error.';
}

mysqli_close($link);
echo json_encode($response);
?>
