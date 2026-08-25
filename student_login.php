<?php
session_start();
require_once "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = trim($_POST["student_id"]);
    $password   = $_POST["password"];

    if (empty($student_id) || empty($password)) {
        $error = "Please enter Student ID and Password.";
    } else {

        $stmt = $conn->prepare(
            "SELECT id, student_id, name, password 
             FROM students 
             WHERE student_id = ? 
             LIMIT 1"
        );

        $stmt->bind_param("s", $student_id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $student = $result->fetch_assoc();

            if (
                !empty($student["password"]) &&
                password_verify($password, $student["password"])
            ) {

                $_SESSION["student_logged_in"] = true;
                $_SESSION["student_db_id"] = $student["id"];
                $_SESSION["student_id"] = $student["student_id"];
                $_SESSION["student_name"] = $student["name"];

                header("Location: student_dashboard.php");
                exit;

            } else {
                $error = "Invalid Student ID or Password.";
            }

        } else {
            $error = "Invalid Student ID or Password.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .login-box {
            width: 100%;
            max-width: 400px;
            background: #fff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        h2 {
            margin-top: 0;
            text-align: center;
        }

        .subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 7px;
            margin-bottom: 18px;
            font-size: 15px;
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

        .error {
            background: #fee2e2;
            color: #b91c1c;
            padding: 10px;
            border-radius: 7px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .forgot {
            display: block;
            text-align: center;
            margin-top: 18px;
            color: #2563eb;
            text-decoration: none;
            font-size: 14px;
        }
    </style>
</head>

<body>

<div class="login-box">

    <h2>Student Login</h2>

    <div class="subtitle">
        Login to your student account
    </div>

    <?php if (!empty($error)): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label for="student_id">Student ID</label>

        <input
            type="text"
            id="student_id"
            name="student_id"
            placeholder="Enter your Student ID"
            required
        >

        <label for="password">Password</label>

        <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter your password"
            required
        >

        <button type="submit">
            Login
        </button>

    </form>

    <a href="forgot_password.php" class="forgot">
        Forgot Password?
    </a>

</div>

</body>
</html>