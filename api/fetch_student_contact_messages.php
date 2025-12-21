<?php
// api/fetch_student_contact_messages.php - Fetch contact messages for logged-in student
session_start();
require_once('config.php');
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if user is logged in as a student
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access', 'debug' => 'Not logged in or wrong role']);
    exit;
}

try {
    // Get student email from session
    $userId = $_SESSION['user_id'];
    
    error_log("[v0 DEBUG] Fetching messages for UserID: " . $userId);
    
    // First get the student's email
    $sql = "SELECT Email FROM STUDENT WHERE UserID = ?";
    $stmt = @mysqli_prepare($link, $sql);
    
    if ($stmt) {
        @mysqli_stmt_bind_param($stmt, "i", $userId);
        @mysqli_stmt_execute($stmt);
        @mysqli_stmt_bind_result($stmt, $email);
        @mysqli_stmt_fetch($stmt);
        @mysqli_stmt_close($stmt);
        
        error_log("[v0 DEBUG] Found email: " . ($email ? $email : 'NULL'));
        
        if ($email) {
            // Fetch all contact messages for this email
            $sql = "SELECT 
                        MessageID,
                        FullName,
                        Email,
                        Subject,
                        MessageText,
                        Status,
                        DATE_FORMAT(SubmittedAt, '%M %d, %Y') as FormattedDate,
                        SubmittedAt
                    FROM CONTACT_MESSAGE 
                    WHERE Email = ?
                    ORDER BY SubmittedAt DESC";
            
            error_log("[v0 DEBUG] Searching CONTACT_MESSAGE table for email: " . $email);
            
            $stmt = @mysqli_prepare($link, $sql);
            if ($stmt) {
                @mysqli_stmt_bind_param($stmt, "s", $email);
                @mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                $messages = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $messages[] = $row;
                }
                
                error_log("[v0 DEBUG] Found " . count($messages) . " messages");
                
                echo json_encode([
                    'success' => true,
                    'messages' => $messages,
                    'debug_info' => [
                        'logged_in_email' => $email,
                        'user_id' => $userId,
                        'message_count' => count($messages),
                        'query_used' => "SELECT FROM CONTACT_MESSAGE WHERE Email = '$email'"
                    ]
                ]);
                
                @mysqli_free_result($result);
                @mysqli_stmt_close($stmt);
            } else {
                error_log("[v0 DEBUG] Query preparation failed: " . mysqli_error($link));
                echo json_encode(['success' => false, 'message' => 'Query preparation failed', 'error' => mysqli_error($link)]);
            }
        } else {
            error_log("[v0 DEBUG] No email found for UserID: " . $userId);
            echo json_encode(['success' => false, 'message' => 'Student email not found', 'userId' => $userId]);
        }
    } else {
        error_log("[v0 DEBUG] Email lookup failed: " . mysqli_error($link));
        echo json_encode(['success' => false, 'message' => 'Database error', 'error' => mysqli_error($link)]);
    }
    
} catch (Exception $e) {
    error_log("[v0 DEBUG] Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

@mysqli_close($link);
?>
