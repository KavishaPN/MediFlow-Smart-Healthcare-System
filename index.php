<?php
/*
    File: index.php
    Purpose: Landing page - Navbar, Hero, Features, Login/Register buttons, Footer.
             Fills the full screen height.
*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>MediFlow - Smart Healthcare System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --accent: #3F72AF; }
        html, body { height: 100%; }
        body { background-color: #ffffff; }
        .bg-primary { background-color: var(--accent) !important; }
        .btn-primary { background-color: var(--accent) !important; border-color: var(--accent) !important; }
        .btn-outline-primary { color: var(--accent) !important; border-color: var(--accent) !important; }
        .btn-outline-primary:hover { background-color: var(--accent) !important; color: #fff !important; }
        .text-primary { color: var(--accent) !important; }
        .icon-box {
            width: 45px; height: 45px; border-radius: 50%;
            background-color: #eaf1fb; color: var(--accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; margin: 0 auto 10px auto;
        }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
        footer { border-top: 1px solid #e9ecef; }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-primary py-2">
        <div class="container">
            <span class="navbar-brand mb-0 h1"><i class="fa-solid fa-hospital me-2"></i>MediFlow</span>
        </div>
    </nav>

    <!-- MAIN CONTENT: fills remaining screen space -->
    <main class="flex-grow-1 d-flex flex-column justify-content-center">

        <!-- HERO -->
        <section class="text-center py-4">
            <div class="container">
                <h1 class="text-primary mb-2">MediFlow - Smart Healthcare System</h1>
                <p class="text-muted col-md-8 mx-auto mb-3">
                    A Hospital Management System to manage departments,
                    doctors, patients, and appointments in one place.
                </p>
                <a href="auth/login.php" class="btn btn-primary me-2">Login</a>
                <a href="auth/register.php" class="btn btn-outline-primary">Patient Registration</a>
            </div>
        </section>

        <!-- FEATURES -->
        <section class="py-3">
            <div class="container">
                <div class="row g-3 text-center">

                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="icon-box"><i class="fa-solid fa-building-columns"></i></div>
                                <h6>Departments</h6>
                                <p class="text-muted small mb-0">Admin manages hospital departments.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="icon-box"><i class="fa-solid fa-user-doctor"></i></div>
                                <h6>Doctors</h6>
                                <p class="text-muted small mb-0">Doctors manage their appointments.</p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="icon-box"><i class="fa-solid fa-calendar-check"></i></div>
                                <h6>Appointments</h6>
                                <p class="text-muted small mb-0">Patients book and track appointments.</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main>

    <!-- FOOTER -->
    <footer class="text-center py-2">
        <p class="text-muted small mb-0">
            &copy; <?php echo date("Y"); ?> MediFlow - Smart Healthcare System
        </p>
    </footer>

</body>
</html>