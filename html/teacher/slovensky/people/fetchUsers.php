<?php
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Teacher']);
$conn = getDatabaseConnection();

$sql = "SELECT 
            u.id, 
            u.username, 
            u.email, 
            u.first_name, 
            u.last_name, 
            u.role, 
            u.active
        FROM users AS u
        WHERE u.role IN ('Student')";

$result = $conn->query($sql);

$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['active'] = $row['active'] == 1 ? "Active" : "Inactive";
        $row['actions'] = "<div class='btn-group' role='group'>
                            <button class='editBtn btn btn-info' data-id='{$row['id']}'>Zmeniť stav</button>
                            <button class='deleteBtn btn btn-danger' data-id='{$row['id']}'>Odstrániť</button>
                          </div>";
        $data[] = $row;
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode(['data' => $data]);
