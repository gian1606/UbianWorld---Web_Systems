<?php
// api/update_student_record.php

session_start();
require_once('config.php');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Update failed.'];

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $studentID = (int) ($_POST['student_id'] ?? 0);
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $programOfStudy = trim($_POST['program_of_study'] ?? '');
    $enrollmentStatus = trim($_POST['enrollment_status'] ?? 'Active');

    if ($studentID <= 0 || empty($firstName) || empty($lastName) || empty($email)) {
        $response['message'] = 'Required fields are missing.';
        echo json_encode($response);
        exit;
    }

    $sql = "UPDATE STUDENT 
            SET FirstName = ?, 
                LastName = ?, 
                Email = ?, 
                Nationality = ?, 
                PhoneNumber = ?, 
                ProgramOfStudy = ?,
                EnrollmentStatus = ?
            WHERE StudentID = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssssssi", 
            $firstName, $lastName, $email, $nationality, 
            $phoneNumber, $programOfStudy, $enrollmentStatus, $studentID);
        
        if (mysqli_stmt_execute($stmt)) {
            $response['success'] = true;
            $response['message'] = 'Student record updated successfully.';
        } else {
            $response['message'] = 'Database error: ' . mysqli_error($link);
        }
        mysqli_stmt_close($stmt);
    } else {
        $response['message'] = 'Database prepare error.';
    }
}

mysqli_close($link);
echo json_encode($response);
?>
