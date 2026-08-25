<?php

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Get Result ID
|--------------------------------------------------------------------------
*/

$id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


if ($id <= 0) {

    header("Location: index.php?error=delete_failed");
    exit;

}


/*
|--------------------------------------------------------------------------
| Check Result Exists
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id
    FROM results
    WHERE id = ?
    LIMIT 1
");


if (!$stmt) {

    header("Location: index.php?error=delete_failed");
    exit;

}


$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

$stmt->close();


if ($result->num_rows !== 1) {

    header("Location: index.php?error=delete_failed");
    exit;

}


/*
|--------------------------------------------------------------------------
| Delete Result
|--------------------------------------------------------------------------
*/

$delete_stmt = $conn->prepare("
    DELETE FROM results
    WHERE id = ?
");


if (!$delete_stmt) {

    header("Location: index.php?error=delete_failed");
    exit;

}


$delete_stmt->bind_param("i", $id);


if ($delete_stmt->execute()) {

    $delete_stmt->close();

    header(
        "Location: index.php?success=result_deleted"
    );

    exit;

}


$delete_stmt->close();


header(
    "Location: index.php?error=delete_failed"
);

exit;