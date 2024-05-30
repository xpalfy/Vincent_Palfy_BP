<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function check($allowedRoles = []): void
{
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header('Location: /index.php');
        exit;
    }
    $userRole = $_SESSION['role'] ?? 'none';
    if (!in_array($userRole, $allowedRoles)) {
        header('Location: /index.php');
        session_unset();
        session_destroy();
        exit;
    }
}
