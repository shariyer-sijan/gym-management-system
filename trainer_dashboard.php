<?php
include "config.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "trainer") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// get this trainer's own profile
$result = mysqli_query($conn, "SELECT * FROM trainers WHERE user_id = '$user_id'");
$trainer = mysqli_fetch_assoc($result);
$trainer_id = $trainer["trainer_id"];

// count members under this trainer
$count_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM members WHERE trainer_id = '$trainer_id'");
$total_members = mysqli_fetch_assoc($count_result)["total"];

// count how many of them are currently active
$active_result = mysqli_query($conn, "
    SELECT COUNT(DISTINCT m.member_id) AS total
    FROM members m
    JOIN subscriptions s ON s.member_id = m.member_id
    WHERE m.trainer_id = '$trainer_id' AND s.status = 'active' AND s.end_date >= CURDATE()
");
$total_active = mysqli_fetch_assoc($active_result)["total"];

// get the full list of members assigned to this trainer, with their latest subscription
$members_sql = "
    SELECT m.member_id, m.name, m.contact, s.end_date, s.status
    FROM members m
    LEFT JOIN subscriptions s ON s.subscription_id = (
        SELECT subscription_id FROM subscriptions
        WHERE member_id = m.member_id
        ORDER BY end_date DESC LIMIT 1
    )
    WHERE m.trainer_id = '$trainer_id'
    ORDER BY m.name
";
$members = mysqli_query($conn, $members_sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trainer Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="navbar">
    <div class="nav-links">
        <span class="brand">🏋️ FitTrack</span>
        <a href="trainer_dashboard.php">My Dashboard</a>
    </div>
    <div class="nav-right">
        Logged in as <b>&nbsp;<?php echo htmlspecialchars($_SESSION["username"]); ?>&nbsp;</b> |
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Welcome, Coach <?php echo htmlspecialchars($trainer["name"]); ?> 💪</h2>

    <div class="stats">
        <div class="stat-box">
            <h2><?php echo $total_members; ?></h2>
            <p>Total Members Assigned</p>
        </div>
        <div class="stat-box">
            <h2><?php echo $total_active; ?></h2>
            <p>Currently Active Members</p>
        </div>
        <div class="stat-box">
            <h2><?php echo htmlspecialchars($trainer["specialization"]); ?></h2>
            <p>My Specialization</p>
        </div>
    </div>

    <h3>My Profile</h3>
    <table>
        <tr><th>Contact</th><td><?php echo htmlspecialchars($trainer["contact"]); ?></td></tr>
        <tr><th>Salary</th><td>৳<?php echo $trainer["salary"]; ?></td></tr>
    </table>

    <h3>Members Training Under Me</h3>
    <table>
        <tr><th>Name</th><th>Contact</th><th>Membership Expiry</th><th>Status</th></tr>
        <?php if (mysqli_num_rows($members) > 0): ?>
            <?php while ($m = mysqli_fetch_assoc($members)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($m["name"]); ?></td>
                    <td><?php echo htmlspecialchars($m["contact"]); ?></td>
                    <td><?php echo $m["end_date"] ?? "N/A"; ?></td>
                    <td>
                        <?php if (($m["status"] ?? "") == "active" && $m["end_date"] >= date("Y-m-d")): ?>
                            <span class="badge-active">Active</span>
                        <?php else: ?>
                            <span class="badge-expired">Expired</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No members assigned to you yet.</td></tr>
        <?php endif; ?>
    </table>
</div>
</body>
</html>
