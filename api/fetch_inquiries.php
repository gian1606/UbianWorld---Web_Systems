<?php
// api/fetch_inquiries.php

session_start();
require_once('config.php');
header('Content-Type: application/json');
error_reporting(0);
ini_set('display_errors', 0);

$response = ['success' => false, 'message' => 'Access denied or Invalid Request.'];

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode($response);
    exit;
}

$role = $_SESSION['role'];

if ($role === 'admin') {
    $sql = "SELECT 
                i.InquiryID,
                i.Subject,
                i.Description,
                i.Status,
                i.Priority,
                i.CreatedAt,
                i.UpdatedAt,
                i.Response,
                s.FirstName AS StudentFirstName,
                s.LastName AS StudentLastName,
                s.Email AS StudentEmail,
                u.Username,
                i.StudentID
            FROM 
                INQUIRY i
            JOIN 
                STUDENT s ON i.StudentID = s.StudentID
            JOIN
                USERS u ON s.UserID = u.UserID
            ORDER BY 
                FIELD(i.Status, 'pending', 'in_progress', 'resolved', 'closed'),
                i.CreatedAt DESC";
    
    if ($result = @mysqli_query($link, $sql)) {
        $inquiries = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $row['SubmittedOn'] = date('M d, Y', strtotime($row['CreatedAt']));
            $row['StudentName'] = $row['StudentFirstName'] . ' ' . $row['StudentLastName'];
            $inquiries[] = $row;
        }
        $response['success'] = true;
        $response['inquiries'] = $inquiries;
        $response['count'] = count($inquiries);
        @mysqli_free_result($result);
    } else {
        $response['message'] = 'Database query failed.';
    }
    
} elseif ($role === 'student') {
    // Students see only their inquiries
    $student_id = null;
    $sql_get = "SELECT StudentID FROM STUDENT WHERE UserID = ?";
    if ($stmt = @mysqli_prepare($link, $sql_get)) {
        @mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
        @mysqli_stmt_execute($stmt);
        @mysqli_stmt_bind_result($stmt, $student_id);
        @mysqli_stmt_fetch($stmt);
        @mysqli_stmt_close($stmt);
    }
    
    if ($student_id) {
        $sql = "SELECT 
                    InquiryID,
                    Subject,
                    Description,
                    Status,
                    Priority,
                    CreatedAt,
                    UpdatedAt,
                    Response
                FROM 
                    INQUIRY
                WHERE 
                    StudentID = ?
                ORDER BY 
                    CreatedAt DESC";
        
        if ($stmt = @mysqli_prepare($link, $sql)) {
            @mysqli_stmt_bind_param($stmt, "i", $student_id);
            if (@mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                $inquiries = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $row['SubmittedOn'] = date('M d, Y', strtotime($row['CreatedAt']));
                    $inquiries[] = $row;
                }
                $response['success'] = true;
                $response['inquiries'] = $inquiries;
                $response['count'] = count($inquiries);
                @mysqli_free_result($result);
            }
            @mysqli_stmt_close($stmt);
        }
    }
}

@mysqli_close($link);
echo json_encode($response);
?>
