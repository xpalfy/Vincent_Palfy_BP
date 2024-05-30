<?php

function generateCsrfToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        try {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } catch (Exception) {
        }
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(): bool
{
    $csrf_token = filter_input(INPUT_POST, 'csrf_token');
    error_log($csrf_token);
    error_log($_SESSION['csrf_token']);
    if ($csrf_token && isset($_SESSION['csrf_token'])) {
        return hash_equals($_SESSION['csrf_token'], $csrf_token);
    }
    return false;
}
    
