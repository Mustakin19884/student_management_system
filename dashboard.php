<?php

require_once "config/auth.php";
require_once "config/db.php";


/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Total Students
|--------------------------------------------------------------------------
*/

$total_students_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM students
");

$total_students = 0;

if ($total_students_result) {

    $total_students =
        (int) $total_students_result->fetch_assoc()['total'];

}


/*
|--------------------------------------------------------------------------
| Active Students
|--------------------------------------------------------------------------
*/

$active_students_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM students
    WHERE status = 'Active'
");

$active_students = 0;

if ($active_students_result) {

    $active_students =
        (int) $active_students_result->fetch_assoc()['total'];

}


/*
|--------------------------------------------------------------------------
| Inactive Students
|--------------------------------------------------------------------------
*/

$inactive_students_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM students
    WHERE status = 'Inactive'
");

$inactive_students = 0;

if ($inactive_students_result) {

    $inactive_students =
        (int) $inactive_students_result->fetch_assoc()['total'];

}


/*
|--------------------------------------------------------------------------
| Total Departments
|--------------------------------------------------------------------------
*/

$total_departments_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM departments
");

$total_departments = 0;

if ($total_departments_result) {

    $total_departments =
        (int) $total_departments_result->fetch_assoc()['total'];

}


/*
|--------------------------------------------------------------------------
| New Students This Month
|--------------------------------------------------------------------------
*/

$new_students_result = $conn->query("
    SELECT COUNT(*) AS total
    FROM students
    WHERE created_at >= DATE_FORMAT(
        CURDATE(),
        '%Y-%m-01'
    )
");

$new_students = 0;

if ($new_students_result) {

    $new_students =
        (int) $new_students_result->fetch_assoc()['total'];

}


/*
|--------------------------------------------------------------------------
| Students By Department
|--------------------------------------------------------------------------
*/

$department_stats = $conn->query("
    SELECT

        departments.id,

        departments.name,

        COUNT(students.id) AS student_count

    FROM departments

    LEFT JOIN students

        ON students.department_id = departments.id

    GROUP BY

        departments.id,

        departments.name

    ORDER BY

        student_count DESC,

        departments.name ASC
");


/*
|--------------------------------------------------------------------------
| Recent Students
|--------------------------------------------------------------------------
*/

$recent_students = $conn->query("
    SELECT

        students.id,

        students.name,

        students.email,

        students.photo,

        students.status,

        departments.name AS department_name

    FROM students

    LEFT JOIN departments

        ON students.department_id = departments.id

    ORDER BY students.id DESC

    LIMIT 5
");

?>


<?php include "includes/header.php"; ?>

<?php include "includes/sidebar.php"; ?>


<main class="main-content">


    <!-- =========================================================
         Topbar
    ========================================================== -->

    <header class="topbar">


        <div>

            <h1>
                Dashboard
            </h1>

            <p>
                Welcome back! Here's what's happening today.
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
             Statistics Cards
        ====================================================== -->


        <div class="stats-grid">


            <!-- Total Students -->

            <div class="stat-card">


                <div class="stat-icon purple">

                    👥

                </div>


                <div>

                    <span>
                        Total Students
                    </span>


                    <h2>
                        <?php echo $total_students; ?>
                    </h2>

                </div>


            </div>


            <!-- Departments -->

            <div class="stat-card">


                <div class="stat-icon blue">

                    ▤

                </div>


                <div>

                    <span>
                        Departments
                    </span>


                    <h2>
                        <?php echo $total_departments; ?>
                    </h2>

                </div>


            </div>


            <!-- Active Students -->

            <div class="stat-card">


                <div class="stat-icon green">

                    ✓

                </div>


                <div>

                    <span>
                        Active Students
                    </span>


                    <h2>
                        <?php echo $active_students; ?>
                    </h2>

                </div>


            </div>


            <!-- Inactive Students -->

            <div class="stat-card">


                <div class="stat-icon orange">

                    ⏸

                </div>


                <div>

                    <span>
                        Inactive Students
                    </span>


                    <h2>
                        <?php echo $inactive_students; ?>
                    </h2>

                </div>


            </div>


        </div>


        <!-- =====================================================
             Students By Department
        ====================================================== -->


        <div class="content-card">


            <div class="card-header">


                <div>

                    <h2>
                        Students by Department
                    </h2>


                    <p>
                        Student distribution across departments.
                    </p>

                </div>


                <a
                    href="departments/index.php"
                    class="btn btn-secondary"
                >
                    Manage Departments
                </a>


            </div>


            <div class="department-stat-list">


                <?php if (
                    $department_stats &&
                    $department_stats->num_rows > 0
                ): ?>


                    <?php while (
                        $department =
                        $department_stats->fetch_assoc()
                    ): ?>


                        <?php

                        $department_count =
                            (int) $department['student_count'];


                        $percentage = 0;


                        if ($total_students > 0) {

                            $percentage =
                                (
                                    $department_count
                                    / $total_students
                                ) * 100;

                        }

                        ?>


                        <div class="department-stat">


                            <div class="department-stat-header">


                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $department['name']
                                    );

                                    ?>

                                </strong>


                                <span>

                                    <?php

                                    echo $department_count;

                                    ?>

                                    <?php

                                    echo (
                                        $department_count === 1
                                    )
                                    ? ' Student'
                                    : ' Students';

                                    ?>

                                </span>


                            </div>


                            <div class="progress-bar">


                                <div
                                    class="progress-fill"
                                    style="
                                        width:
                                        <?php
                                        echo round(
                                            $percentage,
                                            2
                                        );
                                        ?>%;
                                    "
                                ></div>


                            </div>


                        </div>


                    <?php endwhile; ?>


                <?php else: ?>


                    <div class="empty-state">


                        <div class="empty-icon">
                            🏫
                        </div>


                        <h3>
                            No departments found
                        </h3>


                        <p>
                            Add departments to see student distribution.
                        </p>


                        <a
                            href="departments/create.php"
                            class="btn btn-primary"
                        >
                            Add Department
                        </a>


                    </div>


                <?php endif; ?>


            </div>


        </div>


        <!-- =====================================================
             Recent Students
        ====================================================== -->


        <div class="content-card">


            <div class="card-header">


                <div>

                    <h2>
                        Recent Students
                    </h2>


                    <p>
                        Recently added students.
                    </p>

                </div>


                <a
                    href="students/index.php"
                    class="btn btn-secondary"
                >
                    View All
                </a>


            </div>


            <?php if (
                $recent_students &&
                $recent_students->num_rows > 0
            ): ?>


                <div class="table-wrapper">


                    <table class="data-table">


                        <thead>


                            <tr>

                                <th>
                                    Student
                                </th>

                                <th>
                                    Email
                                </th>

                                <th>
                                    Department
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>


                        </thead>


                        <tbody>


                            <?php while (
                                $student =
                                $recent_students->fetch_assoc()
                            ): ?>


                                <tr>


                                    <!-- Student -->

                                    <td>


                                        <div class="student-info">


                                            <div class="student-avatar">


                                                <?php

                                                if (
                                                    !empty(
                                                        $student['photo']
                                                    )
                                                ):

                                                ?>


                                                    <img
                                                        src="uploads/students/<?php echo htmlspecialchars($student['photo']); ?>"
                                                        alt="<?php echo htmlspecialchars($student['name']); ?>"
                                                    >


                                                <?php else: ?>


                                                    <?php

                                                    echo strtoupper(
                                                        substr(
                                                            $student['name'],
                                                            0,
                                                            1
                                                        )
                                                    );

                                                    ?>


                                                <?php endif; ?>


                                            </div>


                                            <strong>

                                                <?php

                                                echo htmlspecialchars(
                                                    $student['name']
                                                );

                                                ?>

                                            </strong>


                                        </div>


                                    </td>


                                    <!-- Email -->

                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $student['email']
                                        );

                                        ?>

                                    </td>


                                    <!-- Department -->

                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $student['department_name']
                                            ?? 'Not Assigned'
                                        );

                                        ?>

                                    </td>


                                    <!-- Status -->

                                    <td>


                                        <?php if (
                                            ($student['status'] ?? 'Active')
                                            === 'Active'
                                        ): ?>


                                            <span class="badge">

                                                Active

                                            </span>


                                        <?php else: ?>


                                            <span class="badge">

                                                Inactive

                                            </span>


                                        <?php endif; ?>


                                    </td>


                                    <!-- Action -->

                                    <td>


                                        <div class="actions">


                                            <a
                                                href="students/view.php?id=<?php echo $student['id']; ?>"
                                                class="action-btn view"
                                            >
                                                View
                                            </a>


                                        </div>


                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        </tbody>


                    </table>


                </div>


            <?php else: ?>


                <div class="empty-state">


                    <div class="empty-icon">
                        👨‍🎓
                    </div>


                    <h3>
                        No students yet
                    </h3>


                    <p>
                        Start by adding your first student.
                    </p>


                    <a
                        href="students/create.php"
                        class="btn btn-primary"
                    >
                        Add Student
                    </a>


                </div>


            <?php endif; ?>


        </div>


    </section>


</main>


<?php include "includes/footer.php"; ?>