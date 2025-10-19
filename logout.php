<?php
session_start();

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

header("Location: index.php");

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_login.php");
} else {
   header("Location: index.php");
}

exit();
?>
