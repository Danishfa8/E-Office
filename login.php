<!-- login.php -->
<?php
session_start();

// Jika sudah login, langsung redirect ke dashboard/admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php"); // Ganti dengan halaman yang sesuai
    exit;
}

// Proses login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Gantilah username dan password ini dengan yang sesuai
    $username = "admin";
    $password = "admin123"; // Password yang di-hash lebih baik

    $input_username = $_POST['username'];
    $input_password = $_POST['password'];

    // Verifikasi kredensial login
    if ($input_username === $username && $input_password === $password) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin_dashboard.php"); // Ganti dengan halaman admin
        exit;
    } else {
        $error_message = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link rel="icon" href="images/bps.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5">
        <h2 class="text-center">Login Admin</h2>
        <form method="POST">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </div>

</body>

</html>