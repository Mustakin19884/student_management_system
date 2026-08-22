<?php


require_once "../config/auth.php";

require_once "../config/db.php";

$departments = $conn->query(
    "SELECT * FROM departments ORDER BY name ASC"
);

$errors = [];

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
            INSERT INTO students
            (
                name,
                email,
                phone,
                dob,
                gender,
                department_id,
                address
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sssssis",
            $name,
            $email,
            $phone,
            $dob,
            $gender,
            $department_id,
            $address
        );


        if ($stmt->execute()) {

            header("Location: index.php?success=student_added");

            exit;

        } else {

            if ($conn->errno == 1062) {
                $errors[] = "This email address is already registered.";
            } else {
                $errors[] = "Something went wrong. Please try again.";
            }

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
            <h1>Add Student</h1>

            <p>
                Add a new student to the system.
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
                        Enter the student's details below.
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
                            placeholder="Enter student's full name"
                            value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
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
                            placeholder="student@example.com"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
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
                            placeholder="01XXXXXXXXX"
                            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Date of Birth
                        </label>

                        <input
                            type="date"
                            name="dob"
                            value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>"
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

                            <option value="Male">
                                Male
                            </option>

                            <option value="Female">
                                Female
                            </option>

                            <option value="Other">
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
                        placeholder="Enter student's address"
                    ><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>

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
                        Add Student
                    </button>

                </div>

            </form>

        </div>

    </section>

</main>


<?php include "../includes/footer.php"; ?>