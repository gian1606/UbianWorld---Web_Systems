<?php
// api/fetch_all_documents.php - Fetch all student documents for admin review

session_start();
require_once('config.php');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Access denied.'];

// Only admins can view all documents
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    http_response_code(401);
    echo json_encode($response);
    exit;
}

$sql = "SELECT 
            d.DocumentID, 
            d.StudentID,
            CONCAT(s.FirstName, ' ', s.LastName) AS StudentName,
            s.Email AS StudentEmail,
            d.FileName, 
            d.FileType,
            d.FilePath,
            DATE_FORMAT(d.UploadDate, '%Y-%m-%d %H:%i') AS UploadDateFormatted,
            d.ReviewStatus,
            d.ReviewNotes,
            DATE_FORMAT(d.ReviewDate, '%Y-%m-%d %H:%i') AS ReviewDateFormatted
        FROM 
            DOCUMENT d
        INNER JOIN 
            STUDENT s ON d.StudentID = s.StudentID
        ORDER BY 
            d.UploadDate DESC";

$documents = [];

if ($result = mysqli_query($link, $sql)) {
    while ($row = mysqli_fetch_assoc($result)) {
        $documents[] = $row;
    }
    
    // Calculate summary statistics
    $pending = 0;
    $approved = 0;
    $rejected = 0;
    $underReview = 0;
    
    foreach ($documents as $doc) {
        switch ($doc['ReviewStatus']) {
            case 'Pending':
                $pending++;
                break;
            case 'Approved':
                $approved++;
                break;
            case 'Rejected':
                $rejected++;
                break;
            case 'Under Review':
                $underReview++;
                break;
        }
    }
    
    $response['success'] = true;
    $response['data'] = $documents;
    $response['count'] = count($documents);
    $response['summary'] = [
        'total' => count($documents),
        'pending' => $pending,
        'under_review' => $underReview,
        'approved' => $approved,
        'rejected' => $rejected
    ];
    $response['message'] = 'Documents fetched successfully.';
    mysqli_free_result($result);
} else {
    $response['message'] = 'Database error: ' . mysqli_error($link);
}

mysqli_close($link);
echo json_encode($response);
?>
