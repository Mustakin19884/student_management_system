<?php

session_start();

require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Fetch Assigned Courses
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        sc.id,

        sc.student_id,

        sc.course_id,

        sc.semester,

        sc.assigned_at,

        s.student_id AS student_code,

        s.name AS student_name,

        c.course_code,

        c.course_name,

        c.credit

    FROM student_courses sc

    INNER JOIN students s
        ON sc.student_id = s.id

    INNER JOIN courses c
        ON sc.course_id = c.id

    ORDER BY sc.id DESC

";


$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Assigned Courses</title>
<style>

    * {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    padding: 0;
}

body {
    font-family: Arial, sans-serif;
    background: #f5f7fb;
    color: #1f2937;
}


/* =========================================
   APP LAYOUT
========================================= */

.app-layout {
    display: flex;
    min-height: 100vh;
}


/* =========================================
   SIDEBAR
========================================= */

.sidebar {
    width: 250px;
    height: 100vh;

    position: fixed;
    left: 0;
    top: 0;

    background: #111827;
    color: #ffffff;

    display: flex;
    flex-direction: column;

    z-index: 1000;
}


/* =========================================
   LOGO
========================================= */

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 12px;

    padding: 25px 20px;

    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.logo-icon {
    width: 42px;
    height: 42px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #2563eb;
    color: #ffffff;

    border-radius: 10px;

    font-size: 20px;
    font-weight: 700;
}

.logo-text h2 {
    margin: 0;

    font-size: 19px;
    font-weight: 700;
}

.logo-text span {
    display: block;

    margin-top: 3px;

    color: #9ca3af;

    font-size: 11px;
}


/* =========================================
   NAVIGATION
========================================= */

.sidebar-nav {
    flex: 1;

    padding: 20px 12px;

    overflow-y: auto;
}

.nav-section-title {
    padding: 0 12px 10px;

    color: #6b7280;

    font-size: 11px;
    font-weight: 700;

    letter-spacing: 0.8px;
}


.nav-item {
    display: flex;
    align-items: center;

    gap: 12px;

    width: 100%;

    padding: 11px 13px;

    margin-bottom: 5px;

    color: #d1d5db;

    text-decoration: none;

    border-radius: 7px;

    font-size: 14px;

    transition: all 0.2s ease;
}


.nav-item:hover {
    background: #1f2937;
    color: #ffffff;
}


.nav-item.active {
    background: #2563eb;
    color: #ffffff;
}


.nav-icon {
    width: 22px;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 17px;
}


.nav-label {
    flex: 1;
}


/* =========================================
   SIDEBAR FOOTER
========================================= */

.sidebar-footer {
    padding: 15px;

    border-top: 1px solid rgba(255, 255, 255, 0.08);
}


/* =========================================
   ADMIN PROFILE
========================================= */

.admin-mini {
    display: flex;
    align-items: center;

    gap: 10px;

    margin-bottom: 15px;
}

.avatar {
    width: 38px;
    height: 38px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #2563eb;

    border-radius: 50%;

    color: #ffffff;

    font-size: 15px;
    font-weight: 700;
}

.admin-info {
    display: flex;
    flex-direction: column;

    min-width: 0;
}

.admin-info strong {
    color: #ffffff;

    font-size: 13px;

    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.admin-info span {
    margin-top: 3px;

    color: #9ca3af;

    font-size: 11px;
}


/* =========================================
   LOGOUT
========================================= */

.logout-link {
    display: flex;
    align-items: center;

    gap: 10px;

    padding: 10px 12px;

    color: #fca5a5;

    text-decoration: none;

    border-radius: 6px;

    font-size: 13px;

    transition: all 0.2s ease;
}

.logout-link:hover {
    background: rgba(239, 68, 68, 0.12);

    color: #f87171;
}

.logout-icon {
    font-size: 16px;
}


/* =========================================
   MAIN CONTENT
========================================= */

.main-content {
    width: calc(100% - 250px);

    margin-left: 250px;

    padding: 40px;

    min-height: 100vh;
}


/* =========================================
   PAGE HEADER
========================================= */

.page-header {
    display: flex;

    justify-content: space-between;
    align-items: center;

    margin-bottom: 25px;
}

.page-header h2 {
    margin: 0;

    font-size: 25px;

    color: #111827;
}


/* =========================================
   ADD BUTTON
========================================= */

.add-btn {
    display: inline-flex;

    align-items: center;

    padding: 11px 18px;

    background: #2563eb;

    color: #ffffff;

    text-decoration: none;

    border-radius: 7px;

    font-size: 14px;
    font-weight: 600;
    margin-bottom: 20px;
}

.add-btn:hover {
    background: #1d4ed8;
}


/* =========================================
   ALERT
========================================= */

.alert {
    padding: 13px 16px;

    margin-bottom: 20px;

    border-radius: 7px;

    font-size: 14px;
}

.success {
    background: #dcfce7;
    color: #166534;
}

.error {
    background: #fee2e2;
    color: #991b1b;
}


/* =========================================
   TABLE
========================================= */

.table-wrapper {
    background: #ffffff;

    border-radius: 10px;

    overflow-x: auto;

    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.06);
}

table {
    width: 100%;

    border-collapse: collapse;
}

th {
    padding: 15px;

    background: #f8fafc;

    color: #111827;

    font-size: 13px;

    font-weight: 700;

    text-align: left;

    border-bottom: 1px solid #e5e7eb;
}

td {
    padding: 15px;

    color: #374151;

    font-size: 14px;

    border-bottom: 1px solid #eeeeee;
}

tr:last-child td {
    border-bottom: none;
}

tr:hover td {
    background: #fafafa;
}


/* =========================================
   COURSE
========================================= */

.course-code {
    font-weight: 700;

    color: #111827;
}

.credit {
    color: #6b7280;
}


/* =========================================
   REMOVE BUTTON
========================================= */

.remove-btn {
    display: inline-block;

    padding: 7px 12px;

    background: #fee2e2;

    color: #dc2626;

    text-decoration: none;

    border-radius: 5px;

    font-size: 12px;

    font-weight: 600;
}

.remove-btn:hover {
    background: #fecaca;
}


/* =========================================
   EMPTY
========================================= */

.empty {
    padding: 40px !important;

    text-align: center;

    color: #6b7280;
}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 900px) {

    .sidebar {
        width: 220px;
    }

    .main-content {
        width: calc(100% - 220px);

        margin-left: 220px;

        padding: 25px;
    }

}


@media (max-width: 650px) {

    .sidebar {
        width: 70px;
    }

    .logo-text,
    .nav-label,
    .nav-section-title,
    .admin-info,
    .logout-link span:last-child {
        display: none;
    }

    .sidebar-logo {
        justify-content: center;

        padding: 15px 5px;
    }

    .nav-item {
        justify-content: center;

        padding: 12px 5px;
    }

    .sidebar-footer {
        padding: 10px 5px;
    }

    .admin-mini {
        justify-content: center;
    }

    .main-content {
        width: calc(100% - 70px);

        margin-left: 70px;

        padding: 20px;
    }

}
</style>
</head>
<body>

<div class="app-layout">

    <?php include "../includes/sidebar.php"; ?>

    <main class="main-content">

       <div class="container">


    <div class="header">

        <h2>
            Assigned Courses
        </h2>

        <a
            href="create.php"
            class="add-btn"
        >
            + Assign Course
        </a>

    </div>


    <!-- Success -->

    <?php if (isset($_SESSION['success'])): ?>

        <div class="alert success">

            <?= htmlspecialchars($_SESSION['success']); ?>

        </div>

        <?php unset($_SESSION['success']); ?>

    <?php endif; ?>


    <!-- Error -->

    <?php if (isset($_SESSION['error'])): ?>

        <div class="alert error">

            <?= htmlspecialchars($_SESSION['error']); ?>

        </div>

        <?php unset($_SESSION['error']); ?>

    <?php endif; ?>


    <div class="table-wrapper">

        <table>

            <thead>

                <tr>

                    <th>
                        Student
                    </th>

                    <th>
                        Student ID
                    </th>

                    <th>
                        Semester
                    </th>

                    <th>
                        Course
                    </th>

                    <th>
                        Credit
                    </th>

                    <th>
                        Assigned At
                    </th>

                    <th>
                        Action
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php if ($result && $result->num_rows > 0): ?>


                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>


                        <td>

                            <?= htmlspecialchars(
                                $row['student_name']
                            ); ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['student_code']
                            ); ?>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['semester']
                            ); ?>

                        </td>


                        <td>

                            <span class="course-code">

                                <?= htmlspecialchars(
                                    $row['course_code']
                                ); ?>

                            </span>

                            -

                            <?= htmlspecialchars(
                                $row['course_name']
                            ); ?>

                        </td>


                        <td>

                            <span class="credit">

                                <?= htmlspecialchars(
                                    $row['credit']
                                ); ?>

                            </span>

                        </td>


                        <td>

                            <?= htmlspecialchars(
                                $row['assigned_at']
                            ); ?>

                        </td>


                        <td>

                            <a
                                href="delete.php?id=<?= (int)$row['id']; ?>"
                                class="remove-btn"
                                onclick="
                                    return confirm(
                                        'Are you sure you want to remove this course assignment?'
                                    );
                                "
                            >
                                Remove
                            </a>

                        </td>


                    </tr>

                <?php endwhile; ?>


            <?php else: ?>

                <tr>

                    <td
                        colspan="7"
                        class="empty"
                    >
                        No courses assigned yet.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>


</div>



    </main>

</div>






</body>

</html>