<?php

require_once "../config/auth.php";
require_once "../config/db.php";


// --------------------------------------------------
// Student ID
// --------------------------------------------------

// আপাতত login session-এ admin_id আছে,
// তাই student ID কোথা থেকে আসছে সেটা দেখতে হবে।

$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id) {
    die("Student ID not found in session.");
}


// --------------------------------------------------
// Selected Semester
// --------------------------------------------------

$semester = $_GET['semester'] ?? '';


// --------------------------------------------------
// Grade Point Scale
// --------------------------------------------------

$grade_points = [
    "A+" => 4.00,
    "A"  => 3.75,
    "A-" => 3.50,
    "B+" => 3.25,
    "B"  => 3.00,
    "B-" => 2.75,
    "C+" => 2.50,
    "C"  => 2.25,
    "D"  => 2.00,
    "F"  => 0.00
];


// --------------------------------------------------
// Get Assigned Courses
// --------------------------------------------------

$sql = "
    SELECT
        c.id AS course_id,
        c.course_code,
        c.course_name,
        c.credit,

        r.grade,
        r.grade_point

    FROM student_courses sc

    INNER JOIN courses c
        ON c.id = sc.course_id

    LEFT JOIN results r
        ON r.student_id = sc.student_id
        AND r.course_id = sc.course_id
        AND r.semester = sc.semester_id

    WHERE sc.student_id = ?
";

$params = [$student_id];


// Semester filter
if ($semester !== '') {

    $sql .= " AND sc.semester_id = ?";

    $params[] = $semester;
}


$sql .= " ORDER BY c.course_code ASC";


$stmt = $conn->prepare($sql);

$types = str_repeat("i", count($params));

$stmt->bind_param($types, ...$params);

$stmt->execute();

$result = $stmt->get_result();


// --------------------------------------------------
// GPA Calculation
// --------------------------------------------------

$total_quality_points = 0;
$total_credits = 0;

$courses = [];


while ($row = $result->fetch_assoc()) {

    $courses[] = $row;

    if (
        $row['grade'] !== null &&
        $row['grade_point'] !== null
    ) {

        $credit = (float) $row['credit'];
        $grade_point = (float) $row['grade_point'];

        $total_quality_points +=
            $credit * $grade_point;

        $total_credits += $credit;
    }
}


// --------------------------------------------------
// GPA
// --------------------------------------------------

$gpa = 0;

if ($total_credits > 0) {

    $gpa =
        $total_quality_points /
        $total_credits;
}

$gpa = number_format($gpa, 2);

?>


<div class="result-container">

    <h2>My Results</h2>


    <?php if (empty($courses)): ?>

        <div class="alert alert-info">
            No courses assigned for this semester.
        </div>

    <?php else: ?>

        <table class="table table-bordered">

            <thead>

                <tr>
                    <th>#</th>
                    <th>Course Code</th>
                    <th>Course Name</th>
                    <th>Credit</th>
                    <th>Grade</th>
                    <th>Grade Point</th>
                </tr>

            </thead>


            <tbody>

                <?php foreach ($courses as $index => $course): ?>

                    <tr>

                        <td>
                            <?= $index + 1 ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($course['course_code']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($course['course_name']) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($course['credit']) ?>
                        </td>

                        <td>

                            <?php if ($course['grade']): ?>

                                <strong>
                                    <?= htmlspecialchars($course['grade']) ?>
                                </strong>

                            <?php else: ?>

                                <span class="text-muted">
                                    Not Published
                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <?php if ($course['grade_point'] !== null): ?>

                                <?= number_format(
                                    (float) $course['grade_point'],
                                    2
                                ) ?>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </tbody>

        </table>


        <!-- GPA -->

        <div class="gpa-box">

            <h3>
                Current GPA
            </h3>

            <div class="gpa-value">
                <?= $gpa ?>
            </div>

            <p>
                Total Credits:
                <strong><?= $total_credits ?></strong>
            </p>

        </div>


    <?php endif; ?>

</div>