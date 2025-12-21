<?php
// api/update_document_status.php

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
    
    $documentID = (int) ($_POST['document_id'] ?? 0);
    $reviewStatus = trim($_POST['review_status'] ?? '');
    $reviewNotes = trim($_POST['review_notes'] ?? '');

    if ($documentID <= 0 || empty($reviewStatus)) {
        $response['message'] = 'Document ID and review status are required.';
        echo json_encode($response);
        exit;
    }

    $valid_statuses = ['Pending', 'Approved', 'Rejected', 'Under Review'];
    if (!in_array($reviewStatus, $valid_statuses)) {
        $response['message'] = 'Invalid review status.';
        echo json_encode($response);
        exit;
    }

    $staff_id = $_SESSION['staff_id'] ?? null;
    
    $sql = "UPDATE DOCUMENT 
            SET ReviewStatus = ?, 
                ReviewNotes = ?,
                ReviewedBy = ?,
                ReviewDate = NOW()
            WHERE DocumentID = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "ssii", $reviewStatus, $reviewNotes, $staff_id, $documentID);
        
        if (mysqli_stmt_execute($stmt)) {
            $response['success'] = true;
            $response['message'] = 'Document status updated successfully.';
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
