<?php
// api/update_inquiry_status.php

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
    
    $inquiryID = (int) ($_POST['inquiry_id'] ?? 0);
    $status = trim($_POST['status'] ?? '');
    $response_text = trim($_POST['response'] ?? '');

    if ($inquiryID <= 0 || empty($status)) {
        $response['message'] = 'Inquiry ID and status are required.';
        echo json_encode($response);
        exit;
    }

    $valid_statuses = ['pending', 'in_progress', 'resolved', 'closed'];
    if (!in_array($status, $valid_statuses)) {
        $response['message'] = 'Invalid status value.';
        echo json_encode($response);
        exit;
    }

    $staff_id = $_SESSION['staff_id'] ?? null;
    
    if ($status === 'resolved' || $status === 'closed') {
        $sql = "UPDATE INQUIRY 
                SET Status = ?, 
                    Response = ?, 
                    AssignedTo = ?,
                    ResolvedAt = NOW()
                WHERE InquiryID = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssii", $status, $response_text, $staff_id, $inquiryID);
            
            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = 'Inquiry updated successfully.';
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $sql = "UPDATE INQUIRY 
                SET Status = ?, 
                    Response = ?,
                    AssignedTo = ?
                WHERE InquiryID = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssii", $status, $response_text, $staff_id, $inquiryID);
            
            if (mysqli_stmt_execute($stmt)) {
                $response['success'] = true;
                $response['message'] = 'Inquiry updated successfully.';
            }
            mysqli_stmt_close($stmt);
        }
    }
}

mysqli_close($link);
echo json_encode($response);
?>
