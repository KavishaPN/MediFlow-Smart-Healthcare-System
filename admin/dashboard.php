<?php
/*
    ===============================================
    File: admin/dashboard.php
    Purpose: Admin's home page after login.
             Just navigation links to the other admin pages
             (no charts, no analytics — kept simple).
    ===============================================
*/
session_start();
require '../config/db_connect.php';

// Protect this page: only a logged-in admin can view it.
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - MediFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --accent: #3F72AF; }
        body { background-color: #F9FBFD; }
        .bg-primary { background-color: var(--accent) !important; }
        .text-primary { color: var(--accent) !important; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .icon-box {
            width: 50px; height: 50px; border-radius: 50%;
            background-color: #eaf1fb; color: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; margin: 0 auto 10px auto;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-primary py-2">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-hospital me-2"></i>MediFlow Admin</span>
            <div>
                <span class="text-white me-3">Hi, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h4 class="text-primary mb-4 text-center">Admin Dashboard</h4>

        <div class="row g-3 justify-content-center">

            <div class="col-md-3 col-sm-6">
                <a href="departments.php" class="text-decoration-none">
                    <div class="card text-center p-3">
                        <div class="icon-box"><i class="fa-solid fa-building-columns"></i></div>
                        <h6 class="mb-0">Departments</h6>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="doctors.php" class="text-decoration-none">
                    <div class="card text-center p-3">
                        <div class="icon-box"><i class="fa-solid fa-user-doctor"></i></div>
                        <h6 class="mb-0">Doctors</h6>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="patients.php" class="text-decoration-none">
                    <div class="card text-center p-3">
                        <div class="icon-box"><i class="fa-solid fa-user-injured"></i></div>
                        <h6 class="mb-0">Patients</h6>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="appointments.php" class="text-decoration-none">
                    <div class="card text-center p-3">
                        <div class="icon-box"><i class="fa-solid fa-calendar-check"></i></div>
                        <h6 class="mb-0">Appointments</h6>
                    </div>
                </a>
            </div>

        </div>
    </div>

</body>
</html>