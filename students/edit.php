<?php
require_once "../config/auth.php";
require_once "../config/db.php";

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


/* Get student */

$stmt = $conn->prepare("
    SELECT *
    FROM students
    WHERE id = ?
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


/* Get departments */

$departments = $conn->query("
    SELECT *
    FROM departments
    ORDER BY name ASC
");


$errors = [];


/* Update student */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $dob = $_POST["dob"] ?? "";
    $gender = $_POST["gender"] ?? "";
    $department_id = $_POST["department_id"] ?? "";
    $address = trim($_POST["address"] ?? "");


    if ($name === "") {
        $errors[] = "Student name is required.";
    }


    if ($email === "") {

        $errors[] = "Email is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "Please enter a valid email address.";

    }


    if (empty($errors)) {

        $stmt = $conn->prepare("
            UPDATE students

            SET
                name = ?,
                email = ?,
                phone = ?,
                dob = ?,
                gender = ?,
                department_id = ?,
                address = ?

            WHERE id = ?
        ");


        $stmt->bind_param(
            "sssssisi",
            $name,
            $email,
            $phone,
            $dob,
            $gender,
            $department_id,
            $address,
            $id
        );


        if ($stmt->execute()) {

            header("Location: index.php?success=student_updated");

            exit;

        } else {

            if ($conn->errno == 1062) {

                $errors[] =
                    "This email address is already registered.";

            } else {

                $errors[] =
                    "Something went wrong. Please try again.";

            }

        }

        $stmt->close();
    }


    /* Keep submitted values */

    $student['name'] = $name;
    $student['email'] = $email;
    $student['phone'] = $phone;
    $student['dob'] = $dob;
    $student['gender'] = $gender;
    $student['department_id'] = $department_id;
    $student['address'] = $address;
}

?>

<?php include "../includes/header.php"; ?>

<?php include "../includes/sidebar.php"; ?>


<main class="main-content">

    <header class="topbar">

        <div>

            <h1>Edit Student</h1>

            <p>
                Update student information.
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

        <div class="form-card">

            <div class="form-header">

                <div>

                    <h2>Student Information</h2>

                    <p>
                        Update the student's details below.
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

                <div class="form-grid">


                    <div class="form-group">

                        <label>
                            Full Name
                            <span>*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="<?php echo htmlspecialchars($student['name']); ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Email Address
                            <span>*</span>
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="<?php echo htmlspecialchars($student['email']); ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Phone Number
                        </label>

                        <input
                            type="text"
                            name="phone"
                            value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Date of Birth
                        </label>

                        <input
                            type="date"
                            name="dob"
                            value="<?php echo htmlspecialchars($student['dob'] ?? ''); ?>"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Gender
                        </label>

                        <select name="gender">

                            <option value="">
                                Select Gender
                            </option>

                            <option
                                value="Male"
                                <?php echo ($student['gender'] === 'Male') ? 'selected' : ''; ?>
                            >
                                Male
                            </option>

                            <option
                                value="Female"
                                <?php echo ($student['gender'] === 'Female') ? 'selected' : ''; ?>
                            >
                                Female
                            </option>

                            <option
                                value="Other"
                                <?php echo ($student['gender'] === 'Other') ? 'selected' : ''; ?>
                            >
                                Other
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <label>
                            Department
                        </label>

                        <select name="department_id">

                            <option value="">
                                Select Department
                            </option>


                            <?php while ($department = $departments->fetch_assoc()): ?>

                                <option
                                    value="<?php echo $department['id']; ?>"
                                    <?php
                                    echo (
                                        $student['department_id']
                                        == $department['id']
                                    )
                                    ? 'selected'
                                    : '';
                                    ?>
                                >
                                    <?php
                                    echo htmlspecialchars(
                                        $department['name']
                                    );
                                    ?>
                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                </div>


                <div class="form-group">

                    <label>
                        Address
                    </label>

                    <textarea
                        name="address"
                        rows="4"
                    ><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>

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