<?php

session_start();
require_once "config.php";

$message = "";
$error = "";


/*
|--------------------------------------------------------------------------
| RESET PASSWORD
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["reset_request"])) {

    $request_id = intval($_POST["request_id"]);

    // Get request + student information
    $stmt = $conn->prepare("
        SELECT 
            r.id AS request_id,
            r.student_id,
            s.name
        FROM password_reset_requests r
        INNER JOIN students s
            ON r.student_id = s.student_id
        WHERE r.id = ?
        AND r.status = 'pending'
        LIMIT 1
    ");

    $stmt->bind_param("i", $request_id);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $request = $result->fetch_assoc();

        /*
        |--------------------------------------------------------------------------
        | Generate new password
        |--------------------------------------------------------------------------
        */

        $new_password = bin2hex(random_bytes(4));

        /*
        |--------------------------------------------------------------------------
        | Hash password
        |--------------------------------------------------------------------------
        */

        $hashed_password = password_hash(
            $new_password,
            PASSWORD_DEFAULT
        );


        /*
        |--------------------------------------------------------------------------
        | Update student password
        |--------------------------------------------------------------------------
        */

        $update = $conn->prepare("
            UPDATE students
            SET password = ?
            WHERE student_id = ?
        ");

        $update->bind_param(
            "ss",
            $hashed_password,
            $request["student_id"]
        );

        if ($update->execute()) {

            /*
            |--------------------------------------------------------------------------
            | Mark request completed
            |--------------------------------------------------------------------------
            */

            $complete = $conn->prepare("
                UPDATE password_reset_requests
                SET 
                    status = 'completed',
                    resolved_at = NOW()
                WHERE id = ?
            ");

            $complete->bind_param(
                "i",
                $request_id
            );

            $complete->execute();

            $complete->close();


            /*
            |--------------------------------------------------------------------------
            | Store generated password temporarily
            |--------------------------------------------------------------------------
            */

            $message = "Password reset successful.";

            $generated_password = $new_password;
            $generated_student = $request;

        } else {

            $error = "Failed to update student password.";
        }

        $update->close();

    } else {

        $error = "This reset request is no longer available.";
    }

    $stmt->close();
}


/*
|--------------------------------------------------------------------------
| GET PENDING REQUESTS
|--------------------------------------------------------------------------
*/

$requests = $conn->query("
    SELECT
        r.id,
        r.student_id,
        s.name,
        r.requested_at,
        r.status

    FROM password_reset_requests r

    INNER JOIN students s
        ON r.student_id = s.student_id

    WHERE r.status = 'pending'

    ORDER BY r.requested_at DESC
");

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Password Reset Requests</title>

<style>

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
    padding: 40px;
}

.container {
    max-width: 1000px;
    margin: auto;
}

h1 {
    margin-bottom: 25px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 10px;

    box-shadow:
        0 5px 20px rgba(0,0,0,0.07);

    margin-bottom: 20px;
}

.success {
    background: #dcfce7;
    color: #166534;

    padding: 15px;

    border-radius: 7px;

    margin-bottom: 20px;
}

.error {
    background: #fee2e2;
    color: #991b1b;

    padding: 15px;

    border-radius: 7px;

    margin-bottom: 20px;
}

.password-box {
    background: #f3f4f6;

    padding: 20px;

    border-radius: 8px;

    margin-top: 15px;
}

.password {
    font-size: 24px;
    font-weight: bold;

    letter-spacing: 2px;

    margin-top: 8px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    padding: 14px;

    text-align: left;

    border-bottom: 1px solid #eee;
}

th {
    background: #f8fafc;
}

button {
    border: none;

    background: #2563eb;

    color: white;

    padding: 9px 14px;

    border-radius: 6px;

    cursor: pointer;
}

button:hover {
    background: #1d4ed8;
}

.empty {
    text-align: center;

    color: #777;

    padding: 30px;
}

</style>

</head>

<body>

<div class="container">

    <h1>Password Reset Requests</h1>


    <?php if (!empty($message)): ?>

        <div class="success">

            <?= htmlspecialchars($message) ?>

        </div>


        <?php if (!empty($generated_password)): ?>

            <div class="card">

                <strong>
                    Password Reset Successful
                </strong>

                <div class="password-box">

                    Student:

                    <strong>
                        <?= htmlspecialchars(
                            $generated_student["name"]
                        ) ?>
                    </strong>

                    <br><br>

                    Student ID:

                    <strong>
                        <?= htmlspecialchars(
                            $generated_student["student_id"]
                        ) ?>
                    </strong>

                    <br><br>

                    New Password:

                    <div class="password">
                        <?= htmlspecialchars(
                            $generated_password
                        ) ?>
                    </div>

                </div>

            </div>

        <?php endif; ?>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div class="error">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <div class="card">

        <?php if ($requests && $requests->num_rows > 0): ?>

            <table>

                <thead>

                    <tr>

                        <th>
                            Student ID
                        </th>

                        <th>
                            Student Name
                        </th>

                        <th>
                            Requested
                        </th>

                        <th>
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody>

                <?php while ($row = $requests->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars(
                                $row["student_id"]
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row["name"]
                            ) ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $row["requested_at"]
                            ) ?>
                        </td>

                        <td>

                            <form
                                method="POST"
                                onsubmit="return confirm(
                                    'Are you sure you want to reset this password?'
                                );"
                            >

                                <input
                                    type="hidden"
                                    name="request_id"
                                    value="<?= $row["id"] ?>"
                                >

                                <button
                                    type="submit"
                                    name="reset_request"
                                >
                                    Reset Password
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endwhile; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty">

                No pending password reset requests.

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>