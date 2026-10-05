<?php
/*
    ===============================================
    File: admin/appointments.php
    Purpose: View Appointments — read-only list for the admin.
             Admin can see every appointment; only the doctor
             updates its status.
    ===============================================
*/
session_start();
require '../config/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// JOIN with patients and doctors so we can show names instead of just IDs.
$appointments = $pdo->query(
    "SELECT appointments.*, patients.full_name AS patient_name, doctors.full_name AS doctor_name
     FROM appointments
     JOIN patients ON appointments.patient_id = patients.patient_id
     JOIN doctors ON appointments.doctor_id = doctors.doctor_id
     ORDER BY appointments.appointment_date DESC, appointments.appointment_time DESC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Appointments - MediFlow</title>
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
        <h4 class="text-primary mb-4">Appointments</h4>

        <div class="card p-3">
            <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $a) { ?>
                    <tr>
                        <td><?php echo $a['appointment_id']; ?></td>
                        <td><?php echo htmlspecialchars($a['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($a['doctor_name']); ?></td>
                        <td><?php echo date("d-m-Y", strtotime($a['appointment_date'])); ?></td>
                        <td><?php echo date("h:i A", strtotime($a['appointment_time'])); ?></td>
                        <td><?php echo htmlspecialchars($a['reason']); ?></td>
                        <td>
                            <?php
                                // Simple color coding for status, using Bootstrap badge classes.
                                $badge = "secondary";
                                if ($a['status'] == 'Approved')  $badge = "primary";
                                if ($a['status'] == 'Completed') $badge = "success";
                                if ($a['status'] == 'Cancelled') $badge = "danger";
                                if ($a['status'] == 'Pending')   $badge = "warning";
                            ?>
                            <span class="badge bg-<?php echo $badge; ?>"><?php echo $a['status']; ?></span>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if (count($appointments) == 0) { ?>
                    <tr><td colspan="7" class="text-center text-muted">No appointments booked yet.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

</body>
</html>