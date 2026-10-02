<?php
include "config.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

// count how many members each trainer currently has
$sql = "
    SELECT t.*, COUNT(m.member_id) AS member_count
    FROM trainers t
    LEFT JOIN members m ON m.trainer_id = t.trainer_id
    GROUP BY t.trainer_id
    ORDER BY t.trainer_id DESC
";
$trainers = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Trainers</title>
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
    <h2>All Trainers</h2>

    <?php if (isset($_GET["msg"])): ?>
        <p class="success"><?php echo htmlspecialchars($_GET["msg"]); ?></p>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Specialization</th><th>Contact</th>
            <th>Salary</th><th>Members Assigned</th><th>Login</th><th>Actions</th>
        </tr>
        <?php while ($t = mysqli_fetch_assoc($trainers)): ?>
            <tr>
                <td><?php echo $t["trainer_id"]; ?></td>
                <td><?php echo htmlspecialchars($t["name"]); ?></td>
                <td><?php echo htmlspecialchars($t["specialization"]); ?></td>
                <td><?php echo htmlspecialchars($t["contact"]); ?></td>
                <td>৳<?php echo $t["salary"]; ?></td>
                <td><?php echo $t["member_count"]; ?></td>
                <td>
                    <?php if ($t["user_id"]): ?>
                        <span class="badge-active">Set up</span>
                    <?php else: ?>
                        <span class="badge-expired">No login</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit_trainer.php?id=<?php echo $t['trainer_id']; ?>">Edit</a> |
                    <a href="delete_trainer.php?id=<?php echo $t['trainer_id']; ?>"
                       onclick="return confirm('Delete this trainer? Members assigned to them will become unassigned (not deleted).');"
                       style="color:#c0392b;">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
