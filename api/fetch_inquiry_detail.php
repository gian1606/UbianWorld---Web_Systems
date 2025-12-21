<?php
// api/fetch_inquiry_detail.php
// Fetches detailed information for a single inquiry

session_start();
require_once('config.php');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Access denied or invalid request.'];

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['inquiry_id'])) {
    $inquiryID = (int) $_GET['inquiry_id'];
    $role = $_SESSION['role'];
    
    if ($inquiryID <= 0) {
        $response['message'] = 'Invalid inquiry ID.';
        echo json_encode($response);
        exit;
    }
    
    if ($role === 'admin') {
        // Admin can view all inquiry details
        $sql = "SELECT 
                    i.InquiryID,
                    i.Subject,
                    i.Description,
                    i.Status,
                    i.Priority,
                    i.CreatedAt,
                    i.UpdatedAt,
                    i.Response,
                    i.ResolvedAt,
                    s.StudentID,
                    s.FirstName AS StudentFirstName,
                    s.LastName AS StudentLastName,
                    s.Email AS StudentEmail,
                    u.Username,
                    st.FirstName AS StaffFirstName,
                    st.LastName AS StaffLastName
                FROM 
                    INQUIRY i
                JOIN 
                    STUDENT s ON i.StudentID = s.StudentID
                JOIN
                    USERS u ON s.UserID = u.UserID
                LEFT JOIN
                    STAFF st ON i.AssignedTo = st.StaffID
                WHERE 
                    i.InquiryID = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $inquiryID);
            
            if (mysqli_stmt_execute($stmt)) {
                $result = mysqli_stmt_get_result($stmt);
                
                if ($row = mysqli_fetch_assoc($result)) {
                    $row['SubmittedOn'] = date('M d, Y', strtotime($row['CreatedAt']));
                    $row['StudentName'] = $row['StudentFirstName'] . ' ' . $row['StudentLastName'];
                    
                    if ($row['StaffFirstName']) {
                        $row['StaffName'] = $row['StaffFirstName'] . ' ' . $row['StaffLastName'];
                    } else {
                        $row['StaffName'] = null;
                    }
                    
                    $response['success'] = true;
                    $response['inquiry'] = $row;
                } else {
                    $response['message'] = 'Inquiry not found.';
                }
            }
            mysqli_stmt_close($stmt);
        }
        
    } elseif ($role === 'student') {
        // Students can only view their own inquiries
        $student_id = null;
        $sql_get = "SELECT StudentID FROM STUDENT WHERE UserID = ?";
        if ($stmt = mysqli_prepare($link, $sql_get)) {
            mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $student_id);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
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
                        Response,
                        ResolvedAt
                    FROM 
                        INQUIRY
                    WHERE 
                        InquiryID = ? AND StudentID = ?";
            
            if ($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ii", $inquiryID, $student_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if ($row = mysqli_fetch_assoc($result)) {
                        $row['SubmittedOn'] = date('M d, Y', strtotime($row['CreatedAt']));
                        
                        $response['success'] = true;
                        $response['inquiry'] = $row;
                    } else {
                        $response['message'] = 'Inquiry not found or access denied.';
                    }
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

mysqli_close($link);
echo json_encode($response);
?>
