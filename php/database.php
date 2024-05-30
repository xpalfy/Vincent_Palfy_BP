<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';

if (!isset($_SESSION['username'])) {
    exit("User not logged in");
}

$name = $_SESSION['username'];
$user = getUser($name);

if ($user) {

    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['telephone'] = $user['telephone'];

}

function getUser($name): bool|array|null
{
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("SELECT first_name, last_name, email, telephone FROM users WHERE username = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $conn->close();
    return $result;
}

