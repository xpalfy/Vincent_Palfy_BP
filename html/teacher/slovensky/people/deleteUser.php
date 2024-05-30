<?php
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Teacher']);
header('Content-Type: application/json');

function deleteUserAccount($username): void
{
    $cookieName = '2fa_verified_user_' . urlencode($username);
    if (isset($_COOKIE[$cookieName])) {
        unset($_COOKIE[$cookieName]);
        setcookie($cookieName, '', time() - 3600, "/");
    }
    $conn = getDatabaseConnection();

    try {
        $stmtResults = $conn->prepare("DELETE FROM results WHERE username = ?");
        if ($stmtResults === false) {
            throw new Exception("Prepare statement failed for deleting results");
        }
        $stmtResults->bind_param("s", $username);
        $stmtResults->execute();
        $stmtResults->close();

        $stmtDeleteUser = $conn->prepare("DELETE FROM users WHERE username = ?");
        if ($stmtDeleteUser === false) {
            throw new Exception("Prepare statement failed for deleting user");
        }
        $stmtDeleteUser->bind_param("s", $username);
        $stmtDeleteUser->execute();
        $stmtDeleteUser->close();

        $conn->close();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Error: " . $e->getMessage()]);
    }
}

if (isset($_POST['user_id'])) {
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    $conn = getDatabaseConnection();

    try {
        $stmt = $conn->prepare("SELECT role, username FROM users WHERE id = ?");
        if ($stmt === false) {
            throw new Exception("Prepare statement failed");
        }
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($role, $username);
        $stmt->fetch();
        $stmt->close();

        if ($role === "Student") {
            deleteUserAccount($username);
            echo json_encode([
                'status' => 'success',
                'message' => 'Student account has been successfully deleted'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Only teachers or students can be deleted.']);
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Error: " . $e->getMessage()]);
    } finally {
        $conn->close();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'User ID has not been provided']);
}


