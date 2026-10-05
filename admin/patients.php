<?php
/*
    ===============================================
    File: admin/patients.php
    Purpose: View Patients — read-only list for the admin.
             Admin cannot add/edit/delete patients (patients
             manage their own accounts via registration/profile).
    ===============================================
*/
session_start();
require '../config/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch all registered patients.
$patients = $pdo->query("SELECT * FROM patients ORDER BY patient_id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Patients - MediFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root { --accent: #3F72AF; }
        body { background-color: #F9FBFD; }
        .bg-primary { background-color: var(--accent) !important; }
        .text-primary { color: var(--accent) !important; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-primary py-2">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand mb-0 h1"><i class="fa-solid fa-hospital me-2"></i>MediFlow Admin</a>
            <div>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
                <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h4 class="text-primary mb-4">Registered Patients</h4>

        <div class="card p-3">
            <div class="table-responsive">
                <p class="text-muted">
                    Total Patients:
                    <span class="text-primary fw-bold"><?php echo count($patients); ?></span>
                </p>
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($patients as $p) { ?>
                    <tr>
                        <td><?php echo $p['patient_id']; ?></td>
                        <td><?php echo htmlspecialchars($p['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($p['email']); ?></td>
                        <td><?php echo htmlspecialchars($p['phone']); ?></td>
                        <td><?php echo htmlspecialchars($p['gender']); ?></td>
                        <td><?php echo htmlspecialchars($p['address']); ?></td>
                    </tr>
                    <?php } ?>
                    <?php if (count($patients) == 0) { ?>
                    <tr><td colspan="6" class="text-center text-muted">No patients registered yet.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

</body>
</html>