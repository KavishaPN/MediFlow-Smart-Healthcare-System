<?php
/*
    ===============================================
    File: patient/profile.php
    Purpose: Patient can view and edit their own profile
             (name, phone, gender, address). Email is shown
             read-only since it's used as the login ID.
    ===============================================
*/
session_start();
require '../config/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'patient') {
    header("Location: ../auth/login.php");
    exit;
}

$patient_id = $_SESSION['user_id'];
$success    = "";
$error      = "";

// --- UPDATE PROFILE: triggered when the edit form is submitted ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {

    $full_name = trim($_POST['full_name']);
    $phone     = trim($_POST['phone']);
    $gender    = $_POST['gender'];
    $address   = trim($_POST['address']);

    if ($full_name == "") {
        $error = "Full name is required.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Phone number must contain exactly 10 digits.";
    } else {
        $stmt = $pdo->prepare(
            "UPDATE patients SET full_name = ?, phone = ?, gender = ?, address = ? WHERE patient_id = ?"
        );
        $stmt->execute([$full_name, $phone, $gender, $address, $patient_id]);

        // Keep the session name in sync with the update, so the navbar greeting stays correct.
        $_SESSION['full_name'] = $full_name;

        $success = "Profile updated successfully.";
    }
}

// --- Fetch this patient's current data to fill the form ---
$stmt = $pdo->prepare("SELECT * FROM patients WHERE patient_id = ?");
$stmt->execute([$patient_id]);
$patient = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - MediFlow</title>
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
        <h4 class="text-primary mb-4">My Profile</h4>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4">

                    <?php if ($error != "") { ?>
                        <div class="alert alert-danger py-2"><?php echo $error; ?></div>
                    <?php } ?>
                    <?php if ($success != "") { ?>
                        <div class="alert alert-success py-2"><?php echo $success; ?></div>
                    <?php } ?>

                    <form method="post" action="profile.php">

                        <div class="mb-3">
                            <label class="form-label">Email (cannot be changed)</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($patient['email']); ?>" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control"
                                   value="<?php echo htmlspecialchars($patient['full_name']); ?>" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text"
                                name="phone"
                                class="form-control"
                                maxlength="10"
                                pattern="[0-9]{10}"
                                title="Enter a 10-digit phone number"
                                value="<?php echo htmlspecialchars($patient['phone']); ?>"
                                required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="Male"   <?php if ($patient['gender'] == 'Male')   echo 'selected'; ?>>Male</option>
                                    <option value="Female" <?php if ($patient['gender'] == 'Female') echo 'selected'; ?>>Female</option>
                                    <option value="Other"  <?php if ($patient['gender'] == 'Other')  echo 'selected'; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?php echo htmlspecialchars($patient['address']); ?></textarea>
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-primary w-100">Save Changes</button>
                    </form>

                </div>
            </div>
        </div>
    </div>

</body>
</html>