<?php
require 'vendor/autoload.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';


use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$username = $_SESSION['username'] ?? null;


if (!$username) {
    $_SESSION['toast'] = array("status" => "error", "message" => "User not logged in");
    header("Location: index.php");
    exit;
}

$conn = getDatabaseConnection();

$stmt = $conn->prepare("SELECT two_factor_secret FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows == 0) {
    echo "User not found.";
    exit;
}

$stmt->bind_result($existingTwoFactorSecret);
$stmt->fetch();
$stmt->close();

if (empty($existingTwoFactorSecret)) {
    $g = new GoogleAuthenticator();
    $twoFactorSecret = $g->generateSecret();
} else {
    $twoFactorSecret = $existingTwoFactorSecret;
}

$issuer = 'Bakalarska_Praca_Vincent_Palfy';
$label = urlencode("$issuer:$username");
$secret = $twoFactorSecret;

$qrCodeUrl = "otpauth://totp/$label?secret=$secret&issuer=" . urlencode($issuer);

$foregroundColor = new Color(0, 0, 0);

$qrCode = QrCode::create($qrCodeUrl)
    ->setSize(300)
    ->setMargin(20)
    ->setForegroundColor($foregroundColor);

$writer = new PngWriter();
$result = $writer->write($qrCode);

$qrCodeDataUri = $result->getDataUri();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Setup Two-Factor Authentication</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="css/setup_2fa.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="js/regex.js"></script>
</head>
<script>
    window.onload = function () {
        toastr.info('The data is saved to cookies');
    }
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
            <div class="content">
                <h2 class="text-center">Two-Factor Authentication Setup</h2>
                <p class="text-center">Scan the QR code below with your two-factor authentication app.</p>
                <div class="qr-container">
                    <img src="<?php echo $qrCodeDataUri; ?>" alt="QR Code" class="qr-code" style="margin-bottom: 20px;">
                </div>
                <p class="text-center">After scanning, enter the generated code to verify setup.</p>
                <form action="verify-2fa.php" method="post">
                    <div class="form-group">
                        <div class="input-group">
                            <label for="code" class="sr-only">Enter the code from the app</label>
                            <input type="text" name="code" id="code" class="form-control"
                                   placeholder="Enter the code from the app" required oninput="isValidNumber(this)">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">Verify Code</button>
                            </div>
                        </div>
                        <input type="hidden" name="username" id="username"
                               value="<?php echo htmlentities($username); ?>">
                        <input type="hidden" name="secret" id="secret" value="<?php echo $twoFactorSecret; ?>">
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

