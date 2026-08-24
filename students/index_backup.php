<?php

require_once "../config/config.php";
require_once "../config/db.php";
require_once "../config/auth.php";

requireLogin();

$page_title = "Students";

$conn = getDBConnection();

/*
|--------------------------------------------------------------------------
| Search & Filter
|--------------------------------------------------------------------------
*/

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

$department_id = isset($_GET['department'])
    ? (int) $_GET['department']
    : 0;


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

$per_page = 10;

$page = isset($_GET['page'])
    ? (int) $_GET['page']
    : 1;

if ($page < 1) {
    $page = 1;
}


/*
|--------------------------------------------------------------------------
| Build WHERE condition
|--------------------------------------------------------------------------
*/

$where = [];
$params = [];
$types = "";


/*
| Search
*/

if ($search !== '') {

    $where[] = "
        (
            s.student_id LIKE ?
            OR s.first_name LIKE ?
            OR s.last_name LIKE ?
            OR s.email LIKE ?
        )
    ";

    $search_value = "%" . $search . "%";

    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;
    $params[] = $search_value;

    $types .= "ssss";
}


/*
| Department filter
*/

if ($department_id > 0) {

    $where[] = "s.department_id = ?";

    $params[] = $department_id;

    $types .= "i";
}


/*
|--------------------------------------------------------------------------
| WHERE SQL
|--------------------------------------------------------------------------
*/

$where_sql = "";

if (!empty($where)) {

    $where_sql = "WHERE " . implode(" AND ", $where);
}


/*
|--------------------------------------------------------------------------
| Count total students
|--------------------------------------------------------------------------
*/

$count_sql = "
    SELECT COUNT(*) AS total
    FROM students s
    $where_sql
";

$count_stmt = $conn->prepare($count_sql);

if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}

$count_stmt->execute();

$count_result = $count_stmt->get_result();

$total_students = $count_result->fetch_assoc()['total'];

$count_stmt->close();


/*
|--------------------------------------------------------------------------
| Pagination calculation
|--------------------------------------------------------------------------
*/

$total_pages = ceil($total_students / $per_page);

if ($total_pages > 0 && $page > $total_pages) {
    $page = $total_pages;
}

$offset = ($page - 1) * $per_page;


/*
|--------------------------------------------------------------------------
| Fetch students
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        s.id,
        s.student_id,
        s.first_name,
        s.last_name,
        s.email,
        s.phone,
        s.gender,
        s.status,
        d.name AS department_name

    FROM students s

    LEFT JOIN departments d
        ON s.department_id = d.id

    $where_sql

    ORDER BY s.id DESC

    LIMIT ? OFFSET ?
";


$stmt = $conn->prepare($sql);


/*
| Add pagination parameters
*/

$query_params = $params;

$query_types = $types . "ii";

$query_params[] = $per_page;
$query_params[] = $offset;


$stmt->bind_param(
    $query_types,
    ...$query_params
);

$stmt->execute();

$result = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| Departments
|--------------------------------------------------------------------------
*/

$department_sql = "
    SELECT id, name
    FROM departments
    ORDER BY name ASC
";

$department_result = $conn->query($department_sql);


/*
|--------------------------------------------------------------------------
| Include Layout
|--------------------------------------------------------------------------
*/

require_once "../includes/header.php";
require_once "../includes/sidebar.php";

?>

<div class="main-content">

    <div class="page-header">

        <div>

            <h1>Students</h1>

            <p>
                Manage all registered students.
            </p>

        </div>

        <a
            href="create.php"
            class="btn btn-primary"
        >
            + Add Student
        </a>

    </div>


    <!-- Filters -->

    <div class="card student-filter-card">

        <form
            method="GET"
            action=""
            class="student-filter-form"
        >

            <!-- Search -->

            <div class="filter-group">

                <label for="search">
                    Search
                </label>

                <input
                    type="text"
                    id="search"
                    name="search"
                    value="<?= htmlspecialchars($search) ?>"
                    placeholder="Name, Student ID or Email"
                >

            </div>


            <!-- Department -->

            <div class="filter-group">

                <label for="department">
                    Department
                </label>

                <select
                    name="department"
                    id="department"
                >

                    <option value="">
                        All Departments
                    </option>

                    <?php while ($department = $department_result->fetch_assoc()): ?>

                        <option
                            value="<?= $department['id'] ?>"
                            <?= $department_id == $department['id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($department['name']) ?>
                        </option>

                    <?php endwhile; ?>

                </select>

            </div>


            <!-- Buttons -->

            <div class="filter-actions">

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Search
                </button>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    Clear
                </a>

            </div>

        </form>

    </div>


    <!-- Result Information -->

    <div class="student-result-info">

        <span>

            <?php if ($total_students > 0): ?>

                Showing
                <strong>
                    <?= $offset + 1 ?>
                </strong>

                -
                <strong>
                    <?= min($offset + $per_page, $total_students) ?>
                </strong>

                of

                <strong>
                    <?= $total_students ?>
                </strong>

                students

            <?php else: ?>

                No students found.

            <?php endif; ?>

        </span>

    </div>


    <!-- Students Table -->

    <div class="card table-card">

        <div class="table-responsive">

            <table class="data-table">

                <thead>

                    <tr>

                        <th>
                            Student ID
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
                            Status
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody>

                <?php if ($result->num_rows > 0): ?>

                    <?php while ($student = $result->fetch_assoc()): ?>

                        <tr>

                            <td>

                                <strong>
                                    <?= htmlspecialchars($student['student_id']) ?>
                                </strong>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $student['first_name'] . ' ' . $student['last_name']
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars($student['email']) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $student['phone'] ?? '-'
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $student['department_name'] ?? 'Not Assigned'
                                ) ?>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $student['gender'] ?? '-'
                                ) ?>

                            </td>


                            <td>

                                <?php if ($student['status'] === 'active'): ?>

                                    <span class="status-badge status-active">
                                        Active
                                    </span>

                                <?php else: ?>

                                    <span class="status-badge status-inactive">
                                        Inactive
                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <div class="table-actions">

                                    <a
                                        href="edit.php?id=<?= $student['id'] ?>"
                                        class="action-btn edit-btn"
                                    >
                                        Edit
                                    </a>

                                    <a
                                        href="delete.php?id=<?= $student['id'] ?>"
                                        class="action-btn delete-btn"
                                        onclick="return confirm('Are you sure you want to delete this student?');"
                                    >
                                        Delete
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="8"
                            class="empty-state"
                        >

                            <div class="empty-icon">
                                🎓
                            </div>

                            <h3>
                                No students found
                            </h3>

                            <p>
                                Try changing your search or filter.
                            </p>

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>


    <!-- Pagination -->

    <?php if ($total_pages > 1): ?>

        <div class="pagination">

            <?php

            $query_string = http_build_query([
                'search' => $search,
                'department' => $department_id
            ]);

            ?>


            <!-- Previous -->

            <?php if ($page > 1): ?>

                <a
                    href="?<?= $query_string ?>&page=<?= $page - 1 ?>"
                    class="page-btn"
                >
                    ←
                </a>

            <?php endif; ?>


            <!-- Page Numbers -->

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>

                <a
                    href="?<?= $query_string ?>&page=<?= $i ?>"
                    class="page-btn <?= $i == $page ? 'active' : '' ?>"
                >
                    <?= $i ?>
                </a>

            <?php endfor; ?>


            <!-- Next -->

            <?php if ($page < $total_pages): ?>

                <a
                    href="?<?= $query_string ?>&page=<?= $page + 1 ?>"
                    class="page-btn"
                >
                    →
                </a>

            <?php endif; ?>

        </div>

    <?php endif; ?>

</div>


<?php

$stmt->close();

$conn->close();

require_once "../includes/footer.php";

?>