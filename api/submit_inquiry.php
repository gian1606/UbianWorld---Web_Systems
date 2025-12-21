<?php
// api/submit_inquiry.php

session_start();
require_once('config.php');
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

$response = ['success' => false, 'message' => 'Inquiry submission failed.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $student_identifier = trim($_POST['student_id'] ?? ''); // Can be username or student ID
    
    if (empty($subject) || empty($description) || empty($student_identifier)) {
        $response['message'] = 'All fields (Student ID, Subject, and Description) are required.';
        echo json_encode($response);
        exit;
    }
    
    $student_id = null;
    
    // First try to get student_id if user is logged in
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SESSION['role'] === 'student') {
        $student_user_id = $_SESSION['user_id'];
        $sql_get_student_id = "SELECT StudentID FROM STUDENT WHERE UserID = ?";
        
        if ($stmt_id = @mysqli_prepare($link, $sql_get_student_id)) {
            @mysqli_stmt_bind_param($stmt_id, "i", $student_user_id);
            @mysqli_stmt_execute($stmt_id);
            @mysqli_stmt_bind_result($stmt_id, $student_id);
            @mysqli_stmt_fetch($stmt_id);
            @mysqli_stmt_close($stmt_id);
        }
    } else {
        // Try finding by username first
        $sql_find = "SELECT s.StudentID FROM STUDENT s 
                     JOIN USERS u ON s.UserID = u.UserID 
                     WHERE u.Username = ?";
        
        if ($stmt_find = @mysqli_prepare($link, $sql_find)) {
            @mysqli_stmt_bind_param($stmt_find, "s", $student_identifier);
            @mysqli_stmt_execute($stmt_find);
            @mysqli_stmt_bind_result($stmt_find, $student_id);
            @mysqli_stmt_fetch($stmt_find);
            @mysqli_stmt_close($stmt_find);
        }
        
        // If still not found, try to find by StudentID directly (if they entered a number)
        if (!$student_id && is_numeric($student_identifier)) {
            $sql_find_direct = "SELECT StudentID FROM STUDENT WHERE StudentID = ?";
            if ($stmt_find_direct = @mysqli_prepare($link, $sql_find_direct)) {
                @mysqli_stmt_bind_param($stmt_find_direct, "i", $student_identifier);
                @mysqli_stmt_execute($stmt_find_direct);
                @mysqli_stmt_bind_result($stmt_find_direct, $student_id);
                @mysqli_stmt_fetch($stmt_find_direct);
                @mysqli_stmt_close($stmt_find_direct);
            }
        }
    }
    
    if (!$student_id) {
        $response['message'] = 'Student ID not found. Please check your Student ID/Username and try again.';
        echo json_encode($response);
        exit;
    }

    $sql_insert = "INSERT INTO INQUIRY (StudentID, Subject, Description, Status) VALUES (?, ?, ?, 'pending')";
    
    if ($stmt_insert = @mysqli_prepare($link, $sql_insert)) {
        @mysqli_stmt_bind_param($stmt_insert, "iss", $student_id, $subject, $description);
        
        if (@mysqli_stmt_execute($stmt_insert)) {
            $response['success'] = true;
            $response['inquiry_id'] = mysqli_insert_id($link);
            $response['message'] = 'Your inquiry has been successfully submitted! An ISSO Staff member will review it shortly.';
        } else {
            $response['message'] = 'Database error: Failed to insert inquiry.';
        }
        @mysqli_stmt_close($stmt_insert);
    } else {
        $response['message'] = 'Database error: Could not prepare inquiry insertion.';
    }
}

@mysqli_close($link);
echo json_encode($response);
?>
