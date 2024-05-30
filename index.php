<?php
session_unset();
session_start();
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="/css/login.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
<div class="container">
    <div class="row justify-content-center align-items-center" style="height: 100vh;">
        <div class="col-sm-10 col-md-6 col-lg-6">
            <div class="login-container">
                <h1 class="text-center mb-4">Login</h1>
                <form action="./php/login.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" name="username" id="username" class="form-control" required
                               oninput="isValidInput(this)">
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password" class="form-control" autocomplete="off"
                               required>
                    </div>
                    <div class="form-group">
                        <input type="submit" value="Login" class="btn btn-primary btn-block">
                    </div>
                    <div class="form-group text-center">
                        <a href="./html/register.php" class="register">Register</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    let form = document.querySelector('form');
    form.addEventListener('submit', checkForm);
</script>
</body>
</html>

