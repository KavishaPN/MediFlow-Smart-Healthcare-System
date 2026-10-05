<?php
// We must start the session before we can access or clear it.
session_start();

// session_unset() removes all session variables (user_id, role, full_name).
session_unset();

// session_destroy() deletes the session itself on the server.
session_destroy();

// Redirect back to the homepage. "../" because this file
header("Location: ../index.php");
exit;
?>