<?php
// api/update_announcement.php

session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

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
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input) {
        $input = $_POST;
    }

    $content_id = $input['content_id'] ?? null;
    $title = trim($input['title'] ?? "");
    $content_text = trim($input['content_text'] ?? "");
    $content_url = trim($input['content_url'] ?? "");

    $isCreate = empty($content_id) || $content_id === "0" || $content_id === 0;

    if ($isCreate) {
        // CREATE NEW ANNOUNCEMENT
        if (empty($title) || empty($content_text)) {
            throw new Exception("Title and content are required.");
        }

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
    } else {
        // UPDATE EXISTING ANNOUNCEMENT
        $content_id = intval($content_id);
        
        $stmt_old = mysqli_prepare($link, "SELECT title, content_text, content_url FROM announcements WHERE content_id = ?");
        mysqli_stmt_bind_param($stmt_old, "i", $content_id);
        mysqli_stmt_execute($stmt_old);
        $result = mysqli_stmt_get_result($stmt_old);
        $old = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt_old);

        if (!$old) {
            throw new Exception("Record not found for content_id = " . $content_id);
        }

        if ($title === "") $title = $old["title"];
        if ($content_text === "") $content_text = $old["content_text"];
        if ($content_url === "") $content_url = $old["content_url"];

        $stmt = mysqli_prepare($link, "
            UPDATE announcements 
            SET title = ?, content_text = ?, content_url = ?
            WHERE content_id = ?
        ");

        mysqli_stmt_bind_param($stmt, "sssi", $title, $content_text, $content_url, $content_id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_errno($stmt)) {
            throw new Exception("MySQL Error: " . mysqli_stmt_error($stmt));
        }

        echo json_encode([
            "success" => true,
            "message" => "Announcement updated successfully",
            "updated_data" => [
                "content_id" => $content_id,
                "title" => $title,
                "content_text" => $content_text,
                "content_url" => $content_url
            ]
        ]);

        mysqli_stmt_close($stmt);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

mysqli_close($link);
?>
