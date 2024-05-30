<?php
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Teacher']);
$conn = getDatabaseConnection();

$query = "SELECT DISTINCT active FROM users WHERE role IN ('Student')";
$result = $conn->query($query);

$statuses = [];
while ($row = $result->fetch_assoc()) {
    $statuses[] = $row['active'] == '1' ? "Active" : "Inactive";
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($statuses);
