<?php

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

$course_code = "";
$course_name = "";
$credit = "";

$errors = [];


/*
|--------------------------------------------------------------------------
| Handle Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $course_code = trim($_POST["course_code"] ?? "");
    $course_name = trim($_POST["course_name"] ?? "");
    $credit = trim($_POST["credit"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($course_code === "") {

        $errors[] = "Course code is required.";

    }


    if ($course_name === "") {

        $errors[] = "Course name is required.";

    }


    if ($credit === "") {

        $errors[] = "Course credit is required.";

    } elseif (!is_numeric($credit) || $credit <= 0) {

        $errors[] = "Please enter a valid course credit.";

    }


    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Course Code
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $check_stmt = $conn->prepare("
            SELECT id
            FROM courses
            WHERE course_code = ?
            LIMIT 1
        ");

        if (!$check_stmt) {

            $errors[] =
                "Database error: " . $conn->error;

        } else {

            $check_stmt->bind_param(
                "s",
                $course_code
            );

            $check_stmt->execute();

            $check_result =
                $check_stmt->get_result();


            if ($check_result->num_rows > 0) {

                $errors[] =
                    "This course code already exists.";

            }


            $check_stmt->close();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Insert Course
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $conn->prepare("
            INSERT INTO courses
            (
                course_code,
                course_name,
                credit
            )
            VALUES (?, ?, ?)
        ");


        if (!$stmt) {

            $errors[] =
                "Database error: " . $conn->error;

        } else {

            $credit_value = (float) $credit;

            $stmt->bind_param(
                "ssd",
                $course_code,
                $course_name,
                $credit_value
            );


            if ($stmt->execute()) {

                $stmt->close();

                header(
                    "Location: index.php?success=course_created"
                );

                exit;

            } else {

                $errors[] =
                    "Failed to create course.";

                $stmt->close();

            }

        }

    }

}

?>


<?php include "../includes/header.php"; ?>

<?php include "../includes/sidebar.php"; ?>


<main class="main-content">


    <!-- =========================================================
         Topbar
    ========================================================== -->

    <header class="topbar">


        <div>

            <h1>
                Create Course
            </h1>

            <p>
                Add a new course to the system.
            </p>

        </div>


        <!-- Admin Profile -->

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


        <!-- =====================================================
             Create Course Form
        ====================================================== -->

        <div class="content-card">


            <div class="card-header">


                <div>

                    <h2>
                        Course Information
                    </h2>

                    <p>
                        Enter the course details below.
                    </p>

                </div>


                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    ← Back to Courses
                </a>


            </div>


            <!-- Errors -->

            <?php if (!empty($errors)): ?>


                <div
                    style="
                        background: #fee2e2;
                        color: #991b1b;
                        padding: 15px;
                        border-radius: 8px;
                        margin-bottom: 20px;
                    "
                >

                    <ul
                        style="
                            margin: 0;
                            padding-left: 20px;
                        "
                    >

                        <?php foreach ($errors as $error): ?>

                            <li>

                                <?php
                                echo htmlspecialchars($error);
                                ?>

                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>


            <?php endif; ?>


            <!-- Form -->

            <form
                method="POST"
                action=""
            >


                <!-- Course Code -->

                <div
                    style="
                        margin-bottom: 20px;
                    "
                >

                    <label
                        for="course_code"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 600;
                        "
                    >
                        Course Code
                    </label>


                    <input
                        type="text"
                        id="course_code"
                        name="course_code"
                        value="<?php
                            echo htmlspecialchars(
                                $course_code
                            );
                        ?>"
                        placeholder="e.g. CSE101"
                        maxlength="50"
                        required
                        style="
                            width: 100%;
                            padding: 12px 14px;
                            border: 1px solid #ddd;
                            border-radius: 8px;
                            font-size: 15px;
                        "
                    >

                </div>


                <!-- Course Name -->

                <div
                    style="
                        margin-bottom: 20px;
                    "
                >

                    <label
                        for="course_name"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 600;
                        "
                    >
                        Course Name
                    </label>


                    <input
                        type="text"
                        id="course_name"
                        name="course_name"
                        value="<?php
                            echo htmlspecialchars(
                                $course_name
                            );
                        ?>"
                        placeholder="e.g. Introduction to Programming"
                        maxlength="150"
                        required
                        style="
                            width: 100%;
                            padding: 12px 14px;
                            border: 1px solid #ddd;
                            border-radius: 8px;
                            font-size: 15px;
                        "
                    >

                </div>


                <!-- Credit -->

                <div
                    style="
                        margin-bottom: 25px;
                    "
                >

                    <label
                        for="credit"
                        style="
                            display: block;
                            margin-bottom: 8px;
                            font-weight: 600;
                        "
                    >
                        Credit
                    </label>


                    <input
                        type="number"
                        id="credit"
                        name="credit"
                        value="<?php
                            echo htmlspecialchars(
                                $credit
                            );
                        ?>"
                        placeholder="e.g. 3.0"
                        min="0.5"
                        max="10"
                        step="0.5"
                        required
                        style="
                            width: 100%;
                            padding: 12px 14px;
                            border: 1px solid #ddd;
                            border-radius: 8px;
                            font-size: 15px;
                        "
                    >

                </div>


                <!-- Buttons -->

                <div
                    style="
                        display: flex;
                        gap: 10px;
                    "
                >

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        + Create Course
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


<?php include "../includes/footer.php"; ?>