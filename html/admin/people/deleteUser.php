<?php

use JetBrains\PhpStorm\NoReturn;

require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Admin']);
header('Content-Type: application/json');

#[NoReturn] function deleteAccount($currentUser): void
{
    $conn = getDatabaseConnection();
    deleteLessons($conn, $currentUser);
    deleteUser($conn, $currentUser);
}

function deleteLessons($conn, $currentUser): void
{
    $stmt = $conn->prepare("
        SELECT 
            lesson.id, 
            lesson.test, 
            lesson.pdf, 
            lesson.learn, 
            learn.english_test_database, 
            learn.slovak_test_database
        FROM lesson
        LEFT JOIN learn ON lesson.learn = learn.name
        WHERE lesson.creator = ?
    ");
    $stmt->bind_param("s", $currentUser);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        handlePDF($conn, $row['pdf']);
        deleteLessonResources($conn, $row);
        dropTestTables($row);
    }
    $stmt->close();
}

function handlePDF($db, $pdfPath): void
{
    if (!empty($pdfPath)) {
        $stmtPDF = $db->prepare("SELECT COUNT(*) as cnt FROM lesson WHERE pdf = ?");
        $stmtPDF->bind_param("s", $pdfPath);
        $stmtPDF->execute();
        $countResult = $stmtPDF->get_result();
        $countRow = $countResult->fetch_assoc();
        $stmtPDF->close();
        if ($countRow['cnt'] <= 1) {
            $fullPath = $_SERVER['DOCUMENT_ROOT'] . $pdfPath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }
}

function deleteLessonResources($conn, $lessonRow): void
{
    $topicID = $lessonRow['id'];
    $stmtDel = $conn->prepare("DELETE FROM lesson WHERE id = ?");
    $stmtDel->bind_param("i", $topicID);
    $stmtDel->execute();
    $stmtDel->close();
}

function dropTestTables($lessonRow): void
{
    $testTableName = $lessonRow['test'];
    $databases = [$lessonRow['english_test_database'], $lessonRow['slovak_test_database']];
    foreach ($databases as $dbName) {
        if (!empty($dbName)) {
            $db = getDatabaseConnection($dbName);
            $db->query("DROP TABLE IF EXISTS `$testTableName`");
            $db->close();
        }
    }
}

function deleteUser($conn, $currentUser): void
{
    $cookieName = '2fa_verified_user_' . urlencode($currentUser);
    if (isset($_COOKIE[$cookieName])) {
        unset($_COOKIE[$cookieName]);
        setcookie($cookieName, '', time() - 3600, "/");
    }
    $stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
    $stmt->bind_param("s", $currentUser);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Teacher account has been deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error deleting account']);
    }
    $conn->close();
}

function deleteUserAccount($username): void
{
    $conn = getDatabaseConnection();
    $cookieName = '2fa_verified_user_' . urlencode($username);

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
        if (isset($_COOKIE[$cookieName])) {
            unset($_COOKIE[$cookieName]);
            setcookie($cookieName, '', time() - 3600, "/");
        }

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

        if ($role === "Teacher") {
            deleteAccount($username);
        } else if ($role === "Student") {
            deleteUserAccount($username);
            echo json_encode([
                'status' => 'success',
                'message' => 'Student account has been deleted successfully'
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
    echo json_encode(['status' => 'error', 'message' => 'User ID not provided']);
}