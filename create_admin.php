<?php

require_once "config/db.php";


$name = "Admin";

$email = "admin@studenthub.com";

$password = "Admin@123";


$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);


$stmt = $conn->prepare("
    INSERT INTO admins
    (name, email, password)
    VALUES (?, ?, ?)
");


$stmt->bind_param(
    "sss",
    $name,
    $email,
    $hashed_password
);


if ($stmt->execute()) {

    echo "Admin created successfully.";

} else {

    echo "Error: " . $conn->error;

}


$stmt->close();