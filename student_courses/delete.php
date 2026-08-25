<?php

session_start();

require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Get Assignment ID
|--------------------------------------------------------------------------
*/

$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($id <= 0) {

    $_SESSION['error'] =
        "Invalid course assignment.";

    header("Location: index.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Delete Assignment
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    DELETE FROM student_courses
    WHERE id = ?
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();


/*
|--------------------------------------------------------------------------
| Check Result
|--------------------------------------------------------------------------
*/

if ($stmt->affected_rows > 0) {

    $_SESSION['success'] =
        "Course assignment removed successfully.";

} else {

    $_SESSION['error'] =
        "Course assignment not found.";

}


$stmt->close();


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header("Location: index.php");
exit;