<?php
require_once "../config/auth.php";
require_once "../config/db.php";


$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($id <= 0) {

    header("Location: index.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| Check Students
|--------------------------------------------------------------------------
*/

$check = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM students
    WHERE department_id = ?
");

$check->bind_param("i", $id);

$check->execute();

$result = $check->get_result();

$data = $result->fetch_assoc();

$check->close();


/*
|--------------------------------------------------------------------------
| Prevent Delete
|--------------------------------------------------------------------------
*/

if ($data['total'] > 0) {

    header(
        "Location: index.php?error=department_has_students"
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    DELETE FROM departments
    WHERE id = ?
");

$stmt->bind_param("i", $id);


if ($stmt->execute()) {

    header(
        "Location: index.php?success=department_deleted"
    );

    exit;
}


$stmt->close();


header("Location: index.php");

exit;