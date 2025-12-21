<?php
// api/generate_report.php

session_start();
require_once('config.php');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Report generation failed.'];

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode($response);
    exit;
}

$report_type = $_GET['type'] ?? 'overview';

try {
    $report_data = [];
    
    switch ($report_type) {
        case 'overview':
            // Total students
            $result = mysqli_query($link, "SELECT COUNT(*) as total FROM STUDENT");
            $report_data['total_students'] = mysqli_fetch_assoc($result)['total'];
            
            // Active students
            $result = mysqli_query($link, "SELECT COUNT(*) as active FROM STUDENT WHERE EnrollmentStatus = 'Active'");
            $report_data['active_students'] = mysqli_fetch_assoc($result)['active'];
            
            // Pending inquiries
            $result = mysqli_query($link, "SELECT COUNT(*) as pending FROM INQUIRY WHERE Status = 'pending'");
            $report_data['pending_inquiries'] = mysqli_fetch_assoc($result)['pending'];
            
            // Pending documents
            $result = mysqli_query($link, "SELECT COUNT(*) as pending FROM DOCUMENT WHERE ReviewStatus = 'Pending'");
            $report_data['pending_documents'] = mysqli_fetch_assoc($result)['pending'];
            
            // Students by nationality
            $result = mysqli_query($link, "SELECT Nationality, COUNT(*) as count FROM STUDENT GROUP BY Nationality ORDER BY count DESC LIMIT 10");
            $nationalities = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $nationalities[] = $row;
            }
            $report_data['top_nationalities'] = $nationalities;
            break;
            
        case 'students':
            $result = mysqli_query($link, "
                SELECT 
                    s.StudentID,
                    s.FirstName,
                    s.LastName,
                    s.Email,
                    s.Nationality,
                    s.EnrollmentStatus,
                    s.ProgramOfStudy,
                    COUNT(DISTINCT d.DocumentID) as document_count,
                    COUNT(DISTINCT i.InquiryID) as inquiry_count
                FROM STUDENT s
                LEFT JOIN DOCUMENT d ON s.StudentID = d.StudentID
                LEFT JOIN INQUIRY i ON s.StudentID = i.StudentID
                GROUP BY s.StudentID
                ORDER BY s.LastName, s.FirstName
            ");
            
            $students = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $students[] = $row;
            }
            $report_data['students'] = $students;
            break;
            
        case 'inquiries':
            $result = mysqli_query($link, "
                SELECT 
                    i.InquiryID,
                    i.Subject,
                    i.Status,
                    i.Priority,
                    i.CreatedAt,
                    CONCAT(s.FirstName, ' ', s.LastName) as StudentName
                FROM INQUIRY i
                JOIN STUDENT s ON i.StudentID = s.StudentID
                ORDER BY i.CreatedAt DESC
            ");
            
            $inquiries = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $inquiries[] = $row;
            }
            $report_data['inquiries'] = $inquiries;
            break;
            
        case 'documents':
            $result = mysqli_query($link, "
                SELECT 
                    d.DocumentID,
                    d.FileName,
                    d.ReviewStatus,
                    d.UploadDate,
                    CONCAT(s.FirstName, ' ', s.LastName) as StudentName
                FROM DOCUMENT d
                JOIN STUDENT s ON d.StudentID = s.StudentID
                ORDER BY d.UploadDate DESC
            ");
            
            $documents = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $documents[] = $row;
            }
            $report_data['documents'] = $documents;
            break;
            
        default:
            throw new Exception('Invalid report type.');
    }
    
    $response['success'] = true;
    $response['report_type'] = $report_type;
    $response['data'] = $report_data;
    $response['generated_at'] = date('Y-m-d H:i:s');
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

mysqli_close($link);
echo json_encode($response);
?>
