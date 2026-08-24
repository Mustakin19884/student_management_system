<?php

require_once "../config/auth.php";
require_once "../config/db.php";


/*
|--------------------------------------------------------------------------
| Get Student ID
|--------------------------------------------------------------------------
*/

$student_id = isset($_GET['id'])
    ? (int) $_GET['id']
    : 0;


if ($student_id <= 0) {

    header("Location: index.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| Get Student
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT *
    FROM students
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $student_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {

    $stmt->close();

    header("Location: index.php");
    exit;

}


$student = $result->fetch_assoc();

$stmt->close();


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


/*
|--------------------------------------------------------------------------
| Errors
|--------------------------------------------------------------------------
*/

$errors = [];


/*
|--------------------------------------------------------------------------
| Form Submission
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
    $address = trim($_POST["address"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | Existing Photo
    |--------------------------------------------------------------------------
    */

    $old_photo = $student['photo'] ?? null;

    $new_photo_name = $old_photo;

    $photo_uploaded = false;

    $remove_photo = isset($_POST['remove_photo'])
        && $_POST['remove_photo'] === '1';


    /*
    |--------------------------------------------------------------------------
    | Validate Name
    |--------------------------------------------------------------------------
    */

    if ($name === "") {

        $errors[] = "Student name is required.";

    }


    /*
    |--------------------------------------------------------------------------
    | Validate Email
    |--------------------------------------------------------------------------
    */

    if ($email === "") {

        $errors[] = "Email is required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "Please enter a valid email address.";

    }


    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Email
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        $email_stmt = $conn->prepare("
            SELECT id
            FROM students
            WHERE email = ?
            AND id != ?
            LIMIT 1
        ");

        $email_stmt->bind_param(
            "si",
            $email,
            $student_id
        );

        $email_stmt->execute();

        $email_result = $email_stmt->get_result();


        if ($email_result->num_rows > 0) {

            $errors[] =
                "This email address is already registered.";

        }


        $email_stmt->close();

    }


    /*
    |--------------------------------------------------------------------------
    | Photo Upload
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES["photo"]) &&
        $_FILES["photo"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        $photo = $_FILES["photo"];


        /*
        | Check upload error
        */

        if ($photo["error"] !== UPLOAD_ERR_OK) {

            $errors[] =
                "There was an error uploading the photo.";

        }


        /*
        | Check file size
        */

        if ($photo["size"] > 2 * 1024 * 1024) {

            $errors[] =
                "Photo size must be less than 2MB.";

        }


        /*
        | Check MIME type
        */

        if (empty($errors)) {

            $allowed_types = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            $file_type = mime_content_type(
                $photo["tmp_name"]
            );


            if (!in_array($file_type, $allowed_types, true)) {

                $errors[] =
                    "Only JPG, PNG and WEBP images are allowed.";

            }

        }


        /*
        | Generate filename
        */

        if (empty($errors)) {

            $extension = strtolower(
                pathinfo(
                    $photo["name"],
                    PATHINFO_EXTENSION
                )
            );


            $new_photo_name =
                uniqid("student_", true)
                . "."
                . $extension;


            $photo_uploaded = true;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Update Student
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {


        /*
        | Remove photo if requested
        */

        if (
            $remove_photo &&
            !$photo_uploaded
        ) {

            $new_photo_name = null;

        }


        /*
        |--------------------------------------------------------------------------
        | Prepare Update
        |--------------------------------------------------------------------------
        */

        $update_stmt = $conn->prepare("
            UPDATE students

            SET
                name = ?,
                email = ?,
                phone = ?,
                dob = ?,
                gender = ?,
                department_id = ?,
                address = ?,
                photo = ?

            WHERE id = ?
        ");


        /*
        |--------------------------------------------------------------------------
        | Correct bind_param
        |--------------------------------------------------------------------------
        |
        | name            = s
        | email           = s
        | phone           = s
        | dob             = s
        | gender          = s
        | department_id  = i
        | address         = s
        | photo           = s
        | student_id      = i
        |
        */

        $update_stmt->bind_param(
            "sssssissi",
            $name,
            $email,
            $phone,
            $dob,
            $gender,
            $department_id,
            $address,
            $new_photo_name,
            $student_id
        );


        /*
        |--------------------------------------------------------------------------
        | Execute
        |--------------------------------------------------------------------------
        */

        if ($update_stmt->execute()) {


            /*
            |--------------------------------------------------------------------------
            | Upload Directory
            |--------------------------------------------------------------------------
            */

            $upload_directory =
                "../uploads/students/";


            if (!is_dir($upload_directory)) {

                mkdir(
                    $upload_directory,
                    0755,
                    true
                );

            }


            /*
            |--------------------------------------------------------------------------
            | New Photo Upload
            |--------------------------------------------------------------------------
            */

            if ($photo_uploaded) {

                $new_photo_path =
                    $upload_directory
                    . $new_photo_name;


                if (
                    move_uploaded_file(
                        $_FILES["photo"]["tmp_name"],
                        $new_photo_path
                    )
                ) {


                    /*
                    | Delete old photo
                    */

                    if (
                        !empty($old_photo) &&
                        file_exists(
                            $upload_directory
                            . $old_photo
                        )
                    ) {

                        unlink(
                            $upload_directory
                            . $old_photo
                        );

                    }

                } else {


                    /*
                    | Rollback database photo
                    */

                    $rollback_stmt = $conn->prepare("
                        UPDATE students
                        SET photo = ?
                        WHERE id = ?
                    ");

                    $rollback_stmt->bind_param(
                        "si",
                        $old_photo,
                        $student_id
                    );

                    $rollback_stmt->execute();

                    $rollback_stmt->close();


                    $errors[] =
                        "Photo upload failed.";

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Remove Existing Photo
            |--------------------------------------------------------------------------
            */

            elseif (
                $remove_photo &&
                !empty($old_photo)
            ) {

                $old_photo_path =
                    $upload_directory
                    . $old_photo;


                if (file_exists($old_photo_path)) {

                    unlink($old_photo_path);

                }

            }


            /*
            |--------------------------------------------------------------------------
            | Redirect After Success
            |--------------------------------------------------------------------------
            */

            if (empty($errors)) {

                $update_stmt->close();

                header(
                    "Location: index.php?success=student_updated"
                );

                exit;

            }

        } else {

            /*
            |--------------------------------------------------------------------------
            | Database Error
            |--------------------------------------------------------------------------
            */

            if ($conn->errno == 1062) {

                $errors[] =
                    "This email address is already registered.";

            } else {

                $errors[] =
                    "Something went wrong. Please try again.";

            }

        }


        $update_stmt->close();

    }


    /*
    |--------------------------------------------------------------------------
    | Preserve Form Data
    |--------------------------------------------------------------------------
    */

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


    <!-- Topbar -->

    <header class="topbar">

        <div>

            <h1>
                Edit Student
            </h1>

            <p>
                Update student information.
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


            <!-- Form Header -->

            <div class="form-header">

                <div>

                    <h2>
                        Student Information
                    </h2>

                    <p>
                        Update the student's details below.
                    </p>

                </div>

            </div>


            <!-- Errors -->

            <?php if (!empty($errors)): ?>

                <div class="alert alert-error">

                    <strong>
                        Please fix the following:
                    </strong>

                    <ul>

                        <?php foreach ($errors as $error): ?>

                            <li>

                                <?php
                                echo htmlspecialchars($error);
                                ?>

                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            <?php endif; ?>


            <!-- Form -->

            <form
                method="POST"
                class="student-form"
                enctype="multipart/form-data"
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
                            value="<?php echo htmlspecialchars($student['name'] ?? ''); ?>"
                            required
                        >

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
                            value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>"
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
                            value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>"
                        >

                    </div>


                    <!-- Date of Birth -->

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
                                <?php
                                echo (
                                    ($student['gender'] ?? '') === 'Male'
                                )
                                ? 'selected'
                                : '';
                                ?>
                            >
                                Male
                            </option>

                            <option
                                value="Female"
                                <?php
                                echo (
                                    ($student['gender'] ?? '') === 'Female'
                                )
                                ? 'selected'
                                : '';
                                ?>
                            >
                                Female
                            </option>

                            <option
                                value="Other"
                                <?php
                                echo (
                                    ($student['gender'] ?? '') === 'Other'
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

                            <?php while ($department = $departments->fetch_assoc()): ?>

                                <option
                                    value="<?php echo $department['id']; ?>"
                                    <?php
                                    echo (
                                        ($student['department_id'] ?? '')
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


                <!-- Profile Photo -->

                <div class="form-group">

                    <label>
                        Profile Photo
                    </label>


                    <?php if (!empty($student['photo'])): ?>

                        <div
                            style="
                                margin-bottom: 15px;
                                display: flex;
                                align-items: center;
                                gap: 15px;
                            "
                        >

                            <img
                                src="../uploads/students/<?php echo htmlspecialchars($student['photo']); ?>"
                                alt="Student Photo"
                                style="
                                    width: 90px;
                                    height: 90px;
                                    object-fit: cover;
                                    border-radius: 50%;
                                    border: 3px solid #eee;
                                "
                            >


                            <div>

                                <p style="margin: 0 0 8px;">
                                    Current profile photo
                                </p>


                                <label
                                    style="
                                        display: flex;
                                        align-items: center;
                                        gap: 8px;
                                        cursor: pointer;
                                    "
                                >

                                    <input
                                        type="checkbox"
                                        name="remove_photo"
                                        value="1"
                                    >

                                    Remove current photo

                                </label>

                            </div>

                        </div>

                    <?php endif; ?>


                    <input
                        type="file"
                        name="photo"
                        accept=".jpg,.jpeg,.png,.webp"
                    >


                    <small>
                        JPG, PNG or WEBP. Maximum size: 2MB.
                    </small>

                </div>


                <!-- Address -->

                <div class="form-group">

                    <label>
                        Address
                    </label>

                    <textarea
                        name="address"
                        rows="4"
                        placeholder="Enter student's address"
                    ><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>

                </div>


                <!-- Actions -->

                <div class="form-actions">

                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>


                    <a
                        href="view.php?id=<?php echo urlencode($student_id); ?>"
                        class="btn btn-secondary"
                    >
                        View Profile
                    </a>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Update Student
                    </button>

                </div>


            </form>


        </div>


    </section>


</main>


<?php include "../includes/footer.php"; ?>