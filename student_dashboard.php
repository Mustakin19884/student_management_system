<?php

session_start();
require_once "config.php";

/*
|--------------------------------------------------------------------------
| Student Authentication
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["student_logged_in"]) ||
    $_SESSION["student_logged_in"] !== true
) {
    header("Location: student_login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Logged-in Student
|--------------------------------------------------------------------------
*/

$student_id = $_SESSION["student_id"];

$stmt = $conn->prepare("
    SELECT
        s.id,
        s.student_id,
        s.name,
        s.email,
        s.phone,
        s.dob,
        s.gender,
        s.department_id,
        s.admission_date,
        s.STATUS,
        s.address,
        s.photo,
        d.name AS department_name

    FROM students s

    LEFT JOIN departments d
        ON s.department_id = d.id

    WHERE s.student_id = ?

    LIMIT 1
");


if (!$stmt) {
    die("Database query failed: " . $conn->error);
}


$stmt->bind_param("s", $student_id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    session_unset();
    session_destroy();

    header("Location: student_login.php");
    exit;
}


$student = $result->fetch_assoc();

$stmt->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Student Dashboard</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    font-family: Arial, sans-serif;

    background: #f4f6f9;
}


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

.header {

    background: #2563eb;

    color: white;

    padding: 18px 30px;

    display: flex;

    justify-content: space-between;

    align-items: center;
}


.header h2 {

    margin: 0;
}


.logout {

    color: white;

    text-decoration: none;

    background: rgba(255,255,255,0.15);

    padding: 9px 15px;

    border-radius: 6px;
}


/*
|--------------------------------------------------------------------------
| Container
|--------------------------------------------------------------------------
*/

.container {

    max-width: 1100px;

    margin: 40px auto;

    padding: 0 20px;
}


/*
|--------------------------------------------------------------------------
| Welcome
|--------------------------------------------------------------------------
*/

.welcome {

    background: white;

    padding: 25px;

    border-radius: 12px;

    margin-bottom: 25px;

    box-shadow:
        0 5px 20px rgba(0,0,0,0.05);
}


.welcome h1 {

    margin-top: 0;
}


.welcome p {

    color: #666;
}



/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

.stats-grid {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 20px;

    margin-bottom: 25px;
}


.stat-card {

    background: white;

    padding: 22px;

    border-radius: 12px;

    box-shadow:
        0 5px 20px rgba(0,0,0,0.05);

    border: 1px solid #eee;
}


.stat-label {

    font-size: 13px;

    color: #777;

    margin-bottom: 8px;
}


.stat-value {

    font-size: 20px;

    font-weight: 700;

    color: #2563eb;

    word-break: break-word;
}


@media (max-width: 900px) {

    .stats-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media (max-width: 500px) {

    .stats-grid {

        grid-template-columns: 1fr;

    }

}
/*
|--------------------------------------------------------------------------
| Profile Card
|--------------------------------------------------------------------------
*/

.profile-card {

    background: white;

    border-radius: 12px;

    padding: 30px;

    box-shadow:
        0 5px 20px rgba(0,0,0,0.05);
}


.profile-card h2 {

    margin-top: 0;

    margin-bottom: 25px;
}


/*
|--------------------------------------------------------------------------
| Profile Grid
|--------------------------------------------------------------------------
*/

.profile-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 20px;
}


.info-box {

    background: #f8fafc;

    padding: 18px;

    border-radius: 8px;

    border: 1px solid #eee;
}


.info-label {

    font-size: 13px;

    color: #777;

    margin-bottom: 7px;
}


.info-value {

    font-size: 16px;

    font-weight: 600;

    color: #222;

    word-break: break-word;
}


.profile-header {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
}

.profile-photo {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.12);
    background: #f1f5f9;
}

.profile-photo-placeholder {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    background: #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    font-weight: bold;
    color: #64748b;
}

.profile-header-info h2 {
    margin: 0 0 6px;
}

.profile-header-info p {
    margin: 0;
    color: #777;
}
/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (max-width: 700px) {

    .header {

        padding: 15px 18px;
    }


    .container {

        margin-top: 25px;
    }


    .profile-grid {

        grid-template-columns: 1fr;
    }

}

</style>

</head>


<body>


<!-- Header -->

<header class="header">

    <h2>
        Student Portal
    </h2>


    <a
        href="student_logout.php"
        class="logout"
    >
        Logout
    </a>

</header>


<!-- Main -->

<main class="container">


    <!-- Welcome -->

    <section class="welcome">

        <h1>

            Welcome,
            <?= htmlspecialchars(
                $student["name"]
            ) ?>

        </h1>


        <p>

            Welcome to your student portal.

        </p>

    </section>


    
    <!-- Student Information -->

    <section class="profile-card">

    <div class="profile-header">

        <?php if (!empty($student["photo"])): ?>

    <img
        src="uploads/students/<?= htmlspecialchars($student["photo"]) ?>"
        alt="Student Photo"
        class="profile-photo"
    >

<?php else: ?>

    <div class="profile-photo-placeholder">
        <?= strtoupper(
            substr($student["name"], 0, 1)
        ) ?>
    </div>

<?php endif; ?>


        <div class="profile-header-info">

            <h2>
                <?= htmlspecialchars($student["name"]) ?>
            </h2>

            <p>
                Student ID:
                <?= htmlspecialchars($student["student_id"]) ?>
            </p>

        </div>

    </div>

   


        <div class="profile-grid">


            <!-- Student ID -->

            <div class="info-box">

                <div class="info-label">
                    Student ID
                </div>


                <div class="info-value">

                    <?= htmlspecialchars(
                        $student["student_id"]
                    ) ?>

                </div>

            </div>


            <!-- Name -->

            <div class="info-box">

                <div class="info-label">
                    Name
                </div>


                <div class="info-value">

                    <?= htmlspecialchars(
                        $student["name"]
                    ) ?>

                </div>

            </div>


            <!-- Department -->

            <div class="info-box">

                <div class="info-label">
                    Department
                </div>


                <div class="info-value">

                    <?= htmlspecialchars(
                        $student["department_name"]
                        ?? "Not Assigned"
                    ) ?>

                </div>

            </div>


            <!-- Email -->

            <div class="info-box">

                <div class="info-label">
                    Email
                </div>


                <div class="info-value">

                    <?= htmlspecialchars(
                        $student["email"]
                    ) ?>

                </div>

            </div>


            <!-- Phone -->

            <div class="info-box">

                <div class="info-label">
                    Phone
                </div>


                <div class="info-value">

                    <?= htmlspecialchars(
                        $student["phone"]
                        ?? "Not Provided"
                    ) ?>

                </div>

            </div>


            <!-- Date of Birth -->

            <div class="info-box">

                <div class="info-label">
                    Date of Birth
                </div>


                <div class="info-value">

                    <?= htmlspecialchars(
                        $student["dob"]
                        ?? "Not Provided"
                    ) ?>

                </div>

            </div>


            <!-- Gender -->

            <div class="info-box">

                <div class="info-label">
                    Gender
                </div>


                <div class="info-value">

                    <?= htmlspecialchars(
                        $student["gender"]
                        ?? "Not Provided"
                    ) ?>

                </div>

            </div>


            <!-- Admission Date -->

            <div class="info-box">

                <div class="info-label">
                    Admission Date
                </div>


                <div class="info-value">

                    <?= htmlspecialchars(
                        $student["admission_date"]
                        ?? "Not Provided"
                    ) ?>

                </div>

            </div>


            <!-- Account Status -->

            <div class="info-box">

                <div class="info-label">
                    Account Status
                </div>


                <div class="info-value">

                    <?= htmlspecialchars(
                        $student["STATUS"]
                    ) ?>

                </div>

            </div>


            <!-- Address -->

            <div class="info-box">

                <div class="info-label">
                    Address
                </div>


                <div class="info-value">

                    <?= htmlspecialchars(
                        $student["address"]
                        ?? "Not Provided"
                    ) ?>

                </div>

            </div>


        </div>

    </section>


</main>


</body>

</html>