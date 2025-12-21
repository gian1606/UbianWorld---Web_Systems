<?php
// api/update_contact_message_status.php - Update contact message status and response
header('Content-Type: application/json');
session_start();

require_once('config.php');
error_reporting(0);
ini_set('display_errors', 0);

// Check if user is logged in as admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['messageId']) || !isset($data['status'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    $messageId = intval($data['messageId']);
    $status = $data['status'];
    $response = isset($data['response']) ? trim($data['response']) : null;
    
    // Validate status
    $validStatuses = ['new', 'read', 'responded', 'archived'];
    if (!in_array($status, $validStatuses)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid status']);
        exit;
    }
    
    $staffIdQuery = "SELECT StaffID FROM STAFF WHERE UserID = " . intval($_SESSION['user_id']);
    $staffResult = @mysqli_query($link, $staffIdQuery);
    $staffRow = $staffResult ? mysqli_fetch_assoc($staffResult) : null;
    $staffId = $staffRow ? $staffRow['StaffID'] : null;
    
    if ($response && !empty($response)) {
        // If a response is provided, automatically set status to 'responded' if it's 'new' or 'read'
        if ($status === 'new' || $status === 'read') {
            $status = 'responded';
        }
        
        $updateQuery = "UPDATE CONTACT_MESSAGE 
                        SET Status = '" . mysqli_real_escape_string($link, $status) . "', 
                            ReadAt = COALESCE(ReadAt, CURRENT_TIMESTAMP),
                            ResponseText = '" . mysqli_real_escape_string($link, $response) . "',
                            RespondedBy = " . ($staffId ? intval($staffId) : 'NULL') . ",
                            RespondedAt = CURRENT_TIMESTAMP
                        WHERE MessageID = " . intval($messageId);
        $updateResult = @mysqli_query($link, $updateQuery);
    } else {
        // No response provided, just update status
        $updateQuery = "UPDATE CONTACT_MESSAGE 
                        SET Status = '" . mysqli_real_escape_string($link, $status) . "'";
        
        if ($status === 'read' || $status === 'responded') {
            $updateQuery .= ", ReadAt = COALESCE(ReadAt, CURRENT_TIMESTAMP)";
        }
        
        $updateQuery .= " WHERE MessageID = " . intval($messageId);
        $updateResult = @mysqli_query($link, $updateQuery);
    }
    
    if ($updateResult && mysqli_affected_rows($link) > 0) {
        echo json_encode([
            'success' => true,
            'message' => $response ? 'Response submitted successfully' : 'Status updated successfully'
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Message not found or no changes made']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

@mysqli_close($link);
?>
