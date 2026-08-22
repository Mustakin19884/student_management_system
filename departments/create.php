<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/db.php";

$errors = [];

$name = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");


    if ($name === "") {

        $errors[] = "Department name is required.";

    }


    if (empty($errors)) {

        // Check duplicate department

        $check = $conn->prepare("
            SELECT id
            FROM departments
            WHERE name = ?
        ");

        $check->bind_param("s", $name);

        $check->execute();

        $check_result = $check->get_result();


        if ($check_result->num_rows > 0) {

            $errors[] = "This department already exists.";

        }


        $check->close();
    }


    if (empty($errors)) {

        $stmt = $conn->prepare("
            INSERT INTO departments (name)
            VALUES (?)
        ");

        $stmt->bind_param("s", $name);


        if ($stmt->execute()) {

            header(
                "Location: index.php?success=department_added"
            );

            exit;

        } else {

            $errors[] =
                "Something went wrong. Please try again.";

        }

        $stmt->close();
    }

}

?>


<?php include "../includes/header.php"; ?>

<?php include "../includes/sidebar.php"; ?>


<main class="main-content">


    <header class="topbar">

        <div>

            <h1>Add Department</h1>

            <p>
                Create a new academic department.
            </p>

        </div>


        <div class="profile">

            <div class="avatar">
                A
            </div>

            <div>

                <strong>Admin</strong>

                <span>
                    Administrator
                </span>

            </div>

        </div>

    </header>


    <section class="dashboard-content">


        <div class="form-card">


            <div class="form-header">

                <div>

                    <h2>
                        Department Information
                    </h2>

                    <p>
                        Enter the department name below.
                    </p>

                </div>

            </div>


            <?php if (!empty($errors)): ?>

                <div class="alert alert-error">

                    <strong>
                        Please fix the following:
                    </strong>

                    <ul>

                        <?php foreach ($errors as $error): ?>

                            <li>
                                <?php echo htmlspecialchars($error); ?>
                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php endif; ?>


            <form
                method="POST"
                class="student-form"
            >


                <div class="form-group">

                    <label>
                        Department Name
                        <span>*</span>
                    </label>


                    <input
                        type="text"
                        name="name"
                        placeholder="e.g. Computer Science & Engineering"
                        value="<?php echo htmlspecialchars($name); ?>"
                        required
                    >


                    <small>
                        Enter the full official department name.
                    </small>

                </div>


                <div class="form-actions">

                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Add Department
                    </button>

                </div>


            </form>


        </div>

    </section>

</main>


<?php include "../includes/footer.php"; ?>