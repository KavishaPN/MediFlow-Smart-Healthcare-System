<?php
/*
    ===============================================
    File: admin/departments.php
    Purpose: Manage Departments — Add, Edit, Delete, and View,
             all inside this ONE file (no separate add/edit/delete files).
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

// --- DELETE: triggered by a link like departments.php?action=delete&id=3 ---
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM departments WHERE department_id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: departments.php");
    exit;
}

// --- ADD or UPDATE: triggered when the form below is submitted ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_department'])) {

    $department_id   = $_POST['department_id']; // empty string = new department
    $department_name = trim($_POST['department_name']);
    $description     = trim($_POST['description']);

    // Check if a department with this name already exists.
    // When editing, we exclude the current department's own row from this check
    // (otherwise saving an edit without changing the name would wrongly show an error).
    $check = $pdo->prepare("SELECT department_id FROM departments WHERE department_name = ? AND department_id != ?");
    $check->execute([$department_name, $department_id == "" ? 0 : $department_id]);

    if ($department_name == "") {
        $error = "Department name is required.";
    } elseif ($check->fetch()) {
        $error = "Department already exists.";
    } elseif ($department_id == "") {
        // No ID means this is a NEW department -> INSERT
        $stmt = $pdo->prepare("INSERT INTO departments (department_name, description) VALUES (?, ?)");
        $stmt->execute([$department_name, $description]);
        $success = "Department added successfully.";
    } else {
        // An ID was passed -> UPDATE the existing department
        $stmt = $pdo->prepare("UPDATE departments SET department_name = ?, description = ? WHERE department_id = ?");
        $stmt->execute([$department_name, $description, $department_id]);
        $success = "Department updated successfully.";
    }
}

// --- EDIT: if editing, load that department's current data to prefill the form ---
$edit_department = null;
if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM departments WHERE department_id = ?");
    $stmt->execute([$_GET['id']]);
    $edit_department = $stmt->fetch();
}

// --- Fetch all departments to display in the table below ---
$departments = $pdo->query("SELECT * FROM departments ORDER BY department_id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Departments - MediFlow</title>
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
        <h4 class="text-primary mb-4">Manage Departments</h4>

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
                        <?php echo $edit_department ? "Edit Department" : "Add New Department"; ?>
                    </h6>

                    <form method="post" action="departments.php">
                        <!-- Hidden field: empty means "add new", filled means "update this one" -->
                        <input type="hidden" name="department_id" value="<?php echo $edit_department ? $edit_department['department_id'] : ''; ?>">

                        <div class="mb-3">
                            <label class="form-label">Department Name</label>
                            <input type="text" name="department_name" class="form-control"
                                   value="<?php echo $edit_department ? htmlspecialchars($edit_department['department_name']) : ''; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"><?php echo $edit_department ? htmlspecialchars($edit_department['description']) : ''; ?></textarea>
                        </div>

                        <button type="submit" name="save_department" class="btn btn-primary w-100">
                            <?php echo $edit_department ? "Update Department" : "Add Department"; ?>
                        </button>

                        <?php if ($edit_department) { ?>
                            <a href="departments.php" class="btn btn-outline-secondary w-100 mt-2">Cancel</a>
                        <?php } ?>
                    </form>
                </div>
            </div>

            <!-- LIST OF DEPARTMENTS -->
            <div class="col-md-8">
                <div class="card p-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $dept) { ?>
                            <tr>
                                <td><?php echo $dept['department_id']; ?></td>
                                <td><?php echo htmlspecialchars($dept['department_name']); ?></td>
                                <td><?php echo htmlspecialchars($dept['description']); ?></td>
                                <td>
                                    <a href="departments.php?action=edit&id=<?php echo $dept['department_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="departments.php?action=delete&id=<?php echo $dept['department_id']; ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Delete this department?');">Delete</a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</body>
</html>