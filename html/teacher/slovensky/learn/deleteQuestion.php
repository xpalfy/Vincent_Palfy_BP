<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Teacher']);

function getTestDatabases($masterDB, $selectedLecture): array
{
    $englishDBName = $slovakDBName = "";
    if ($selectedLecture) {
        $stmt = $masterDB->prepare("SELECT english_test_database, slovak_test_database FROM learn WHERE name = ?");
        $stmt->bind_param("s", $selectedLecture);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $englishDBName = $row['english_test_database'];
            $slovakDBName = $row['slovak_test_database'];
        }
        $stmt->close();
    }
    return [$englishDBName, $slovakDBName];
}

function deleteQuestion($db, $selectedTopic, $questionID): void
{
    $stmt = $db->prepare("DELETE FROM `$selectedTopic` WHERE id = ?");
    $stmt->bind_param("i", $questionID);
    $stmt->execute();
    $stmt->close();
}

function getCurrentNum($masterDB, $selectedTopic)
{
    $num = 0;
    if ($selectedTopic) {
        $stmt = $masterDB->prepare("SELECT num FROM lesson WHERE test = ?");
        $stmt->bind_param("s", $selectedTopic);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $num = $row['num'];
        }
        $stmt->close();
    }
    return $num;
}

function updateNum($db, $masterDB, $selectedTopic): void
{
    echo $selectedTopic;
    $stmt = $db->prepare("SELECT COUNT(*) AS questionCount FROM `$selectedTopic`");
    $stmt->execute();
    $result = $stmt->get_result();
    $availableQuestions = $result->fetch_assoc()['questionCount'];
    $stmt->close();
    $currentNum = getCurrentNum($masterDB, $selectedTopic);
    if ($currentNum < $availableQuestions) {
        $availableQuestions = $currentNum;
    }
    $updateStmt = $masterDB->prepare("UPDATE lesson SET num = ? WHERE test = ?");
    $updateStmt->bind_param("is", $availableQuestions, $selectedTopic);
    $updateStmt->execute();
    $updateStmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deleteQuestion'])) {
    if (validateCsrfToken()) {
        $masterDB = getDatabaseConnection();
        $questionID = filter_input(INPUT_POST, 'questionID', FILTER_SANITIZE_NUMBER_INT);
        $selectedTopic = filter_input(INPUT_POST, 'topicName', FILTER_UNSAFE_RAW);
        $selectedLecture = filter_input(INPUT_POST, 'lectureName', FILTER_UNSAFE_RAW);
        list($englishDBName, $slovakDBName) = getTestDatabases($masterDB, $selectedLecture);
        $englishDB = getDatabaseConnection($englishDBName);
        $slovakDB = getDatabaseConnection($slovakDBName);
        deleteQuestion($englishDB, $selectedTopic, $questionID);
        deleteQuestion($slovakDB, $selectedTopic, $questionID);
        updateNum($englishDB, $masterDB, $selectedTopic);
        exit;
    }
}
