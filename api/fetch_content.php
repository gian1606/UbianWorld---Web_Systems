<?php
// api/fetch_content.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once('config.php');

try {
    $type = $_GET['type'] ?? 'all';
    
    if ($type !== 'all') {
        $valid_types = ['Announcement', 'Guide', 'FAQ'];
        if (!in_array($type, $valid_types)) {
            throw new Exception("Invalid content type.");
        }
        
        $sql = "SELECT 
                    ResourceID,
                    Title,
                    Type,
                    ContentText,
                    ContentURL,
                    DatePosted
                FROM CONTENT
                WHERE Type = ?
                ORDER BY DatePosted DESC";
        
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, "s", $type);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $sql = "SELECT 
                    ResourceID,
                    Title,
                    Type,
                    ContentText,
                    ContentURL,
                    DatePosted
                FROM CONTENT
                ORDER BY DatePosted DESC";
        
        $result = mysqli_query($link, $sql);
    }
    
    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($link));
    }

    $content = [];
    while($row = mysqli_fetch_assoc($result)) {
        $content[] = $row;
    }
    
    http_response_code(200);
    echo json_encode(["success" => true, "data" => $content]);
    
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    } else {
        mysqli_free_result($result);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}

mysqli_close($link);
?>
