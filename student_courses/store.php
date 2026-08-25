<?php

session_start();

require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Only POST Request
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: create.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Get Form Data
|--------------------------------------------------------------------------
*/

$student_id = isset($_POST['student_id'])
    ? (int) $_POST['student_id']
    : 0;

$semester = trim($_POST['semester'] ?? '');

$course_ids = $_POST['course_ids'] ?? [];


/*
|--------------------------------------------------------------------------
| Validation
|--------------------------------------------------------------------------
*/

if ($student_id <= 0) {

    $_SESSION['error'] = "Please select a student.";

    header("Location: create.php");
    exit;

}


if ($semester === '') {

    $_SESSION['error'] = "Please select a semester.";

    header("Location: create.php");
    exit;

}


if (empty($course_ids)) {

    $_SESSION['error'] = "Please select at least one course.";

    header("Location: create.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Prepare Insert
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    INSERT IGNORE INTO student_courses
    (
        student_id,
        course_id,
        semester
    )
    VALUES (?, ?, ?)
");


$assigned = 0;
$duplicate = 0;


foreach ($course_ids as $course_id) {

    $course_id = (int) $course_id;

    if ($course_id <= 0) {
        continue;
    }


    $stmt->bind_param(
        "iis",
        $student_id,
        $course_id,
        $semester
    );


    $stmt->execute();


    if ($stmt->affected_rows > 0) {

        $assigned++;

    } else {

        $duplicate++;

    }

}


$stmt->close();


/*
|--------------------------------------------------------------------------
| Success Message
|--------------------------------------------------------------------------
*/

if ($assigned > 0) {

    $_SESSION['success'] =
        $assigned . " course(s) assigned successfully.";

} else {

    $_SESSION['error'] =
        "Selected courses are already assigned to this student for this semester.";

}


header("Location: index.php");
exit;