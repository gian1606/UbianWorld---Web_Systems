<?php
// api/manage_form.php

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
    $description = trim($input['description'] ?? "");
    $download_url = trim($input['download_url'] ?? "");

    $isCreate = empty($content_id) || $content_id === "0" || $content_id === 0;

    if ($isCreate) {
        // CREATE NEW FORM
        if (empty($title) || empty($description) || empty($download_url)) {
            throw new Exception("Title, description, and download URL are required.");
        }

        $stmt = mysqli_prepare($link, "
            INSERT INTO announcements (title, content_text, content_url, page_type, date_posted) 
            VALUES (?, ?, ?, 'form', NOW())
        ");

        mysqli_stmt_bind_param($stmt, "sss", $title, $description, $download_url);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_errno($stmt)) {
            throw new Exception("MySQL Error: " . mysqli_stmt_error($stmt));
        }

        $new_id = mysqli_insert_id($link);

        echo json_encode([
            "success" => true,
            "message" => "Form created successfully",
            "form_id" => $new_id
        ]);

        mysqli_stmt_close($stmt);
    } else {
        // UPDATE EXISTING FORM
        $content_id = intval($content_id);
        
        if (empty($title) || empty($description) || empty($download_url)) {
            throw new Exception("Title, description, and download URL are required.");
        }

        $stmt = mysqli_prepare($link, "
            UPDATE announcements 
            SET title = ?, content_text = ?, content_url = ?
            WHERE content_id = ? AND page_type = 'form'
        ");

        mysqli_stmt_bind_param($stmt, "sssi", $title, $description, $download_url, $content_id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_errno($stmt)) {
            throw new Exception("MySQL Error: " . mysqli_stmt_error($stmt));
        }

        echo json_encode([
            "success" => true,
            "message" => "Form updated successfully"
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
