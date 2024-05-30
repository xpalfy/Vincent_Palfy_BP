<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
check(['Student']);

$conn = getDatabaseConnection();
$lessons = [];
$learn = null;
if (isset($_GET['name'])) {
    $learn = $_GET['name'];

    $stmt = $conn->prepare("SELECT id, slovak_name, english_name, learn, test, pdf, page FROM lesson WHERE learn = ?");
    $stmt->bind_param("s", $learn);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $lessons[] = $row;
    }
    $stmt->close();
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
    <title>Topics</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/student/language.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
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
                                        href="/html/student/slovensky/language.php?name=<?php echo htmlspecialchars($learn); ?>">Slovak
                        version</a></li>
                <li class="nav-item"><a class="nav-link" href="/php/logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="row">
        <?php
        if (!empty($lessons)) {
            foreach ($lessons as $lesson) {
                echo "<div class='col-md-4 mb-4'>";
                echo "<div class='card'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>" . htmlspecialchars($lesson['english_name']) . "</h5>";
                echo "<p class='card-text'>Lesson ID: " . htmlspecialchars($lesson['id']) . "</p>";
                echo "<a href='lesson.php?id=" . htmlspecialchars($lesson['id']) . "' class='btn btn-primary'>View Lesson</a>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
        } else {
            echo "<div class='col'>";
            echo "<p>There is no 'name' parameter in the URL or no lessons were found.</p>";
            echo "</div>";
        }
        ?>
    </div>
</div>
<footer class="footer text-center footer-dark bg-dark fixed-bottom">
    <p style="color: white;">© 2023 - 2024 - Bakalárska práca 1 - Vincent Pálfy</p>
</footer>
</body>
</html>
