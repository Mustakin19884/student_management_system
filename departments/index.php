<?php

$success_message = null;


/*
|--------------------------------------------------------------------------
| Success Messages
|--------------------------------------------------------------------------
*/

if (isset($_GET['success'])) {

    switch ($_GET['success']) {

        case 'department_added':

            $success_message = [
                'title' => 'Department Added Successfully!',
                'text'  => 'The new department has been created.'
            ];

            break;


        case 'department_updated':

            $success_message = [
                'title' => 'Department Updated Successfully!',
                'text'  => 'The department has been updated.'
            ];

            break;


        case 'department_deleted':

            $success_message = [
                'title' => 'Department Deleted Successfully!',
                'text'  => 'The department has been removed.'
            ];

            break;
    }

}


/*
|--------------------------------------------------------------------------
| Error Reporting
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);


/*
|--------------------------------------------------------------------------
| Authentication & Database
|--------------------------------------------------------------------------
*/

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Department Initials
|--------------------------------------------------------------------------
|
| Examples:
| Management Information Systems → MIS
| Computer Science & Engineering → CSE
| Business Administration → BA
|
*/

function getDepartmentInitials($name)
{
    $name = trim($name);

    if (empty($name)) {
        return '';
    }

    $words = preg_split('/\s+/', $name);

    $initials = '';

    foreach ($words as $word) {

        if ($word === '&') {
            continue;
        }

        if (!empty($word)) {

            $initials .= strtoupper(
                substr($word, 0, 1)
            );

        }

    }

    return $initials;
}


/*
|--------------------------------------------------------------------------
| Get Departments + Student Count
|--------------------------------------------------------------------------
*/

$result = $conn->query("
    SELECT
        departments.id,
        departments.name,
        departments.created_at,
        COUNT(students.id) AS student_count

    FROM departments

    LEFT JOIN students
        ON students.department_id = departments.id

    GROUP BY
        departments.id,
        departments.name,
        departments.created_at

    ORDER BY departments.id DESC
");


/*
|--------------------------------------------------------------------------
| Check Query
|--------------------------------------------------------------------------
*/

if (!$result) {

    die(
        "Database Error: "
        . htmlspecialchars($conn->error)
    );

}

?>


<?php include "../includes/header.php"; ?>

<?php include "../includes/sidebar.php"; ?>


<?php if ($success_message): ?>

    <!-- Success Popup -->

    <div
        class="success-popup"
        id="successPopup"
    >

        <div class="success-icon">
            ✓
        </div>


        <div class="success-content">

            <h3>
                <?php

                echo htmlspecialchars(
                    $success_message['title']
                );

                ?>
            </h3>


            <p>
                <?php

                echo htmlspecialchars(
                    $success_message['text']
                );

                ?>
            </p>

        </div>


        <button
            class="success-close"
            onclick="closeSuccessPopup()"
        >
            ×
        </button>

    </div>

<?php endif; ?>


<main class="main-content">


    <!-- Topbar -->

    <header class="topbar">

        <div>

            <h1>
                Departments
            </h1>


            <p>
                Manage all academic departments.
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


        <div class="content-card">


            <!-- Card Header -->

            <div class="card-header">

                <div>

                    <h2>
                        All Departments
                    </h2>


                    <p>
                        Manage departments available for students.
                    </p>

                </div>


                <a
                    href="create.php"
                    class="btn btn-primary"
                >
                    + Add Department
                </a>

            </div>


            <!-- Table -->

            <div class="table-wrapper">


                <?php if ($result->num_rows > 0): ?>


                    <table class="data-table">


                        <thead>

                            <tr>

                                <th>
                                    ID
                                </th>


                                <th>
                                    Department Name
                                </th>


                                <th>
                                    Students
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


                            <?php while ($department = $result->fetch_assoc()): ?>


                                <tr>


                                    <!-- ID -->

                                    <td>

                                        #<?php

                                        echo htmlspecialchars(
                                            $department['id']
                                        );

                                        ?>

                                    </td>


                                    <!-- Department -->

                                    <td>

                                        <div class="department-info">


                                            <!-- Dynamic Initial -->

                                            <div class="department-icon">

                                                <?php

                                                echo htmlspecialchars(
                                                    getDepartmentInitials(
                                                        $department['name']
                                                    )
                                                );

                                                ?>

                                            </div>


                                            <!-- Name -->

                                            <strong>

                                                <?php

                                                echo htmlspecialchars(
                                                    $department['name']
                                                );

                                                ?>

                                            </strong>


                                        </div>

                                    </td>


                                    <!-- Student Count -->

                                    <td>

    <?php if ((int) $department['student_count'] > 0): ?>

        <a
            href="../students/index.php?department=<?php echo urlencode($department['id']); ?>"
            class="badge"
            style="
                text-decoration: none;
                cursor: pointer;
            "
            title="View students in this department"
        >

            <?php echo (int) $department['student_count']; ?>

            <?php
            echo (
                $department['student_count'] == 1
            )
            ? ' Student'
            : ' Students';
            ?>

        </a>

    <?php else: ?>

        <span class="badge">

            0 Students

        </span>

    <?php endif; ?>

</td>


                                    <!-- Created -->

                                    <td>

                                        <?php

                                        if (
                                            !empty(
                                                $department['created_at']
                                            )
                                        ) {

                                            echo date(
                                                'd M Y',
                                                strtotime(
                                                    $department['created_at']
                                                )
                                            );

                                        } else {

                                            echo 'N/A';

                                        }

                                        ?>

                                    </td>


                                    <!-- Actions -->

                                    <td>

                                        <div class="actions">


                                            <a
                                                href="edit.php?id=<?php echo urlencode($department['id']); ?>"
                                                class="action-btn edit"
                                            >
                                                Edit
                                            </a>


                                            <a
                                                href="delete.php?id=<?php echo urlencode($department['id']); ?>"
                                                class="action-btn delete"
                                                onclick="return confirm('Are you sure you want to delete this department?');"
                                            >
                                                Delete
                                            </a>


                                        </div>

                                    </td>


                                </tr>


                            <?php endwhile; ?>


                        </tbody>


                    </table>


                <?php else: ?>


                    <!-- Empty State -->

                    <div class="empty-state">


                        <div class="empty-icon">
                            🏫
                        </div>


                        <h3>
                            No departments found
                        </h3>


                        <p>
                            Start by adding your first department.
                        </p>


                        <a
                            href="create.php"
                            class="btn btn-primary"
                        >
                            Add Department
                        </a>


                    </div>


                <?php endif; ?>


            </div>


        </div>


    </section>


</main>


<?php include "../includes/footer.php"; ?>