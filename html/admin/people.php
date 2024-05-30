<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require $_SERVER['DOCUMENT_ROOT'] . '/php/checkLog.php';
require $_SERVER['DOCUMENT_ROOT'] . '/php/connectToDatabase.php';
check(['Admin']);
$conn = getDatabaseConnection();

$rolesResult = $conn->query("SELECT DISTINCT role FROM users WHERE role IN ('Teacher', 'Student') ORDER BY role");
$roles = [];
while ($row = $rolesResult->fetch_assoc()) {
    $roles[] = $row['role'];
}

$statusesResult = $conn->query("SELECT DISTINCT active FROM users WHERE role IN ('Teacher', 'Student')");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css"
          href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="/css/admin/people.css">
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
<div class="container mt-5 content">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="table-responsive" style="overflow-x: auto">
                <div class="mb-3">
                    <label for="roleFilter">Filter by Role:</label>
                    <select id="roleFilter" class="form-control">
                        <option value="">All</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role; ?>"><?php echo $role; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="statusFilter">Filter by Status:</label>
                    <select id="statusFilter" class="form-control">
                        <option value="">All</option>
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
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                </table>
            </div>
            <a href="people/addUser.php" class="btn btn-success mt-3 btn-block">Add user</a>
        </div>
    </div>
</div>
<footer class='footer text-center footer-dark bg-dark fixed-bottom'>
    <p style="color: white;">© 2023 - 2024 - Bakalárska práca - Vincent Pálfy</p>
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
                    statusFilter.append($('<option>', {value: '', text: 'All'}));
                    $.each(data, function (index, value) {
                        statusFilter.append($('<option>', {value: value, text: value}));
                    });
                }
            });
        }

        function updateRoleFilter() {
            $.ajax({
                url: 'people/fetchRoles.php',
                type: 'GET',
                dataType: 'json',
                success: function (data) {
                    let roleFilter = $('#roleFilter');
                    roleFilter.empty();
                    roleFilter.append($('<option>', {value: '', text: 'All'}));
                    $.each(data, function (index, value) {
                        roleFilter.append($('<option>', {value: value, text: value}));
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
                    if (json.length === 0) {
                        toastr.info('No data available in table.');
                        return [];
                    } else {
                        return json.data || [];
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
            "initComplete": function () {
                $('#roleFilter').on('change', function () {
                    let searchVal = this.value;
                    table.column(5).search(searchVal).draw();
                });

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
                            toastr.success("Status changed successfully");
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
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
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
                                updateRoleFilter();
                                updateStatusFilter();
                                toastr.success("Successfully deleted user");
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
