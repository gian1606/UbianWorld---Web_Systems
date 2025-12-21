<?php
// api/update_faq.php

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
    
    $faq_id = (int) ($_POST['faq_id'] ?? 0);
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $display_order = (int) ($_POST['display_order'] ?? 0);

    if ($faq_id <= 0 || empty($question) || empty($answer)) {
        $response['message'] = 'FAQ ID, question, and answer are required.';
        echo json_encode($response);
        exit;
    }

    $sql = "UPDATE faqs 
            SET question = ?, 
                answer = ?, 
                category = ?,
                display_order = ?
            WHERE faq_id = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssii", $question, $answer, $category, $display_order, $faq_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $response['success'] = true;
            $response['message'] = 'FAQ updated successfully.';
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
