<?php

use JetBrains\PhpStorm\NoReturn;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
check(['Admin']);
$conn = getDatabaseConnection();
#[NoReturn] function insertUserIntoDatabase($conn): void
{
    $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
    $password = filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW);
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_UNSAFE_RAW);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_UNSAFE_RAW);
    $email = filter_input(INPUT_POST, 'email', FILTER_UNSAFE_RAW);
    $telephone = filter_input(INPUT_POST, 'telephone', FILTER_UNSAFE_RAW);
    $role = filter_input(INPUT_POST, 'role', FILTER_UNSAFE_RAW);

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $active = ($role === 'Teacher') ? 0 : 1;

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['toast'] = array("status" => "error", "message" => "Username already exists");
        header("Location: " . '/html/admin/people/addUser.php');
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, email, telephone, role, active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssi", $username, $hashed_password, $first_name, $last_name, $email, $telephone, $role, $active);
    $stmt->execute();
    if ($stmt->error) {
        $_SESSION['toast'] = array("status" => "error", "message" => "Error adding user to database");
        $stmt->close();
        $conn->close();
        header("Location: " . '/html/admin/people/addUser.php');
    } else {
        $_SESSION['toast'] = array("status" => "success", "message" => "User added successfully");
        $stmt->close();
        $conn->close();
        header("Location: " . '/html/admin/people.php');
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
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registration Form</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="/css/admin/addUser.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
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
        <a class="navbar-brand" href="../menu.php">E-Learning</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarResponsive"
                aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="/html/admin/menu.php">Menu</a></li>
                <li class="nav-item"><a class="nav-link" href="/html/admin/people.php">People</a></li>
                <li class="nav-item"><a class="nav-link" href="/html/admin/learn.php">Learn</a></li>
                <li class="nav-item"><a class="nav-link" href="/html/admin/profil.php">Profile</a></li>
                <li class="nav-item"><a class="nav-link" href="/php/logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
    <div class="register">
        <h1 class="text-center" style="margin-bottom:50px">Registration Form</h1>
        <form action="" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Username:</label>
                <input type="text" class="form-control" id="username" name="username" required
                       oninput="isValidInput(this)">
            </div>
            <div class="form-group">
                <label for="password"><i class="fas fa-lock"></i> Password:</label>
                <input type="password" class="form-control" id="password" name="password" autocomplete="off" required
                       oninput="isValidPassword(this)">
            </div>
            <div class="form-row">
                <div class="col">
                    <label for="first_name"><i class="fas fa-user"></i> First Name:</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" required
                           oninput="isValidName(this)">
                </div>
                <div class="col">
                    <label for="last_name"><i class="fas fa-user"></i> Last Name:</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" required
                           oninput="isValidName(this)">
                </div>
            </div>
            <div class="form-group" style="margin-top: 10px;">
                <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                <input type="email" class="form-control" id="email" name="email" required oninput="isValidEmail(this)">
            </div>
            <div class="form-group">
                <label for="telephone"><i class="fas fa-phone"></i> Telephone:</label>
                <input type="tel" class="form-control" id="telephone" name="telephone" required
                       oninput="isValidTelephone(this)">
            </div>
            <div class="form-group">
                <label for="role"><i class="fas fa-user-graduate"></i> Role:</label>
                <select class="form-control" id="role" name="role" required>
                    <option value="Student">Student</option>
                    <option value="Teacher">Teacher</option>
                    <option value="Admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
            <a href="../people.php" class="btn btn-link">Back</a>
        </form>
    </div>
</div>
<script>
    let form = document.querySelector('form');
    form.addEventListener('submit', checkForm);
</script>
<footer class='footer text-center footer-dark bg-dark fixed-bottom'>
    <p style="color: white;">© 2023 - 2024 - Bakalárska práca - Vincent Pálfy</p>
</footer>
</body>
</html>
