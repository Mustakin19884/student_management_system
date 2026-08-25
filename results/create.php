<?php

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Grade Point Scale
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

$selected_student = 0;
$selected_semester = "";

$errors = [];


/*
|--------------------------------------------------------------------------
| Get Students
|--------------------------------------------------------------------------
*/

$students = $conn->query("
    SELECT
        id,
        student_id,
        name
    FROM students
    ORDER BY student_id ASC
");


/*
|--------------------------------------------------------------------------
| Get Courses
|--------------------------------------------------------------------------
*/

$courses = $conn->query("
    SELECT
        id,
        course_code,
        course_name,
        credit
    FROM courses
    ORDER BY course_code ASC
");


/*
|--------------------------------------------------------------------------
| Handle Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $selected_student =
        (int) ($_POST["student_id"] ?? 0);

    $selected_semester =
        trim($_POST["semester"] ?? "");

    $grades =
        $_POST["grades"] ?? [];


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($selected_student <= 0) {

        $errors[] =
            "Please select a student.";

    }


    if ($selected_semester === "") {

        $errors[] =
            "Please select a semester.";

    }


    if (
        empty($grades) ||
        !is_array($grades)
    ) {

        $errors[] =
            "Please select at least one course.";

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Grades
    |--------------------------------------------------------------------------
    */

    $valid_grades = [];

    if (is_array($grades)) {

        foreach ($grades as $course_id => $grade) {

            $course_id = (int) $course_id;

            $grade = trim($grade);


            // Empty grade means course not selected

            if ($grade === "") {
                continue;
            }


            if (
                $course_id <= 0 ||
                !isset($grade_points[$grade])
            ) {

                $errors[] =
                    "Invalid course or grade selected.";

                continue;

            }


            $valid_grades[$course_id] =
                $grade;

        }

    }


    if (empty($valid_grades)) {

        $errors[] =
            "Please select grade for at least one course.";

    }


    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Results
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        foreach (
            $valid_grades as $course_id => $grade
        ) {

            $check_stmt = $conn->prepare("
                SELECT id

                FROM results

                WHERE student_id = ?
                AND course_id = ?
                AND semester = ?

                LIMIT 1
            ");


            if (!$check_stmt) {

                $errors[] =
                    "Database error.";

                break;

            }


            $check_stmt->bind_param(
                "iis",
                $selected_student,
                $course_id,
                $selected_semester
            );


            $check_stmt->execute();


            $check_result =
                $check_stmt->get_result();


            if (
                $check_result->num_rows > 0
            ) {

                $errors[] =
                    "A result already exists for one or more selected courses in "
                    . $selected_semester
                    . ".";

                $check_stmt->close();

                break;

            }


            $check_stmt->close();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Insert Results
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $conn->begin_transaction();


        try {

            $insert_stmt = $conn->prepare("
                INSERT INTO results
                (
                    student_id,
                    course_id,
                    semester,
                    grade,
                    grade_point
                )

                VALUES (?, ?, ?, ?, ?)
            ");


            if (!$insert_stmt) {

                throw new Exception(
                    "Failed to prepare database query."
                );

            }


            foreach (
                $valid_grades
                as $course_id => $grade
            ) {

                $grade_point =
                    $grade_points[$grade];


                $insert_stmt->bind_param(
                    "iissd",
                    $selected_student,
                    $course_id,
                    $selected_semester,
                    $grade,
                    $grade_point
                );


                if (
                    !$insert_stmt->execute()
                ) {

                    throw new Exception(
                        "Failed to save result."
                    );

                }

            }


            $insert_stmt->close();


            $conn->commit();


            header(
                "Location: index.php?success=result_created"
            );

            exit;


        } catch (Exception $e) {

            $conn->rollback();


            $errors[] =
                $e->getMessage();

        }

    }

}

?>


<?php include "../includes/header.php"; ?>

<?php include "../includes/sidebar.php"; ?>


<style>

/*
|--------------------------------------------------------------------------
| Result Form
|--------------------------------------------------------------------------
*/

.result-form {

    max-width: 1000px;

}


.form-group {

    margin-bottom: 22px;
    margin-left:20px;
    margin-top:20px;

}


.form-group label {

    display: block;

    margin-bottom: 8px;

    font-weight: 600;

    color: #333;

}


.form-group select {

    width: 100%;

    padding: 12px 14px;

    border: 1px solid #ddd;

    border-radius: 8px;

    font-size: 15px;

    background: #fff;

}


.form-group select:focus {

    outline: none;

    border-color: #2563eb;

    box-shadow:
        0 0 0 3px
        rgba(37, 99, 235, 0.10);

}


/*
|--------------------------------------------------------------------------
| Error
|--------------------------------------------------------------------------
*/

.error-box {

    background: #fee2e2;

    color: #991b1b;

    border-left: 4px solid #ef4444;

    padding: 15px;

    border-radius: 8px;

    margin-bottom: 20px;

}


.error-box ul {

    margin: 0;

    padding-left: 20px;

}


/*
|--------------------------------------------------------------------------
| Course Table
|--------------------------------------------------------------------------
*/

.course-table-wrapper {

    overflow-x: auto;

    margin-top: 30px;
    margin-left:20px;

}


.course-table {

    width: 100%;

    border-collapse: collapse;

}


.course-table th {

    text-align: left;

    padding: 14px;

    background: #f8fafc;

    border-bottom: 1px solid #e5e7eb;

    font-size: 14px;

}


.course-table td {

    padding: 14px;

    border-bottom: 1px solid #eee;

    vertical-align: middle;

}


.course-table tr:hover {

    background: #fafafa;

}


.course-code {

    font-weight: 700;

    color: #2563eb;

}


.course-name {

    color: #333;

}


.course-credit {

    font-weight: 600;

}


/*
|--------------------------------------------------------------------------
| Grade Select
|--------------------------------------------------------------------------
*/

.grade-select {

    width: 130px !important;

    padding: 9px 10px !important;

}


/*
|--------------------------------------------------------------------------
| GP Display
|--------------------------------------------------------------------------
*/

.gp-display {

    font-weight: 700;

    color: #2563eb;

}


/*
|--------------------------------------------------------------------------
| Form Actions
|--------------------------------------------------------------------------
*/

.form-actions {

    display: flex;

    gap: 10px;

    margin-top: 30px;
    margin-bottom: 30px;

}


/*
|--------------------------------------------------------------------------
| Selected Count
|--------------------------------------------------------------------------
*/

.selected-summary {

    background: #f1f5f9;

    padding: 14px 16px;

    border-radius: 8px;

    margin-top: 20px;
    margin-left:20px;

    color: #475569;

}


.selected-summary strong {

    color: #2563eb;

}


@media (max-width: 700px) {

    .course-table th,
    .course-table td {

        padding: 10px;

    }

    .grade-select {

        width: 100px !important;

    }

}

</style>


<main class="main-content">


    <!-- =========================================================
         Topbar
    ========================================================== -->

    <header class="topbar">


        <div>

            <h1>
                Add Semester Results
            </h1>

            <p>
                Add multiple course results for a student.
            </p>

        </div>


        <div class="profile">


            <div class="avatar">
                A
            </div>


            <div>

                <strong>
                    Admin
                </strong>

                <span>
                    Administrator
                </span>

            </div>


        </div>


    </header>


    <section class="dashboard-content">


        <div class="content-card">


            <!-- =================================================
                 Header
            ================================================== -->

            <div class="card-header">


                <div>

                    <h2>
                        Semester Result Entry
                    </h2>

                    <p>
                        Select a student and semester, then enter grades for courses.
                    </p>

                </div>


                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    ← Back to Results
                </a>


            </div>


            <!-- =================================================
                 Errors
            ================================================== -->

            <?php if (!empty($errors)): ?>


                <div class="error-box">


                    <ul>

                        <?php foreach (
                            $errors as $error
                        ): ?>

                            <li>

                                <?= htmlspecialchars(
                                    $error
                                ) ?>

                            </li>

                        <?php endforeach; ?>

                    </ul>


                </div>


            <?php endif; ?>


            <!-- =================================================
                 Form
            ================================================== -->

            <form
                method="POST"
                class="result-form"
                id="resultForm"
            >


                <!-- Student -->

                <div class="form-group">


                    <label for="student_id">
                        Student
                    </label>


                    <select
                        name="student_id"
                        id="student_id"
                        required
                    >

                        <option value="">
                            Select Student
                        </option>


                        <?php if ($students): ?>


                            <?php while (
                                $student =
                                $students->fetch_assoc()
                            ): ?>


                                <option
                                    value="<?= (int) $student["id"] ?>"
                                    <?= (
                                        $selected_student
                                        ==
                                        $student["id"]
                                    )
                                    ? "selected"
                                    : ""
                                    ?>
                                >

                                    <?= htmlspecialchars(
                                        $student["student_id"]
                                    ) ?>

                                    -
                                    <?= htmlspecialchars(
                                        $student["name"]
                                    ) ?>

                                </option>


                            <?php endwhile; ?>


                        <?php endif; ?>


                    </select>


                </div>


                <!-- Semester -->

                <div class="form-group">


                    <label for="semester">
                        Semester
                    </label>


                    <select
                        name="semester"
                        id="semester"
                        required
                    >

                        <option value="">
                            Select Semester
                        </option>


                        <?php

                        $semesters = [

                            "Spring 2026",
                            "Summer 2026",
                            "Fall 2026",
                            "Spring 2027",
                            "Summer 2027",
                            "Fall 2027"

                        ];

                        ?>


                        <?php foreach (
                            $semesters
                            as $semester_option
                        ): ?>


                            <option
                                value="<?= htmlspecialchars(
                                    $semester_option
                                ) ?>"
                                <?= (
                                    $selected_semester
                                    ===
                                    $semester_option
                                )
                                ? "selected"
                                : ""
                                ?>
                            >

                                <?= htmlspecialchars(
                                    $semester_option
                                ) ?>

                            </option>


                        <?php endforeach; ?>


                    </select>


                </div>


                <!-- =================================================
                     Courses
                ================================================== -->

                <div class="course-table-wrapper">


                    <table class="course-table">


                        <thead>

                            <tr>

                                <th>
                                    Course Code
                                </th>

                                <th>
                                    Course Name
                                </th>

                                <th>
                                    Credit
                                </th>

                                <th>
                                    Grade
                                </th>

                                <th>
                                    Grade Point
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php if ($courses): ?>


                                <?php while (
                                    $course =
                                    $courses->fetch_assoc()
                                ): ?>


                                    <?php

                                    $course_id =
                                        (int) $course["id"];

                                    $selected_grade =
                                        $grades[$course_id]
                                        ?? "";

                                    ?>


                                    <tr>


                                        <!-- Course Code -->

                                        <td>

                                            <span
                                                class="course-code"
                                            >

                                                <?= htmlspecialchars(
                                                    $course["course_code"]
                                                ) ?>

                                            </span>

                                        </td>


                                        <!-- Course Name -->

                                        <td>

                                            <span
                                                class="course-name"
                                            >

                                                <?= htmlspecialchars(
                                                    $course["course_name"]
                                                ) ?>

                                            </span>

                                        </td>


                                        <!-- Credit -->

                                        <td>

                                            <span
                                                class="course-credit"
                                            >

                                                <?= htmlspecialchars(
                                                    $course["credit"]
                                                ) ?>

                                            </span>

                                        </td>


                                        <!-- Grade -->

                                        <td>


                                            <select
                                                name="grades[<?= $course_id ?>]"
                                                class="grade-select"
                                                data-course="<?= $course_id ?>"
                                            >


                                                <option value="">
                                                    Select
                                                </option>


                                                <?php foreach (
                                                    $grade_points
                                                    as $grade_option
                                                    => $point
                                                ): ?>


                                                    <option
                                                        value="<?= $grade_option ?>"
                                                        data-point="<?= $point ?>"
                                                        <?= (
                                                            $selected_grade
                                                            ===
                                                            $grade_option
                                                        )
                                                        ? "selected"
                                                        : ""
                                                        ?>
                                                    >

                                                        <?= $grade_option ?>

                                                    </option>


                                                <?php endforeach; ?>


                                            </select>


                                        </td>


                                        <!-- GP -->

                                        <td>


                                            <span
                                                class="gp-display"
                                                data-gp-for="<?= $course_id ?>"
                                            >

                                                <?php

                                                if (
                                                    isset(
                                                        $grade_points[
                                                            $selected_grade
                                                        ]
                                                    )
                                                ) {

                                                    echo number_format(
                                                        $grade_points[
                                                            $selected_grade
                                                        ],
                                                        2
                                                    );

                                                } else {

                                                    echo "—";

                                                }

                                                ?>

                                            </span>


                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <tr>

                                    <td
                                        colspan="5"
                                        style="text-align:center;"
                                    >

                                        No courses found.

                                        <br><br>

                                        Please create courses first.

                                    </td>

                                </tr>


                            <?php endif; ?>


                        </tbody>


                    </table>


                </div>


                <!-- Selected Summary -->

                <div class="selected-summary">

                    Selected Courses:

                    <strong
                        id="selectedCount"
                    >
                        0
                    </strong>

                </div>


                <!-- Buttons -->

                <div class="form-actions">


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save All Results
                    </button>


                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>


                </div>


            </form>


        </div>


    </section>


</main>


<script>

/*
|--------------------------------------------------------------------------
| Grade Point Update
|--------------------------------------------------------------------------
*/

const gradeSelects =
    document.querySelectorAll(
        ".grade-select"
    );


function updateGradePoints() {

    let selectedCount = 0;


    gradeSelects.forEach(
        function(select) {

            const courseId =
                select.dataset.course;

            const option =
                select.options[
                    select.selectedIndex
                ];


            const point =
                option.dataset.point
                || "";


            const gpElement =
                document.querySelector(
                    '[data-gp-for="' +
                    courseId +
                    '"]'
                );


            if (point !== "") {

                gpElement.textContent =
                    parseFloat(point)
                    .toFixed(2);

                selectedCount++;

            } else {

                gpElement.textContent =
                    "—";

            }

        }
    );


    document.getElementById(
        "selectedCount"
    ).textContent =
        selectedCount;

}


gradeSelects.forEach(
    function(select) {

        select.addEventListener(
            "change",
            updateGradePoints
        );

    }
);


/*
|--------------------------------------------------------------------------
| Initial Update
|--------------------------------------------------------------------------
*/

updateGradePoints();


/*
|--------------------------------------------------------------------------
| Form Validation
|--------------------------------------------------------------------------
*/

document.getElementById(
    "resultForm"
).addEventListener(
    "submit",
    function(event) {

        const student =
            document.getElementById(
                "student_id"
            ).value;


        const semester =
            document.getElementById(
                "semester"
            ).value;


        let selectedCount = 0;


        gradeSelects.forEach(
            function(select) {

                if (
                    select.value !== ""
                ) {

                    selectedCount++;

                }

            }
        );


        if (
            student === "" ||
            semester === ""
        ) {

            return;

        }


        if (
            selectedCount === 0
        ) {

            event.preventDefault();

            alert(
                "Please select a grade for at least one course."
            );

        }

    }
);

</script>


<?php include "../includes/footer.php"; ?>