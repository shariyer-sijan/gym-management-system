<?php
include "config.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: manage_members.php");
    exit();
}

$member_id = intval($_GET["id"]);
$message = "";

// handle form submission (update)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name       = mysqli_real_escape_string($conn, $_POST["name"]);
    $dob        = $_POST["dob"];
    $gender     = $_POST["gender"];
    $contact    = mysqli_real_escape_string($conn, $_POST["contact"]);
    $address    = mysqli_real_escape_string($conn, $_POST["address"]);
    $trainer_id = $_POST["trainer_id"];

    $sql = "UPDATE members SET
                name = '$name',
                dob = '$dob',
                gender = '$gender',
                contact = '$contact',
                address = '$address',
                trainer_id = '$trainer_id'
            WHERE member_id = '$member_id'";

    if (mysqli_query($conn, $sql)) {
        header("Location: manage_members.php?msg=" . urlencode("Member updated successfully."));
        exit();
    } else {
        $message = "<p class='error'>Error: " . mysqli_error($conn) . "</p>";
    }
}

// fetch current member data to pre-fill the form
$result = mysqli_query($conn, "SELECT * FROM members WHERE member_id = '$member_id'");
$member = mysqli_fetch_assoc($result);

if (!$member) {
    header("Location: manage_members.php");
    exit();
}

$trainers = mysqli_query($conn, "SELECT * FROM trainers");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Member</title>
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
    <h2>Edit Member: <?php echo htmlspecialchars($member["name"]); ?></h2>
    <?php echo $message; ?>

    <form method="POST" action="edit_member.php?id=<?php echo $member_id; ?>">
        <label>Full Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($member['name']); ?>" required>

        <label>Date of Birth</label>
        <input type="date" name="dob" value="<?php echo $member['dob']; ?>" required>

        <label>Gender</label>
        <select name="gender" required>
            <option value="Male" <?php if ($member['gender']=='Male') echo 'selected'; ?>>Male</option>
            <option value="Female" <?php if ($member['gender']=='Female') echo 'selected'; ?>>Female</option>
            <option value="Other" <?php if ($member['gender']=='Other') echo 'selected'; ?>>Other</option>
        </select>

        <label>Contact Number</label>
        <input type="text" name="contact" value="<?php echo htmlspecialchars($member['contact']); ?>" required>

        <label>Address</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($member['address']); ?>">

        <label>Assigned Trainer</label>
        <select name="trainer_id" required>
            <?php while ($t = mysqli_fetch_assoc($trainers)): ?>
                <option value="<?php echo $t['trainer_id']; ?>"
                    <?php if ($t['trainer_id'] == $member['trainer_id']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($t['name']); ?> (<?php echo htmlspecialchars($t['specialization']); ?>)
                </option>
            <?php endwhile; ?>
        </select>

        <input type="submit" value="Save Changes">
        <a href="manage_members.php"><button type="button">Cancel</button></a>
    </form>
</div>
</body>
</html>
