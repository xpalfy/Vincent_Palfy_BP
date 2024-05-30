<?php

use JetBrains\PhpStorm\NoReturn;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Teacher']);
$conn = getDatabaseConnection();
#[NoReturn] function insertUserIntoDatabase($conn): void
{
    $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_UNSAFE_RAW);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_UNSAFE_RAW);
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $telephone = filter_input(INPUT_POST, 'telephone', FILTER_UNSAFE_RAW);
    $role = filter_input(INPUT_POST, 'role', FILTER_UNSAFE_RAW);

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $active = ($role === 'Student') ? 1 : 0;

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['toast'] = array("status" => "error", "message" => "The username already exists");
        header("Location: " . '/html/teacher/slovensky/people/addUser.php');
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, email, telephone, role, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssi", $username, $hashed_password, $first_name, $last_name, $email, $telephone, $role, $active);
    $stmt->execute();
    if ($stmt->error) {
        $_SESSION['toast'] = array("status" => "error", "message" => "Error when adding a user to the database");
        $stmt->close();
        $conn->close();
        header("Location: " . '/html/teacher/slovensky/people/addUser.php');
    } else {
        $_SESSION['toast'] = array("status" => "success", "message" => "User has been successfully added");
        $stmt->close();
        $conn->close();
        header("Location: " . '/html/teacher/slovensky/students.php');
    }
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!validateCSRFToken()) {
        die("CSRF token validation failed.");
    }
    insertUserIntoDatabase($conn);
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registračný formulár</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="/css/teacher/addUser.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
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
                <li class="nav-item"><a class="nav-link" href="/html/teacher/english/people/addUser.php">Anglická
                        verzia</a></li>
                <li class="nav-item"><a class="nav-link" href="/php/logout.php">Odhlásiť sa</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
    <div class="register">
        <h1 class="text-center" style="margin-bottom:50px">Registračný formulár</h1>
        <form action="" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Používateľské meno:</label>
                <input type="text" class="form-control" id="username" name="username" required
                       oninput="isValidInput(this)">
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Heslo:</label>
                <input type="password" class="form-control" id="password" name="password" autocomplete="off" required
                       oninput="isValidPassword(this)">
            </div>
            <div class="form-row">
                <div class="col">
                    <label for="first_name"><i class="fas fa-user"></i> Meno:</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required
                           oninput="isValidName(this)">
                </div>
                <div class="col">
                    <label for="last_name"><i class="fas fa-user"></i> Priezvisko:</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required
                           oninput="isValidName(this)">
                </div>
            </div>
            <div class="form-group" style="margin-top: 10px;">
                <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                <input type="email" class="form-control" id="email" name="email" required oninput="isValidEmail(this)">
            </div>
            <div class="form-group">
                <label for="telephone"><i class="fas fa-phone"></i> Telefón:</label>
                <input type="tel" class="form-control" id="telephone" name="telephone" required
                       oninput="isValidTelephone(this)">
            </div>
            <div class="form-group">
                <label for="role"><i class="fas fa-user-graduate"></i> Role:</label>
                <select class="form-control" id="role" name="role" required>
                    <option value="Student" selected>Študent</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Registrácia</button>
            <a href="../students.php" class="btn btn-link">Späť</a>
        </form>
    </div>
</div>
<footer class='footer text-center fixed-bottom'>
    <div class="container">
        <p>© 2023 - 2024 - Bakalárska práca - Vincent Pálfy</p>
    </div>
</footer>
<script>
    let form = document.querySelector('form');
    form.addEventListener('submit', checkForm);
</script>
</body>
</html>
