<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Teacher']);
header('Content-type: application/json');

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

function getQuestions($db, $selectedTopic): array
{
    $questions = [];
    if ($selectedTopic) {
        $query = $db->prepare("SELECT id, question FROM `$selectedTopic`");
        $query->execute();
        $result = $query->get_result();
        while ($row = $result->fetch_assoc()) {
            $questions[$row['id']] = $row['question'];
        }
        $query->close();
    }

    return $questions;
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


$masterDB = getDatabaseConnection();
$selectedTopic = $_GET['topic'] ?? '';
$selectedLecture = $_GET['lecture'] ?? '';
list($englishDBName, $slovakDBName) = getTestDatabases($masterDB, $selectedLecture);
$englishDB = getDatabaseConnection($englishDBName);
$slovakDB = getDatabaseConnection($slovakDBName);
$questionsEnglish = getQuestions($englishDB, $selectedTopic);
$questionsSlovak = getQuestions($slovakDB, $selectedTopic);
$num = getCurrentNum($masterDB, $selectedTopic);

$combinedQuestions = [
    'english' => $questionsEnglish,
    'slovak' => $questionsSlovak,
    'num' => $num
];

$masterDB->close();
$englishDB->close();
$slovakDB->close();

echo json_encode($combinedQuestions);
