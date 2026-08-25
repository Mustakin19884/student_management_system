<?php

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Success / Error Messages
|--------------------------------------------------------------------------
*/

$success_message = "";
$error_message = "";


if (isset($_GET["success"])) {

    if ($_GET["success"] === "result_created") {

        $success_message = "Result added successfully.";

    } elseif ($_GET["success"] === "result_updated") {

        $success_message = "Result updated successfully.";

    } elseif ($_GET["success"] === "result_deleted") {

        $success_message = "Result deleted successfully.";

    }

}


if (isset($_GET["error"])) {

    if ($_GET["error"] === "delete_failed") {

        $error_message = "Failed to delete result.";

    }

}


/*
|--------------------------------------------------------------------------
| Get All Results
|--------------------------------------------------------------------------
*/

$results = $conn->query("
    SELECT

        results.id,

        results.semester,

        results.grade,

        results.grade_point,

        students.name AS student_name,

        students.student_id,

        courses.course_code,

        courses.course_name,

        courses.credit

    FROM results

    INNER JOIN students

        ON results.student_id = students.id

    INNER JOIN courses

        ON results.course_id = courses.id

    ORDER BY results.id DESC
");

?>


<?php include "../includes/header.php"; ?>

<?php include "../includes/sidebar.php"; ?>


<style>

.success-popup {

    position: fixed;

    top: 25px;

    right: 25px;

    min-width: 320px;

    max-width: 420px;

    background: #fff;

    border-left: 5px solid #22c55e;

    border-radius: 10px;

    padding: 16px 18px;

    display: flex;

    align-items: center;

    gap: 14px;

    box-shadow:
        0 10px 30px rgba(0,0,0,0.15);

    z-index: 9999;

    animation: popupSlideIn 0.3s ease;

}


.popup-icon {

    width: 38px;

    height: 38px;

    border-radius: 50%;

    background: #dcfce7;

    color: #16a34a;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 20px;

    font-weight: bold;

    flex-shrink: 0;

}


.popup-content {

    flex: 1;

}


.popup-content strong {

    display: block;

    font-size: 15px;

    margin-bottom: 3px;

}


.popup-content p {

    margin: 0;

    font-size: 13px;

    color: #666;

}


.popup-close {

    border: none;

    background: transparent;

    font-size: 22px;

    color: #999;

    cursor: pointer;

}


.error-popup {

    border-left-color: #ef4444;

}


.error-popup .popup-icon {

    background: #fee2e2;

    color: #dc2626;

}


@keyframes popupSlideIn {

    from {

        opacity: 0;

        transform: translateX(30px);

    }

    to {

        opacity: 1;

        transform: translateX(0);

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
                Results
            </h1>

            <p>
                Manage student academic results.
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


        <!-- =====================================================
             Success Popup
        ====================================================== -->

        <?php if ($success_message): ?>


            <div
                class="success-popup"
                id="successPopup"
            >


                <div class="popup-icon">
                    ✓
                </div>


                <div class="popup-content">

                    <strong>
                        Success
                    </strong>

                    <p>

                        <?= htmlspecialchars(
                            $success_message
                        ) ?>

                    </p>

                </div>


                <button
                    type="button"
                    class="popup-close"
                    onclick="closeSuccessPopup()"
                >
                    ×
                </button>


            </div>


        <?php endif; ?>


        <!-- =====================================================
             Error Popup
        ====================================================== -->

        <?php if ($error_message): ?>


            <div
                class="success-popup error-popup"
                id="successPopup"
            >


                <div class="popup-icon">
                    !
                </div>


                <div class="popup-content">

                    <strong>
                        Error
                    </strong>

                    <p>

                        <?= htmlspecialchars(
                            $error_message
                        ) ?>

                    </p>

                </div>


                <button
                    type="button"
                    class="popup-close"
                    onclick="closeSuccessPopup()"
                >
                    ×
                </button>


            </div>


        <?php endif; ?>


        <!-- =====================================================
             Results Card
        ====================================================== -->

        <div class="content-card">


            <div class="card-header">


                <div>

                    <h2>
                        Result List
                    </h2>

                    <p>
                        View all student results.
                    </p>

                </div>


                <a
                    href="create.php"
                    class="btn btn-primary"
                >
                    + Add Result
                </a>


            </div>


            <?php if (
                $results &&
                $results->num_rows > 0
            ): ?>


                <div class="table-wrapper">


                    <table class="data-table">


                        <thead>

                            <tr>

                                <th>
                                    Student
                                </th>

                                <th>
                                    Student ID
                                </th>

                                <th>
                                    Course
                                </th>

                                <th>
                                    Credit
                                </th>

                                <th>
                                    Semester
                                </th>

                                <th>
                                    Grade
                                </th>

                                <th>
                                    Grade Point
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php while (
                                $result =
                                $results->fetch_assoc()
                            ): ?>


                                <tr>


                                    <!-- Student -->

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $result["student_name"]
                                            ) ?>

                                        </strong>

                                    </td>


                                    <!-- Student ID -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $result["student_id"]
                                        ) ?>

                                    </td>


                                    <!-- Course -->

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $result["course_code"]
                                            ) ?>

                                        </strong>

                                        <br>

                                        <small>

                                            <?= htmlspecialchars(
                                                $result["course_name"]
                                            ) ?>

                                        </small>

                                    </td>


                                    <!-- Credit -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $result["credit"]
                                        ) ?>

                                    </td>


                                    <!-- Semester -->

                                    <td>

                                        <?= htmlspecialchars(
                                            $result["semester"]
                                        ) ?>

                                    </td>


                                    <!-- Grade -->

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $result["grade"]
                                            ) ?>

                                        </strong>

                                    </td>


                                    <!-- Grade Point -->

                                    <td>

                                        <?= number_format(
                                            (float)
                                            $result["grade_point"],
                                            2
                                        ) ?>

                                    </td>


                                    <!-- Action -->

                                    <td>

                                        <div class="actions">


                                            <a
                                                href="edit.php?id=<?= $result["id"] ?>"
                                                class="action-btn view"
                                            >
                                                Edit
                                            </a>


                                            <a
                                                href="delete.php?id=<?= $result["id"] ?>"
                                                class="action-btn"
                                                onclick="
                                                    return confirm(
                                                        'Are you sure you want to delete this result?'
                                                    );
                                                "
                                            >
                                                Delete
                                            </a>


                                        </div>

                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        </tbody>


                    </table>


                </div>


            <?php else: ?>


                <!-- Empty State -->

                <div class="empty-state">


                    <div class="empty-icon">
                        📊
                    </div>


                    <h3>
                        No results found
                    </h3>


                    <p>
                        Start by adding a student result.
                    </p>


                    <a
                        href="create.php"
                        class="btn btn-primary"
                    >
                        + Add Result
                    </a>


                </div>


            <?php endif; ?>


        </div>


    </section>


</main>


<script>

function closeSuccessPopup() {

    const popup =
        document.getElementById("successPopup");

    if (popup) {

        popup.remove();

    }

}


setTimeout(function () {

    closeSuccessPopup();

}, 4000);

</script>


<?php include "../includes/footer.php"; ?>