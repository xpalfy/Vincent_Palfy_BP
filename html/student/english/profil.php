<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/database.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
check(['Student']);

function deleteAccount($conn): void
{

    $username = $_SESSION['username'];
    $cookieName = '2fa_verified_user_' . urlencode($username);
    try {
        $stmtResults = $conn->prepare("DELETE FROM results WHERE username = ?");
        if ($stmtResults === false) {
            throw new Exception("Prepare statement failed for deleting results");
        }
        $stmtResults->bind_param("s", $username);
        $stmtResults->execute();
        $stmtResults->close();

        $stmtDeleteUser = $conn->prepare("DELETE FROM users WHERE username = ?");
        if ($stmtDeleteUser === false) {
            throw new Exception("Prepare statement failed for deleting user");
        }
        $stmtDeleteUser->bind_param("s", $username);
        $stmtDeleteUser->execute();
        $stmtDeleteUser->close();

        if (isset($_COOKIE[$cookieName])) {
            unset($_COOKIE[$cookieName]);
            setcookie($cookieName, '', time() - 3600, "/");
        }

        $conn->close();
        header("Location: /index.php");
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken()) {
        die("CSRF token validation failed.");
    }

    $conn = getDatabaseConnection();

    if (isset($_POST['delete_account'])) {
        deleteAccount($conn);
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="/css/student/profil.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <li class="nav-item"><a class="nav-link" href="/html/student/slovensky/profil.php">Slovak version</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="/php/logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container custom-container">
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Profile Information</div>
                <div class="card-body text-center">
                    <img src="/img/Student.png" alt="profile picture" id="profile_picture"
                         class="img-fluid profile-picture mb-3">
                    <p class="card-text">First Name: <?php echo htmlspecialchars($_SESSION['first_name']); ?></p>
                    <p class="card-text">Last Name: <?php echo htmlspecialchars($_SESSION['last_name']); ?></p>
                    <p class="card-text">Email: <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                    <p class="card-text">Telephone: <?php echo htmlspecialchars($_SESSION['telephone']); ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header">Edit Profile</div>
                <div class="card-body">
                    <h5 class="card-title">Profile</h5>
                    <form action="/php/changeData.php" method="post" class="mb-4">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <div class="form-group">
                            <label for="first_name">First Name:</label>
                            <input type="text" name="first_name" id="first_name" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($_SESSION['first_name']); ?>" required
                                   oninput="isValidName(this)">
                        </div>
                        <div class="form-group">
                            <label for="last_name">Last Name:</label>
                            <input type="text" name="last_name" id="last_name" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($_SESSION['last_name']); ?>" required
                                   oninput="isValidName(this)">
                        </div>
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" id="email" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($_SESSION['email']); ?>" required
                                   oninput="isValidEmail(this)">
                        </div>
                        <div class="form-group">
                            <label for="telephone">Telephone:</label>
                            <input type="tel" name="telephone" id="telephone" class="form-control"
                                   placeholder="<?php echo htmlspecialchars($_SESSION['telephone']); ?>" required
                                   oninput="isValidTelephone(this)">
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" name="password" id="password" class="form-control" autocomplete="off"
                                   placeholder="*******" required oninput="isValidPassword(this)">
                        </div>
                        <button type="submit" class="btn btn-primary">Change</button>
                    </form>
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="deleteForm" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <button type="submit" name="delete_account" class="btn btn-danger">Delete Account</button>
                        <input type="hidden" name="delete_account">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class='footer text-center footer-dark bg-dark fixed-bottom'>
        <p style="color: white;">© 2023 - 2024 - Bakalárska práca 1 - Vincent Pálfy</p>
    </footer>
    <script>
        document.querySelector('#deleteForm').addEventListener("submit", function (e) {
            e.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            })
        });
    </script>
    <script>
        let form = document.querySelector('form');
        form.addEventListener('submit', checkForm);
    </script>
</body>

</html>
