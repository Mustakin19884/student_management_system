<?php

session_start();
require_once "config.php";

/*
|--------------------------------------------------------------------------
| Admin Authentication
|--------------------------------------------------------------------------
| তোমার existing admin login/session থাকলে এখানে সেটার condition বসাবে।
| আপাতত testing-এর জন্য নিচের অংশটি comment রাখা হলো।
*/

// if (!isset($_SESSION["admin_logged_in"])) {
//     header("Location: admin_login.php");
//     exit;
// }


$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = trim($_POST["student_id"]);

    if (empty($student_id)) {

        $error = "Please enter Student ID.";

    } else {

        // Check student
        $stmt = $conn->prepare(
            "SELECT id, student_id, name
             FROM students
             WHERE student_id = ?
             LIMIT 1"
        );






        $stmt->bind_param("s", $student_id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $student = $result->fetch_assoc();

            /*
             * Generate random password
             */
            $new_password = bin2hex(random_bytes(4));

            /*
             * Hash password
             */
            $hashed_password = password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );

            /*
             * Update database
             */
            $update = $conn->prepare(
                "UPDATE students
                 SET password = ?
                 WHERE student_id = ?"
            );

            $update->bind_param(
                "ss",
                $hashed_password,
                $student_id
            );

            if ($update->execute()) {

                $message = "Password generated successfully.";
$generated_password = $new_password;
$generated_student = $student;

            } else {

                $error = "Failed to update password.";
            }

            $update->close();

        } else {

            $error = "Student ID not found.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Reset Student Password</title>

<style>

body {
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    padding: 40px;
}

.container {
    max-width: 500px;
    margin: auto;
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.08);
}

h2 {
    margin-top: 0;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
}

input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    margin-bottom: 15px;
}

button {
    width: 100%;
    padding: 12px;
    border: none;
    background: #2563eb;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    font-size: 15px;
}

button:hover {
    background: #1d4ed8;
}

.success {
    background: #dcfce7;
    color: #166534;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
}

.error {
    background: #fee2e2;
    color: #991b1b;
    padding: 12px;
    border-radius: 6px;
    margin-bottom: 15px;
}

.password-box {
    margin-top: 20px;
    padding: 20px;
    background: #f3f4f6;
    border-radius: 8px;
}

.password {
    font-size: 22px;
    font-weight: bold;
    letter-spacing: 2px;
}

</style>

</head>

<body>

<div class="container">

    <h2>Reset Student Password</h2>

   <?php if (!empty($generated_password)): ?>

    <div class="password-box">

        <strong>
            Student:
        </strong>

        <?= htmlspecialchars($generated_student["name"]) ?>

        <br><br>

        <strong>
            Student ID:
        </strong>

        <?= htmlspecialchars($generated_student["student_id"]) ?>

        <br><br>

        <strong>
            New Password:
        </strong>

        <div class="password">
            <?= htmlspecialchars($generated_password) ?>
        </div>

    </div>

<?php endif; ?>


    <?php if (!empty($error)): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <label>Student ID</label>

        <input
            type="text"
            name="student_id"
            placeholder="Enter Student ID"
            required
        >

        <button type="submit">
            Generate New Password
        </button>

    </form>

</div>

</body>

</html>