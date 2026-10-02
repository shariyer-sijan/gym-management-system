<?php
include "config.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

$message = "";

// get all members for the dropdown
$members = mysqli_query($conn, "SELECT member_id, name FROM members ORDER BY name");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $member_id = $_POST["member_id"];
    $today = date("Y-m-d");
    $now_time = date("H:i:s");

    // check if this member already has attendance marked today
    $check = mysqli_query($conn, "SELECT * FROM attendance WHERE member_id = '$member_id' AND attendance_date = '$today'");

    if (mysqli_num_rows($check) > 0) {
        $message = "<p class='error'>Attendance already marked for this member today.</p>";
    } else {
        $sql = "INSERT INTO attendance (member_id, attendance_date, check_in_time)
                VALUES ('$member_id', '$today', '$now_time')";
        if (mysqli_query($conn, $sql)) {
            $message = "<p class='success'>Attendance marked successfully at $now_time.</p>";
        } else {
            $message = "<p class='error'>Error: " . mysqli_error($conn) . "</p>";
        }
    }
}

// show today's attendance list
$today = date("Y-m-d");
$today_list = mysqli_query($conn, "
    SELECT a.attendance_id, m.name, a.check_in_time
    FROM attendance a
    JOIN members m ON a.member_id = m.member_id
    WHERE a.attendance_date = '$today'
    ORDER BY a.check_in_time DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mark Attendance</title>
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
    <h2>Mark Attendance</h2>
    <?php echo $message; ?>

    <form method="POST" action="mark_attendance.php">
        <label>Select Member</label>
        <select name="member_id" required>
            <?php while ($m = mysqli_fetch_assoc($members)): ?>
                <option value="<?php echo $m['member_id']; ?>"><?php echo htmlspecialchars($m['name']); ?></option>
            <?php endwhile; ?>
        </select>
        <input type="submit" value="Mark Check-In">
    </form>

    <h3>Today's Attendance (<?php echo $today; ?>)</h3>
    <table>
        <tr><th>Member Name</th><th>Check-In Time</th></tr>
        <?php while ($row = mysqli_fetch_assoc($today_list)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row["name"]); ?></td>
                <td><?php echo $row["check_in_time"]; ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
