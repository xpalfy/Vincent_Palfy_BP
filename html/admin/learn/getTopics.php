<?php
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Admin']);
$db = getDatabaseConnection();

$lecture = $_GET['lecture'] ?? '';

$response = [];
if ($lecture !== '') {
    $stmt = $db->prepare("SELECT id, english_name FROM lesson WHERE learn = ?");
    $stmt->bind_param("s", $lecture);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $response[] = $row;
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($response);
