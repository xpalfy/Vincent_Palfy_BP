<?php

use JetBrains\PhpStorm\NoReturn;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
check(['Teacher']);

#[NoReturn] function deleteAccount(): void
{
    $currentUser = filter_var($_SESSION['username']);
    $conn = getDatabaseConnection();
    deleteLessons($conn, $currentUser);
    deleteUser($conn, $currentUser);
    exit();
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
        session_destroy();
        header("Location: /index.php");
        exit();
    } else {
        echo "Error deleting account";
    }
    $conn->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    if (!validateCsrfToken()) {
        die("CSRF token validation failed.");
    }
    deleteAccount();
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="/css/teacher/profil.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <li class="nav-item"><a class="nav-link" href="/html/teacher/slovensky/profil.php">Slovak version</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="/php/logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container custom-container">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Profile information</div>
                <div class="card-body text-center">
                    <img src="/img/Teacher.png" alt="profile picture" id="profile_picture"
                         class="img-fluid profile-picture mb-3">
                    <p class="card-text">First name: <?php echo htmlspecialchars($_SESSION['first_name']); ?></p>
                    <p class="card-text">Last name: <?php echo htmlspecialchars($_SESSION['last_name']); ?></p>
                    <p class="card-text">Email: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                    <p class="card-text">Telephone: <?php echo htmlspecialchars($_SESSION['telephone']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Edit profile</div>
                <div class="card-body">
                    <h5 class="card-title">Profile</h5>
                    <form action="/php/changeData.php" method="post" class="mb-4">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="form-group">
                            <label for="first_name">First name:</label>
                            <input type="text" name="first_name" id="first_name" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($_SESSION['first_name']); ?>" required
                                   oninput="isValidName(this)">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last name:</label>
                            <input type="text" name="last_name" id="last_name" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($_SESSION['last_name']); ?>" required
                                   oninput="isValidName(this)">
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" id="email" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($_SESSION['email']); ?>" required
                                   oninput="isValidEmail(this)">
                        </div>
                        <div class="form-group">
                            <label for="telephone">Telephone:</label>
                            <input type="tel" name="telephone" id="telephone" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($_SESSION['telephone']); ?>" required
                                   oninput="isValidTelephone(this)">
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password" class="form-control" autocomplete="off"
                                   placeholder="*******" required oninput="isValidPassword(this)">
                        </div>
                        <input type="hidden" name="delete_account">
                        <button type="submit" class="btn btn-primary">Change</button>
                    </form>
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="deleteForm" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <button type="submit" name="delete_account" class="btn btn-danger">Delete account</button>
                        <input type="hidden" name="delete_account">
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class='footer text-center footer-dark bg-dark fixed-bottom'>
    <p style="color: white;">© 2023 - 2024 - Bakalárska práca - Vincent Pálfy</p>
</footer>
<script>
    let form = document.querySelector('form');
    form.addEventListener('submit', checkForm);
</script>

<script>
    document.querySelector('#deleteForm').addEventListener("submit", function (e) {
        e.preventDefault();

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        })
    });
</script>
</body>

</html>
