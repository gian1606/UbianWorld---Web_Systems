<?php
// api/fetch_students.php

session_start();
require_once('config.php');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Access denied or Invalid Request.'];

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode($response);
    exit;
}

$sql = "SELECT 
            s.StudentID, 
            s.FirstName, 
            s.LastName, 
            s.Email, 
            s.Nationality, 
            s.PhoneNumber,
            s.ProgramOfStudy,
            s.EnrollmentStatus,
            u.Username AS StudentUsername,
            s.CreatedAt
        FROM 
            STUDENT s
        JOIN 
            USERS u ON s.UserID = u.UserID
        ORDER BY 
            s.LastName, s.FirstName";

$students = [];

if ($result = mysqli_query($link, $sql)) {
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $students[] = $row;
        }
        $response['success'] = true;
        $response['data'] = $students;
        $response['count'] = count($students);
        $response['message'] = 'Student records fetched successfully.';
    } else {
        $response['message'] = 'No international student records found.';
        $response['count'] = 0;
        $response['data'] = [];
        $response['success'] = true;
    }
    mysqli_free_result($result);
} else {
    $response['message'] = 'Database query failed: ' . mysqli_error($link);
    http_response_code(500);
}

mysqli_close($link);
echo json_encode($response);
?>
