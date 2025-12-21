<?php
// api/submit_contact_form.php

require_once('config.php');
header('Content-Type: application/json');

session_start();

$response = ['success' => false, 'message' => 'Message submission failed.'];

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    $response['message'] = 'You must be logged in to send a message.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $fullName = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $messageText = trim($_POST['message'] ?? '');
    

    if (empty($fullName) || empty($email) || empty($subject) || empty($messageText)) {
        $response['message'] = 'All fields (Name, Email, Subject, Message) are required.';
        echo json_encode($response);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please provide a valid email address.';
        echo json_encode($response);
        exit;
    }
    
    $sql_insert = "INSERT INTO CONTACT_MESSAGE (FullName, Email, Subject, MessageText) VALUES (?, ?, ?, ?)";
    
    if ($stmt_insert = mysqli_prepare($link, $sql_insert)) {
        mysqli_stmt_bind_param($stmt_insert, "ssss", $fullName, $email, $subject, $messageText);
        
        if (mysqli_stmt_execute($stmt_insert)) {
            $response['success'] = true;
            $response['message_id'] = mysqli_insert_id($link);
            $response['message'] = 'Thank you for your message! We will get back to you shortly.';
        } else {
            $response['message'] = 'A server error occurred during submission.';
        }
        mysqli_stmt_close($stmt_insert);
    } else {
        $response['message'] = 'Database error: Could not prepare insertion statement.';
    }

} else {
    $response['message'] = 'Invalid request method.';
}

mysqli_close($link);
echo json_encode($response);
?>
