<?php

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Get Departments
|--------------------------------------------------------------------------
*/

$departments = $conn->query("
    SELECT id, name
    FROM departments
    ORDER BY name ASC
");


$errors = [];


/*
|--------------------------------------------------------------------------
| Generate Student ID
|--------------------------------------------------------------------------
*/

function generateStudentId($conn)
{
    $year = date("Y");

    $result = $conn->query("
        SELECT id
        FROM students
        ORDER BY id DESC
        LIMIT 1
    ");

    $last_id = 0;

    if ($result && $result->num_rows > 0) {

        $row = $result->fetch_assoc();

        $last_id = (int) $row['id'];

    }

    $next_id = $last_id + 1;

    return "STU-" . $year . "-" . str_pad(
        $next_id,
        4,
        "0",
        STR_PAD_LEFT
    );
}


/*
|--------------------------------------------------------------------------
| Form Submit
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /*
    |--------------------------------------------------------------------------
    | Get Form Data
    |--------------------------------------------------------------------------
    */

    $name = trim($_POST["name"] ?? "");

    $email = trim($_POST["email"] ?? "");

    $phone = trim($_POST["phone"] ?? "");

    $dob = $_POST["dob"] ?? "";

    $gender = $_POST["gender"] ?? "";

    $department_id = !empty($_POST["department_id"])
        ? (int) $_POST["department_id"]
        : null;

    $admission_date = $_POST["admission_date"] ?? "";

    $status = $_POST["status"] ?? "Active";

    $address = trim($_POST["address"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($name === "") {

        $errors[] =
            "Student name is required.";

    }


    if ($email === "") {

        $errors[] =
            "Email is required.";

    } elseif (!filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )) {

        $errors[] =
            "Please enter a valid email address.";

    }


    /*
    |--------------------------------------------------------------------------
    | Check Email
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $check_stmt = $conn->prepare("
            SELECT id
            FROM students
            WHERE email = ?
            LIMIT 1
        ");

        $check_stmt->bind_param(
            "s",
            $email
        );

        $check_stmt->execute();

        $check_result =
            $check_stmt->get_result();


        if ($check_result->num_rows > 0) {

            $errors[] =
                "This email address is already registered.";

        }


        $check_stmt->close();

    }


    /*
    |--------------------------------------------------------------------------
    | Photo Upload
    |--------------------------------------------------------------------------
    */

    $photo_name = null;


    if (
        isset($_FILES["photo"])
        &&
        $_FILES["photo"]["error"]
        !== UPLOAD_ERR_NO_FILE
    ) {


        if (
            $_FILES["photo"]["error"]
            !== UPLOAD_ERR_OK
        ) {

            $errors[] =
                "There was an error uploading the photo.";

        } else {


            $allowed_types = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];


            $file_type =
                mime_content_type(
                    $_FILES["photo"]["tmp_name"]
                );


            $file_size =
                $_FILES["photo"]["size"];


            if (!in_array(
                $file_type,
                $allowed_types,
                true
            )) {

                $errors[] =
                    "Only JPG, PNG and WEBP images are allowed.";

            }


            if ($file_size > 2 * 1024 * 1024) {

                $errors[] =
                    "Photo size must be less than 2MB.";

            }


            if (empty($errors)) {


                $upload_dir =
                    "../uploads/students/";


                if (!is_dir($upload_dir)) {

                    mkdir(
                        $upload_dir,
                        0755,
                        true
                    );

                }


                $extension =
                    strtolower(
                        pathinfo(
                            $_FILES["photo"]["name"],
                            PATHINFO_EXTENSION
                        )
                    );


                $photo_name =
                    uniqid(
                        "student_",
                        true
                    )
                    . "."
                    . $extension;


                $upload_path =
                    $upload_dir
                    . $photo_name;


                if (!move_uploaded_file(
                    $_FILES["photo"]["tmp_name"],
                    $upload_path
                )) {

                    $errors[] =
                        "Failed to upload student photo.";

                    $photo_name = null;

                }

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Insert Student
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {


        $student_id =
            generateStudentId($conn);


        $stmt = $conn->prepare("
            INSERT INTO students
            (
                student_id,
                name,
                email,
                phone,
                dob,
                gender,
                department_id,
                admission_date,
                status,
                address,
                photo
            )

            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
            )
        ");


        $stmt->bind_param(
            "ssssssissss",
            $student_id,
            $name,
            $email,
            $phone,
            $dob,
            $gender,
            $department_id,
            $admission_date,
            $status,
            $address,
            $photo_name
        );


        if ($stmt->execute()) {


            $stmt->close();


            header(
                "Location: index.php?success=student_added"
            );

            exit;


        } else {


            if (
                $conn->errno == 1062
            ) {

                $errors[] =
                    "Student ID or email already exists.";

            } else {

                $errors[] =
                    "Something went wrong. Please try again.";

            }


            $stmt->close();

        }

    }

}

?>


<?php include "../includes/header.php"; ?>

<?php include "../includes/sidebar.php"; ?>


<main class="main-content">


    <!-- =====================================================
         Topbar
    ====================================================== -->

    <header class="topbar">


        <div>

            <h1>
                Add Student
            </h1>

            <p>
                Add a new student to the system.
            </p>

        </div>


        <div class="profile">


            <div class="avatar">
                A
            </div>


            <div>

                <strong>
                    Admin
                </strong>

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
                        Student Information
                    </h2>

                    <p>
                        Enter the student's details below.
                    </p>

                </div>


            </div>


            <!-- =================================================
                 Errors
            ================================================== -->


            <?php if (!empty($errors)): ?>


                <div class="alert alert-error">


                    <strong>
                        Please fix the following:
                    </strong>


                    <ul>


                        <?php foreach (
                            $errors as $error
                        ): ?>


                            <li>

                                <?php

                                echo htmlspecialchars(
                                    $error
                                );

                                ?>

                            </li>


                        <?php endforeach; ?>


                    </ul>


                </div>


            <?php endif; ?>


            <!-- =================================================
                 Form
            ================================================== -->


            <form
                method="POST"
                enctype="multipart/form-data"
                class="student-form"
            >


                <div class="form-grid">


                    <!-- Full Name -->

                    <div class="form-group">


                        <label>

                            Full Name

                            <span>*</span>

                        </label>


                        <input
                            type="text"
                            name="name"
                            placeholder="Enter student's full name"
                            value="<?php echo htmlspecialchars(
                                $_POST['name'] ?? ''
                            ); ?>"
                            required
                        >


                    </div>


                    <!-- Student ID -->

                    <div class="form-group">


                        <label>
                            Student ID
                        </label>


                        <input
                            type="text"
                            value="Automatically generated"
                            disabled
                        >


                        <small>
                            Student ID will be generated automatically.
                        </small>


                    </div>


                    <!-- Email -->

                    <div class="form-group">


                        <label>

                            Email Address

                            <span>*</span>

                        </label>


                        <input
                            type="email"
                            name="email"
                            placeholder="student@example.com"
                            value="<?php echo htmlspecialchars(
                                $_POST['email'] ?? ''
                            ); ?>"
                            required
                        >


                    </div>


                    <!-- Phone -->

                    <div class="form-group">


                        <label>
                            Phone Number
                        </label>


                        <input
                            type="text"
                            name="phone"
                            placeholder="01XXXXXXXXX"
                            value="<?php echo htmlspecialchars(
                                $_POST['phone'] ?? ''
                            ); ?>"
                        >


                    </div>


                    <!-- DOB -->

                    <div class="form-group">


                        <label>
                            Date of Birth
                        </label>


                        <input
                            type="date"
                            name="dob"
                            value="<?php echo htmlspecialchars(
                                $_POST['dob'] ?? ''
                            ); ?>"
                        >


                    </div>


                    <!-- Gender -->

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
                                <?php echo (
                                    ($_POST['gender'] ?? '')
                                    === 'Male'
                                )
                                ? 'selected'
                                : '';
                                ?>
                            >
                                Male
                            </option>


                            <option
                                value="Female"
                                <?php echo (
                                    ($_POST['gender'] ?? '')
                                    === 'Female'
                                )
                                ? 'selected'
                                : '';
                                ?>
                            >
                                Female
                            </option>


                            <option
                                value="Other"
                                <?php echo (
                                    ($_POST['gender'] ?? '')
                                    === 'Other'
                                )
                                ? 'selected'
                                : '';
                                ?>
                            >
                                Other
                            </option>


                        </select>


                    </div>


                    <!-- Department -->

                    <div class="form-group">


                        <label>
                            Department
                        </label>


                        <select name="department_id">


                            <option value="">
                                Select Department
                            </option>


                            <?php while (
                                $department =
                                $departments->fetch_assoc()
                            ): ?>


                                <option
                                    value="<?php echo $department['id']; ?>"
                                    <?php echo (
                                        ($_POST['department_id'] ?? '')
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


                    <!-- Admission Date -->

                    <div class="form-group">


                        <label>
                            Admission Date
                        </label>


                        <input
                            type="date"
                            name="admission_date"
                            value="<?php echo htmlspecialchars(
                                $_POST['admission_date']
                                ?? ''
                            ); ?>"
                        >


                    </div>


                    <!-- Status -->

                    <div class="form-group">


                        <label>
                            Status
                        </label>


                        <select name="status">


                            <option
                                value="Active"
                                <?php echo (
                                    ($_POST['status'] ?? 'Active')
                                    === 'Active'
                                )
                                ? 'selected'
                                : '';
                                ?>
                            >
                                Active
                            </option>


                            <option
                                value="Inactive"
                                <?php echo (
                                    ($_POST['status'] ?? '')
                                    === 'Inactive'
                                )
                                ? 'selected'
                                : '';
                                ?>
                            >
                                Inactive
                            </option>


                        </select>


                    </div>


                </div>


                <!-- =================================================
                     Photo
                ================================================== -->


                <div class="form-group">


                    <label>
                        Student Photo
                    </label>


                    <input
                        type="file"
                        name="photo"
                        accept="image/jpeg,image/png,image/webp"
                    >


                    <small>
                        JPG, PNG or WEBP. Maximum size 2MB.
                    </small>


                </div>


                <!-- =================================================
                     Address
                ================================================== -->


                <div class="form-group">


                    <label>
                        Address
                    </label>


                    <textarea
                        name="address"
                        rows="4"
                        placeholder="Enter student's address"
                    ><?php echo htmlspecialchars(
                        $_POST['address'] ?? ''
                    ); ?></textarea>


                </div>


                <!-- =================================================
                     Actions
                ================================================== -->


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