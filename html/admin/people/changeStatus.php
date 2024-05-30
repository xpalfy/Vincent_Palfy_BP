<?php
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Admin']);
$conn = getDatabaseConnection();
header('Content-Type: application/json');

if (isset($_POST['user_id'])) {
    $userId = filter_var($_POST['user_id'], FILTER_VALIDATE_INT);

    $sql = "UPDATE users SET active = IF (active = '1', '0', '1') WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Status changed successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error changing status']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'User ID not provided']);
}

$conn->close();