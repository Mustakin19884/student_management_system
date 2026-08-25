<?php

require_once "../config/auth.php";
require_once "../config/db.php";


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