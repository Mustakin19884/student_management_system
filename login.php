<?php

session_start();

require_once "config/db.php";


/*
|--------------------------------------------------------------------------
| Already Logged In
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['admin_id'])) {

    header(
        "Location: dashboard.php"
    );

    exit;
}


$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim(
        $_POST['email'] ?? ''
    );

    $password = $_POST['password'] ?? '';


    if ($email === '' || $password === '') {

        $error =
            "Email and password are required.";

    } else {


        $stmt = $conn->prepare("
            SELECT
                id,
                name,
                email,
                password

            FROM admins

            WHERE email = ?

            LIMIT 1
        ");


        $stmt->bind_param(
            "s",
            $email
        );


        $stmt->execute();


        $result = $stmt->get_result();


        if ($result->num_rows === 1) {

            $admin = $result->fetch_assoc();


            if (
                password_verify(
                    $password,
                    $admin['password']
                )
            ) {


                /*
                | Prevent session fixation
                */

                session_regenerate_id(true);


                $_SESSION['admin_id'] =
                    $admin['id'];

                $_SESSION['admin_name'] =
                    $admin['name'];

                $_SESSION['admin_email'] =
                    $admin['email'];


                header(
                    "Location: dashboard.php"
                );

                exit;


            } else {

                $error =
                    "Invalid email or password.";

            }


        } else {

            $error =
                "Invalid email or password.";

        }


        $stmt->close();

    }

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Admin Login - StudentHub
    </title>


    <link
        rel="stylesheet"
        href="/student_management/assets/css/style.css"
    >

</head>


<body class="login-page">


<div class="login-container">


    <div class="login-card">


        <!-- Logo -->

        <div class="login-logo">

            <div class="login-logo-icon">
                S
            </div>

            <h1>
                StudentHub
            </h1>

            <p>
                Student Management System
            </p>

        </div>


        <!-- Heading -->

        <div class="login-heading">

            <h2>
                Welcome back
            </h2>

            <p>
                Sign in to access the admin panel.
            </p>

        </div>


        <!-- Error -->

        <?php if ($error): ?>

            <div class="login-error">

                <span>!</span>

                <p>
                    <?php
                    echo htmlspecialchars($error);
                    ?>
                </p>

            </div>

        <?php endif; ?>


        <!-- Form -->

        <form
            method="POST"
            class="login-form"
        >


            <div class="form-group">

                <label>
                    Email Address
                </label>

                <input
                    type="email"
                    name="email"
                    placeholder="admin@studenthub.com"
                    value="<?php
                    echo htmlspecialchars(
                        $_POST['email'] ?? ''
                    );
                    ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>


            <button
                type="submit"
                class="login-button"
            >
                Sign In
            </button>


        </form>


        <div class="login-footer">

            <span>
                © <?php echo date('Y'); ?>
                StudentHub
            </span>

        </div>


    </div>

</div>


</body>

</html>