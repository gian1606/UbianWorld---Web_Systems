<?php
// api/signup.php

require_once('config.php');
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Registration failed.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = trim($_POST['student_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $nationality = trim($_POST['nationality'] ?? '');

    if (empty($username) || empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
        $response['message'] = 'All required fields must be filled out.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please provide a valid email address.';
        echo json_encode($response);
        exit;
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $check_sql = "SELECT UserID FROM USERS WHERE Username = ?";
    if ($stmt = mysqli_prepare($link, $check_sql)) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $response['message'] = 'The Student ID is already registered.';
            mysqli_stmt_close($stmt);
            echo json_encode($response);
            exit;
        }
        mysqli_stmt_close($stmt);
    }

    $check_sql = "SELECT StudentID FROM STUDENT WHERE Email = ?";
    if ($stmt = mysqli_prepare($link, $check_sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $response['message'] = 'The email address is already in use.';
            mysqli_stmt_close($stmt);
            echo json_encode($response);
            exit;
        }
        mysqli_stmt_close($stmt);
    }

    $link->begin_transaction();
    $role = 'student';
    $users_sql = "INSERT INTO USERS (Username, PasswordHash, Role) VALUES (?, ?, ?)";
    
    if ($stmt_users = mysqli_prepare($link, $users_sql)) {
        mysqli_stmt_bind_param($stmt_users, "sss", $username, $hashed_password, $role);
        
        if (mysqli_stmt_execute($stmt_users)) {
            $new_user_id = mysqli_insert_id($link);

            $student_sql = "INSERT INTO STUDENT (UserID, FirstName, LastName, Email, Nationality) VALUES (?, ?, ?, ?, ?)";
            
            if ($stmt_student = mysqli_prepare($link, $student_sql)) {
                mysqli_stmt_bind_param($stmt_student, "issss", $new_user_id, $firstName, $lastName, $email, $nationality);
                
                if (mysqli_stmt_execute($stmt_student)) {
                    $link->commit();
                    $response['success'] = true;
                    $response['message'] = 'Registration successful! You can now log in.';
                } else {
                    $link->rollback();
                    $response['message'] = 'Registration failed during student data insertion.';
                }
                mysqli_stmt_close($stmt_student);
            } else {
                $link->rollback();
                $response['message'] = 'Database error: Could not prepare student insertion.';
            }
        } else {
            $response['message'] = 'Database error: Could not register user ID.';
        }
        mysqli_stmt_close($stmt_users);
    } else {
        $response['message'] = 'Database error: Could not prepare user insertion.';
    }

}

mysqli_close($link);
echo json_encode($response);
?>
