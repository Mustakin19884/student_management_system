<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Check Authentication
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['admin_id'])) {

    header(
        "Location: /student_management/login.php"
    );

    exit;
}