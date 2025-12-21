<?php
// api/login.php

error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once('config.php');

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Invalid request method.'];

if (DB_CONNECTION_ERROR !== false) {
    $response['message'] = 'Database connection failed. Please try again later.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        $data = $_POST;
    }
    
    $username = trim($data['student_id'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        $response['message'] = 'Please enter both username/ID and password.';
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT UserID, PasswordHash, Role FROM USERS WHERE Username = ?";
    
    if ($stmt = @mysqli_prepare($link, $sql)) {
        @mysqli_stmt_bind_param($stmt, "s", $param_username);
        $param_username = $username;

        if (@mysqli_stmt_execute($stmt)) {
            @mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) == 1) {
                @mysqli_stmt_bind_result($stmt, $user_id, $hashed_password, $role);
                
                if (@mysqli_stmt_fetch($stmt)) {
                    
                    $is_password_correct = password_verify($password, $hashed_password);

                    if ($is_password_correct) {
                        $_SESSION['loggedin'] = true;
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $username;
                        $_SESSION['role'] = $role;

                        $firstName = 'User';
                        $staffId = null;
                        
                        if ($role === 'student') {
                            $sql = "SELECT FirstName FROM STUDENT WHERE UserID = ?";
                        } elseif ($role === 'admin') {
                            $sql = "SELECT FirstName, StaffID FROM STAFF WHERE UserID = ?";
                        }

                        if (isset($sql) && $stmt2 = @mysqli_prepare($link, $sql)) {
                            @mysqli_stmt_bind_param($stmt2, "i", $user_id);
                            if (@mysqli_stmt_execute($stmt2)) {
                                if ($role === 'admin') {
                                    @mysqli_stmt_bind_result($stmt2, $firstName, $staffId);
                                } else {
                                    @mysqli_stmt_bind_result($stmt2, $firstName);
                                }
                                if (@mysqli_stmt_fetch($stmt2)) {
                                    $_SESSION['firstName'] = $firstName;
                                    if ($staffId) {
                                        $_SESSION['staff_id'] = $staffId;
                                    }
                                }
                            }
                            @mysqli_stmt_close($stmt2);
                        }

                        $response['success'] = true;
                        $response['message'] = "Login successful!";
                        $response['role'] = $role;
                        $response['firstName'] = $firstName;
                    } else {
                        $response['message'] = 'Invalid password.';
                    }
                }
            } else {
                $response['message'] = 'Username not found.';
            }
        } else {
            $response['message'] = 'Database error during execution.';
        }

        @mysqli_stmt_close($stmt);
    } else {
        $response['message'] = 'Database query error. Please try again.';
    }
}

@mysqli_close($link);
echo json_encode($response);
?>
