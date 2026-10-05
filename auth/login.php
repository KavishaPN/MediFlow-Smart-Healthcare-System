<?php
//To start the session
session_start();

//To connect database
require '../config/db_connect.php';

// To hold an error message if login fails.
$error = "";

// This block only runs when the form is submitted (POST request).
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Read form values. trim() removes accidental extra spaces.
    $role     = $_POST['role'];
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // We will look up the user in a different table depending on
    // which role was selected, but the overall logic is the same:
    // 1. Find the row matching the username/email
    // 2. Check the password using password_verify()
    // 3. Store session data and redirect to that role's dashboard

    if ($role == 'admin') {
        // Admin table uses "username" as the login field.
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['admin_id'];
            $_SESSION['role']      = 'admin';
            $_SESSION['full_name'] = $user['full_name'];
            header("Location: ../admin/dashboard.php");
            exit;
        } else {
            $error = "Invalid admin username or password.";
        }

    } elseif ($role == 'doctor') {
        // Doctors table uses "email" as the login field.
        $stmt = $pdo->prepare("SELECT * FROM doctors WHERE email = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['doctor_id'];
            $_SESSION['role']      = 'doctor';
            $_SESSION['full_name'] = $user['full_name'];
            header("Location: ../doctor/dashboard.php");
            exit;
        } else {
            $error = "Invalid doctor email or password.";
        }

    } elseif ($role == 'patient') {
        // Patients table uses "email" as the login field.
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE email = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['patient_id'];
            $_SESSION['role']      = 'patient';
            $_SESSION['full_name'] = $user['full_name'];
            header("Location: ../patient/dashboard.php");
            exit;
        } else {
            $error = "Invalid patient email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - MediFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --accent: #3F72AF; }
        body { background-color: #F9FBFD; }
        .bg-primary { background-color: var(--accent) !important; }
        .btn-primary { background-color: var(--accent) !important; border-color: var(--accent) !important; }
        .text-primary { color: var(--accent) !important; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-primary py-2">
        <div class="container">
            <a href="../index.php" class="navbar-brand mb-0 h1">
                <i class="fa-solid fa-hospital me-2"></i>MediFlow
            </a>
        </div>
    </nav>

    <!-- LOGIN FORM -->
    <main class="flex-grow-1 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body p-4">
                            <h4 class="text-primary text-center mb-4">Login to MediFlow</h4>

                            <!-- Show an error message if login failed -->
                            <?php if ($error != "") { ?>
                                <div class="alert alert-danger py-2"><?php echo $error; ?></div>
                            <?php } ?>

                            <!-- method="post" sends data securely (not shown in URL) -->
                            <form method="post" action="login.php">

                                <div class="mb-3">
                                    <label class="form-label">Login as</label>
                                    <select name="role" class="form-select" required>
                                        <option value="admin">Admin</option>
                                        <option value="doctor">Doctor</option>
                                        <option value="patient" selected>Patient</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Username / Email</label>
                                    <input type="text" name="username" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Login</button>
                            </form>

                            <p class="text-center text-muted small mt-3 mb-0">
                                New patient?
                                <a href="register.php" class="text-primary">Register here</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>