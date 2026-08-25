<?php

require_once "config.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = trim($_POST["student_id"]);

    if (empty($student_id)) {

        $error = "Please enter your Student ID.";

    } else {

        // Check if student exists
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

            // Check existing pending request
            $check = $conn->prepare(
                "SELECT id
                 FROM password_reset_requests
                 WHERE student_id = ?
                 AND status = 'pending'
                 LIMIT 1"
            );

            $check->bind_param("s", $student_id);
            $check->execute();

            $existing = $check->get_result();

            if ($existing->num_rows > 0) {

                $error = "You already have a pending password reset request.";

            } else {

                // Create reset request
                $insert = $conn->prepare(
                    "INSERT INTO password_reset_requests (student_id)
                     VALUES (?)"
                );

                $insert->bind_param("s", $student_id);

                if ($insert->execute()) {

                    $message = "Your password reset request has been sent to the administrator.";

                } else {

                    $error = "Unable to submit request. Please try again.";
                }

                $insert->close();
            }

            $check->close();

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

<title>Forgot Password</title>

<style>

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    min-height: 100vh;

    display: flex;
    align-items: center;
    justify-content: center;
}

.container {
    width: 100%;
    max-width: 400px;
    background: white;
    padding: 30px;
    border-radius: 12px;

    box-shadow:
        0 8px 30px rgba(0,0,0,0.08);
}

h2 {
    text-align: center;
    margin-top: 0;
}

.description {
    text-align: center;
    color: #777;
    margin-bottom: 25px;
    line-height: 1.5;
}

label {
    display: block;
    font-weight: 600;
    margin-bottom: 7px;
}

input {
    width: 100%;
    padding: 12px;

    border: 1px solid #ddd;
    border-radius: 7px;

    margin-bottom: 18px;

    font-size: 15px;
    box-sizing: border-box;
}

button {
    width: 100%;
    padding: 12px;

    border: none;
    border-radius: 7px;

    background: #2563eb;
    color: white;

    font-size: 16px;
    cursor: pointer;
}

button:hover {
    background: #1d4ed8;
}

.success {
    background: #dcfce7;
    color: #166534;

    padding: 12px;
    border-radius: 7px;

    margin-bottom: 18px;
}

.error {
    background: #fee2e2;
    color: #991b1b;

    padding: 12px;
    border-radius: 7px;

    margin-bottom: 18px;
}

.back-login {
    display: block;
    text-align: center;

    margin-top: 18px;

    color: #2563eb;
    text-decoration: none;
}

</style>

</head>

<body>

<div class="container">

    <h2>Forgot Password?</h2>

    <div class="description">
        Enter your Student ID to request a password reset from the administrator.
    </div>

    <?php if (!empty($message)): ?>

        <div class="success">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <label for="student_id">
            Student ID
        </label>

        <input
            type="text"
            id="student_id"
            name="student_id"
            placeholder="Enter your Student ID"
            required
        >

        <button type="submit">
            Request Password Reset
        </button>

    </form>


    <a
        href="student_login.php"
        class="back-login"
    >
        Back to Login
    </a>

</div>

</body>

</html>