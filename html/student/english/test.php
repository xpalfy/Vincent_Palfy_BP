<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';

check(['Student']);

$_SESSION['test_started'] = true;
$_SESSION['lesson_started'] = false;

if ($_SESSION['test_submited']) {
    $_SESSION['toast'] = array("status" => "error", "message" => "The test has already been completed");
    echo "<script> window.history.forward(); </script>";
    exit;
}

$database = filter_input(INPUT_GET, 'database');
$test = filter_input(INPUT_GET, 'test');
$lesson_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$conn = getDatabaseConnection($database);

function deleteLastTestAttempt($conn, $lessonId, $username): void
{
    $query = "DELETE FROM results WHERE test_id = ? AND username = ? ORDER BY time DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $lessonId, $username);
    $stmt->execute();
    $stmt->close();
}

$learningConn = getDatabaseConnection();
deleteLastTestAttempt($learningConn, $lesson_id, $_SESSION['username']);

function getNumQuestions($learningConn, $lesson_id)
{
    $stmt = $learningConn->prepare("SELECT num FROM lesson WHERE id = ?");
    $stmt->bind_param("i", $lesson_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result ? $result['num'] : 0;
}

function generateQuestions($conn, $test, $numQuestions, $lesson_id): void
{
    if ($numQuestions > 0) {
        $query = "SELECT * FROM `$test` ORDER BY RAND() LIMIT ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $numQuestions);
        $stmt->execute();
        $result = $stmt->get_result();
        $_SESSION['questions'] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $_SESSION['test_started'] = false;
        $_SESSION['test_submited'] = false;
        $_SESSION['toast'] = array("status" => "error", "message" => "No questions are available for this test.");
        header("Location: /html/student/english/lesson.php?id=$lesson_id");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Test</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="/css/student/test.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
</head>
<script>
    $(function () {
        <?php if(!empty($_SESSION['toast'])): ?>
        toastr.<?php echo $_SESSION['toast']['status']; ?>('<?php echo $_SESSION['toast']['message']; ?>');
        <?php unset($_SESSION['toast']); ?>
        <?php endif; ?>
    });
</script>
<body>
<div class="container">
    <h1 class="mt-5">Test</h1>
    <form action="test.php?id=<?php echo urlencode($lesson_id); ?>&database=<?php echo urlencode($database); ?>&test=<?php echo urlencode($test); ?>"
          method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <?php
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if (!isset($_SESSION['questions'])) {
                $numQuestions = getNumQuestions($learningConn, $lesson_id);
                generateQuestions($conn, $test, $numQuestions, $lesson_id);
            }

            foreach ($_SESSION['questions'] as $row) {
                ?>
                <div class="question">
                    <p><b>Question:</b> <?php echo htmlspecialchars($row["question"]); ?></p>
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                   name="answer[<?php echo htmlspecialchars($row["id"]); ?>]" value="A"
                                   id="answerA<?php echo htmlspecialchars($row["id"]); ?>">
                            <label class="form-check-label" for="answerA<?php echo htmlspecialchars($row["id"]); ?>">A:
                                <?php echo htmlspecialchars($row["A"]); ?></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                   name="answer[<?php echo htmlspecialchars($row["id"]); ?>]" value="B"
                                   id="answerB<?php echo htmlspecialchars($row["id"]); ?>">
                            <label class="form-check-label" for="answerB<?php echo htmlspecialchars($row["id"]); ?>">B:
                                <?php echo htmlspecialchars($row["B"]); ?></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                   name="answer[<?php echo htmlspecialchars($row["id"]); ?>]" value="C"
                                   id="answerC<?php echo htmlspecialchars($row["id"]); ?>">
                            <label class="form-check-label" for="answerC<?php echo htmlspecialchars($row["id"]); ?>">C:
                                <?php echo htmlspecialchars($row["C"]); ?></label>
                        </div>
                    </div>
                </div>
                <?php
            }
        }
        ?>
        <div class="row">
            <div class="col-6">
                <button type="submit" class="btn btn-primary btn-block">Submit Answers</button>
            </div>
        </div>

        <div class="space"></div>

    </form>
</div>
</body>
</html>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!validateCsrfToken()) {
        die("CSRF token validation failed.");
    }
    $answers = $_POST['answer'];
    $score = 0;
    $_SESSION['user_answers'] = $answers;

    foreach ($answers as $id => $answer) {
        $stmt = $conn->prepare("SELECT correct_answer FROM `$test` WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result && $result['correct_answer'] == $answer) {
            $score++;
        }
        $stmt->close();
    }


    $totalQuestions = count($_SESSION['questions']);
    $percentageScore = ($score / $totalQuestions) * 100;

    $passed = $percentageScore >= 75 ? 1 : 0;

    $userDbConn = getDatabaseConnection();

    $username = $_SESSION['username'];
    $insertStmt = $userDbConn->prepare("INSERT INTO results (test_id, username, points, passed) VALUES (?, ?, ?, ?)");
    $insertStmt->bind_param("isii", $lesson_id, $username, $score, $passed);
    if (!$insertStmt->execute()) {
        echo "Error storing test results: " . $userDbConn->error;
    }
    $insertStmt->close();

    header('Location: test_results.php?id=' . urlencode($lesson_id) . '&database=' . urlencode($database) . '&test=' . urlencode($test));
    $conn->close();
    $learningConn->close();
    $userDbConn->close();
    exit();
}
?>
