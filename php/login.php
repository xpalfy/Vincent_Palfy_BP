<?php

use JetBrains\PhpStorm\NoReturn;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!validateCsrfToken()) {
        die("CSRF token validation failed.");
    }

    $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);

    [$userRole, $isActive, $twoFactorSecret] = validateUser($username, $password);

    if ($isActive === false) {
        $_SESSION['toast'] = array("status" => "error", "message" => "Account is inactive");
        header("Location: ../index.php");
        exit();
    }

    if ($userRole) {
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $userRole;

        $cookieName = '2fa_verified_user_' . urlencode($username);
        if (isset($_COOKIE[$cookieName]) && $_COOKIE[$cookieName] === $username) {
            redirectToDashboard($userRole);
        } else {
            if (empty($twoFactorSecret)) {
                header("Location: ../setup-2fa.php");
            } else {
                header("Location: ../login_2FA/verify-2fa.php");
            }
            exit;
        }
    } else {
        $_SESSION['toast'] = array("status" => "error", "message" => "Invalid username or password");
        header("Location: ../index.php");
        exit();
    }
} else {
    $_SESSION['toast'] = array("status" => "error", "message" => "Invalid request method");
    header("Location: ../index.php");
    exit();
}

function validateUser($username, $password): array
{
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("SELECT password, role, active, two_factor_secret FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($user['active'] == 1) {
                return [$user['role'], true, $user['two_factor_secret']];
            } else {
                return [null, false, null];
            }
        }
    }

    $stmt->close();
    $conn->close();
    return [false, null, null];
}

#[NoReturn] function redirectToDashboard($userRole): void
{
    switch ($userRole) {
        case 'Admin':
            header("Location: ../html/admin/menu.php");
            exit;
        case 'Teacher':
            header("Location: ../html/teacher/slovensky/menu.php");
            exit;
        case 'Student':
            header("Location: ../html/student/slovensky/menu.php");
            exit;
        default:
            header("Location: index.php");
            exit();
    }
}

