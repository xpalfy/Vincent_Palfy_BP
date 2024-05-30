<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Teacher']);
$name = htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Webapp for E-Learning</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/teacher/menu.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="/js/student/menu.js"></script>
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
<div class="bg-image">
    <div class="overlay">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
            <div class="container">
                <a class="navbar-brand" href="/html/teacher/slovensky/menu.php">E-Learning</a>
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
                        <li class="nav-item"><a class="nav-link" href="/html/teacher/slovensky/menu.php">Slovak
                                version</a></li>
                        <li class="nav-item"><a class="nav-link" href="/php/logout.php">Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="mt-5 mb-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-12">
                        <div class="card bg-dark text-white">
                            <div class="card-body text-center">
                                <h2 class="card-title">Hello <?php echo $name; ?>!</h2>
                                <div id="clock"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<footer class='footer text-center footer-dark bg-dark fixed-bottom'>
    <p style="color: white;">© 2023 - 2024 - Bakalárska práca - Vincent Pálfy</p>
</footer>
</body>
</html>
