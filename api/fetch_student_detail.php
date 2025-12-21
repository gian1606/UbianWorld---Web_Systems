<?php
// api/fetch_student_detail.php

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
            s.StudentID,
            s.FirstName,
            s.LastName,
            s.Email,
            s.Nationality,
            s.PhoneNumber,
            s.DateOfBirth,
            s.ProgramOfStudy,
            s.EnrollmentStatus,
            u.Username AS StudentUsername,
            s.CreatedAt,
            s.UpdatedAt
        FROM 
            STUDENT s
        JOIN 
            USERS u ON s.UserID = u.UserID
        WHERE 
            s.StudentID = ?";

if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $studentID);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $response['success'] = true;
            $response['data'] = $row;
            $response['message'] = 'Student details fetched successfully.';
        } else {
            $response['message'] = 'Student not found.';
        }
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
