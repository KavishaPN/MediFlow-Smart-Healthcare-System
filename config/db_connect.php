<?php
/*
    ===============================================
    File: config/db_connect.php
    Purpose: Create ONE shared PDO database connection
             that every page in the project will reuse.
    ===============================================
*/

// --- Database settings (change these if your setup is different) ---
$db_host = "localhost";      // Server address (XAMPP default)
$db_name = "db_mediflow";    // Your database name
$db_user = "root";           // Default XAMPP username
$db_pass = "";                // Default XAMPP password (empty)

try {
    // Create a new PDO connection using DSN (Data Source Name)
    // DSN tells PDO: which driver (mysql), which host, which database
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass
    );

    // Make PDO throw real exceptions on errors (easier to debug)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // If connection fails, stop the script and show the error
    die("Database connection failed: " . $e->getMessage());
}

/*
    From now on, in every other PHP file, we simply write:
        require 'config/db_connect.php';
    and the variable $pdo becomes available to run queries.
*/
?>