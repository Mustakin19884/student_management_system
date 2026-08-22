<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/db.php";


$id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($id <= 0) {

    header("Location: index.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| Get Department
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT *
    FROM departments
    WHERE id = ?
");

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    header("Location: index.php");

    exit;
}


$department = $result->fetch_assoc();

$stmt->close();


$name = $department['name'];

$errors = [];


/*
|--------------------------------------------------------------------------
| Update Department
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? "");


    if ($name === "") {

        $errors[] =
            "Department name is required.";

    }


    /*
    | Check duplicate
    */

    if (empty($errors)) {

        $check = $conn->prepare("
            SELECT id
            FROM departments
            WHERE name = ?
            AND id != ?
        ");

        $check->bind_param(
            "si",
            $name,
            $id
        );

        $check->execute();

        $check_result = $check->get_result();


        if ($check_result->num_rows > 0) {

            $errors[] =
                "This department already exists.";

        }

        $check->close();
    }


    /*
    | Update
    */

    if (empty($errors)) {

        $stmt = $conn->prepare("
            UPDATE departments
            SET name = ?
            WHERE id = ?
        ");

        $stmt->bind_param(
            "si",
            $name,
            $id
        );


        if ($stmt->execute()) {

            header(
                "Location: index.php?success=department_updated"
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

            <h1>Edit Department</h1>

            <p>
                Update department information.
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
                        Update the department name.
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
                        value="<?php echo htmlspecialchars($name); ?>"
                        required
                    >

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
                        Save Changes
                    </button>

                </div>


            </form>


        </div>

    </section>

</main>


<?php include "../includes/footer.php"; ?>