<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
check(['Teacher']);
$conn = getDatabaseConnection();

$rolesResult = $conn->query("SELECT DISTINCT role FROM users WHERE role IN ('Student') ORDER BY role");
$roles = [];
while ($row = $rolesResult->fetch_assoc()) {
    $roles[] = $row['role'];
}

$statusesResult = $conn->query("SELECT DISTINCT active FROM users WHERE role IN ('Student')");
$statuses = [];
while ($row = $statusesResult->fetch_assoc()) {
    $statuses[] = $row['active'] ? "Active" : "Inactive";
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Správa používateľov</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css"
          href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="/css/teacher/students.css">
    <script src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                <li class="nav-item"><a class="nav-link" href="/html/teacher/english/students.php">Anglická verzia</a>
                </li>
                <li class="nav-item"><a class="nav-link" href="/php/logout.php">Odhlásiť sa</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container mt-5 content">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="table-responsive" style="overflow-x: auto">
                <div class="mb-3">
                    <label for="statusFilter">Filtrovanie podľa stavu:</label>
                    <select id="statusFilter" class="form-control">
                        <option value="">Všetky</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo $status; ?>"><?php echo $status; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <table id="usersTable" class="table display" style="width:100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Meno</th>
                        <th>Priezvisko</th>
                        <th>Role</th>
                        <th>Stav</th>
                        <th>Akcie</th>
                    </tr>
                    </thead>
                </table>
            </div>
            <a href="people/addUser.php" class="btn btn-success mt-3 btn-block">Pridať používateľa</a>
        </div>
    </div>
</div>
<footer class='footer text-center fixed-bottom'>
    <div class="container">
        <p>© 2023 - 2024 - Bakalárska práca - Vincent Pálfy</p>
    </div>
</footer>
<script>
    $(function () {
        function updateStatusFilter() {
            $.ajax({
                url: 'people/fetchStatuses.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    let statusFilter = $('#statusFilter');
                    statusFilter.empty();
                    statusFilter.append($('<option>', {value: '', text: 'Všetky'}));
                    $.each(data, function (index, value) {
                        statusFilter.append($('<option>', {value: value, text: value}));
                    });
                }
            });
        }

        let table = $('#usersTable').DataTable({
            "responsive": true,
            "ajax": {
                url: "people/fetchUsers.php",
                type: 'GET',
                dataSrc: function (json) {
                    if (json.data.length === 0) {
                        toastr.info('No data is available in the table.');
                        return [];
                    } else {
                        return json.data;
                    }
                },
                error: function () {
                    toastr.error('Failed to retrieve data.');
                }
            },
            "columns": [
                {"data": "id"},
                {"data": "username"},
                {"data": "email"},
                {"data": "first_name"},
                {"data": "last_name"},
                {"data": "role"},
                {"data": "active"},
                {"data": "actions", "orderable": false, "searchable": false}
            ],
            "language": {
                "emptyTable": "No data available."
            },
            "initComplete": function () {
                $('#statusFilter').on('change', function () {
                    let selectedStatus = this.value;
                    if (selectedStatus === 'Active' || selectedStatus === 'Inactive') {
                        table.column(6).search("^" + selectedStatus + "$", true, false).draw();
                    } else {
                        table.column(6).search('').draw();
                    }
                });
            }
        });


        $('#usersTable tbody').on('click', '.editBtn, .deleteBtn', function () {
            let userId = $(this).data('id');

            if ($(this).hasClass('editBtn')) {
                $.ajax({
                    url: 'people/changeStatus.php',
                    type: 'POST',
                    data: {user_id: userId},
                    dataType: 'json',
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success("The status has been successfully changed");
                        } else {
                            toastr.error(response.message);
                        }
                        table.ajax.reload();
                        updateStatusFilter();
                    },
                    error: function () {
                        toastr.error('There was an error in your request');
                    }
                });
            } else if ($(this).hasClass('deleteBtn')) {
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
                        $.ajax({
                            url: 'people/deleteUser.php',
                            type: 'POST',
                            data: {user_id: userId},
                            dataType: 'json',
                            success: function () {
                                table.ajax.reload();
                                updateStatusFilter();
                                toastr.success("Successfully removed user");
                            },
                            error: function () {
                                toastr.error('There was an error in your request');
                            }
                        });
                    }
                });
            }
        });
    });
</script>
</body>
</html>
