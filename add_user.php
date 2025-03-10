<?php 
include 'db.php'; 
session_start();

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true || $_SESSION['level'] !== 'admin') {
    $_SESSION['redirect_message'] = 'Anda belum login';
    header("Location: index.php");
    exit();
}

$alert = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add"])) {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $level = $_POST["level"];
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $check_user = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($check_user->num_rows > 0) {
        $alert = "duplicate";
    } else {
        $conn->query("INSERT INTO users (username, password, level) VALUES ('$username', '$password_hash', '$level')");
        $alert = "add";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update"])) {
    $id = $_POST["id"];
    $username = $_POST["username"];
    $level = $_POST["level"];

    $check_user = $conn->query("SELECT * FROM users WHERE username='$username' AND id != $id");
    if ($check_user->num_rows > 0) {
        $alert = "duplicate";
    } else {
        $conn->query("UPDATE users SET username='$username', level='$level' WHERE id=$id");
        $alert = "update";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete"])) {
    $id = $_POST["id"];
    $conn->query("DELETE FROM users WHERE id=$id");

    $alert = "delete";
}

$users = $conn->query("SELECT * FROM users");

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen User</title>
    <link rel="icon" href="images/bps.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f4f8;
            transition: background-color 0.3s ease-in-out;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 250px;
            background-color: #2f3542;
            color: white;
            padding-top: 40px;
            z-index: 1000;
            transition: transform 0.3s ease-in-out;
        }

        .sidebar .logo {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            padding: 15px;
        }

        .sidebar .menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar .menu li {
            padding: 10px 20px;
            font-size: 1.1rem;
            cursor: pointer;
        }

        .sidebar .menu li:hover {
            background-color: #4e5d6e;
            transition: background-color 0.2s ease-in-out;
        }

        .content {
            margin-left: 250px;
            padding: 40px 30px;
            min-height: 100vh;
            transition: margin-left 0.3s ease-in-out;
        }

        .toggle-btn {
            display: none;
        }

        .toggle-btn+label {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1001;
            transition: background-color 0.3s ease;
        }

        .toggle-btn:checked+label {
            background-color: #ff4d4d;
        }

        .toggle-btn:checked~.sidebar {
            transform: translateX(-100%);
        }

        .toggle-btn:checked~.content {
            margin-left: 0;
        }

        .card {
            border: none;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            border-radius: 8px;
            overflow: hidden;
        }

        .card-header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            font-size: 1.25rem;
            text-align: center;
        }

        .card-body {
            padding: 20px;
        }

        .table img {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
        }

        .modal-header {
            background-color: #007bff;
            color: white;
        }

        .modal-content {
            border-radius: 10px;
        }

        .btn-sm {
            font-size: 14px;
        }
    </style>
</head>

<body>
    <input type="checkbox" class="toggle-btn" id="toggle-btn">
    <label for="toggle-btn"><i class="fas fa-bars"></i></label>

    <div class="sidebar">
        <div class="logo">Admin</div>
        <ul class="menu">
            <a href="index.php" style="text-decoration : none; color:white;">
                <li>Manajemen Menu</li>
            </a>
            <a href="add_user.php" style="text-decoration : none; color:white;">
                <li>Manajemen User</li>
            </a>
            <a href="logout.php" style="text-decoration : none; color:white;">
                <li>Keluar</li>
            </a>
        </ul>
    </div>

    <div class="content">

        <div class="container mt-5" id="user">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-plus"></i> Manajemen User
                </div>
                <div class="card-body">
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus btn-icon"></i>Tambah User
                    </button>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="userTable">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Level</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $users->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['username']; ?></td>
                                        <td><?php echo $row['level']; ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $row['id']; ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $row['id']; ?>">
                                                <i class="fas fa-trash"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit User</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <div class="mb-3">
                                                            <label>Username</label>
                                                            <input type="text" name="username" class="form-control" value="<?php echo $row['username']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label>Level</label>
                                                            <select name="level" class="form-control" required>
                                                                <option value="admin" <?php echo ($row['level'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                                                <option value="user" <?php echo ($row['level'] == 'user') ? 'selected' : ''; ?>>User</option>
                                                            </select>
                                                        </div>
                                                        <button type="submit" name="update" class="btn btn-primary">Simpan Perubahan</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="deleteModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Hapus User</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Apakah Anda yakin ingin menghapus user ini?</p>
                                                    <form method="POST">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <button type="submit" name="delete" class="btn btn-danger">Hapus</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Level</label>
                            <select name="level" class="form-control" required>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                        </div>
                        <button type="submit" name="add" class="btn btn-primary">Tambah User</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.onload = function() {
            <?php if ($alert == "add"): ?>
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'User berhasil ditambahkan.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            <?php elseif ($alert == "update"): ?>
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'User berhasil diperbarui.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            <?php elseif ($alert == "delete"): ?>
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'User berhasil dihapus.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            <?php elseif ($alert == "duplicate"): ?>
                Swal.fire({
                    title: 'Error!',
                    text: 'Username sudah digunakan.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            <?php endif; ?>
        };
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#userTable').DataTable();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
