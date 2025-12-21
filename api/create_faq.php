<?php
// api/create_faq.php

session_start();
require_once('config.php');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Creation failed.'];

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Admin access required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = trim($_POST['question'] ?? '');
    $answer = trim($_POST['answer'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $display_order = (int) ($_POST['display_order'] ?? 0);

    if (empty($question) || empty($answer)) {
        $response['message'] = 'Question and answer are required.';
        echo json_encode($response);
        exit;
    }

    $sql = "INSERT INTO faqs (question, answer, category, display_order) VALUES (?, ?, ?, ?)";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssi", $question, $answer, $category, $display_order);
        
        if (mysqli_stmt_execute($stmt)) {
            $response['success'] = true;
            $response['message'] = 'FAQ created successfully.';
            $response['faq_id'] = mysqli_insert_id($link);
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
