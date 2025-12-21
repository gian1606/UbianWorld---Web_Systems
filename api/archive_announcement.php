<?php
// api/archive_announcement.php

session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once('config.php');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized. Admin access required."
    ]);
    exit;
}

if (!isset($_POST['id'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Missing required parameter: id"
    ]);
    exit;
}

$content_id = intval($_POST['id']);

try {
    $stmt = mysqli_prepare($link, "UPDATE announcements SET is_archived = 1 WHERE content_id = ? AND page_type = 'announcement'");
    
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . mysqli_error($link));
    }
    
    mysqli_stmt_bind_param($stmt, "i", $content_id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_errno($stmt)) {
        throw new Exception("MySQL Error: " . mysqli_stmt_error($stmt));
    }

    if (mysqli_stmt_affected_rows($stmt) === 0) {
        throw new Exception("Announcement not found or already archived.");
    }

    echo json_encode([
        "success" => true,
        "message" => "Announcement archived successfully"
    ]);

    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

mysqli_close($link);
?>
