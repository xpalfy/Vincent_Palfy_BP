<?php

use JetBrains\PhpStorm\NoReturn;

require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
check(['Teacher']);

$currentUserName = $_SESSION['username'];

#[NoReturn] function createTopic($masterDB, $currentUserName): void
{
    $slovakName = filter_input(INPUT_POST, 'slovakName');
    $englishName = filter_input(INPUT_POST, 'englishName');
    $selectedLecture = filter_input(INPUT_POST, 'lecture');
    $page = filter_input(INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT);
    $stmt = $masterDB->prepare("SELECT COUNT(*) AS cnt FROM lesson WHERE (slovak_name = ? OR english_name = ?) AND learn = ?");
    $stmt->bind_param("sss", $slovakName, $englishName, $selectedLecture);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    if ($row['cnt'] > 0) {
        $_SESSION['toast'] = array("status" => "error", "message" => "The topic already exists.");
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
    $num = 0;
    $test = strtolower($selectedLecture) . "_" . strtolower($englishName);
    $pdfPath = handlePdfUpload();
    if ($pdfPath == "") {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
    $stmt = $masterDB->prepare("INSERT INTO lesson (slovak_name, english_name, learn, test, pdf, page, creator, num) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssisi", $slovakName, $englishName, $selectedLecture, $test, $pdfPath, $page, $currentUserName, $num);
    $stmt->execute();
    $stmt->close();
    createTestTables($masterDB, $selectedLecture, $test);
    $_SESSION['toast'] = array("status" => "success", "message" => "The topic was successfully created.");
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

function handlePdfUpload()
{
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/pdf/";
    $uploadOk = 1;
    $pdfPath = "";
    if (!empty($_FILES['fileToUpload']['name'])) {
        $file = $_FILES['fileToUpload'];
        $target_file = $target_dir . basename($file["name"]);
        $pdfPath = "/pdf/" . basename($file["name"]);
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if (file_exists($target_file)) {
            $_SESSION['toast'] = array("status" => "error", "message" => "Sorry, the file already exists.");
            $uploadOk = 0;
        }
        if ($file["size"] > 5000000) {
            $_SESSION['toast'] = array("status" => "error", "message" => "Sorry, your file is too big.");
            $uploadOk = 0;
        }
        if ($fileType != "pdf") {
            $_SESSION['toast'] = array("status" => "error", "message" => "Sorry, only PDF files are allowed.");
            $uploadOk = 0;
        }
        if ($uploadOk == 0) {
            return "";
        } else {
            if (move_uploaded_file($file["tmp_name"], $target_file)) {
                $_SESSION['toast'] = array("status" => "success", "message" => "File " . htmlspecialchars(basename($file["name"])) . " has been uploaded.");
            } else {
                $_SESSION['toast'] = array("status" => "error", "message" => "Sorry, there was an error uploading your file.");
                return "";
            }
        }
    } elseif (isset($_POST['selectedPDF']) && $_POST['selectedPDF'] != '') {
        $pdfPath = filter_input(INPUT_POST, 'selectedPDF');
    }
    return $pdfPath;
}

function createTestTables($masterDB, $selectedLecture, $test): void
{
    list($englishDBName, $slovakDBName) = getTestDatabases($masterDB, $selectedLecture);
    $tableCreationQuery = "CREATE TABLE IF NOT EXISTS `$test` (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            question TEXT NULL,
            A TEXT NULL,
            B TEXT NULL,
            C TEXT NULL,
            correct_answer CHAR(1) NULL
        )";
    executeTableQuery($englishDBName, $tableCreationQuery);
    executeTableQuery($slovakDBName, $tableCreationQuery);
}


function getTestDatabases($masterDB, $selectedLecture): array
{
    $stmt = $masterDB->prepare("SELECT english_test_database, slovak_test_database FROM learn WHERE name = ?");
    $stmt->bind_param("s", $selectedLecture);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return [$row['english_test_database'], $row['slovak_test_database']];
}

function executeTableQuery($dbName, $query): void
{
    global $servername, $dbusername, $dbpassword;
    $db = new mysqli($servername, $dbusername, $dbpassword, $dbName);
    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }
    $db->query($query);
    $db->close();
}

#[NoReturn] function deleteTopic($masterDB): void
{
    $topicID = filter_input(INPUT_POST, 'topicID', FILTER_SANITIZE_NUMBER_INT);
    if (!$topicID) {
        $_SESSION['toast'] = array("status" => "error", "message" => "The topic was not found.");
    }
    $details = fetchTopicDetails($masterDB, $topicID);
    if (!$details) {
        $_SESSION['toast'] = array("status" => "error", "message" => "The topic was not found.");
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
    delete_from_results(getDatabaseConnection(), $topicID);
    deletePdfIfUnused($masterDB, $details['pdfPath']);
    $stmt = $masterDB->prepare("DELETE FROM lesson WHERE id = ?");
    $stmt->bind_param("i", $topicID);
    $stmt->execute();
    $stmt->close();
    dropTestTable($details['testTableName'], $details['lectureName']);
    $_SESSION['toast'] = array("status" => "success", "message" => "The topic has been successfully removed.");
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

function fetchTopicDetails($masterDB, $topicID): ?array
{
    $stmt = $masterDB->prepare("
            SELECT 
                lesson.test, 
                lesson.learn, 
                lesson.pdf,
                learn.english_test_database,
                learn.slovak_test_database
            FROM lesson
            LEFT JOIN learn ON lesson.learn = learn.name
            WHERE lesson.id = ?
        ");
    $stmt->bind_param("i", $topicID);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    if ($row = $result->fetch_assoc()) {
        return [
            'testTableName' => $row['test'],
            'lectureName' => $row['learn'],
            'pdfPath' => $row['pdf'],
            'englishDBName' => $row['english_test_database'],
            'slovakDBName' => $row['slovak_test_database']
        ];
    } else {
        return null;
    }
}


function delete_from_results($db, $id): void
{
    $stmt = $db->prepare("DELETE FROM results WHERE test_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

function deletePdfIfUnused($masterDB, $pdfPath): void
{
    if (empty($pdfPath)) return;
    $stmt = $masterDB->prepare("SELECT COUNT(*) AS cnt FROM lesson WHERE pdf = ?");
    $stmt->bind_param("s", $pdfPath);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    if ($row['cnt'] == 1) {
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . $pdfPath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}

function dropTestTable($testTableName, $lectureName): void
{
    $databases = getTestDatabases(getDatabaseConnection(), $lectureName);
    foreach ($databases as $dbName) {
        executeTableQuery($dbName, "DROP TABLE IF EXISTS `$testTableName`");
    }
}

function loadTopics($masterDB, $currentUserName): array
{
    $selectedLecture = filter_input(INPUT_POST, 'lecture') ?: '';
    $topics = [];
    if (!empty($selectedLecture)) {
        $stmt = $masterDB->prepare("SELECT id, english_name FROM lesson WHERE learn = ? AND creator = ?");
        $stmt->bind_param("ss", $selectedLecture, $currentUserName);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $topics[] = $row;
        }
        $stmt->close();
    }
    return $topics;
}

function getPDFFiles(): array
{
    $pdfDirectory = $_SERVER['DOCUMENT_ROOT'] . '/pdf/';
    $pdfFiles = array_diff(scandir($pdfDirectory), array('..', '.'));
    $pdfList = [];
    foreach ($pdfFiles as $file) {
        if (is_file($pdfDirectory . $file)) {
            $pdfList[] = $file;
        }
    }
    return $pdfList;
}

$masterDB = getDatabaseConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!validateCsrfToken()) {
        die("CSRF token validation failed.");
    }
    if (isset($_POST['submitTopic'])) {
        createTopic($masterDB, $currentUserName);
    } elseif (isset($_POST['deleteTopic'])) {
        deleteTopic($masterDB);
    }
}

$pdfFiles = getPDFFiles();
$selectedLecture = filter_input(INPUT_POST, 'lecture');
$topics = !empty($selectedLecture) ? loadTopics($masterDB, $currentUserName) : [];
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Create a new topic</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="/css/teacher/learn.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    \
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/js/teacher/learn.js"></script>
    <script src="/js/regex.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const fileInput = document.getElementById('fileToUpload');

            fileInput.addEventListener('change', function () {
                if (this.files.length > 0) {
                    const fileName = this.files[0].name;
                    const label = this.nextElementSibling;
                    label.innerHTML = fileName;
                }
            });
            const lectureSelectForTopics = document.getElementById('lectureSelectForTopics');
            const deleteTopicSection = document.getElementById('deleteTopicSection');
            const topicSelection = document.getElementById('topicID');
            const deleteTopicButton = document.getElementById('deleteTopic');

            lectureSelectForTopics.addEventListener('change', function () {
                const lecture = this.value;
                if (lecture) {
                    fetch(`learn/getTopics.php?lecture=${lecture}`)
                        .then(response => response.json())
                        .then(data => {
                            const topicSelect = document.querySelector('[name="topicID"]');
                            topicSelect.innerHTML = '<option value="">Select a Topic</option>';
                            data.forEach(topic => {
                                const option = new Option(topic.english_name, topic.id);
                                topicSelect.add(option);
                            });
                            if (lectureSelectForTopics.value === '') {
                                deleteTopicSection.style.display = 'none';
                                topicSelection.style.display = 'none';
                                deleteTopicButton.style.display = 'none';
                            } else {
                                deleteTopicSection.style.display = 'block';
                                topicSelection.style.display = 'block';
                                deleteTopicButton.style.display = 'none';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            deleteTopicSection.style.display = 'none';
                        });
                } else {
                    deleteTopicSection.style.display = 'none';
                }
            });
            topicSelection.addEventListener('change', function () {
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
                    document.getElementById('deleteTopicForm').submit();
                }
            });
        }

    </script>
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
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/html/teacher/english/menu.php">E-Learning</a>
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
                <li class="nav-item"><a class="nav-link" href="/html/teacher/slovensky/learn.php">Slovak version</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="/php/logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
    <h2>Create a new topic</h2>
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data"
          id="form">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <input type="hidden" name="submitTopic" value="true">
        <div class="form-group">
            <label for="slovakName">Slovak name:</label>
            <input type="text" class="form-control" id="slovakName" name="slovakName" required
                   oninput="isValidInput(this)">
        </div>
        <div class="form-group">
            <label for="englishName">English name:</label>
            <input type="text" class="form-control" id="englishName" name="englishName" required
                   oninput="isValidInput(this)">
        </div>
        <div class="form-group">
            <label for="lecture">Select a lecture:</label>
            <select class="form-control" id="lecture" name="lecture" required>
                <option value="">Select a lecture</option>
                <?php
                $result = $masterDB->query("SELECT name FROM learn");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row['name']) . "'>" . htmlspecialchars($row['name']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="page">Number of pages:</label>
            <input type="number" class="form-control" id="page" name="page" required>
        </div>
        <div class="form-group">
            <label for="pdfSelect" id="pdfLabel">Select PDF:</label>
            <select class="form-control" id="pdfSelect" name="selectedPDF">
                <option value="">Select PDF</option>
                <?php foreach (getPDFFiles() as $file): ?>
                    <option value="<?php echo '/pdf/' . htmlspecialchars($file); ?>"><?php echo htmlspecialchars($file); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="pdfUpload" style="display: none;">
            <div class="form-group">
                <label for="pdfToUpload">Upload PDF:</label>
                <div class="form-group custom-file">
                    <input type="file" class="custom-file-input" id="fileToUpload" name="fileToUpload" accept=".pdf">
                    <label class="custom-file-label" for="fileToUpload">Select file</label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-6">
                    <input type="submit" class="btn btn-success btn-block" value="Vytvoriť tému" name="submitTopic">
                </div>
                <div class="col-md-6 mt-2 mt-md-0">
                    <button type="button" class="btn btn-primary btn-block" onclick="togglePDFUpload()">Add new
                        PDF file
                    </button>
                </div>
            </div>
        </div>
    </form>
    <h2>Remove topic</h2>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <div class="form-group">
            <label for="lectureSelectForTopics">Select a lecture:</label>
            <select class="form-control" id="lectureSelectForTopics" name="lecture">
                <option value="">Select lecture</option>
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

    <div id="deleteTopicSection" style="display: none;">
        <form action="" method="post" id="deleteTopicForm" name="deleteTopicForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <div class="form-group">
                <label for="topicID">Select a topic</label>
                <select class="form-control" name="topicID" id="topicID" required>
                    <option value="">Select topic</option>
                </select>
            </div>
            <button type="submit" class="btn btn-danger" name="deleteTopic" id="deleteTopic" style="display: none;"
                    onclick="confirmDelete(event)">
                Delete topic
            </button>
            <input type="hidden" name="deleteTopic">
        </form>
    </div>

</div>
<footer class="footer text-center fixed-bottom">
    <div class="container">
        <p>© 2023 - 2024 - Bakalárska práca - Vincent Pálfy</p>
    </div>
</footer>
<script>
    document.getElementById("form").addEventListener('submit', checkFormB);

    function checkFormB(e) {
        let form = e.target;
        let slovakName = document.getElementById("slovakName");
        let englishName = document.getElementById("englishName");
        let pdfSelect = document.getElementById("pdfSelect");
        let fileToUpload = document.getElementById("fileToUpload");
        document.getElementById("fileToUpload");
        let pdfUpload = document.getElementById("pdfUpload");
        e.preventDefault();
        let valid = true;
        if (!isValidInput(slovakName)) {
            valid = false;
        }
        if (!isValidInput(englishName)) {
            valid = false;
        }
        if (pdfSelect.value === "" && pdfUpload.style.display === "none") {
            pdfSelect.classList.add("is-invalid");
            valid = false;
        } else {
            pdfSelect.classList.remove("is-invalid");
        }
        if (pdfUpload.style.display === "block" && fileToUpload.value === "") {
            fileToUpload.classList.add("is-invalid");
            valid = false;
        } else {
            fileToUpload.classList.remove("is-invalid");
        }
        if (valid) {
            form.removeEventListener('submit', checkFormB);
            form.submit();
        } else {
            toastr.error("Please fill in all fields correctly.");
        }
    }
</script>
<?php $masterDB->close(); ?>
</body>
</html>
