<?php
require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Success Message
|--------------------------------------------------------------------------
*/

$success_message = null;

if (isset($_GET['success'])) {

    switch ($_GET['success']) {

        case 'student_added':

            $success_message = [
                'title' => 'Student Added Successfully!',
                'text' => 'The student has been added to the database.'
            ];

            break;


        case 'student_updated':

            $success_message = [
                'title' => 'Student Updated Successfully!',
                'text' => 'The student information has been updated.'
            ];

            break;


        case 'student_deleted':

            $success_message = [
                'title' => 'Student Deleted Successfully!',
                'text' => 'The student has been removed from the system.'
            ];

            break;
    }
}


/*
|--------------------------------------------------------------------------
| Search & Filter
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');

$department_id = isset($_GET['department'])
    ? (int) $_GET['department']
    : 0;


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

$per_page = 8;

$page = isset($_GET['page'])
    ? max(1, (int) $_GET['page'])
    : 1;

$offset = ($page - 1) * $per_page;


/*
|--------------------------------------------------------------------------
| Get Departments
|--------------------------------------------------------------------------
*/

$departments = $conn->query("
    SELECT id, name
    FROM departments
    ORDER BY name ASC
");


/*
|--------------------------------------------------------------------------
| Build Search Conditions
|--------------------------------------------------------------------------
*/

$where = [];

$params = [];

$types = "";


if ($search !== '') {

    $where[] = "
    (
        students.student_id LIKE ?
        OR students.name LIKE ?
        OR students.email LIKE ?
        OR students.phone LIKE ?
    )
";

$search_value = "%{$search}%";

$params[] = $search_value;
$params[] = $search_value;
$params[] = $search_value;
$params[] = $search_value;

$types .= "ssss";
}


if ($department_id > 0) {

    $where[] = "students.department_id = ?";

    $params[] = $department_id;

    $types .= "i";
}


$where_sql = '';

if (!empty($where)) {

    $where_sql = 'WHERE ' . implode(' AND ', $where);
}


/*
|--------------------------------------------------------------------------
| Count Total Students
|--------------------------------------------------------------------------
*/

$count_sql = "
    SELECT COUNT(*) AS total
    FROM students
    $where_sql
";


$count_stmt = $conn->prepare($count_sql);


if (!empty($params)) {

    $count_stmt->bind_param(
        $types,
        ...$params
    );
}


$count_stmt->execute();

$count_result = $count_stmt->get_result();

$total_students = $count_result->fetch_assoc()['total'];

$count_stmt->close();


/*
|--------------------------------------------------------------------------
| Calculate Pages
|--------------------------------------------------------------------------
*/

$total_pages = max(
    1,
    ceil($total_students / $per_page)
);


if ($page > $total_pages) {
    $page = $total_pages;
    $offset = ($page - 1) * $per_page;
}

/*
|--------------------------------------------------------------------------
| Get Students
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        students.*,
        departments.name AS department_name

    FROM students

    LEFT JOIN departments
        ON students.department_id = departments.id

    $where_sql

    ORDER BY students.id DESC

    LIMIT ? OFFSET ?
";


$stmt = $conn->prepare($sql);


/*
|--------------------------------------------------------------------------
| Bind Search Parameters + Pagination
|--------------------------------------------------------------------------
*/

if (!empty($params)) {

    $student_types = $types . "ii";

    $student_params = [
        ...$params,
        $per_page,
        $offset
    ];

    $stmt->bind_param(
        $student_types,
        ...$student_params
    );

} else {

    $stmt->bind_param(
        "ii",
        $per_page,
        $offset
    );
}


$stmt->execute();

$result = $stmt->get_result();

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


    <!-- Topbar -->

    <header class="topbar">

        <div>

            <h1>Students</h1>

            <p>
                Manage all registered students.
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


            <!-- Header -->

            <div class="card-header">

                <div>

                    <h2>
                        All Students
                    </h2>

                    <p>

    <?php echo $total_students; ?>

    <?php echo $total_students == 1 ? 'student' : 'students'; ?>

    found

    <?php if ($department_id > 0): ?>

        <?php
        $selected_department_name = '';

        $department_name_stmt = $conn->prepare("
            SELECT name
            FROM departments
            WHERE id = ?
            LIMIT 1
        ");

        $department_name_stmt->bind_param(
            "i",
            $department_id
        );

        $department_name_stmt->execute();

        $department_name_result =
            $department_name_stmt->get_result();

        if ($department_name_result->num_rows > 0) {

            $selected_department =
                $department_name_result->fetch_assoc();

            $selected_department_name =
                $selected_department['name'];

        }

        $department_name_stmt->close();
        ?>

        in

        <strong>
            <?php
            echo htmlspecialchars(
                $selected_department_name
            );
            ?>
        </strong>

    <?php endif; ?>

</p>

                </div>


                <a
                    href="create.php"
                    class="btn btn-primary"
                >
                    + Add Student
                </a>

            </div>


            <!-- Search -->

            <form
                method="GET"
                class="filter-bar"
            >

                <div class="search-box">

                    <span>
                        🔍
                    </span>

                    <input
                        type="text"
                        name="search"
                        placeholder="Search by name, email or phone..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >

                </div>


                <select
                    name="department"
                    class="filter-select"
                >

                    <option value="">
                        All Departments
                    </option>


                    <?php while ($department = $departments->fetch_assoc()): ?>

                        <option
                            value="<?php echo $department['id']; ?>"
                            <?php
                            echo (
                                $department_id
                                == $department['id']
                            )
                            ? 'selected'
                            : '';
                            ?>
                        >

                            <?php
                            echo htmlspecialchars(
                                $department['name']
                            );
                            ?>

                        </option>

                    <?php endwhile; ?>

                </select>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Search
                </button>


                <?php if ($search !== '' || $department_id > 0): ?>

                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >
                        Clear
                    </a>

                <?php endif; ?>

            </form>


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
                                    Student
                                </th>

                                <th>
                                    Email
                                </th>

                                <th>
                                    Phone
                                </th>

                                <th>
                                    Department
                                </th>

                                <th>
                                    Gender
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php while ($student = $result->fetch_assoc()): ?>


                                <tr>


                                    <td>

    <strong class="student-id">

        <?php

        echo htmlspecialchars(
            $student['student_id']
            ?? 'N/A'
        );

        ?>

    </strong>

</td>
                                    <td>

                                        <div class="student-info">


                                            <div class="student-avatar">

                                                <?php

                                                echo strtoupper(
                                                    substr(
                                                        $student['name'],
                                                        0,
                                                        1
                                                    )
                                                );

                                                ?>

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


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $student['email']
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $student['phone']
                                            ?: '-'
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $student['department_name']
                                            ?? 'Not Assigned'
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <span class="badge">

                                            <?php

                                            echo htmlspecialchars(
                                                $student['gender']
                                                ?: '-'
                                            );

                                            ?>

                                        </span>

                                    </td>


                                    <td>

                                        <div class="actions">


                                            <a
                                                href="view.php?id=<?php echo $student['id']; ?>"
                                                class="action-btn view"
                                            >
                                                View
                                            </a>


                                            
                                            <a
                                                href="delete.php?id=<?php echo $student['id']; ?>"
                                                class="action-btn delete"
                                                onclick="return confirm('Are you sure you want to delete this student?');"
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
                            🔍
                        </div>

                        <h3>
                            No students found
                        </h3>

                        <p>
                            Try changing your search or filter.
                        </p>

                        <a
                            href="index.php"
                            class="btn btn-secondary"
                        >
                            Clear Filters
                        </a>

                    </div>


                <?php endif; ?>


            </div>


            <!-- Pagination -->

            <?php if ($total_pages > 1): ?>

                <div class="pagination">


                    <?php

                    $query = [];

                    if ($search !== '') {
                        $query['search'] = $search;
                    }

                    if ($department_id > 0) {
                        $query['department'] = $department_id;
                    }

                    ?>


                    <?php if ($page > 1): ?>

                        <?php

                        $query['page'] = $page - 1;

                        ?>

                        <a
                            href="?<?php echo http_build_query($query); ?>"
                            class="page-btn"
                        >
                            ←
                        </a>

                    <?php endif; ?>


                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>

                        <?php

                        $query['page'] = $i;

                        ?>


                        <a
                            href="?<?php echo http_build_query($query); ?>"
                            class="page-btn
                            <?php echo $i === $page ? 'active' : ''; ?>"
                        >

                            <?php echo $i; ?>

                        </a>


                    <?php endfor; ?>


                    <?php if ($page < $total_pages): ?>

                        <?php

                        $query['page'] = $page + 1;

                        ?>

                        <a
                            href="?<?php echo http_build_query($query); ?>"
                            class="page-btn"
                        >
                            →
                        </a>

                    <?php endif; ?>


                </div>

            <?php endif; ?>


        </div>

    </section>

</main>


<?php include "../includes/footer.php"; ?>