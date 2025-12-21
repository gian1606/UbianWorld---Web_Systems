<?php
// api/delete_faq.php

session_start();
require_once('config.php');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Deletion failed.'];

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Admin access required.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $faq_id = (int) ($_POST['faq_id'] ?? 0);

    if ($faq_id <= 0) {
        $response['message'] = 'Invalid FAQ ID.';
        echo json_encode($response);
        exit;
    }

    $sql = "DELETE FROM faqs WHERE faq_id = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $faq_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $response['success'] = true;
            $response['message'] = 'FAQ deleted successfully.';
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
