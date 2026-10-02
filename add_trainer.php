<?php
include "config.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username       = mysqli_real_escape_string($conn, $_POST["username"]);
    $password       = $_POST["password"];
    $name           = mysqli_real_escape_string($conn, $_POST["name"]);
    $specialization = mysqli_real_escape_string($conn, $_POST["specialization"]);
    $contact        = mysqli_real_escape_string($conn, $_POST["contact"]);
    $salary         = $_POST["salary"];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 1. create the login account (role = trainer)
    $sql1 = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', 'trainer')";

    if (mysqli_query($conn, $sql1)) {
        $user_id = mysqli_insert_id($conn);

        // 2. create the trainer profile, linked to that login
        $sql2 = "INSERT INTO trainers (user_id, name, specialization, contact, salary)
                 VALUES ('$user_id', '$name', '$specialization', '$contact', '$salary')";

        if (mysqli_query($conn, $sql2)) {
            $message = "<p class='success'>Trainer added successfully! Give them this login -> Username: $username</p>";
        } else {
            $message = "<p class='error'>Error: " . mysqli_error($conn) . "</p>";
        }
    } else {
        $message = "<p class='error'>Error: Username may already be taken.</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Trainer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="navbar">
    <div class="nav-links">
        <span class="brand">🏋️ FitTrack</span>
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_members.php">Manage Members</a>
        <a href="manage_trainers.php">Manage Trainers</a>
        <a href="add_member.php">Add Member</a>
        <a href="add_trainer.php">Add Trainer</a>
        <a href="mark_attendance.php">Mark Attendance</a>
    </div>
    <div class="nav-right"><a href="logout.php">Logout</a></div>
</div>

<div class="container">
    <h2>Add New Trainer</h2>
    <?php echo $message; ?>

    <form method="POST" action="add_trainer.php">
        <h3>Login Details</h3>
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <h3>Trainer Details</h3>
        <label>Trainer Name</label>
        <input type="text" name="name" required>

        <label>Specialization</label>
        <input type="text" name="specialization" placeholder="e.g. Weight Training, Yoga, Cardio">

        <label>Contact Number</label>
        <input type="text" name="contact" required>

        <label>Salary</label>
        <input type="text" name="salary" required>

        <input type="submit" value="Add Trainer">
    </form>
</div>
</body>
</html>
