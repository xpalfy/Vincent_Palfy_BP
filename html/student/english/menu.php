<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Student']);
$name = htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="/css/student/menu.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="/js/student/menu.js"></script>
    <title>Webapp for e-learning</title>
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
<div class="bg-image">
    <div class="overlay">
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
                        <li class="nav-item"><a class="nav-link" href="/html/student/english/profil.php">Profile</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="/html/student/slovensky/menu.php">Slovak
                                version</a></li>
                        <li class="nav-item"><a class="nav-link" href="/php/logout.php">Odhlásenie</a></li>
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
<div class="container">
    <div class="paragraph_1 mt-5 mb-5 row">
        <div class="col-md-6 text-center d-flex align-items-center">
            <div class="mx-auto">
                <img src="../../../img/img_2.png" alt="png_1" class="img-fluid mt-5 image">
            </div>
        </div>
        <div class="col-md-6 text-center d-flex align-items-center">
            <div>
                <h2 class="mt-5 mb-4">Welcome to our innovative web application for programmers!</h2>
                <p>
                    We are thrilled that you have decided to use our app to support your programming projects.
                    Our platform is designed to ease your way through the world of coding, optimization and innovation.
                    Whether you're a beginner or a seasoned professional, our app is ready to help you achieve your
                    goals. We offer a wide range of tools, tutorials, and options for collaborating with others
                    programmers. Whether you're working on websites, mobile apps, or complex
                    software projects, our app is here to support you.
                    Take advantage of our advanced features like collaborative programming, rapid debugging, and version
                    control,
                    to help you work more efficiently and get results faster. Our community of programmers is
                    here to inspire you, share experiences and solve problems.
                    If you have any questions, please don't hesitate to contact our support team, who are ready to help
                    24/7.
                    Thank you for choosing our web application. We are looking forward to coding together and
                    programming. May it be full of success and innovation!
                </p>
            </div>
        </div>
    </div>
    <div class="paragraph_2 mb-5 row content">
        <div class="col-md-6 text-center d-flex align-items-center">
            <div>
                <h2 class="mt-5 mb-4">What is programming for?</h2>
                <p>
                    Programming is the process of creating and writing a set of instructions that a computer executes.
                    These
                    instructions, also called code, specify how the computer should perform certain tasks and
                    operations. Programming
                    is a language that allows people to interact with a computer and control its behaviour.
                    Why should you learn to program and why is it so important?
                <ul>
                    <li>Expanding your creativity: programming allows you to create different kinds of apps, web
                        Programming can help you create different kinds of apps, websites, games and software. It's like
                        creating your own digital artwork, where your
                        imagination is the only limit.
                    </li>
                    <li>Developing problem thinking: programming teaches you to break down complex problems into smaller
                        parts and
                        to find effective solutions. It's like a puzzle you have to crack.
                    </li>
                    <li>Employability: programming is one of the fastest growing sectors in the job market.
                        Knowledge of programming is often in demand and can open doors to many interesting and well-paid
                        opportunities.
                    </li>
                    <li>Innovation: programming is the driving force behind technological innovation. Many new
                        technologies and
                        applications are created by programmers who create solutions to improve our world.
                    </li>
                    <li>Solving specific problems: programming allows you to create applications and tools that
                        can help solve specific problems in your life or in the world around you. For example, you can
                        create an app to track your financial spending or create an online platform for
                        support nonprofit organizations.
                    </li>
                    <li>Career opportunities:If you learn to code, you will open doors to many industries, including
                        software development, data analytics, cybersecurity and more. You can work in a variety of
                        and create your own career.
                    </li>
                </ul>
            </div>
        </div>
        <div class="col-md-6 text-center d-flex align-items-center">
            <div class="mx-auto">
                <img src="../../../img/img_3.png" alt="png_2" class="img-fluid mt-5 image">
            </div>
        </div>
    </div>
    <div>
        <p class="text-center fs-5 mt-5"><strong>If you have any questions, feel free to contact us by email</strong>
        </p>
        <a id="mail" href="mailto:xpalfy@stuba.sk?subject=Question about the website"
           class="btn btn-primary btn-lg d-block mx-auto mt-3">Send e-mail</a>
    </div>
</div>
<div class="space"></div>
<footer class='footer text-center fixed-bottom'>
    <p>© 2023 - 2024 - Bakalárska práca - Vincent Pálfy</p>
</footer>
</body>
</html>
