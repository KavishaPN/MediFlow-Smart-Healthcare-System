<?php
/*
    ===============================================
    File: doctor/appointments.php
    Purpose: Doctor views ONLY their own appointments,
             and can update the status of each one
             (Pending -> Approved -> Completed, or Cancelled).
    ===============================================
*/
session_start();
require '../config/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../auth/login.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];
$success   = "";

// --- UPDATE STATUS: triggered when the doctor changes the dropdown and clicks Update ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {

    $appointment_id = $_POST['appointment_id'];
    $new_status     = $_POST['status'];

    // IMPORTANT: we also check "doctor_id = ?" here so a doctor can only
    // update an appointment that actually belongs to them.
    $stmt = $pdo->prepare(
        "UPDATE appointments SET status = ? WHERE appointment_id = ? AND doctor_id = ?"
    );
    $stmt->execute([$new_status, $appointment_id, $doctor_id]);

    $success = "Appointment status updated.";
}

// --- Fetch only appointments that belong to this logged-in doctor ---
$stmt = $pdo->prepare(
    "SELECT appointments.*, patients.full_name AS patient_name, patients.phone AS patient_phone
     FROM appointments
     JOIN patients ON appointments.patient_id = patients.patient_id
     WHERE appointments.doctor_id = ?
     ORDER BY appointments.appointment_date DESC, appointments.appointment_time DESC"
);
$stmt->execute([$doctor_id]);
$appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Appointments - MediFlow</title>
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
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-primary py-2">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand mb-0 h1"><i class="fa-solid fa-hospital me-2"></i>MediFlow Doctor</a>
            <div>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
                <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <p class="text-muted mb-3">
            Total Appointments:
            <span class="fw-bold text-primary"><?php echo count($appointments); ?></span>
        </p>

        <?php if ($success != "") { ?>
            <div class="alert alert-success py-2"><?php echo $success; ?></div>
        <?php } ?>

        <div class="card p-3">
            <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Patient</th>
                        <th>Phone</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $a) { ?>
                    <tr>
                        <td><?php echo $a['appointment_id']; ?></td>
                        <td><?php echo htmlspecialchars($a['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($a['patient_phone']); ?></td>
                        <td><?php echo date("d-m-Y", strtotime($a['appointment_date'])); ?></td>
                        <td><?php echo date("h:i A", strtotime($a['appointment_time'])); ?></td>
                        <td><?php echo htmlspecialchars($a['reason']); ?></td>
                        <td>
                            <?php
                                $badge = "secondary";
                                if ($a['status'] == 'Approved')  $badge = "primary";
                                if ($a['status'] == 'Completed') $badge = "success";
                                if ($a['status'] == 'Cancelled') $badge = "danger";
                                if ($a['status'] == 'Pending')   $badge = "warning";
                            ?>
                            <span class="badge bg-<?php echo $badge; ?>"><?php echo $a['status']; ?></span>
                        </td>
                        <td>
                            <!-- Small inline form: change status, then submit -->
                            <form method="post" action="appointments.php" class="d-flex gap-2">
                                <input type="hidden" name="appointment_id" value="<?php echo $a['appointment_id']; ?>">
                                <select name="status" class="form-select form-select-sm" style="width: auto;">
                                    <option value="Pending"   <?php if ($a['status'] == 'Pending')   echo 'selected'; ?>>Pending</option>
                                    <option value="Approved"  <?php if ($a['status'] == 'Approved')  echo 'selected'; ?>>Approved</option>
                                    <option value="Completed" <?php if ($a['status'] == 'Completed') echo 'selected'; ?>>Completed</option>
                                    <option value="Cancelled" <?php if ($a['status'] == 'Cancelled') echo 'selected'; ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if (count($appointments) == 0) { ?>
                    <tr><td colspan="8" class="text-center text-muted">No appointments assigned to you yet.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

</body>
</html>