<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
check(['Student']);

$conn = getDatabaseConnection();
$stmt = $conn->prepare("SELECT id, name, img, english_text, slovak_text FROM learn");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Látky</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/student/learn.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/html/student/slovensky/menu.php">E-Learning</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive"
                aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="/html/student/slovensky/menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="/html/student/slovensky/learn.php">Látky</a></li>
                <li class="nav-item"><a class="nav-link" href="/html/student/slovensky/profil.php">Profil</a></li>
                <li class="nav-item"><a class="nav-link" href="/html/student/english/learn.php">Anglická verzia</a></li>
                <li class="nav-item"><a class="nav-link" href="/php/logout.php">Odhlásiť sa</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container content">
    <div class="row">
        <?php if ($result->num_rows > 0) :
            while ($row = $result->fetch_assoc()) :
                $name = htmlspecialchars($row['name']);
                $img = htmlspecialchars($row["img"]);
                $slovak_text = htmlspecialchars($row["slovak_text"]);
                ?>
                <div class="col-md-6 lang">
                    <div class="card" onclick="location.href='language.php?name=<?php echo $name; ?>'">
                        <img src="<?php echo $img; ?>" alt="<?php echo $name; ?>" class="card-img-top">
                        <div class="card-body">
                            <p class="lecture-name"><?php echo $name; ?></p>
                            <p class="card-text"><?php echo $slovak_text; ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile;
        else : ?>
            <div class="col">
                <p>Nenašli sa žiadne výsledky.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<footer class="footer text-center footer-dark bg-dark fixed-bottom">
    <p style="color: white;">© 2023 - 2024 - Bakalárska práca 1 - Vincent Pálfy</p>
</footer>
</body>
</html>
