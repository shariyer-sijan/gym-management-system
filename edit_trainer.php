<?php
include "config.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: manage_trainers.php");
    exit();
}

$trainer_id = intval($_GET["id"]);
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $specialization = mysqli_real_escape_string($conn, $_POST["specialization"]);
    $contact = mysqli_real_escape_string($conn, $_POST["contact"]);
    $salary = $_POST["salary"];

    $sql = "UPDATE trainers SET
                name = '$name',
                specialization = '$specialization',
                contact = '$contact',
                salary = '$salary'
            WHERE trainer_id = '$trainer_id'";

    if (mysqli_query($conn, $sql)) {
        header("Location: manage_trainers.php?msg=" . urlencode("Trainer updated successfully."));
        exit();
    } else {
        $message = "<p class='error'>Error: " . mysqli_error($conn) . "</p>";
    }
}

$result = mysqli_query($conn, "SELECT * FROM trainers WHERE trainer_id = '$trainer_id'");
$trainer = mysqli_fetch_assoc($result);

if (!$trainer) {
    header("Location: manage_trainers.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Trainer</title>
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
    <h2>Edit Trainer: <?php echo htmlspecialchars($trainer["name"]); ?></h2>
    <?php echo $message; ?>

    <form method="POST" action="edit_trainer.php?id=<?php echo $trainer_id; ?>">
        <label>Trainer Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($trainer['name']); ?>" required>

        <label>Specialization</label>
        <input type="text" name="specialization" value="<?php echo htmlspecialchars($trainer['specialization']); ?>">

        <label>Contact Number</label>
        <input type="text" name="contact" value="<?php echo htmlspecialchars($trainer['contact']); ?>" required>

        <label>Salary</label>
        <input type="text" name="salary" value="<?php echo $trainer['salary']; ?>" required>

        <input type="submit" value="Save Changes">
        <a href="manage_trainers.php"><button type="button">Cancel</button></a>
    </form>
</div>
</body>
</html>
