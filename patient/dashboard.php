<?php
/*
    ===============================================
    File: patient/dashboard.php
    Purpose: Patient's home page after login.
             Simple navigation to Book/View Appointments
             and Edit Profile (no charts, no analytics).
    ===============================================
*/
session_start();
require '../config/db_connect.php';

// Protect this page: only a logged-in patient can view it.
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'patient') {
    header("Location: ../auth/login.php");
    exit;
}

// A quick count of this patient's appointments, just to show on the dashboard.
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_appointments = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Dashboard - MediFlow</title>
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
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-hospital me-2"></i>MediFlow</span>
            <div>
                <span class="text-white me-3">Hi, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h4 class="text-primary mb-4 text-center">Patient Dashboard</h4>

        <div class="row g-3 justify-content-center">

            <div class="col-md-3 col-sm-6">
                <a href="appointments.php" class="text-decoration-none">
                    <div class="card text-center p-4">
                        <div class="icon-box"><i class="fa-solid fa-calendar-check"></i></div>
                        <h6 class="mb-1">Appointments</h6>
                        <p class="text-muted small mb-0"><?php echo $total_appointments; ?> total</p>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="profile.php" class="text-decoration-none">
                    <div class="card text-center p-4">
                        <div class="icon-box"><i class="fa-solid fa-user"></i></div>
                        <h6 class="mb-1">My Profile</h6>
                        <p class="text-muted small mb-0">View / Edit</p>
                    </div>
                </a>
            </div>

            <div class="col-md-3 col-sm-6">
                <a href="referrals.php" class="text-decoration-none">
                    <div class="card text-center p-4">
                        <div class="icon-box"><i class="fa-solid fa-share-from-square"></i></div>
                        <h6 class="mb-1">Referrals</h6>
                        <p class="text-muted small mb-0">View referrals</p>
                    </div>
                </a>
            </div>

        </div>
    </div>

</body>
</html>