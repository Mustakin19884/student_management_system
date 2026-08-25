<?php

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Get Course ID
|--------------------------------------------------------------------------
*/

$course_id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


if ($course_id <= 0) {

    header("Location: index.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Get Existing Course
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        course_code,
        course_name,
        credit

    FROM courses

    WHERE id = ?

    LIMIT 1
");


if (!$stmt) {

    die("Database query failed: " . $conn->error);

}


$stmt->bind_param("i", $course_id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    header("Location: index.php");
    exit;

}


$course = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

$course_code = $course["course_code"];
$course_name = $course["course_name"];
$credit = $course["credit"];

$errors = [];


/*
|--------------------------------------------------------------------------
| Handle Form Submission
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $course_code = trim(
        $_POST["course_code"] ?? ""
    );

    $course_name = trim(
        $_POST["course_name"] ?? ""
    );

    $credit = trim(
        $_POST["credit"] ?? ""
    );


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($course_code === "") {

        $errors[] =
            "Course code is required.";

    }


    if ($course_name === "") {

        $errors[] =
            "Course name is required.";

    }


    if ($credit === "") {

        $errors[] =
            "Course credit is required.";

    } elseif (
        !is_numeric($credit)
        || $credit <= 0
    ) {

        $errors[] =
            "Please enter a valid course credit.";

    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate Course Code
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $check_stmt = $conn->prepare("
            SELECT id
            FROM courses
            WHERE course_code = ?
            AND id != ?
            LIMIT 1
        ");


        if (!$check_stmt) {

            $errors[] =
                "Database error: "
                . $conn->error;

        } else {

            $check_stmt->bind_param(
                "si",
                $course_code,
                $course_id
            );

            $check_stmt->execute();

            $check_result =
                $check_stmt->get_result();


            if (
                $check_result->num_rows > 0
            ) {

                $errors[] =
                    "This course code already exists.";

            }


            $check_stmt->close();

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Update Course
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $stmt = $conn->prepare("
            UPDATE courses

            SET
                course_code = ?,
                course_name = ?,
                credit = ?

            WHERE id = ?
        ");


        if (!$stmt) {

            $errors[] =
                "Database error: "
                . $conn->error;

        } else {

            $credit_value =
                (float) $credit;


            $stmt->bind_param(
                "ssdi",
                $course_code,
                $course_name,
                $credit_value,
                $course_id
            );


            if ($stmt->execute()) {

                $stmt->close();

                header(
                    "Location: index.php?success=course_updated"
                );

                exit;

            } else {

                $errors[] =
                    "Failed to update course.";

                $stmt->close();

            }

        }

    }

}

?>


<?php include "../includes/header.php"; ?>

<?php include "../includes/sidebar.php"; ?>


<main class="main-content">


    <!-- Topbar -->

    <header class="topbar">

        <div>

            <h1>
                Edit Course
            </h1>

            <p>
                Update course information.
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


            <div class="card-header">

                <div>

                    <h2>
                        Course Information
                    </h2>

                    <p>
                        Update the details below.
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

                                <?= htmlspecialchars($error) ?>

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
                        value="<?= htmlspecialchars($course_code) ?>"
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
                        value="<?= htmlspecialchars($course_name) ?>"
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
                        value="<?= htmlspecialchars($credit) ?>"
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
                        Update Course
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