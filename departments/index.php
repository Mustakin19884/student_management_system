<?php
$success_message = null;


if (isset($_GET['success'])) {

    switch ($_GET['success']) {

        case 'department_added':

            $success_message = [
                'title' => 'Department Added Successfully!',
                'text' => 'The new department has been created.'
            ];

            break;


        case 'department_updated':

            $success_message = [
                'title' => 'Department Updated Successfully!',
                'text' => 'The department has been updated.'
            ];

            break;


        case 'department_deleted':

            $success_message = [
                'title' => 'Department Deleted Successfully!',
                'text' => 'The department has been removed.'
            ];

            break;
    }

}
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Get Departments
|--------------------------------------------------------------------------
*/

$result = $conn->query("
    SELECT *
    FROM departments
    ORDER BY id DESC
");

?>

<?php include "../includes/header.php"; ?>

<?php include "../includes/sidebar.php"; ?>


<?php if ($success_message): ?>

    <div
        class="success-popup"
        id="successPopup"
    >

        <div class="success-icon">
            ✓
        </div>


        <div class="success-content">

            <h3>
                <?php echo $success_message['title']; ?>
            </h3>

            <p>
                <?php echo $success_message['text']; ?>
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

    <header class="topbar">

        <div>

            <h1>Departments</h1>

            <p>
                Manage all academic departments.
            </p>

        </div>


        <div class="profile">

            <div class="avatar">
                A
            </div>

            <div>

                <strong>Admin</strong>

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


                                    <td>

                                        #<?php
                                        echo $department['id'];
                                        ?>

                                    </td>


                                    <td>

                                        <div class="department-info">

                                            <div class="department-icon">
                                                D
                                            </div>

                                            <strong>

                                                <?php

                                                echo htmlspecialchars(
                                                    $department['name']
                                                );

                                                ?>

                                            </strong>

                                        </div>

                                    </td>


                                    <td>

                                        <?php

                                        echo date(
                                            'd M Y',
                                            strtotime(
                                                $department['created_at']
                                            )
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <div class="actions">


                                            <a
                                                href="edit.php?id=<?php echo $department['id']; ?>"
                                                class="action-btn edit"
                                            >
                                                Edit
                                            </a>


                                            <a
                                                href="delete.php?id=<?php echo $department['id']; ?>"
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