<?php

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Get Student ID
|--------------------------------------------------------------------------
*/

$student_id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


$student = null;


/*
|--------------------------------------------------------------------------
| Get Student
|--------------------------------------------------------------------------
*/

if ($student_id > 0) {

    $stmt = $conn->prepare("
        SELECT
            students.*,
            departments.name AS department_name

        FROM students

        LEFT JOIN departments
            ON students.department_id = departments.id

        WHERE students.id = ?

        LIMIT 1
    ");

    $stmt->bind_param(
        "i",
        $student_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $student = $result->fetch_assoc();

    }

    $stmt->close();
}


/*
|--------------------------------------------------------------------------
| Page Data
|--------------------------------------------------------------------------
*/

$page_title = "Student Profile";

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
                Student Profile
            </h1>

            <p>
                View detailed information about the student.
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


        <?php if (!$student): ?>


            <!-- =================================================
                 Student Not Found
            ================================================== -->


            <div class="content-card">


                <div class="empty-state">


                    <div class="empty-icon">
                        🔍
                    </div>


                    <h3>
                        Student Not Found
                    </h3>


                    <p>
                        The requested student could not be found.
                    </p>


                    <a
                        href="index.php"
                        class="btn btn-primary"
                    >
                        ← Back to Students
                    </a>


                </div>


            </div>


        <?php else: ?>


            <?php

            /*
            |--------------------------------------------------------------------------
            | Student Photo
            |--------------------------------------------------------------------------
            */

            $has_photo =
                !empty($student['photo']);


            /*
            |--------------------------------------------------------------------------
            | Student Initial
            |--------------------------------------------------------------------------
            */

            $student_initial =
                strtoupper(
                    substr(
                        trim($student['name']),
                        0,
                        1
                    )
                );


            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $status =
                $student['status'] ?? 'Active';


            /*
            |--------------------------------------------------------------------------
            | Admission Date
            |--------------------------------------------------------------------------
            */

            $admission_date = '-';


            if (
                !empty(
                    $student['admission_date']
                )
            ) {

                $admission_date =
                    date(
                        'd M Y',
                        strtotime(
                            $student['admission_date']
                        )
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | Date of Birth
            |--------------------------------------------------------------------------
            */

            $dob = '-';


            if (
                !empty(
                    $student['dob']
                )
            ) {

                $dob =
                    date(
                        'd M Y',
                        strtotime(
                            $student['dob']
                        )
                    );

            }

            ?>


            <!-- =================================================
                 Profile Header
            ================================================== -->


            <div class="student-profile-card">


                <div class="student-profile-main">


                    <!-- Photo -->


                    <div class="student-profile-photo">


                        <?php if ($has_photo): ?>


                            <img
                                src="../uploads/students/<?php echo htmlspecialchars($student['photo']); ?>"
                                alt="<?php echo htmlspecialchars($student['name']); ?>"
                            >


                        <?php else: ?>


                            <span>
                                <?php echo htmlspecialchars(
                                    $student_initial
                                ); ?>
                            </span>


                        <?php endif; ?>


                    </div>


                    <!-- Basic Information -->


                    <div class="student-profile-info">


                        <h2>

                            <?php

                            echo htmlspecialchars(
                                $student['name']
                            );

                            ?>

                        </h2>


                        <p class="student-profile-id">

                            <?php

                            echo htmlspecialchars(
                                $student['student_id']
                                ?? 'N/A'
                            );

                            ?>

                        </p>


                        <p class="student-profile-department">

                            <?php

                            echo htmlspecialchars(
                                $student['department_name']
                                ?? 'Not Assigned'
                            );

                            ?>

                        </p>


                        <span
                            class="status-badge
                            <?php
                            echo strtolower(
                                $status
                            );
                            ?>"
                        >

                            <?php

                            echo htmlspecialchars(
                                $status
                            );

                            ?>

                        </span>


                    </div>


                </div>


                <!-- Actions -->


                <div class="student-profile-actions">


                    <a
                        href="edit.php?id=<?php echo $student['id']; ?>"
                        class="btn btn-primary"
                    >
                        Edit Student
                    </a>


                </div>


            </div>


            <!-- =================================================
                 Personal Information
            ================================================== -->


            <div class="content-card profile-section">


                <div class="card-header">


                    <div>

                        <h2>
                            Personal Information
                        </h2>

                        <p>
                            Student's personal contact information.
                        </p>

                    </div>


                </div>


                <div class="profile-info-grid">


                    <!-- Email -->


                    <div class="profile-info-item">


                        <span>
                            Email Address
                        </span>


                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $student['email']
                            );

                            ?>

                        </strong>


                    </div>


                    <!-- Phone -->


                    <div class="profile-info-item">


                        <span>
                            Phone Number
                        </span>


                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $student['phone']
                                ?: '-'
                            );

                            ?>

                        </strong>


                    </div>


                    <!-- Gender -->


                    <div class="profile-info-item">


                        <span>
                            Gender
                        </span>


                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $student['gender']
                                ?: '-'
                            );

                            ?>

                        </strong>


                    </div>


                    <!-- DOB -->


                    <div class="profile-info-item">


                        <span>
                            Date of Birth
                        </span>


                        <strong>

                            <?php

                            echo $dob;

                            ?>

                        </strong>


                    </div>


                </div>


            </div>


            <!-- =================================================
                 Academic Information
            ================================================== -->


            <div class="content-card profile-section">


                <div class="card-header">


                    <div>

                        <h2>
                            Academic Information
                        </h2>

                        <p>
                            Student's academic details.
                        </p>

                    </div>


                </div>


                <div class="profile-info-grid">


                    <!-- Student ID -->


                    <div class="profile-info-item">


                        <span>
                            Student ID
                        </span>


                        <strong class="profile-student-id">

                            <?php

                            echo htmlspecialchars(
                                $student['student_id']
                                ?? 'N/A'
                            );

                            ?>

                        </strong>


                    </div>


                    <!-- Department -->


                    <div class="profile-info-item">


                        <span>
                            Department
                        </span>


                        <strong>

                            <?php

                            echo htmlspecialchars(
                                $student['department_name']
                                ?? 'Not Assigned'
                            );

                            ?>

                        </strong>


                    </div>


                    <!-- Admission Date -->


                    <div class="profile-info-item">


                        <span>
                            Admission Date
                        </span>


                        <strong>

                            <?php

                            echo $admission_date;

                            ?>

                        </strong>


                    </div>


                    <!-- Status -->


                    <div class="profile-info-item">


                        <span>
                            Current Status
                        </span>


                        <strong>

                            <span
                                class="status-badge
                                <?php
                                echo strtolower(
                                    $status
                                );
                                ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $status
                                );

                                ?>

                            </span>

                        </strong>


                    </div>


                </div>


            </div>


            <!-- =================================================
                 Address
            ================================================== -->


            <div class="content-card profile-section">


                <div class="card-header">


                    <div>

                        <h2>
                            Address
                        </h2>

                        <p>
                            Student's current address.
                        </p>

                    </div>


                </div>


                <div class="profile-address">


                    <?php if (
                        !empty(
                            trim(
                                $student['address']
                                ?? ''
                            )
                        )
                    ): ?>


                        <?php

                        echo nl2br(
                            htmlspecialchars(
                                $student['address']
                            )
                        );

                        ?>


                    <?php else: ?>


                        <span>
                            No address information available.
                        </span>


                    <?php endif; ?>


                </div>


            </div>


            <!-- =================================================
                 Bottom Actions
            ================================================== -->


            <div class="profile-bottom-actions">


                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    ← Back to Students
                </a>


                <a
                    href="edit.php?id=<?php echo $student['id']; ?>"
                    class="btn btn-primary"
                >
                    Edit Student
                </a>


            </div>


        <?php endif; ?>


    </section>


</main>


<?php include "../includes/footer.php"; ?>