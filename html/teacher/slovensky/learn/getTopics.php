<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Teacher']);
$masterDB = getDatabaseConnection();
header('Content-type: application/json');

$lecture = $_GET['lecture'] ?? '';

$response = [];

if ($lecture !== '') {
    $stmt = $masterDB->prepare("SELECT id, slovak_name, test FROM lesson WHERE learn = ? AND creator = ?");
    $stmt->bind_param("ss", $lecture, $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
    $stmt->close();
}

echo json_encode($response);
