<?php
// api/fetch_faqs.php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once('config.php');

try {
    $sql = "SELECT 
                faq_id, 
                question, 
                answer, 
                category,
                display_order
            FROM faqs 
            ORDER BY display_order ASC, faq_id ASC";
    
    $result = mysqli_query($link, $sql);

    if (!$result) {
        throw new Exception("SQL Query failed: " . mysqli_error($link));
    }

    $faqs = [];
    while($row = mysqli_fetch_assoc($result)) {
        $faqs[] = $row;
    }
    
    mysqli_free_result($result);

    http_response_code(200);
    echo json_encode(["success" => true, "data" => $faqs]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}

mysqli_close($link);
?>
