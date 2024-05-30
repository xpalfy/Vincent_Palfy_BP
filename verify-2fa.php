<?php
require 'vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';

use JetBrains\PhpStorm\NoReturn;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$username = filter_var($_POST['username'], FILTER_UNSAFE_RAW) ?? null;
$verificationCode = filter_var($_POST['code'], FILTER_UNSAFE_RAW) ?? null;
$twoFactorSecret = filter_var($_POST['secret'], FILTER_UNSAFE_RAW) ?? null;

if (!$username || !$verificationCode || !$twoFactorSecret) {
    $_SESSION['toast'] = array("status" => "error", "message" => "Invalid request");
    header("Location: setup-2fa.php");
    exit;
}

$conn = getDatabaseConnection();

$g = new GoogleAuthenticator();
$isCodeCorrect = $g->checkCode($twoFactorSecret, $verificationCode);

if ($isCodeCorrect) {
    $cookieName = '2fa_verified_user_' . urlencode($username);
    if (!isset($_COOKIE[$cookieName])) {
        setcookie($cookieName, $username, time() + (86400 * 30), "/");
    }

    $stmt = $conn->prepare("UPDATE users SET two_factor_secret = ? WHERE username = ?");
    $stmt->bind_param("ss", $twoFactorSecret, $username);
    if ($stmt->execute()) {
        $_SESSION['toast'] = array("status" => "success", "message" => "2FA setup complete. You are now logged in.");
        $_SESSION['loggedin'] = true;
        redirectToDashboard($_SESSION['role']);
    } else {
        $_SESSION['toast'] = array("status" => "error", "message" => "Error setting up 2FA");
        header("Location: setup-2fa.php");
    }
    $stmt->close();
    $conn->close();
} else {
    $_SESSION['toast'] = array("status" => "error", "message" => "Invalid verification code");
    header("Location: setup-2fa.php");
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
            header("Location: ./index.php");
            exit;
    }
}

