<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Registration Form</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="/css/register.css">
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
<div class="container">
    <div class="row justify-content-center align-items-center" style="height: 100vh;">
        <div class="col-sm-10 col-md-10 col-lg-10">
            <div class="register">
                <h1 class="text-center mb-5">Registration Form</h1>
                <form action="../php/register.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> Username:</label>
                        <input type="text" class="form-control" id="username" name="username" required
                               oninput="isValidInput(this)">
                    </div>
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password:</label>
                        <input type="password" class="form-control" id="password" name="password" autocomplete="off"
                               required oninput="isValidPassword(this)">
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="first_name"><i class="fas fa-user"></i> First Name:</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required
                                   oninput="isValidName(this)">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="last_name"><i class="fas fa-user"></i> Last Name:</label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required
                                   oninput="isValidName(this)">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               oninput="isValidEmail(this)">
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
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Register</button>
                    <a href="../index.php" class="btn btn-link btn-block">Login</a>
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
