<?php

require_once "../config/auth.php";
require_once "../config/db.php";


$success_message = "";
$error_message = "";


if (isset($_GET["success"])) {

    if ($_GET["success"] === "course_created") {

        $success_message = "Course created successfully.";

    } elseif ($_GET["success"] === "course_updated") {

        $success_message = "Course updated successfully.";

    } elseif ($_GET["success"] === "course_deleted") {

        $success_message = "Course deleted successfully.";

    }

}


if (isset($_GET["error"])) {

    if ($_GET["error"] === "delete_failed") {

        $error_message = "Failed to delete course.";

    }

}


/*
|--------------------------------------------------------------------------
| Get All Courses
|--------------------------------------------------------------------------
*/

$courses = $conn->query("
    SELECT
        id,
        course_code,
        course_name,
        credit,
        created_at

    FROM courses

    ORDER BY course_code ASC
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

    background: #ffffff;

    border-left: 5px solid #22c55e;

    border-radius: 10px;

    padding: 16px 18px;

    display: flex;

    align-items: center;

    gap: 14px;

    box-shadow:
        0 10px 30px rgba(0,0,0,0.15);

    z-index: 9999;

    animation:
        popupSlideIn 0.3s ease;
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

    padding: 0;
}


.popup-close:hover {

    color: #222;

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

        transform:
            translateX(30px);

    }

    to {

        opacity: 1;

        transform:
            translateX(0);

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
                Courses
            </h1>

            <p>
                Manage all courses in the student management system.
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
<?php if ($success_message): ?>

    <div class="success-popup" id="successPopup">

        <div class="popup-icon">
            ✓
        </div>

        <div class="popup-content">

            <strong>
                Success
            </strong>

            <p>
                <?= htmlspecialchars($success_message) ?>
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


<?php if ($error_message): ?>

    <div class="success-popup error-popup" id="successPopup">

        <div class="popup-icon">
            !
        </div>

        <div class="popup-content">

            <strong>
                Error
            </strong>

            <p>
                <?= htmlspecialchars($error_message) ?>
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
             Course List
        ====================================================== -->

        <div class="content-card">


            <div class="card-header">

                <div>

                    <h2>
                        Course List
                    </h2>

                    <p>
                        View and manage all available courses.
                    </p>

                </div>


                <a
                    href="create.php"
                    class="btn btn-primary"
                >
                    + Add Course
                </a>

            </div>


            <?php if (
                $courses &&
                $courses->num_rows > 0
            ): ?>


                <div class="table-wrapper">


                    <table class="data-table">


                        <thead>

                            <tr>

                                <th>
                                    #
                                </th>

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
                                    Created
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php

                            $serial = 1;

                            while (
                                $course =
                                $courses->fetch_assoc()
                            ):

                            ?>


                                <tr>


                                    <!-- Serial -->

                                    <td>

                                        <?php
                                        echo $serial++;
                                        ?>

                                    </td>


                                    <!-- Course Code -->

                                    <td>

                                        <strong>

                                            <?php

                                            echo htmlspecialchars(
                                                $course['course_code']
                                            );

                                            ?>

                                        </strong>

                                    </td>


                                    <!-- Course Name -->

                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $course['course_name']
                                        );

                                        ?>

                                    </td>


                                    <!-- Credit -->

                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $course['credit']
                                        );

                                        ?>

                                    </td>


                                    <!-- Created -->

                                    <td>

                                        <?php

                                        echo !empty(
                                            $course['created_at']
                                        )
                                        ? date(
                                            'd M Y',
                                            strtotime(
                                                $course['created_at']
                                            )
                                        )
                                        : 'N/A';

                                        ?>

                                    </td>


                                    <!-- Action -->

                                    <td>

                                        <div class="actions">


                                            <a
                                                href="edit.php?id=<?php echo $course['id']; ?>"
                                                class="action-btn view"
                                            >
                                                Edit
                                            </a>


                                            <a
                                                href="delete.php?id=<?php echo $course['id']; ?>"
                                                class="action-btn"
                                                onclick="return confirm('Are you sure you want to delete this course?');"
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
                        📚
                    </div>


                    <h3>
                        No courses found
                    </h3>


                    <p>
                        Start by adding your first course.
                    </p>


                    <a
                        href="create.php"
                        class="btn btn-primary"
                    >
                        Add Course
                    </a>


                </div>


            <?php endif; ?>


        </div>


    </section>


</main>


<?php include "../includes/footer.php"; ?>

<script>

function closeSuccessPopup() {

    const popup =
        document.getElementById("successPopup");

    if (popup) {

        popup.remove();

    }


setTimeout(function () {

    closeSuccessPopup();

}, 4000);

</script>