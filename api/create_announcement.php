<?php
// api/create_announcement.php

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

try {
    // Get POST data
    $title = trim($_POST['title'] ?? "");
    $content_text = trim($_POST['content_text'] ?? "");
    $content_url = trim($_POST['content_url'] ?? "#");
    
    // Validate required fields
    if (empty($title) || empty($content_text)) {
        throw new Exception("Title and content are required.");
    }

    // Insert new announcement
    $stmt = mysqli_prepare($link, "
        INSERT INTO announcements (title, content_text, content_url, page_type, date_posted) 
        VALUES (?, ?, ?, 'announcement', NOW())
    ");

    mysqli_stmt_bind_param($stmt, "sss", $title, $content_text, $content_url);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_errno($stmt)) {
        throw new Exception("MySQL Error: " . mysqli_stmt_error($stmt));
    }

    $new_id = mysqli_insert_id($link);

    echo json_encode([
        "success" => true,
        "message" => "Announcement created successfully",
        "announcement_id" => $new_id
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
