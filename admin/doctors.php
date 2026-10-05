<?php
/*
    ===============================================
    File: admin/doctors.php
    Purpose: Manage Doctors — Add, Edit, Delete, and View,
             all inside this ONE file.
    ===============================================
*/
session_start();
require '../config/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$error   = "";
$success = "";

// --- DELETE ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM doctors WHERE doctor_id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: doctors.php");
    exit;
}

// --- ADD or UPDATE ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_doctor'])) {

    $doctor_id      = $_POST['doctor_id']; // empty = new doctor
    $full_name      = trim($_POST['full_name']);
    $email          = trim($_POST['email']);
    $phone          = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);
    $department_id  = $_POST['department_id'];
    $password       = $_POST['password']; // only used when adding a new doctor

    if ($full_name == "" || $email == "") {
    $error = "Name and Email are required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Please enter a valid email address.";
} elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
    $error = "Phone number must contain exactly 10 digits.";
} elseif ($doctor_id == "") {

    // Check whether email already exists
    $stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {

        $error = "Email already exists.";

    } else {

        // Check whether phone already exists
        $stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE phone = ?");
        $stmt->execute([$phone]);

        if ($stmt->fetch()) {

            $error = "Phone number already exists.";

        } elseif ($password == "") {

            $error = "Password is required for a new doctor.";

        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO doctors (full_name, email, password, phone, specialization, department_id)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $full_name,
                $email,
                $hashed_password,
                $phone,
                $specialization,
                $department_id
            ]);

            $success = "Doctor added successfully.";
        }
    }
}else {
        // Existing doctor -> check email/phone aren't used by ANOTHER doctor first.
        // "doctor_id != ?" excludes this doctor's own row from the check.
        $stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE email = ? AND doctor_id != ?");
        $stmt->execute([$email, $doctor_id]);

        if ($stmt->fetch()) {

            $error = "Email already exists.";

        } else {

            $stmt = $pdo->prepare("SELECT doctor_id FROM doctors WHERE phone = ? AND doctor_id != ?");
            $stmt->execute([$phone, $doctor_id]);

            if ($stmt->fetch()) {

                $error = "Phone number already exists.";

            } else {
                // Existing doctor -> UPDATE (password is not changed here, kept simple)
                $stmt = $pdo->prepare(
                    "UPDATE doctors SET full_name = ?, email = ?, phone = ?, specialization = ?, department_id = ?
                     WHERE doctor_id = ?"
                );
                $stmt->execute([$full_name, $email, $phone, $specialization, $department_id, $doctor_id]);
                $success = "Doctor updated successfully.";
            }
        }
    }
}

// --- EDIT: load existing doctor data to prefill the form ---
$edit_doctor = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM doctors WHERE doctor_id = ?");
    $stmt->execute([$_GET['id']]);
    $edit_doctor = $stmt->fetch();
}

// --- Data for the page ---
// JOIN with departments so we can show the department name instead of just its ID.
$doctors = $pdo->query(
    "SELECT doctors.*, departments.department_name
     FROM doctors
     LEFT JOIN departments ON doctors.department_id = departments.department_id
     ORDER BY doctors.doctor_id"
)->fetchAll();

$departments = $pdo->query("SELECT * FROM departments ORDER BY department_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Doctors - MediFlow</title>
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
            <a href="dashboard.php" class="navbar-brand mb-0 h1"><i class="fa-solid fa-hospital me-2"></i>MediFlow Admin</a>
            <div>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm me-2">Dashboard</a>
                <a href="../auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <h4 class="text-primary mb-4">Manage Doctors</h4>

        <?php if ($error != "") { ?>
            <div class="alert alert-danger py-2"><?php echo $error; ?></div>
        <?php } ?>
        <?php if ($success != "") { ?>
            <div class="alert alert-success py-2"><?php echo $success; ?></div>
        <?php } ?>

        <div class="row g-4">

            <!-- ADD / EDIT FORM -->
            <div class="col-md-4">
                <div class="card p-3">
                    <h6 class="text-primary mb-3">
                        <?php echo $edit_doctor ? "Edit Doctor" : "Add New Doctor"; ?>
                    </h6>

                    <form method="post" action="doctors.php">
                        <input type="hidden" name="doctor_id" value="<?php echo $edit_doctor ? $edit_doctor['doctor_id'] : ''; ?>">

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control"
                                   value="<?php echo $edit_doctor ? htmlspecialchars($edit_doctor['full_name']) : ''; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?php echo $edit_doctor ? htmlspecialchars($edit_doctor['email']) : ''; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text"
                            name="phone"
                            class="form-control"
                            maxlength="10"
                            pattern="[0-9]{10}"
                            title="Enter a 10-digit phone number"
                            value="<?php echo $edit_doctor ? htmlspecialchars($edit_doctor['phone']) : ''; ?>"
                            required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Specialization</label>
                            <input type="text" name="specialization" class="form-control"
                                   value="<?php echo $edit_doctor ? htmlspecialchars($edit_doctor['specialization']) : ''; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" class="form-select" required>
                                <?php foreach ($departments as $dept) { ?>
                                    <option value="<?php echo $dept['department_id']; ?>"
                                        <?php if ($edit_doctor && $edit_doctor['department_id'] == $dept['department_id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($dept['department_name']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <!-- Password is only needed when adding a NEW doctor -->
                        <?php if (!$edit_doctor) { ?>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                        </div>
                        <?php } ?>

                        <button type="submit" name="save_doctor" class="btn btn-primary w-100">
                            <?php echo $edit_doctor ? "Update Doctor" : "Add Doctor"; ?>
                        </button>

                        <?php if ($edit_doctor) { ?>
                            <a href="doctors.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                        <?php } ?>
                    </form>
                </div>
            </div>

            <!-- LIST OF DOCTORS -->
            <div class="col-md-8">
                <div class="card p-3">
                    <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Specialization</th>
                                <th>Department</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($doctors as $doc) { ?>
                            <tr>
                                <td><?php echo $doc['doctor_id']; ?></td>
                                <td><?php echo htmlspecialchars($doc['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($doc['email']); ?></td>
                                <td><?php echo htmlspecialchars($doc['phone']); ?></td>
                                <td><?php echo htmlspecialchars($doc['specialization']); ?></td>
                                <td><?php echo htmlspecialchars($doc['department_name']); ?></td>
                                <td>
                                    <a href="doctors.php?action=edit&id=<?php echo $doc['doctor_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="doctors.php?action=delete&id=<?php echo $doc['doctor_id']; ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Delete this doctor?');">Delete</a>
                                </td>
                            </tr>
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