<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/CSRF_Token.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
check(['Teacher']);

$masterDB = getDatabaseConnection();
$currentUser = $_SESSION['username'] ?? '';

?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Test questions</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="/css/teacher/test.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
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
                <li class="nav-item"><a class="nav-link" href="/html/teacher/slovensky/test.php">Slovak version</a></li>
                <li class="nav-item"><a class="nav-link" href="/php/logout.php">Logout</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class='container'>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
        <div class="form-group">
            <label for="lectureSelectForTopics">Select a lecture</label>
            <select class="form-control" id="lectureSelectForTopics" name="lecture">
                <option value="">Select a lecture</option>
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
    <div id="searchQuestions" style="display: none;">
        <form action="" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
            <div class="form-group">
                <label for="topicID">Select topic</label>
                <select class="form-control" name="topicID" id="topicID" required>
                    <option value="">Select topic</option>
                </select>
            </div>
        </form>
        <div id="questionsTableSection" style="display: none;">
            <h3>Questions</h3>
            <table id="questionsTable" class="table table-striped">
                <thead>
                <tr>
                    <th>Slovak questions</th>
                    <th>English questions</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="questionsTableBody">
                </tbody>
            </table>
            <div class="mt-4">
                <form id="updateNumForm" method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    <input type="hidden" id="lectureInput" name="lecture" value="">
                    <input type="hidden" id="topicInput" name="topic" value="">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="basic-addon1">Number of questions:</span>
                                    </div>
                                    <input type="number" class="form-control" name="numQuestions" id="num"
                                           placeholder="Number of Questions" aria-label="Number of Questions"
                                           aria-describedby="basic-addon1">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary" name="updateNum"
                                                id="updateNumBtn">Update number
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <a href="" class="btn btn-primary mt-3" id="addQuestionLink">Add a new question</a>
        </div>
    </div>
    <footer class='footer text-center footer-dark bg-dark fixed-bottom'>
        <p>© 2023 - 2024 - Bakalárska práca - Vincent Pálfy</p>
    </footer>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const lectureSelectForTopics = document.getElementById('lectureSelectForTopics');
        const searchQuestions = document.getElementById('searchQuestions');
        const questionsTableSection = document.getElementById('questionsTableSection');
        document.getElementById('questionsTableBody');
        const questionsTable = $('#questionsTable').DataTable({
            columnDefs: [
                {width: '33%', targets: 0},
                {width: '33%', targets: 1},
                {width: '12%', targets: 2},
            ],
            autoWidth: false
        });

        lectureSelectForTopics.addEventListener('change', function () {
            const lecture = this.value;
            const topicSelect = document.querySelector('[name="topicID"]');

            for (let i = topicSelect.options.length - 1; i > 0; i--) {
                topicSelect.options.remove(i);
            }


            searchQuestions.style.display = 'none';
            questionsTableSection.style.display = 'none';

            if (lecture) {
                fetch(`learn/getTopics.php?lecture=${lecture}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.length === 0) {
                            toastr.error('No topics were found for this lecture.');
                            return;
                        }
                        data.forEach(topic => {
                            const option = new Option(topic.english_name, topic.test);
                            topicSelect.add(option);
                        });
                        if (data.length > 0) {
                            searchQuestions.style.display = 'block';
                        } else {
                            searchQuestions.style.display = 'none';
                        }
                    })
                    .catch(() => {
                        searchQuestions.style.display = 'none';
                    });
            }
        });


        document.querySelector('[name="topicID"]').addEventListener('change', function () {
            const selectedTopic = this.value;
            const selectedLecture = lectureSelectForTopics.value;

            if (!selectedTopic) {
                questionsTableSection.style.display = 'none';
                questionsTable.clear().draw();
            } else {
                fetchQuestions(selectedTopic, selectedLecture);
            }
        });


        function fetchQuestions(topic, lecture) {
            fetch(`learn/getQuestions.php?topic=${topic}&lecture=${lecture}`)
                .then(response => response.json())
                .then(data => {
                    questionsTable.clear();

                    Object.entries(data.english).forEach(([id, question]) => {
                        const slovakQuestion = encodeHtml(data.slovak[id]);
                        const englishQuestion = encodeHtml(question);
                        const changeUrl = `learn/changeQuestion.php?id=${encodeURIComponent(id)}&topic=${encodeURIComponent(topic)}&lecture=${encodeURIComponent(lecture)}`;

                        questionsTable.row.add([
                            slovakQuestion,
                            englishQuestion,
                            `<div class='btn-group' role='group'>
                                <a href="${changeUrl}" class="btn btn-info btn-sm">Change</a>
                                <button type="button" class="btn btn-danger btn-sm deleteQuestionBtn" data-id="${id}" data-topic="${topic}" data-lecture="${lecture}">Remove</button>
                             </div>`
                        ]);
                    });


                    const num = data.num;
                    document.getElementById("num").setAttribute("placeholder", num);
                    document.getElementById("num").value = '';

                    questionsTable.draw();
                    document.getElementById('questionsTableSection').style.display = 'block';

                    const addQuestionLink = document.getElementById('addQuestionLink');
                    addQuestionLink.href = `learn/insertQuestion.php?topic=${encodeURIComponent(topic)}&lecture=${encodeURIComponent(lecture)}`;
                    addQuestionLink.style.display = 'inline-block';

                    document.querySelectorAll('.deleteQuestionBtn').forEach(button => {
                        button.addEventListener('click', function () {
                            const questionID = this.getAttribute('data-id');
                            const topicName = this.getAttribute('data-topic');
                            const lectureName = this.getAttribute('data-lecture');

                            deleteQuestionAjax(questionID, topicName, lectureName);
                        });
                    });
                })
                .catch(error => console.error('Error:', error));
        }

        function deleteQuestionAjax(questionID, topicName, lectureName) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    const csrfToken = document.querySelector('input[name="csrf_token"]').value;
                    const formData = new FormData();
                    formData.append('deleteQuestion', 'true');
                    formData.append('questionID', questionID);
                    formData.append('topicName', topicName);
                    formData.append('lectureName', lectureName);
                    formData.append('csrf_token', csrfToken);

                    fetch(`learn/deleteQuestion.php`, {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => {
                            if (response.ok) {
                                fetchQuestions(topicName, lectureName);
                                toastr.success('The question has been successfully deleted.');
                            } else {
                                toastr.error('Failed to delete the question.');
                            }
                        })
                        .catch(() => {
                            toastr.error('Failed to delete the question.');
                        });
                }
            });
        }


        function encodeHtml(str) {
            return str.replace(/[\u00A0-\u9999<>&]/gim, function (i) {
                return '&#' + i.charCodeAt(0) + ';';
            });
        }

        $('#updateNumForm').submit(function (event) {
            event.preventDefault();
            let selectedLecture = $('#lectureSelectForTopics').val();
            let selectedTopic = $('[name="topicID"]').val();
            $('#lectureInput').val(selectedLecture);
            $('#topicInput').val(selectedTopic);
            let formData = $(this).serialize();
            $.ajax({
                type: 'POST',
                data: formData,
                url: 'learn/updateNumberOfQuestions.php',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        document.getElementById("num").setAttribute("placeholder", document.getElementById("num").value);
                        document.getElementById("num").value = '';
                        toastr.success(response.message);
                    } else {
                        document.getElementById("num").value = '';
                        toastr.error(response.message);
                    }
                },
                error: function () {
                    toastr.error('Network error: Please try again.');
                }
            });
        });
    });

</script>

</body>
</html>
