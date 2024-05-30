<?php
require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';

use JetBrains\PhpStorm\NoReturn;
use Sonata\GoogleAuthenticator\GoogleAuthenticator;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['loggedin']) || !$_SESSION['loggedin'] || !isset($_SESSION['username'])) {
    $_SESSION['toast'] = array("status" => "error", "message" => "User not logged in");
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $verificationCode = filter_var($_POST['code'], FILTER_UNSAFE_RAW) ?? null;

    if (empty($verificationCode)) {
        $_SESSION['toast'] = array("status" => "error", "message" => "Verification code missing");
        header("Location: verify-2fa.php");
        exit;
    }

    $username = $_SESSION['username'];
    $conn = getDatabaseConnection();

    $stmt = $conn->prepare("SELECT two_factor_secret FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['toast'] = array("status" => "error", "message" => "User not found");
        session_destroy();
        header("Location: ../index.php");
        exit;
    }

    $row = $result->fetch_assoc();
    $twoFactorSecret = $row['two_factor_secret'];

    $g = new GoogleAuthenticator();
    $isCodeCorrect = $g->checkCode($twoFactorSecret, $verificationCode);

    if ($isCodeCorrect) {
        $cookieName = '2fa_verified_user_' . urlencode($username);
        if (!isset($_COOKIE[$cookieName])) {
            setcookie($cookieName, $username, time() + (86400 * 30), "/");
        }

        redirectToDashboard($_SESSION['role']);
    } else {
        $_SESSION['toast'] = array("status" => "error", "message" => "Incorrect verification code. Please try again.");
        header("Location: verify-2fa.php");
    }
} else {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>2FA Verification</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
        <link rel="stylesheet" href="/css/verify-2fa.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="/js/regex.js"></script>
    </head>
    <body>
    <div class="container">
        <form action="verify-2fa.php" method="post" class="col-md-6 mx-auto">
            <div class="form-group">
                <label for="code">Enter the 2FA code from your app:</label>
                <input type="text" name="code" id="code" class="form-control" required
                       oninput="isValidNumberVerify(this)">
            </div>
            <div class="form-group d-flex">
                <button type="submit" class="btn btn-primary mr-1">Verify Code</button>
                <a href="../index.php" class="btn btn-secondary">Back</a>
            </div>
        </form>
    </div>
    <script>
        $(function () {
            <?php if(!empty($_SESSION['toast'])): ?>
            toastr.<?php echo $_SESSION['toast']['status']; ?>('<?php echo $_SESSION['toast']['message']; ?>');
            <?php unset($_SESSION['toast']); ?>
            <?php endif; ?>
        });

        let form = document.querySelector('form');
        form.addEventListener('submit', checkForm);
    </script>
    </body>
    </html>
    <?php
}

#[NoReturn] function redirectToDashboard($userRole): void
{
    switch ($userRole) {
        case 'Admin':
            header("Location: ../html/admin/menu.php");
            exit;
        case 'Teacher':
            header("Location: ../html/teacher/slovensky/menu.php");
            exit;
        case 'Student':
            header("Location: ../html/student/slovensky/menu.php");
            exit;
        default:
            exit();
    }
}

?>
