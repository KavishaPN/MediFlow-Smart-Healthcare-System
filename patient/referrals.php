<?php
/*
    ===============================================
    File: patient/referrals.php
    Purpose: Patient views their own referrals (read-only).
    ===============================================
*/
session_start();
require '../config/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'patient') {
    header("Location: ../auth/login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    "SELECT referrals.*, d1.full_name AS referring_name, d2.full_name AS specialist_name
     FROM referrals
     JOIN doctors d1 ON referrals.referring_doctor_id = d1.doctor_id
     JOIN doctors d2 ON referrals.specialist_doctor_id = d2.doctor_id
     WHERE referrals.patient_id = ?
     ORDER BY referrals.referral_date DESC"
);
$stmt->execute([$patient_id]);
$referrals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Referrals - MediFlow</title>
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
            <a href="dashboard.php" class="navbar-brand mb-0 h1"><i class="fa-solid fa-hospital me-2"></i>MediFlow</a>
            <div>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
                <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h4 class="text-primary mb-4">My Referrals</h4>

        <div class="card p-3">
            <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Referring Doctor</th>
                        <th>Specialist</th>
                        <th>Reason</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($referrals as $r) { ?>
                    <tr>
                        <td>Dr. <?php echo htmlspecialchars($r['referring_name']); ?></td>
                        <td>Dr. <?php echo htmlspecialchars($r['specialist_name']); ?></td>
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
                        <td><?php echo $r['referral_date']; ?></td>
                    </tr>
                    <?php } ?>
                    <?php if (count($referrals) == 0) { ?>
                    <tr><td colspan="6" class="text-center text-muted">You have no referrals yet.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

</body>
</html>