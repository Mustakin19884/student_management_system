<?php

session_start();

require_once "../config/db.php";

/*
|--------------------------------------------------------------------------
| Fetch Students
|--------------------------------------------------------------------------
*/

$students = [];

$stmt = $conn->prepare("
    SELECT id, student_id, name
    FROM students
    ORDER BY name ASC
");

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| Fetch Courses
|--------------------------------------------------------------------------
*/

$courses = [];

$stmt = $conn->prepare("
    SELECT id, course_code, course_name, credit
    FROM courses
    ORDER BY course_code ASC
");

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Assign Courses</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 40px;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #1f2937;
        }

        .container {
            max-width: 750px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.08);
        }

        h2 {
            margin-top: 0;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 15px;
            background: #fff;
        }

        select:focus {
            outline: none;
            border-color: #2563eb;
        }

        .semester-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .semester-option {
            border: 1px solid #d1d5db;
            border-radius: 7px;
            padding: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .semester-option:hover {
            background: #f8fafc;
        }

        .semester-option input {
            cursor: pointer;
        }

        .courses {
            margin-top: 10px;
        }

        .course {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            margin-bottom: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fafafa;
        }

        .course:hover {
            background: #f8fafc;
        }

        .course input {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .course-info {
            flex: 1;
        }

        .course-code {
            font-weight: 700;
            margin-bottom: 3px;
        }

        .course-name {
            color: #4b5563;
        }

        .credit {
            font-size: 13px;
            color: #6b7280;
            margin-top: 4px;
        }

        .btn {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 7px;
            background: #2563eb;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .message {
            padding: 12px 15px;
            border-radius: 7px;
            margin-bottom: 20px;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        @media (max-width: 600px) {

            body {
                padding: 20px;
            }

            .semester-options {
                grid-template-columns: 1fr;
            }

        }

    </style>

</head>

<body>

<div class="container">

    <h2>Assign Courses to Student</h2>


    <?php if (isset($_SESSION['error'])): ?>

        <div class="message error">
            <?= htmlspecialchars($_SESSION['error']); ?>
        </div>

        <?php unset($_SESSION['error']); ?>

    <?php endif; ?>


    <form action="store.php" method="POST">


        <!-- Student -->

        <div class="form-group">

            <label for="student_id">
                Select Student
            </label>

            <select
                name="student_id"
                id="student_id"
                required
            >

                <option value="">
                    Select Student
                </option>

                <?php foreach ($students as $student): ?>

                    <option value="<?= (int)$student['id']; ?>">

                        <?= htmlspecialchars($student['name']); ?>

                        -
                        
                        <?= htmlspecialchars($student['student_id']); ?>

                    </option>

                <?php endforeach; ?>

            </select>

        </div>


        <!-- Semester -->

        <div class="form-group">

            <label>
                Select Semester
            </label>

            <div class="semester-options">

                <label class="semester-option">
                    <input
                        type="radio"
                        name="semester"
                        value="Summer 2026"
                        required
                    >
                    Summer 2026
                </label>

                <label class="semester-option">
                    <input
                        type="radio"
                        name="semester"
                        value="Fall 2026"
                    >
                    Fall 2026
                </label>

                <label class="semester-option">
                    <input
                        type="radio"
                        name="semester"
                        value="Spring 2027"
                    >
                    Spring 2027
                </label>

                <label class="semester-option">
                    <input
                        type="radio"
                        name="semester"
                        value="Summer 2027"
                    >
                    Summer 2027
                </label>

                <label class="semester-option">
                    <input
                        type="radio"
                        name="semester"
                        value="Fall 2027"
                    >
                    Fall 2027
                </label>

                <label class="semester-option">
                    <input
                        type="radio"
                        name="semester"
                        value="Spring 2028"
                    >
                    Spring 2028
                </label>

                <label class="semester-option">
                    <input
                        type="radio"
                        name="semester"
                        value="Summer 2028"
                    >
                    Summer 2028
                </label>

                <label class="semester-option">
                    <input
                        type="radio"
                        name="semester"
                        value="Fall 2028"
                    >
                    Fall 2028
                </label>

                <label class="semester-option">
                    <input
                        type="radio"
                        name="semester"
                        value="Spring 2029"
                    >
                    Spring 2029
                </label>

                <label class="semester-option">
                    <input
                        type="radio"
                        name="semester"
                        value="Summer 2029"
                    >
                    Summer 2029
                </label>

                <label class="semester-option">
                    <input
                        type="radio"
                        name="semester"
                        value="Fall 2029"
                    >
                    Fall 2029
                </label>


                <label class="semester-option">
                    <input
                        type="radio"
                        name="semester"
                        value="Spring 2030"
                    >
                    Spring 2030
                </label>


            </div>

        </div>


        <!-- Courses -->

        <div class="form-group">

            <label>
                Select Courses
            </label>

            <div class="courses">

                <?php if (empty($courses)): ?>

                    <p>
                        No courses available.
                    </p>

                <?php else: ?>

                    <?php foreach ($courses as $course): ?>

                        <div class="course">

                            <input
                                type="checkbox"
                                name="course_ids[]"
                                value="<?= (int)$course['id']; ?>"
                            >

                            <div class="course-info">

                                <div class="course-code">

                                    <?= htmlspecialchars(
                                        $course['course_code']
                                    ); ?>

                                </div>

                                <div class="course-name">

                                    <?= htmlspecialchars(
                                        $course['course_name']
                                    ); ?>

                                </div>

                                <div class="credit">

                                    Credit:
                                    <?= htmlspecialchars(
                                        $course['credit']
                                    ); ?>

                                </div>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </div>


        <button
            type="submit"
            class="btn"
        >
            Assign Courses
        </button>


    </form>

</div>

</body>

</html>