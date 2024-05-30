<?php

use JetBrains\PhpStorm\NoReturn;

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
check(['Admin']);

#[NoReturn] function handleFileUpload($db): void
{
    $lectureName = filter_input(INPUT_POST, 'lectureName');
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM learn WHERE name = ?");
    $stmt->bind_param("s", $lectureName);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row['cnt'] > 0) {
        $_SESSION['toast'] = array(
            'status' => 'error',
            'message' => 'Sorry, this lecture already exists.'
        );
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/img/prog_lang/";
    $file = $_FILES["fileToUpload"];
    $target_file = $target_dir . basename($file["name"]);
    $imageName = "/img/prog_lang/" . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if (isset($file)) {
        $check = getimagesize($file["tmp_name"]);
        if ($check === false) {
            $_SESSION['toast'] = array(
                'status' => 'error',
                'message' => 'File is not an image.'
            );
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    if (file_exists($target_file)) {
        $_SESSION['toast'] = array(
            'status' => 'error',
            'message' => 'Sorry, file already exists.'
        );
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    if ($file["size"] > 5000000) {
        $_SESSION['toast'] = array(
            'status' => 'error',
            'message' => 'Sorry, your file is too large.'
        );
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $_SESSION['toast'] = array(
            'status' => 'error',
            'message' => 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.'
        );
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            echo "The file " . htmlspecialchars($imageName) . " has been uploaded.";

            $englishText = filter_input(INPUT_POST, 'englishText');
            $slovakText = filter_input(INPUT_POST, 'slovakText');
            $englishDBName = "questions_" . strtolower($lectureName);
            $slovakDBName = "otazky_" . strtolower($lectureName);

            $stmt = $db->prepare("INSERT INTO learn (name, img, english_text, slovak_text, english_test_database, slovak_test_database) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $lectureName, $imageName, $englishText, $slovakText, $englishDBName, $slovakDBName);
            $stmt->execute();
            $stmt->close();

            $db->query("CREATE DATABASE IF NOT EXISTS `$englishDBName`");
            $db->query("CREATE DATABASE IF NOT EXISTS `$slovakDBName`");
            $_SESSION['toast'] = array(
                'status' => 'success',
                'message' => 'The lecture upload was successful.'
            );
        } else {
            $_SESSION['toast'] = array(
                'status' => 'error',
                'message' => 'Sorry, there was an error uploading your file.'
            );
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

#[NoReturn] function deleteLecture($db): void
{
    $lectureId = filter_input(INPUT_POST, 'lectureId', FILTER_SANITIZE_NUMBER_INT);
    $stmt = $db->prepare("
            SELECT 
                learn.name, 
                learn.img, 
                learn.english_test_database, 
                learn.slovak_test_database, 
                lesson.id AS lesson_id
            FROM learn
            LEFT JOIN lesson ON learn.name = lesson.learn
            WHERE learn.id = ?
        ");
    $stmt->bind_param("i", $lectureId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        if (!empty($row['lesson_id'])) {
            deleteTopicByID($db, $row['lesson_id']);
        }
        if (!isset($processed)) {
            $filename = $_SERVER['DOCUMENT_ROOT'] . $row['img'];
            $englishDBName = $row['english_test_database'];
            $slovakDBName = $row['slovak_test_database'];
            if (file_exists($filename)) {
                unlink($filename);
            }
            $db->query("DROP DATABASE IF EXISTS `$englishDBName`");
            $db->query("DROP DATABASE IF EXISTS `$slovakDBName`");
            $processed = true;
        }
    }
    $stmt = $db->prepare("DELETE FROM learn WHERE id = ?");
    $stmt->bind_param("i", $lectureId);
    $stmt->execute();
    $stmt->close();
    $_SESSION['toast'] = array(
        'status' => 'success',
        'message' => 'The lecture delete was successful.'
    );
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

#[NoReturn] function deleteTopic($db): void
{
    $topicID = filter_input(INPUT_POST, 'topicID', FILTER_SANITIZE_NUMBER_INT);
    deletetopicByID($db, $topicID);
    $_SESSION['toast'] = array(
        'status' => 'success',
        'message' => 'The topic delete was successful.'
    );
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

function deleteTopicByID($db, $id): void
{
    $stmt = $db->prepare("SELECT test, learn, pdf FROM lesson WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $testTableName = $row['test'];
        $lectureName = $row['learn'];
        $pdfPath = $row['pdf'];
        delete_from_results($db, $id);
        checkAndDeletePDF($db, $pdfPath);
        deleteTestTables($lectureName, $testTableName);
    }
    $stmt->close();
    $stmt = $db->prepare("DELETE FROM lesson WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

function delete_from_results($db, $id): void
{
    $stmt = $db->prepare("DELETE FROM results WHERE test_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

function checkAndDeletePDF($db, $pdfPath): void
{
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM lesson WHERE pdf = ?");
    $stmt->bind_param("s", $pdfPath);
    $stmt->execute();
    $countResult = $stmt->get_result();
    $countRow = $countResult->fetch_assoc();
    if ($countRow['cnt'] <= 1 && !empty($pdfPath)) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $pdfPath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
    $stmt->close();
}

function deleteTestTables($lectureName, $testTableName): void
{
    $englishDBName = "questions_" . strtolower($lectureName);
    $slovakDBName = "otazky_" . strtolower($lectureName);
    $englishDB = getDatabaseConnection($englishDBName);
    $englishDB->query("DROP TABLE IF EXISTS `$testTableName`");
    $englishDB->close();
    $slovakDB = getDatabaseConnection($slovakDBName);
    $slovakDB->query("DROP TABLE IF EXISTS `$testTableName`");
    $slovakDB->close();
}

$masterDB = getDatabaseConnection();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!validateCsrfToken()) {
        die("CSRF token validation failed.");
    }
    if (isset($_POST['submitLecture'])) {
        handleFileUpload($masterDB);
    } elseif (isset($_POST['delete']) && isset($_POST['lectureId'])) {
        deleteLecture($masterDB);
    } elseif (isset($_POST['deleteTopic']) && isset($_POST['topicID'])) {
        deleteTopic($masterDB);
    }
}
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Manage Lectures</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="/css/admin/learn.css"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/js/regex.js"></script>
    <script>
        function updateFileName() {
            let input = document.getElementById('fileToUpload');
            let output = document.getElementById('fileLabel');
            output.innerText = input.files[0].name;
        }

        document.addEventListener('DOMContentLoaded', function () {
            const lectureSelect = document.getElementById('lectureSelectForTopics');
            const topicSelectGroup = document.getElementById('topicSelectGroup');
            const topicSelect = document.getElementById('topicSelect');
            const deleteTopicButton = document.getElementById('deleteTopicButton');
            const deleteTopicSection = document.getElementById('deleteTopicSection');

            lectureSelect.addEventListener('change', function () {
                fetch(`learn/getTopics.php?lecture=${this.value}`)
                    .then(response => response.json())
                    .then(data => {
                        topicSelect.innerHTML = '<option value="">Select a Topic</option>';
                        data.forEach(topic => {
                            const option = new Option(topic.english_name, topic.id);
                            topicSelect.add(option);
                        });
                        if (lectureSelect.value === '') {
                            deleteTopicSection.style.display = 'none';
                            topicSelectGroup.style.display = 'none';
                            deleteTopicButton.style.display = 'none';
                        } else {
                            deleteTopicSection.style.display = 'block';
                            topicSelectGroup.style.display = 'block';
                            deleteTopicButton.style.display = 'none';
                        }
                    })
                    .catch(error => {
                        toastr.error('Error:', error);
                        deleteTopicSection.style.display = 'none';
                    });
            });

            topicSelect.addEventListener('change', function () {
                deleteTopicButton.style.display = this.value ? 'block' : 'none';
            });
        });

        function onSelectChange() {
            let selectElement = document.getElementById("lectureSelect");
            let deleteButton = document.getElementById("deleteButton");

            if (selectElement.value !== "") {
                deleteButton.style.display = "block";
            } else {
                deleteButton.style.display = "none";
            }
        }

        function confirmDelete(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (e.target.parentElement.id === 'deleteLectureForm') {
                        document.getElementById('deleteLectureForm').submit();
                    } else {
                        document.getElementById('deleteTopicForm').submit();
                    }
                }
            });
        }
    </script>
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
        <a class="navbar-brand" href="./menu.php">E-Learning</a>
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
    <h2>Upload New Lecture</h2>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data" id="form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <input type="hidden" name="submitLecture" value="true">
        <div class="form-group">
            <label for="lectureName">Lecture Name</label>
            <input type="text" class="form-control" id="lectureName" name="lectureName" required
                   oninput="isValidInput(this)">
        </div>
        <div class="form-group">
            <label for="englishText">English Text</label>
            <textarea class="form-control" id="englishText" name="englishText" rows="4" required
                      oninput="isValidText(this)"></textarea>
        </div>
        <div class="form-group">
            <label for="slovakText">Slovak Text</label>
            <textarea class="form-control" id="slovakText" name="slovakText" rows="4" required
                      oninput="isValidText(this)"></textarea>
        </div>
        <div class="form-group custom-file">
            <input type="file" class="custom-file-input" id="fileToUpload" name="fileToUpload" required
                   accept="image/png" onchange="updateFileName()">
            <label class="custom-file-label" for="fileToUpload" id="fileLabel">Choose file</label>
        </div>

        <button type="submit" class="btn btn-primary" name="submitLecture" style="margin-bottom:30px">Upload Lecture
        </button>
    </form>


    <h2>Delete Lecture</h2>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="deleteLectureForm">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <div class="form-group">
            <label for="lectureSelect">Select Lecture to Delete</label>
            <select class="form-control" id="lectureSelect" name="lectureId" onchange="onSelectChange()">
                <option value="">Select a Lecture to Delete</option>
                <?php
                $result = $masterDB->query("SELECT id, name FROM learn");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-danger" id="deleteButton" name="delete"
                style="display:none; margin-bottom:30px;" onclick="confirmDelete(event)">Delete
        </button>
        <input type="hidden" name="delete">
    </form>

    <h2>Select Lecture to Load Topics</h2>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <div class="form-group">
            <label for="lectureSelectForTopics">Select Lecture</label>
            <select class="form-control" id="lectureSelectForTopics" name="lecture">
                <option value="">Select a Lecture</option>
                <?php
                $result = $masterDB->query("SELECT name FROM learn");
                while ($row = $result->fetch_assoc()) {
                    $isSelected = (isset($_POST['lecture']) && $_POST['lecture'] == $row['name']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row['name']) . "' $isSelected>" . htmlspecialchars($row['name']) . "</option>";
                }
                ?>
            </select>
        </div>
    </form>

    <div id="deleteTopicSection" style="display:none">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="deleteTopicForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <input type="hidden" name="deleteTopic" value="true">
            <div class="form-group" id="topicSelectGroup" style="display:none">
                <label for="topicSelect">Select Topic</label>
                <select class="form-control" name="topicID" id="topicSelect" required>
                    <option value="">Select a Topic</option>
                </select>
            </div>
            <button type="submit" class="btn btn-danger" name="deleteTopic" id="deleteTopicButton" style="display:none"
                    onclick="confirmDelete(event)">Delete Topic
            </button>
            <input type="hidden" name="delete">
        </form>
    </div>

    <footer class='footer text-center footer-dark bg-dark fixed-bottom'>
        <p style="color: white;">© 2023 - 2024 - Bakalárska práca - Vincent Pálfy</p>
    </footer>
    <script>
        let form = document.querySelector('form');
        form.addEventListener('submit', checkForm);
    </script>
    <script>
        function updateFileName() {
            let input = document.getElementById('fileToUpload');
            let output = document.getElementById('fileLabel');
            output.innerText = input.files[0].name;
        }
    </script>
</body>
</html>
