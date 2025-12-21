<?php
// api/fetch_contact_messages.php - Fetch all contact messages for admin dashboard
session_start();
require_once('config.php');
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

// Check if user is logged in as admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

try {
    // Fetch all contact messages
    $sql = "SELECT 
                MessageID,
                FullName,
                Email,
                Subject,
                MessageText,
                Status,
                SubmittedAt,
                ReadAt,
                DATE_FORMAT(SubmittedAt, '%b %d, %Y') as FormattedDate
            FROM CONTACT_MESSAGE
            ORDER BY 
                CASE Status
                    WHEN 'new' THEN 1
                    WHEN 'read' THEN 2
                    WHEN 'responded' THEN 3
                    WHEN 'archived' THEN 4
                END,
                SubmittedAt DESC";
    
    $result = @mysqli_query($link, $sql);
    
    if ($result) {
        $messages = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $messages[] = $row;
        }
        
        // Get status counts
        $countSql = "SELECT 
                        SUM(CASE WHEN Status = 'new' THEN 1 ELSE 0 END) as new_count,
                        SUM(CASE WHEN Status = 'read' THEN 1 ELSE 0 END) as read_count,
                        SUM(CASE WHEN Status = 'responded' THEN 1 ELSE 0 END) as responded_count,
                        SUM(CASE WHEN Status = 'archived' THEN 1 ELSE 0 END) as archived_count,
                        COUNT(*) as total_count
                    FROM CONTACT_MESSAGE";
        
        $countResult = @mysqli_query($link, $countSql);
        $counts = mysqli_fetch_assoc($countResult);
        
        echo json_encode([
            'success' => true,
            'messages' => $messages,
            'counts' => $counts
        ]);
        
        @mysqli_free_result($result);
        @mysqli_free_result($countResult);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database query failed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}

@mysqli_close($link);
?>
