<?php
// api/fetch_guides.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once('config.php');

try {
    $sql = "SELECT 
                content_id,
                title,
                content_text as description,
                content_url,
                date_posted,
                DATE_FORMAT(date_posted, '%M %d, %Y') as formatted_date
            FROM announcements 
            WHERE page_type = 'guide'
            ORDER BY date_posted DESC";
    
    $result = mysqli_query($link, $sql);

    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($link));
    }

    $guides = [];
    while($row = mysqli_fetch_assoc($result)) {
        $guides[] = $row;
    }
    
    mysqli_free_result($result);

    http_response_code(200);
    echo json_encode(["success" => true, "data" => $guides]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}

mysqli_close($link);
?>
