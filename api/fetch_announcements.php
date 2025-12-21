<?php
// api/fetch_announcements.php

require_once('config.php');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Failed to fetch announcements.'];

// Check if database connection failed
if (DB_CONNECTION_ERROR !== false) {
    $response['message'] = 'Database connection failed.';
    http_response_code(500);
    echo json_encode($response);
    exit;
}

$sql = "SELECT 
            content_id as AnnouncementID,
            title as Title,
            content_text as Content,
            content_url as ContentUrl,
            date_posted as RawDate,
            DATE_FORMAT(date_posted, '%M %d, %Y') as PostedDate,
            is_archived as IsArchived
        FROM announcements 
        WHERE page_type = 'announcement'
        ORDER BY date_posted DESC
        LIMIT 50";

$announcements = [];

if ($result = mysqli_query($link, $sql)) {
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $announcements[] = $row;
        }
        $response['success'] = true;
        $response['announcements'] = $announcements;
        $response['count'] = count($announcements);
        $response['message'] = 'Announcements fetched successfully.';
    } else {
        // No announcements found is not an error, just empty data
        $response['success'] = true;
        $response['announcements'] = [];
        $response['count'] = 0;
        $response['message'] = 'No announcements found.';
    }
    mysqli_free_result($result);
} else {
    $response['message'] = 'Database query failed: ' . mysqli_error($link);
    http_response_code(500);
}

mysqli_close($link);
echo json_encode($response);
?>
