<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
check(['Student']);

$_SESSION['test_started'] = false;
$_SESSION['test_submited'] = true;

if ($_SESSION['lesson_started']) {
    $_SESSION['toast'] = array("status" => "error", "message" => "The test has already been evaluated");
    echo "<script> window.history.forward(); </script>";
    exit;
}

$database = filter_input(INPUT_GET, 'database');
$test = filter_input(INPUT_GET, 'test');
$lesson_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

$conn = getDatabaseConnection($database);

if (isset($_POST['action']) && $_POST['action'] == 'clear_and_redirect') {
    unset($_SESSION['questions']);
    unset($_SESSION['user_answers']);
    $_SESSION['test_submited'] = false;
    $_SESSION["lesson_started"] = true;

    $lesson_id = filter_input(INPUT_POST, 'lesson_id', FILTER_SANITIZE_NUMBER_INT);
    header('Location: /html/student/slovensky/lesson.php?id=' . urlencode($lesson_id));
    exit;
}

$userAnswers = $_SESSION['user_answers'] ?? [];
$questions = $_SESSION['questions'] ?? [];
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Test Results</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="/css/student/test_results.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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
    <h1 class="mt-5">Výsledky testov</h1>
    <?php foreach ($questions as $question) : ?>
        <?php
        $id = $question['id'];
        $userAnswer = $userAnswers[$id] ?? '';
        ?>
        <div class='question'>
            <p><b>Otázka:</b> <?= htmlspecialchars($question["question"]) ?></p>
            <?php foreach (['A', 'B', 'C'] as $option) : ?>
                <?php
                $optionText = htmlspecialchars($question[$option]);
                if ($option === $question['correct_answer']) {
                    $class = 'correct';
                    $label = 'Correct Answer';
                } elseif ($option === $userAnswer) {
                    $class = 'incorrect';
                    $label = 'Your Answer';
                } else {
                    $class = '';
                    $label = '';
                }
                ?>
                <p class="<?= $class ?>"><?= $label ?>: <?= $option ?>: <?= $optionText ?></p>
            <?php endforeach; ?>
            <?php if ($userAnswer === $question['correct_answer']) : ?>
                <p class='correct'>Vaša odpoveď je správna.</p>
            <?php elseif ($userAnswer) : ?>
                <p class='incorrect'>Vaša odpoveď je nesprávna.</p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <div class="row">
        <div class="col-6">
            <form action="" method="POST">
                <input type="hidden" name="action" value="clear_and_redirect">
                <input type="hidden" name="lesson_id" value="<?= htmlspecialchars($lesson_id) ?>">
                <button type="submit" class="btn btn-primary btn-block">Návrat na lekciu</button>
            </form>
        </div>
        <div class="col-6">
            <button onclick="window.print();" class="btn btn-secondary btn-block">Vytlačiť túto stránku</button>
        </div>
    </div>
</div>


</body>
</html>

<?php
$conn->close();
?>
