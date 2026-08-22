<?php
require_once "../config/auth.php";
require_once "../config/db.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


$stmt = $conn->prepare("
    SELECT
        students.*,
        departments.name AS department_name

    FROM students

    LEFT JOIN departments
        ON students.department_id = departments.id

    WHERE students.id = ?
");


$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    header("Location: index.php");

    exit;
}


$student = $result->fetch_assoc();

$stmt->close();

?>

<?php include "../includes/header.php"; ?>

<?php include "../includes/sidebar.php"; ?>


<main class="main-content">

    <header class="topbar">

        <div>

            <h1>Student Details</h1>

            <p>
                View complete student information.
            </p>

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

        <div class="profile-card">


            <div class="student-profile-header">

                <div class="large-avatar">

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


                <div>

                    <h2>
                        <?php
                        echo htmlspecialchars(
                            $student['name']
                        );
                        ?>
                    </h2>

                    <p>
                        <?php
                        echo htmlspecialchars(
                            $student['department_name']
                            ?? 'Department not assigned'
                        );
                        ?>
                    </p>

                </div>

            </div>


            <div class="details-grid">


                <div class="detail-item">

                    <span>Student ID</span>

                    <strong>
                        #<?php echo $student['id']; ?>
                    </strong>

                </div>


                <div class="detail-item">

                    <span>Email Address</span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $student['email']
                        );
                        ?>
                    </strong>

                </div>


                <div class="detail-item">

                    <span>Phone Number</span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $student['phone'] ?: '-'
                        );
                        ?>
                    </strong>

                </div>


                <div class="detail-item">

                    <span>Date of Birth</span>

                    <strong>
                        <?php
                        echo $student['dob']
                            ? date(
                                'd M Y',
                                strtotime($student['dob'])
                            )
                            : '-';
                        ?>
                    </strong>

                </div>


                <div class="detail-item">

                    <span>Gender</span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $student['gender'] ?: '-'
                        );
                        ?>
                    </strong>

                </div>


                <div class="detail-item">

                    <span>Department</span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $student['department_name']
                            ?? 'Not Assigned'
                        );
                        ?>
                    </strong>

                </div>


                <div class="detail-item full-width">

                    <span>Address</span>

                    <strong>
                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $student['address'] ?: '-'
                            )
                        );
                        ?>
                    </strong>

                </div>


            </div>


            <div class="profile-actions">

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    Back to Students
                </a>


                <a
                    href="edit.php?id=<?php echo $student['id']; ?>"
                    class="btn btn-primary"
                >
                    Edit Student
                </a>

            </div>


        </div>

    </section>

</main>


<?php include "../includes/footer.php"; ?>