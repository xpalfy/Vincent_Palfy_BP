<?php
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Admin']);
$conn = getDatabaseConnection();

$query = "SELECT DISTINCT role FROM users WHERE role IN ('Teacher', 'Student') ORDER BY role";
$result = $conn->query($query);

$roles = [];
while ($row = $result->fetch_assoc()) {
    $roles[] = $row['role'];
}

$conn->close();
header('Content-Type: application/json');
echo json_encode($roles);
