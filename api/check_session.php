<?php
// api/check_session.php

session_start();
header('Content-Type: application/json');

$response = [
    'isLoggedIn' => isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true,
    'user_id' => $_SESSION['user_id'] ?? null,
    'username' => $_SESSION['username'] ?? null,
    'role' => $_SESSION['role'] ?? null,
    'staff_id' => $_SESSION['staff_id'] ?? null,
    'firstName' => $_SESSION['firstName'] ?? null
];

echo json_encode($response);
?>
