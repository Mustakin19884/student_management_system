<?php

require_once "config/db.php";

$student_result = $conn->query(
    "SELECT COUNT(*) AS total FROM students"
);

$student_data = $student_result->fetch_assoc();

$total_students = $student_data['total'];


$department_result = $conn->query(
    "SELECT COUNT(*) AS total FROM departments"
);

$department_data = $department_result->fetch_assoc();

$total_departments = $department_data['total'];

?>

<?php include "includes/header.php"; ?>

<?php include "includes/sidebar.php"; ?>
<?php

require_once "config/auth.php";

require_once "config/db.php";

?>
<main class="main-content">

    <header class="topbar">

        <div>
            <h1>Dashboard</h1>
            <p>Welcome back! Here's what's happening today.</p>
        </div>

        <div class="profile">

            <div class="avatar">
                A
            </div>

            <div>
                <strong>Admin</strong>
                <span>Administrator</span>
            </div>

        </div>

    </header>


    <section class="dashboard-content">

        <div class="stats-grid">

            <div class="stat-card">

                <div class="stat-icon purple">
                    👥
                </div>

                <div>
                    <span>Total Students</span>
                    <h2><?php echo $total_students; ?></h2>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon blue">
                    ▤
                </div>

                <div>
                    <span>Departments</span>
                    <h2><?php echo $total_departments; ?></h2>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon green">
                    ✓
                </div>

                <div>
                    <span>Active Students</span>
                    <h2><?php echo $total_students; ?></h2>
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-icon orange">
                    +
                </div>

                <div>
                    <span>New This Month</span>
                    <h2>0</h2>
                </div>

            </div>

        </div>


        <div class="content-card">

            <div class="card-header">

                <div>
                    <h2>Recent Students</h2>
                    <p>Recently added students</p>
                </div>

                <a
                    href="students/create.php"
                    class="btn btn-primary"
                >
                    + Add Student
                </a>

            </div>

            <div class="empty-state">

                <div class="empty-icon">
                    👨‍🎓
                </div>

                <h3>No students yet</h3>

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

        </div>

    </section>

</main>

<?php include "includes/footer.php"; ?>