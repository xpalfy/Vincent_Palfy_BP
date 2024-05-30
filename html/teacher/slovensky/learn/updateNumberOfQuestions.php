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

function updateNumQuestions($masterDB, $selectedLecture, $selectedTopic, $numQuestions): bool
{
    list($englishDBName, $slovakDBName) = getTestDatabases($masterDB, $selectedLecture);
    $englishDB = getDatabaseConnection($englishDBName);
    $slovakDB = getDatabaseConnection($slovakDBName);
    $englishQuery = "SELECT COUNT(*) AS questionCount FROM `$selectedTopic`";
    $slovakQuery = "SELECT COUNT(*) AS questionCount FROM `$selectedTopic`";
    $stmtEnglish = $englishDB->prepare($englishQuery);
    $stmtEnglish->execute();
    $resultEnglish = $stmtEnglish->get_result()->fetch_assoc();
    $availableQuestionsEnglish = $resultEnglish['questionCount'];
    $stmtSlovak = $slovakDB->prepare($slovakQuery);
    $stmtSlovak->execute();
    $resultSlovak = $stmtSlovak->get_result()->fetch_assoc();
    $availableQuestionsSlovak = $resultSlovak['questionCount'];
    $availableQuestions = min($availableQuestionsEnglish, $availableQuestionsSlovak);
    if ($numQuestions > $availableQuestions) {
        return false;
    }
    $numQuestions = min($numQuestions, $availableQuestions);
    if ($selectedTopic && $numQuestions > 0) {
        $updateStmt = $masterDB->prepare("UPDATE lesson SET num = ? WHERE test = ?");
        $updateStmt->bind_param("is", $numQuestions, $selectedTopic);
        $updateStmt->execute();
        $updateStmt->close();
    }
    return true;
}

$masterDB = getDatabaseConnection();
$response = ['success' => false, 'message' => 'An unexpected error occurred.'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!validateCsrfToken()) {
        $response['message'] = 'CSRF token validation failed.';
        echo json_encode($response);
        exit;
    }

    $numQuestions = filter_input(INPUT_POST, 'numQuestions', FILTER_SANITIZE_NUMBER_INT);
    $selectedTopic = filter_input(INPUT_POST, 'topic', FILTER_UNSAFE_RAW);
    $selectedLecture = filter_input(INPUT_POST, 'lecture', FILTER_UNSAFE_RAW);

    if (!$numQuestions || !$selectedTopic || !$selectedLecture) {
        $response['message'] = 'Please fill in all the fields.';
        echo json_encode($response);
        exit;
    }

    if (updateNumQuestions($masterDB, $selectedLecture, $selectedTopic, $numQuestions)) {
        $response['success'] = true;
        $response['message'] = 'The number of questions has been successfully updated.';
    } else {
        $response['message'] = 'Failed to update the number of questions.';
    }

    header('Content-type: application/json');
    echo json_encode($response);
    exit;
}
