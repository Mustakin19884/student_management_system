<?php
require_once "../config/auth.php";
require_once "../config/db.php";


$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;


if ($id <= 0) {

    header("Location: index.php");

    exit;
}


$stmt = $conn->prepare("
    DELETE FROM students
    WHERE id = ?
");


$stmt->bind_param("i", $id);


if ($stmt->execute()) {

    header(
        "Location: index.php?success=student_deleted"
    );

    exit;

}


$stmt->close();


header("Location: index.php");

exit;