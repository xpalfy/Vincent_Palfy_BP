<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';

check(['Teacher']);

$masterDB = getDatabaseConnection();
$selectedTopic = $_GET['topic'] ?? '';
$selectedLecture = $_GET['lecture'] ?? '';
$questionID = $_GET['id'] ?? '';
$questionData = ['en' => ['question' => '', 'A' => '', 'B' => '', 'C' => '', 'correct_answer' => ''],
    'sk' => ['question' => '', 'A' => '', 'B' => '', 'C' => '', 'correct_answer' => '']];

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


function fetchQuestionDetails($englishDB, $slovakDB, $selectedTopic, $questionID): array
{
    $stmtEN = $englishDB->prepare("SELECT * FROM `$selectedTopic` WHERE id = ?");
    $stmtEN->bind_param("s", $questionID);
    $stmtEN->execute();
    $resultEN = $stmtEN->get_result();
    $dataEN = $resultEN->fetch_assoc();

    $stmtSK = $slovakDB->prepare("SELECT * FROM `$selectedTopic` WHERE id = ?");
    $stmtSK->bind_param("s", $questionID);
    $stmtSK->execute();
    $resultSK = $stmtSK->get_result();
    $dataSK = $resultSK->fetch_assoc();

    $stmtEN->close();
    $stmtSK->close();

    return ['en' => $dataEN, 'sk' => $dataSK];
}

function updateQuestion($db, $selectedTopic, $question, $answers, $correctAnswer, $id): void
{
    $stmt = $db->prepare("UPDATE `$selectedTopic` SET question = ?, A = ?, B = ?, C = ?, correct_answer = ? WHERE id = ?");
    $stmt->bind_param("ssssss", $question, $answers[0], $answers[1], $answers[2], $correctAnswer, $id);
    $stmt->execute();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!validateCsrfToken()) {
        die('CSRF token validation failed.');
    }
    if (isset($_POST['updateQuestion'])) {
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
        updateQuestion($englishDB, $selectedTopic, $questionEN, $answersEN, $correctAnswer, $questionID);
        updateQuestion($slovakDB, $selectedTopic, $questionSK, $answersSK, $correctAnswer, $questionID);
        $_SESSION['toast'] = array("status" => "success", "message" => "Question has been successfully updated");
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=$questionID&topic=$selectedTopic&lecture=$selectedLecture");
        exit;
    }
}

if ($questionID) {
    list($englishDBName, $slovakDBName) = getTestDatabases($masterDB, $selectedLecture);
    $englishDB = getDatabaseConnection($englishDBName);
    $slovakDB = getDatabaseConnection($slovakDBName);
    $questionData = fetchQuestionDetails($englishDB, $slovakDB, $selectedTopic, $questionID);
}

?>


<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Vytváranie otázok</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="/css/teacher/insertQuestion.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="/js/regex.js"></script>
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
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/html/teacher/slovensky/menu.php">E-Learning</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive"
                aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="/html/teacher/slovensky/menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="/html/teacher/slovensky/students.php">Študenti</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="/html/teacher/slovensky/learn.php">Látky</a></li>
                <li class="nav-item"><a class="nav-link" href="/html/teacher/slovensky/test.php">Test</a></li>
                <li class="nav-item"><a class="nav-link" href="/html/teacher/slovensky/profil.php">Profil</a>
                </li>
                <li class="nav-item"><a class="nav-link"
                                        href="/html/teacher/english/learn/changeQuestion.php?id=<?php echo $questionID; ?>&topic=<?php echo $selectedTopic; ?>&lecture=<?php echo $selectedLecture; ?>">Anglická
                        verzia</a>
                <li class="nav-item"><a class="nav-link" href="/php/logout.php">Odhlásiť sa</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class='container'>
    <div class='question-form'>
        <h3>Vytvoriť otázku</h3>
        <form action="" method="post" class='question-form'>
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <div class="form-group">
                <label for="questionEN">Anglická otázka:</label>
                <textarea class="form-control" id="questionEN" name="questionEN"
                          required oninput="isValidText(this)"><?php echo $questionData['en']['question']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="questionSK">Slovenská otázka:</label>
                <textarea class="form-control" id="questionSK" name="questionSK"
                          required oninput="isValidText(this)"><?php echo $questionData['sk']['question']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="answerAEN">Odpoveď A (anglicky):</label>
                <textarea class="form-control" id="answerAEN" name="answerAEN"
                          required oninput="isValidText(this)"><?php echo $questionData['en']['A']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="answerASK">Odpoveď A (slovensky):</label>
                <textarea class="form-control" id="answerASK" name="answerASK"
                          required oninput="isValidText(this)"><?php echo $questionData['sk']['A']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="answerBEN">Odpoveď B (anglicky):</label>
                <textarea class="form-control" id="answerBEN" name="answerBEN"
                          required oninput="isValidText(this)"><?php echo $questionData['en']['B']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="answerBSK">Odpoveď B (slovensky):</label>
                <textarea class="form-control" id="answerBSK" name="answerBSK"
                          required oninput="isValidText(this)"><?php echo $questionData['sk']['B']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="answerCEN">Odpoveď C (anglicky):</label>
                <textarea class="form-control" id="answerCEN" name="answerCEN"
                          required oninput="isValidText(this)"><?php echo $questionData['en']['C']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="answerCSK">Odpoveď C (slovensky):</label>
                <textarea class="form-control" id="answerCSK" name="answerCSK"
                          required oninput="isValidText(this)"><?php echo $questionData['sk']['C']; ?></textarea>
            </div>
            <div class="form-group">
                <label for="correctAnswer">Správna odpoveď:</label>
                <select class="form-control" id="correctAnswer" name="correctAnswer" required>
                    <option value="A" <?php echo $questionData['en']['correct_answer'] == 'A' ? 'selected' : ''; ?>>A
                    </option>
                    <option value="B" <?php echo $questionData['en']['correct_answer'] == 'B' ? 'selected' : ''; ?>>B
                    </option>
                    <option value="C" <?php echo $questionData['en']['correct_answer'] == 'C' ? 'selected' : ''; ?>>C
                    </option>
                </select>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <button type="submit" name="updateQuestion" class="btn btn-success btn-block">Zmeniť otázku
                    </button>
                </div>
                <div class="col-md-6">
                    <a href="../test.php" class="btn btn-primary btn-block">Späť</a>
                </div>
            </div>
        </form>
    </div>
</div>
<footer class='footer text-center footer-dark bg-dark fixed-bottom'>
    <p>© 2023 - 2024 - Bakalárska práca 1 - Vincent Pálfy</p>
</footer>

</body>
</html>
