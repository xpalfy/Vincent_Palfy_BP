<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';

$username = $_SESSION['username'] ?? '';
$password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
$first_name = filter_input(INPUT_POST, 'first_name', FILTER_UNSAFE_RAW);
$last_name = filter_input(INPUT_POST, 'last_name', FILTER_UNSAFE_RAW);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$telephone = filter_input(INPUT_POST, 'telephone', FILTER_UNSAFE_RAW);

if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
    $_SESSION['toast'] = array("status" => "error", "message" => "Invalid email address");
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}

function changeDataForUser($username, $password, $first_name, $last_name, $email, $telephone): void
{
    $conn = getDatabaseConnection();
    $conn->autocommit(FALSE);
    try {
        $updateParts = [];
        $types = "";
        $params = [];
        if ($password !== false && $password !== null) {
            $updateParts[] = "password=?";
            $types .= "s";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }
        if ($first_name !== false) {
            $updateParts[] = "first_name=?";
            $types .= "s";
            $params[] = $first_name;
        }
        if ($last_name !== false) {
            $updateParts[] = "last_name=?";
            $types .= "s";
            $params[] = $last_name;
        }
        if ($email !== false && $email !== null) {
            $updateParts[] = "email=?";
            $types .= "s";
            $params[] = $email;
        }
        if ($telephone !== false) {
            $updateParts[] = "telephone=?";
            $types .= "s";
            $params[] = $telephone;
        }
        if (!empty($updateParts)) {
            $sql = "UPDATE users SET " . implode(', ', $updateParts) . " WHERE username=?";
            $types .= "s";
            $params[] = $username;
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $stmt->close();
        }
        $conn->commit();
        $conn->close();
        $_SESSION['toast'] = array("status" => "success", "message" => "User data updated successfully");
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    } catch (Exception) {
        $conn->rollback();
        $conn->close();
        $_SESSION['toast'] = array("status" => "error", "message" => "Error updating user data");
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
}

changeDataForUser($username, $password, $first_name, $last_name, $email, $telephone);
