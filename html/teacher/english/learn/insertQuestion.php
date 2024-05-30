<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
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

function insertQuestion($db, $selectedTopic, $question, $answers, $correctAnswer): void
{
    $stmt = $db->prepare("INSERT INTO `$selectedTopic` (question, A, B, C, correct_answer) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $question, $answers[0], $answers[1], $answers[2], $correctAnswer);
    $stmt->execute();
    $stmt->close();
}

$masterDB = getDatabaseConnection();
$selectedTopic = $_GET['topic'] ?? '';
$selectedLecture = $_GET['lecture'] ?? '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!validateCsrfToken()) {
        die('CSRF token validation failed.');
    }
    if (isset($_POST['createQuestion'])) {
        $questionEN = filter_input(INPUT_POST, 'questionEN', FILTER_UNSAFE_RAW);
        $questionSK = filter_input(INPUT_POST, 'questionSK', FILTER_UNSAFE_RAW);
        $answersEN = [
            filter_input(INPUT_POST, 'answerAEN', FILTER_UNSAFE_RAW),
            filter_input(INPUT_POST, 'answerBEN', FILTER_UNSAFE_RAW),
            filter_input(INPUT_POST, 'answerCEN', FILTER_UNSAFE_RAW)
        ];
        $answersSK = [
            filter_input(INPUT_POST, 'answerASK', FILTER_UNSAFE_RAW),
            filter_input(INPUT_POST, 'answerBSK', FILTER_UNSAFE_RAW),
            filter_input(INPUT_POST, 'answerCSK', FILTER_UNSAFE_RAW)
        ];
        $correctAnswer = filter_input(INPUT_POST, 'correctAnswer', FILTER_UNSAFE_RAW);
        list($englishDBName, $slovakDBName) = getTestDatabases($masterDB, $selectedLecture);
        $englishDB = getDatabaseConnection($englishDBName);
        $slovakDB = getDatabaseConnection($slovakDBName);
        insertQuestion($englishDB, $selectedTopic, $questionEN, $answersEN, $correctAnswer);
        insertQuestion($slovakDB, $selectedTopic, $questionSK, $answersSK, $correctAnswer);
        $_SESSION['toast'] = array("status" => "success", "message" => "Question has been successfully added");
        header("Location: " . $_SERVER['PHP_SELF'] . "?topic=$selectedTopic&lecture=$selectedLecture");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Create question</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="/css/teacher/insertQuestion.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="/js/regex.js"></script>
</head>
<body>
<script>
    $(function () {
        <?php if(!empty($_SESSION['toast'])): ?>
        toastr.<?php echo $_SESSION['toast']['status']; ?>('<?php echo $_SESSION['toast']['message']; ?>');
        <?php unset($_SESSION['toast']); ?>
        <?php endif; ?>
    });
</script>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/html/teacher/english/menu.php">E-Learning</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive"
                aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="/html/teacher/english/menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="/html/teacher/english/students.php">Students</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="/html/teacher/english/learn.php">Learn</a></li>
                <li class="nav-item"><a class="nav-link" href="/html/teacher/english/test.php">Test</a></li>
                <li class="nav-item"><a class="nav-link" href="/html/teacher/english/profil.php">Profile</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="/html/teacher/slovensky/learn/insertQuestion.php">Slovak
                        version</a></li>
                <li class="nav-item"><a class="nav-link" href="/php/logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class='container'>
    <div class='question-form'>
        <h3>Create Question</h3>
        <form action="" method="post" class='question-form'>
            <input type="hidden" name="createQuestion" value="true">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <div class="form-group">
                <label for="questionEN">English question:</label>
                <textarea class="form-control" id="questionEN" name="questionEN" required
                          oninput="isValidText(this)"></textarea>
            </div>
            <div class="form-group">
                <label for="questionSK">Slovak question:</label>
                <textarea class="form-control" id="questionSK" name="questionSK" required
                          oninput="isValidText(this)"></textarea>
            </div>
            <div class="form-group">
                <label for="answerAEN">Answer A (in English):</label>
                <textarea class="form-control" id="answerAEN" name="answerAEN" required
                          oninput="isValidText(this)"></textarea>
            </div>
            <div class="form-group">
                <label for="answerASK">Answer A (in Slovak):</label>
                <textarea class="form-control" id="answerASK" name="answerASK" required
                          oninput="isValidText(this)"></textarea>
            </div>
            <div class="form-group">
                <label for="answerBEN">Answer B (in English):</label>
                <textarea class="form-control" id="answerBEN" name="answerBEN" required
                          oninput="isValidText(this)"></textarea>
            </div>
            <div class="form-group">
                <label for="answerBSK">Answer B (in Slovak):</label>
                <textarea class="form-control" id="answerBSK" name="answerBSK" required
                          oninput="isValidText(this)"></textarea>
            </div>
            <div class="form-group">
                <label for="answerCEN">Answer C (in English):</label>
                <textarea class="form-control" id="answerCEN" name="answerCEN" required
                          oninput="isValidText(this)"></textarea>
            </div>
            <div class="form-group">
                <label for="answerCSK">Answer C (in Slovak):</label>
                <textarea class="form-control" id="answerCSK" name="answerCSK" required
                          oninput="isValidText(this)"></textarea>
            </div>
            <div class="form-group">
                <label for="correctAnswer">Correct answer:</label>
                <select class="form-control" id="correctAnswer" name="correctAnswer" required>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                </select>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <button type="submit" name="createQuestion" class="btn btn-success btn-block">Create a question
                    </button>
                </div>
                <div class="col-md-6">
                    <a href="../test.php" class="btn btn-primary btn-block">Back</a>
                </div>
            </div>
        </form>
    </div>
</div>
<footer class='footer text-center footer-dark bg-dark fixed-bottom'>
    <p>© 2023 - 2024 - Bakalárska práca - Vincent Pálfy</p>
</footer>
<script>
    let form = document.querySelector('form');
    form.addEventListener('submit', checkForm);
</script>
</body>
</html>
