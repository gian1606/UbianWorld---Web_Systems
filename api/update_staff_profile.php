<?php
// api/update_staff_profile.php

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
    
    $staff_id = $_SESSION['staff_id'] ?? 0;
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');

    if ($staff_id <= 0 || empty($firstName) || empty($lastName) || empty($email)) {
        $response['message'] = 'Required fields are missing.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please provide a valid email address.';
        echo json_encode($response);
        exit;
    }

    $sql = "UPDATE STAFF 
            SET FirstName = ?, 
                LastName = ?, 
                Email = ?, 
                Position = ?, 
                PhoneNumber = ?
            WHERE StaffID = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssssi", 
            $firstName, $lastName, $email, $position, $phoneNumber, $staff_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $response['success'] = true;
            $response['message'] = 'Profile updated successfully.';
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
