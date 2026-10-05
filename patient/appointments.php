<?php
/*
    ===============================================
    File: patient/appointments.php
    Purpose: Patient can BOOK a new appointment and VIEW
             their appointment history, all in this ONE file.
    ===============================================
*/
session_start();
require '../config/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'patient') {
    header("Location: ../auth/login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];
$error      = "";
$success    = "";

// --- BOOK APPOINTMENT: triggered when the booking form below is submitted ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_appointment'])) {

    $doctor_id        = $_POST['doctor_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $reason           = trim($_POST['reason']);

    // Get today's date and current time, so we can block past bookings.
    $today_date = date("Y-m-d");
    $current_time = date("H:i");

    if ($doctor_id == "" || $appointment_date == "" || $appointment_time == "") {
        $error = "Please choose a doctor, date, and time.";
    } elseif ($appointment_date < $today_date) {
        $error = "You cannot book an appointment for a past date.";
    } elseif ($appointment_date == $today_date && $appointment_time < $current_time) {
        $error = "You cannot book an appointment for a past time today.";
    } else {
        // New appointments always start as "Pending" (default value in the database).
        $stmt = $pdo->prepare(
            "INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, reason)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$patient_id, $doctor_id, $appointment_date, $appointment_time, $reason]);

        $success = "Appointment booked successfully. Status: Pending.";
    }
}

// --- Doctors list for the dropdown, with department name shown alongside ---
$doctors = $pdo->query(
    "SELECT doctors.doctor_id, doctors.full_name, departments.department_name
     FROM doctors
     LEFT JOIN departments ON doctors.department_id = departments.department_id
     ORDER BY doctors.full_name"
)->fetchAll();

// --- This patient's appointment history ---
$stmt = $pdo->prepare(
    "SELECT appointments.*, doctors.full_name AS doctor_name
     FROM appointments
     JOIN doctors ON appointments.doctor_id = doctors.doctor_id
     WHERE appointments.patient_id = ?
     ORDER BY appointments.appointment_date DESC, appointments.appointment_time DESC"
);
$stmt->execute([$patient_id]);
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
            <a href="dashboard.php" class="navbar-brand mb-0 h1"><i class="fa-solid fa-hospital me-2"></i>MediFlow</a>
            <div>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
                <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h4 class="text-primary mb-4">My Appointments</h4>

        <?php if ($error != "") { ?>
            <div class="alert alert-danger py-2"><?php echo $error; ?></div>
        <?php } ?>
        <?php if ($success != "") { ?>
            <div class="alert alert-success py-2"><?php echo $success; ?></div>
        <?php } ?>

        <div class="row g-4">

            <!-- BOOK APPOINTMENT FORM -->
            <div class="col-md-4">
                <div class="card p-3">
                    <h6 class="text-primary mb-3">Book an Appointment</h6>

                    <form method="post" action="appointments.php">

                        <div class="mb-3">
                            <label class="form-label">Doctor</label>
                            <select name="doctor_id" class="form-select" required>
                                <option value="">-- Select Doctor --</option>
                                <?php foreach ($doctors as $doc) { ?>
                                    <option value="<?php echo $doc['doctor_id']; ?>">
                                        Dr. <?php echo htmlspecialchars($doc['full_name']); ?>
                                        (<?php echo htmlspecialchars($doc['department_name']); ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="appointment_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Time</label>
                            <input type="time" name="appointment_time" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea name="reason" class="form-control" rows="2" placeholder="e.g. Fever, checkup..."></textarea>
                        </div>

                        <button type="submit" name="book_appointment" class="btn btn-primary w-100">Book Appointment</button>
                    </form>
                </div>
            </div>

            <!-- APPOINTMENT HISTORY -->
            <div class="col-md-8">
                <div class="card p-3">
                    <h6 class="text-primary mb-3">Appointment History</h6>
                    <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
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
                                <td>Dr. <?php echo htmlspecialchars($a['doctor_name']); ?></td>
                                <td><?php echo $a['appointment_date']; ?></td>
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
                            </tr>
                            <?php } ?>
                            <?php if (count($appointments) == 0) { ?>
                            <tr><td colspan="6" class="text-center text-muted">You have no appointments yet.</td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>