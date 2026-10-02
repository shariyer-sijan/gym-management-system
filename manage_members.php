<?php
include "config.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

// fetch all members with their trainer name and current subscription status
$sql = "
    SELECT m.member_id, m.name, m.contact, m.address, t.name AS trainer_name,
           s.end_date, s.status
    FROM members m
    LEFT JOIN trainers t ON m.trainer_id = t.trainer_id
    LEFT JOIN subscriptions s ON s.subscription_id = (
        SELECT subscription_id FROM subscriptions
        WHERE member_id = m.member_id
        ORDER BY end_date DESC LIMIT 1
    )
    ORDER BY m.member_id DESC
";
$members = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Members</title>
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
    <h2>All Members</h2>

    <?php if (isset($_GET["msg"])): ?>
        <p class="success"><?php echo htmlspecialchars($_GET["msg"]); ?></p>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Contact</th><th>Address</th>
            <th>Trainer</th><th>Expiry</th><th>Status</th><th>Actions</th>
        </tr>
        <?php while ($m = mysqli_fetch_assoc($members)): ?>
            <tr>
                <td><?php echo $m["member_id"]; ?></td>
                <td><?php echo htmlspecialchars($m["name"]); ?></td>
                <td><?php echo htmlspecialchars($m["contact"]); ?></td>
                <td><?php echo htmlspecialchars($m["address"]); ?></td>
                <td><?php echo htmlspecialchars($m["trainer_name"] ?? "Not assigned"); ?></td>
                <td><?php echo $m["end_date"] ?? "N/A"; ?></td>
                <td>
                    <?php if (($m["status"] ?? "") == "active" && $m["end_date"] >= date("Y-m-d")): ?>
                        <span class="badge-active">Active</span>
                    <?php else: ?>
                        <span class="badge-expired">Expired</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="edit_member.php?id=<?php echo $m['member_id']; ?>">Edit</a> |
                    <a href="delete_member.php?id=<?php echo $m['member_id']; ?>"
                       onclick="return confirm('Delete this member? This also removes their login, subscriptions, payments and attendance records. This cannot be undone.');"
                       style="color:#c0392b;">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
