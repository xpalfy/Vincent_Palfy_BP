<?php
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Teacher']);
$conn = getDatabaseConnection();
header('Content-Type: application/json');

if (isset($_POST['user_id'])) {
    $userId = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);

    $sql = "UPDATE users SET active = IF (active = '1', '0', '1') WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'The status has been successfully changed']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Status change error']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'User ID has not been provided']);
}

$conn->close();