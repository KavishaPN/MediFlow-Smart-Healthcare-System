<?php
/*
    ===============================================
    File: auth/register.php
    Purpose: Lets a new patient create their own account.
             On success, the patient is added to the
             "patients" table and sent to the login page.
    ===============================================
*/

require '../config/db_connect.php';

$error   = "";
$success = "";

// This block only runs when the registration form is submitted.
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Read and clean up the form values.
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];
    $phone     = trim($_POST['phone']);
    $gender    = $_POST['gender'];
    $address   = trim($_POST['address']);

    // --- Simple validation (no external libraries needed) ---
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif ($password !== $confirm) {
        $error = "Password and Confirm Password do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Phone number must contain exactly 10 digits.";
    } else {

       // Check if this email is already registered.
        $check_email = $pdo->prepare("SELECT patient_id FROM patients WHERE email = ?");
        $check_email->execute([$email]);

        // Check if this phone number is already registered.
        $check_phone = $pdo->prepare("SELECT patient_id FROM patients WHERE phone = ?");
        $check_phone->execute([$phone]);

        if ($check_email->fetch()) {
            $error = "Email already exists.";
        } elseif ($check_phone->fetch()) {
            $error = "Phone number already exists.";
        } else {
            // Hash the password before saving it — we never store plain text passwords.
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new patient using a prepared statement (safe from SQL injection).
            $insert = $pdo->prepare(
                "INSERT INTO patients (full_name, email, password, phone, gender, address)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $insert->execute([$full_name, $email, $hashed_password, $phone, $gender, $address]);

            $success = "Registration successful! You can now login.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Registration - MediFlow</title>
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
<body class="d-flex flex-column min-vh-100">

    <!-- NAVBAR -->
    <nav class="navbar navbar-dark bg-primary py-2">
        <div class="container">
            <a href="../index.php" class="navbar-brand mb-0 h1">
                <i class="fa-solid fa-hospital me-2"></i>MediFlow
            </a>
        </div>
    </nav>

    <!-- REGISTRATION FORM -->
    <main class="flex-grow-1 d-flex align-items-center py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body p-4">
                            <h4 class="text-primary text-center mb-4">Patient Registration</h4>

                            <!-- Show error or success messages -->
                            <?php if ($error != "") { ?>
                                <div class="alert alert-danger py-2"><?php echo $error; ?></div>
                            <?php } ?>

                            <?php if ($success != "") { ?>
                                <div class="alert alert-success py-2">
                                    <?php echo $success; ?>
                                    <a href="login.php" class="alert-link">Go to Login</a>
                                </div>
                            <?php } else { ?>

                            <form method="post" action="register.php">

                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="full_name" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control" maxlength="10" pattern="[0-9]{10}" title="Enter a 10-digit phone number" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Gender</label>
                                        <select name="gender" class="form-select" required>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-control" rows="2" required></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control" minlength="6" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Confirm Password</label>
                                        <input type="password" name="confirm_password" class="form-control" minlength="6" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Register</button>
                            </form>

                            <p class="text-center text-muted small mt-3 mb-0">
                                Already have an account?
                                <a href="login.php" class="text-primary">Login here</a>
                            </p>

                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>