<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!validateCsrfToken()) {
        die("CSRF token validation failed.");
    }

    $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_UNSAFE_RAW);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_UNSAFE_RAW);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telephone = filter_input(INPUT_POST, 'telephone', FILTER_UNSAFE_RAW);
    $role = filter_input(INPUT_POST, 'role', FILTER_UNSAFE_RAW);
    $validRoles = ["Student", "Teacher"];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['toast'] = array("status" => "error", "message" => "Invalid email address");
        header("Location: ../html/register.php");
        exit;
    }

    if (!in_array($role, $validRoles)) {
        $_SESSION['toast'] = array("status" => "error", "message" => "Invalid role specified");
        header("Location: ../html/register.php");
        exit;
    }

    $conn = getDatabaseConnection();

    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['toast'] = array("status" => "error", "message" => "Username is already taken");
        header("Location: ../html/register.php");
        $stmt->close();
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $active = ($role == "Student") ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, email, telephone, role, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssi", $username, $hashed_password, $first_name, $last_name, $email, $telephone, $role, $active);

    if ($stmt->execute()) {
        $_SESSION['toast'] = array("status" => "success", "message" => "User added successfully");
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;
        header("Location: ../index.php");
    } else {
        $_SESSION['toast'] = array("status" => "error", "message" => "Error adding user to database");
        header("Location: ../html/register.php");
    }
    $stmt->close();
    $conn->close();
} else {
    $_SESSION['toast'] = array("status" => "error", "message" => "Invalid request method");
    header("Location: ../html/register.php");
    session_destroy();
}