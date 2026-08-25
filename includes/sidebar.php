<?php

/*
|--------------------------------------------------------------------------
| Start Session
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| Current Page Detection
|--------------------------------------------------------------------------
*/

$current_page = basename($_SERVER['PHP_SELF']);

$current_folder = basename(
    dirname($_SERVER['PHP_SELF'])
);


/*
|--------------------------------------------------------------------------
| Admin Information
|--------------------------------------------------------------------------
*/

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

$admin_email = $_SESSION['admin_email'] ?? '';


/*
|--------------------------------------------------------------------------
| Admin Initial
|--------------------------------------------------------------------------
*/

$admin_initial = strtoupper(
    substr($admin_name, 0, 1)
);

?>

<aside class="sidebar">


    <!-- =========================================
         LOGO
    ========================================== -->

    <div class="sidebar-logo">

        <div class="logo-icon">
            S
        </div>

        <div class="logo-text">

            <h2>
                StudentHub
            </h2>

            <span>
                Management System
            </span>

        </div>

    </div>


    <!-- =========================================
         NAVIGATION
    ========================================== -->

    <nav class="sidebar-nav">


        <div class="nav-section-title">
            MAIN MENU
        </div>


        <!-- Dashboard -->

        <a
            href="/student_management/dashboard.php"
            class="nav-item
            <?php

            if (
                $current_page === 'dashboard.php'
                &&
                $current_folder === 'student_management'
            ) {

                echo 'active';

            }

            ?>"
        >

            <span class="nav-icon">
                ▦
            </span>

            <span class="nav-label">
                Dashboard
            </span>

        </a>


        <!-- Students -->

        <a
            href="/student_management/students/index.php"
            class="nav-item
            <?php

            if (
                $current_folder === 'students'
            ) {

                echo 'active';

            }

            ?>"
        >

            <span class="nav-icon">
                👨‍🎓
            </span>

            <span class="nav-label">
                Students
            </span>

        </a>


        <!-- Departments -->

        <a
            href="/student_management/departments/index.php"
            class="nav-item
            <?php

            if (
                $current_folder === 'departments'
            ) {

                echo 'active';

            }

            ?>"
        >

            <span class="nav-icon">
                🏫
            </span>

            <span class="nav-label">
                Departments
            </span>

        </a>

<!-- Courses -->

<a
    href="/student_management/courses/index.php"
    class="nav-item
    <?php

    if (
        $current_folder === 'courses'
    ) {

        echo 'active';

    }

    ?>"
>

    <span class="nav-icon">
        📚
    </span>

    <span class="nav-label">
        Courses
    </span>

</a>
    </nav>


    <!-- =========================================
         SIDEBAR FOOTER
    ========================================== -->

    <div class="sidebar-footer">


        <!-- Admin Profile -->

        <div class="admin-mini">


            <div class="avatar">

                <?php

                echo htmlspecialchars(
                    $admin_initial
                );

                ?>

            </div>


            <div class="admin-info">

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $admin_name
                    );

                    ?>

                </strong>


                <span>
                    Administrator
                </span>

            </div>


        </div>


        <!-- Logout -->

        <a
            href="/student_management/logout.php"
            class="logout-link"
            onclick="
                return confirm(
                    'Are you sure you want to logout?'
                );
            "
        >

            <span class="logout-icon">
                ↪
            </span>

            <span>
                Logout
            </span>

        </a>


    </div>


</aside>