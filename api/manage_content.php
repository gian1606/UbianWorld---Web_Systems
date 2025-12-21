<?php
// api/manage_content.php

session_start();
require_once('config.php');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid Request Method or Content Type.'];

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    $response['message'] = 'Access denied. You must be logged in as an ISSO Staff member.';
    http_response_code(401);
    echo json_encode($response);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$staffID = $_SESSION['staff_id'] ?? 1;

if ($method === 'POST') {
    
    $requiredFields = ['title', 'type', 'content_text'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
            $response['message'] = "Missing required field: " . $field;
            echo json_encode($response);
            exit;
        }
    }

    $title = trim($_POST['title']);
    $type = trim($_POST['type']);
    $contentText = trim($_POST['content_text']);
    $contentURL = isset($_POST['content_url']) ? trim($_POST['content_url']) : null;
    
    $validTypes = ['Announcement', 'Guide', 'FAQ'];
    if (!in_array($type, $validTypes)) {
        $response['message'] = 'Invalid content type provided.';
        echo json_encode($response);
        exit;
    }
    
    $sql = "INSERT INTO CONTENT (StaffID, Title, Type, ContentText, ContentURL) 
            VALUES (?, ?, ?, ?, ?)";
            
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "issss", $staffID, $title, $type, $contentText, $contentURL);

        if (mysqli_stmt_execute($stmt)) {
            $newID = mysqli_insert_id($link);
            $response['success'] = true;
            $response['data'] = ['ResourceID' => $newID];
            $response['message'] = "New {$type} posted successfully with ID: {$newID}.";
        } else {
            $response['message'] = 'Database execute error: Could not insert content.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $response['message'] = 'Database prepare error: Could not prepare query.';
    }
}

elseif ($method === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    
    $resourceID = (int) ($_PUT['resource_id'] ?? 0);
    $title = trim($_PUT['title'] ?? '');
    $contentText = trim($_PUT['content_text'] ?? '');
    $contentURL = trim($_PUT['content_url'] ?? '');
    
    if ($resourceID <= 0 || empty($title) || empty($contentText)) {
        $response['message'] = 'Resource ID, title, and content text are required.';
        echo json_encode($response);
        exit;
    }
    
    $sql = "UPDATE CONTENT 
            SET Title = ?, ContentText = ?, ContentURL = ?
            WHERE ResourceID = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "sssi", $title, $contentText, $contentURL, $resourceID);
        
        if (mysqli_stmt_execute($stmt)) {
            $response['success'] = true;
            $response['message'] = 'Content updated successfully.';
        } else {
            $response['message'] = 'Database error: ' . mysqli_error($link);
        }
        mysqli_stmt_close($stmt);
    }
}

elseif ($method === 'DELETE') {
    parse_str(file_get_contents("php://input"), $_DELETE);
    
    $resourceID = (int) ($_DELETE['resource_id'] ?? 0);
    
    if ($resourceID <= 0) {
        $response['message'] = 'Resource ID is required.';
        echo json_encode($response);
        exit;
    }
    
    $sql = "DELETE FROM CONTENT WHERE ResourceID = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $resourceID);
        
        if (mysqli_stmt_execute($stmt)) {
            $response['success'] = true;
            $response['message'] = 'Content deleted successfully.';
        } else {
            $response['message'] = 'Database error: ' . mysqli_error($link);
        }
        mysqli_stmt_close($stmt);
    }
}

mysqli_close($link);
echo json_encode($response);
?>
