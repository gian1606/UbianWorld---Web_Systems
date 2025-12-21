<?php
// api/delete_announcement.php

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
    if (!isset($_POST['content_id'])) {
        throw new Exception("Missing content_id in POST.");
    }

    $content_id = intval($_POST['content_id']);

    // Delete the announcement
    $stmt = mysqli_prepare($link, "DELETE FROM announcements WHERE content_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $content_id);
    mysqli_stmt_execute($stmt);

    if (mysqli_stmt_errno($stmt)) {
        throw new Exception("MySQL Error: " . mysqli_stmt_error($stmt));
    }

    if (mysqli_stmt_affected_rows($stmt) === 0) {
        throw new Exception("Announcement not found or already deleted.");
    }

    echo json_encode([
        "success" => true,
        "message" => "Announcement deleted successfully"
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
