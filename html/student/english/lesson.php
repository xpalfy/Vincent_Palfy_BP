<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
check(['Student']);

$_SESSION['test_submited'] = false;
$_SESSION['lesson_started'] = true;

if ($_SESSION['test_started']) {
    $_SESSION['toast'] = array("status" => "error", "message" => "The test has already been run.");
    echo "<script> window.history.forward(); </script>";
    exit;
}

$conn = getDatabaseConnection();

function fetchLessonDetails($conn, $lessonId)
{
    $query = "SELECT 
                lesson.pdf, 
                lesson.english_name,
                lesson.test, 
                lesson.learn, 
                lesson.page, 
                learn.english_test_database 
              FROM lesson 
              LEFT JOIN learn ON lesson.learn = learn.name 
              WHERE lesson.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $lessonId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row;
    }
    return false;
}

function checkLastTestAttempt($conn, $lessonId, $username)
{
    $query = "SELECT MAX(time) AS last_attempt, passed 
              FROM results 
              WHERE test_id = ? AND username = ? 
              GROUP BY passed";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $lessonId, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

$lessonId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$username = $_SESSION['username'];

$lessonDetails = fetchLessonDetails($conn, $lessonId);
$attemptDetails = checkLastTestAttempt($conn, $lessonId, $username);

$canStartTest = true;
$waitTime = 0;

if ($attemptDetails && $attemptDetails['last_attempt']) {
    $lastAttemptTime = new DateTime($attemptDetails['last_attempt']);
    $lastAttemptTime->setTimezone(new DateTimeZone('Europe/Bratislava'));
    $lastAttemptTime->sub(new DateInterval('PT1H'));
    $currentTime = new DateTime();
    $currentTime->setTimezone(new DateTimeZone('Europe/Bratislava'));
    $timeDiff = $currentTime->diff($lastAttemptTime);
    $minutesSinceLastAttempt = ($timeDiff->days * 24 * 60) + ($timeDiff->h * 60) + $timeDiff->i;

    if ($minutesSinceLastAttempt < 30) {
        $canStartTest = false;
        $waitTime = 30 - $minutesSinceLastAttempt;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Lesson</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="/css/student/lesson.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="/js/student/lesson.js"></script>
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
        <a class="navbar-brand" href="/html/student/english/menu.php">E-Learning</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive"
                aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="/html/student/english/menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="/html/student/english/learn.php">Learn</a></li>
                <li class="nav-item"><a class="nav-link" href="/html/student/english/profil.php">Profile</a></li>
                <li class="nav-item"><a class="nav-link"
                                        href="/html/student/slovensky/lesson.php?id=<?php echo htmlspecialchars($lessonId); ?>">Slovak
                        version</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="/php/logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-2">
    <?php if ($lessonDetails): ?>
        <div class="card">
            <div class="card-body text-center">
                <div class="pdf-container">
                    <iframe class="embed-responsive-item"
                            src="<?php echo htmlspecialchars($lessonDetails['pdf']) . '#page=' . htmlspecialchars($lessonDetails['page']); ?>"
                            allowfullscreen></iframe>
                </div>
                <button class="btn btn-primary mt-3" style="margin-top: 30px !important;"
                        id="testButton<?php echo $lessonId; ?>"
                        onclick="startTestEnglish(<?php echo $lessonId; ?>, '<?php echo htmlspecialchars($lessonDetails['english_test_database']); ?>',
                                '<?php echo htmlspecialchars($lessonDetails['test']); ?>', <?php echo json_encode($canStartTest); ?>,
                        <?php echo json_encode($waitTime); ?>)">
                    <?php echo htmlspecialchars($lessonDetails['english_name']); ?>
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<footer class="footer text-center footer-dark bg-dark fixed-bottom">
    <p style="color: white;">© 2023 - 2024 - Bakalárska práca 1 - Vincent Pálfy</p>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        <?php if (isset($lessonId) && isset($attemptDetails['passed'])): ?>
        updateButtonStatus(<?php echo $lessonId; ?>, <?php echo json_encode($attemptDetails['passed']); ?>, <?php echo json_encode($waitTime); ?>);
        <?php endif; ?>
    });
</script>
</body>
</html>
