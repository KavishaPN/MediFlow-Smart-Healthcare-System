<?php
/*
    ===============================================
    File: doctor/referrals.php
    Purpose: Doctor creates a referral (choose specialist
             department, then specialist doctor from that
             department), views referrals they created, and
             updates the status of referrals they received.
             Status can only move: Pending -> Accepted,
             Pending -> Rejected, Accepted -> Completed.
    ===============================================
*/
session_start();
require '../config/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'doctor') {
    header("Location: ../auth/login.php");
    exit;
}

$doctor_id = $_SESSION['user_id'];
$error     = "";
$success   = "";

// --- CREATE REFERRAL ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_referral'])) {

    $patient_id               = $_POST['patient_id'];
    $specialist_department_id = $_POST['specialist_department_id'];
    $specialist_doctor_id     = $_POST['specialist_doctor_id'];
    $reason                   = trim($_POST['reason']);
    $clinical_notes           = trim($_POST['clinical_notes']);
    $priority                 = $_POST['priority'];

    if ($patient_id == "" || $specialist_doctor_id == "") {
        $error = "Please choose a patient and a specialist doctor.";
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO referrals
                (patient_id, referring_doctor_id, specialist_department_id, specialist_doctor_id,
                 reason, clinical_notes, priority)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $patient_id, $doctor_id, $specialist_department_id, $specialist_doctor_id,
            $reason, $clinical_notes, $priority
        ]);
        $success = "Referral created successfully.";
    }
}

// --- UPDATE STATUS of a referral received as specialist ---
// Allowed moves only: Pending -> Accepted, Pending -> Rejected, Accepted -> Completed
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {

    $referral_id = $_POST['referral_id'];
    $new_status  = $_POST['status'];

    // Get the current status of this referral (only if it belongs to this specialist).
    $stmt = $pdo->prepare("SELECT status FROM referrals WHERE referral_id = ? AND specialist_doctor_id = ?");
    $stmt->execute([$referral_id, $doctor_id]);
    $current = $stmt->fetch();

    $allowed = false;
    if ($current) {
        if ($current['status'] == 'Pending' && ($new_status == 'Accepted' || $new_status == 'Rejected')) {
            $allowed = true;
        }
        if ($current['status'] == 'Accepted' && $new_status == 'Completed') {
            $allowed = true;
        }
    }

    if ($allowed) {
        $stmt = $pdo->prepare(
            "UPDATE referrals SET status = ? WHERE referral_id = ? AND specialist_doctor_id = ?"
        );
        $stmt->execute([$new_status, $referral_id, $doctor_id]);
        $success = "Referral status updated.";
    } else {
        $error = "That status change is not allowed.";
    }
}

// --- Data for the create-referral form ---
$patients    = $pdo->query("SELECT * FROM patients ORDER BY full_name")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();

// Step 2: once a department is chosen, load only doctors from that department.
$step2 = false;
$specialists = [];
$chosen_department = null;
if (isset($_GET['step']) && $_GET['step'] == '2' && isset($_GET['department_id'])) {
    $step2 = true;
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE department_id = ?");
    $stmt->execute([$_GET['department_id']]);
    $chosen_department = $stmt->fetch();

    $stmt = $pdo->prepare(
        "SELECT * FROM doctors WHERE department_id = ? AND doctor_id != ? ORDER BY full_name"
    );
    $stmt->execute([$_GET['department_id'], $doctor_id]);
    $specialists = $stmt->fetchAll();
}

// --- Referrals this doctor created ---
$stmt = $pdo->prepare(
    "SELECT referrals.*, patients.full_name AS patient_name, doctors.full_name AS specialist_name
     FROM referrals
     JOIN patients ON referrals.patient_id = patients.patient_id
     JOIN doctors ON referrals.specialist_doctor_id = doctors.doctor_id
     WHERE referrals.referring_doctor_id = ?
     ORDER BY referrals.referral_date DESC"
);
$stmt->execute([$doctor_id]);
$created_referrals = $stmt->fetchAll();

// --- Referrals this doctor received as specialist ---
$stmt = $pdo->prepare(
    "SELECT referrals.*, patients.full_name AS patient_name, doctors.full_name AS referring_name
     FROM referrals
     JOIN patients ON referrals.patient_id = patients.patient_id
     JOIN doctors ON referrals.referring_doctor_id = doctors.doctor_id
     WHERE referrals.specialist_doctor_id = ?
     ORDER BY referrals.referral_date DESC"
);
$stmt->execute([$doctor_id]);
$received_referrals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Referrals - MediFlow</title>
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
        <h4 class="text-primary mb-4">Referrals</h4>

        <?php if ($error != "") { ?>
            <div class="alert alert-danger py-2"><?php echo $error; ?></div>
        <?php } ?>
        <?php if ($success != "") { ?>
            <div class="alert alert-success py-2"><?php echo $success; ?></div>
        <?php } ?>

        <!-- CREATE REFERRAL -->
        <div class="card p-3 mb-4">
            <h6 class="text-primary mb-3">Create a Referral</h6>

            <?php if (!$step2) { ?>
                <!-- Step 1: choose specialist department -->
                <form method="get" action="referrals.php" class="row g-2">
                    <input type="hidden" name="step" value="2">
                    <div class="col-md-6">
                        <select name="department_id" class="form-select" required>
                            <option value="">-- Select Specialist Department --</option>
                            <?php foreach ($departments as $dept) { ?>
                                <option value="<?php echo $dept['department_id']; ?>">
                                    <?php echo htmlspecialchars($dept['department_name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Continue</button>
                    </div>
                </form>

            <?php } else { ?>
                <!-- Step 2: choose patient, specialist doctor (from chosen department), and details -->
                <p class="text-muted small">
                    Department: <strong><?php echo htmlspecialchars($chosen_department['department_name']); ?></strong>
                    | <a href="referrals.php">Change department</a>
                </p>

                <form method="post" action="referrals.php">
                    <input type="hidden" name="specialist_department_id" value="<?php echo $chosen_department['department_id']; ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Patient</label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">-- Select Patient --</option>
                                <?php foreach ($patients as $p) { ?>
                                    <option value="<?php echo $p['patient_id']; ?>">
                                        <?php echo htmlspecialchars($p['full_name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Specialist Doctor</label>
                            <select name="specialist_doctor_id" class="form-select" required>
                                <option value="">-- Select Specialist --</option>
                                <?php foreach ($specialists as $doc) { ?>
                                    <option value="<?php echo $doc['doctor_id']; ?>">
                                        Dr. <?php echo htmlspecialchars($doc['full_name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="Normal">Normal</option>
                                <option value="Urgent">Urgent</option>
                                <option value="Emergency">Emergency</option>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Reason</label>
                            <input type="text" name="reason" class="form-control">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Clinical Notes</label>
                            <textarea name="clinical_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <button type="submit" name="create_referral" class="btn btn-primary mt-3">Create Referral</button>
                </form>
            <?php } ?>
        </div>

        <!-- REFERRALS I CREATED -->
        <div class="card p-3 mb-4">
            <h6 class="text-primary mb-3">Referrals I Created</h6>
            <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Specialist</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($created_referrals as $r) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['patient_name']); ?></td>
                        <td>Dr. <?php echo htmlspecialchars($r['specialist_name']); ?></td>
                        <td><?php echo $r['priority']; ?></td>
                        <td>
                            <?php
                                $badge = "secondary";
                                if ($r['status'] == 'Accepted')  $badge = "primary";
                                if ($r['status'] == 'Completed') $badge = "success";
                                if ($r['status'] == 'Rejected')  $badge = "danger";
                                if ($r['status'] == 'Pending')   $badge = "warning";
                            ?>
                            <span class="badge bg-<?php echo $badge; ?>"><?php echo $r['status']; ?></span>
                        </td>
                        <td><?php echo $r['referral_date']; ?></td>
                    </tr>
                    <?php } ?>
                    <?php if (count($created_referrals) == 0) { ?>
                    <tr><td colspan="5" class="text-center text-muted">You haven't created any referrals yet.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- REFERRALS I RECEIVED -->
        <div class="card p-3">
            <h6 class="text-primary mb-3">Referrals Received</h6>
            <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Referring Doctor</th>
                        <th>Reason</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($received_referrals as $r) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($r['patient_name']); ?></td>
                        <td>Dr. <?php echo htmlspecialchars($r['referring_name']); ?></td>
                        <td><?php echo htmlspecialchars($r['reason']); ?></td>
                        <td><?php echo $r['priority']; ?></td>
                        <td>
                            <?php
                                $badge = "secondary";
                                if ($r['status'] == 'Accepted')  $badge = "primary";
                                if ($r['status'] == 'Completed') $badge = "success";
                                if ($r['status'] == 'Rejected')  $badge = "danger";
                                if ($r['status'] == 'Pending')   $badge = "warning";
                            ?>
                            <span class="badge bg-<?php echo $badge; ?>"><?php echo $r['status']; ?></span>
                        </td>
                        <td>
                            <?php if ($r['status'] == 'Pending') { ?>
                                <!-- Pending can only move to Accepted or Rejected -->
                                <form method="post" action="referrals.php" class="d-flex gap-2">
                                    <input type="hidden" name="referral_id" value="<?php echo $r['referral_id']; ?>">
                                    <select name="status" class="form-select form-select-sm" style="width: auto;">
                                        <option value="Accepted">Accepted</option>
                                        <option value="Rejected">Rejected</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            <?php } elseif ($r['status'] == 'Accepted') { ?>
                                <!-- Accepted can only move to Completed -->
                                <form method="post" action="referrals.php" class="d-flex gap-2">
                                    <input type="hidden" name="referral_id" value="<?php echo $r['referral_id']; ?>">
                                    <input type="hidden" name="status" value="Completed">
                                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Mark Completed</button>
                                </form>
                            <?php } else { ?>
                                <!-- Rejected or Completed: no further change allowed -->
                                <span class="text-muted small">&mdash;</span>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if (count($received_referrals) == 0) { ?>
                    <tr><td colspan="6" class="text-center text-muted">No referrals received yet.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        </div>

    </div>

</body>
</html>