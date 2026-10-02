<?php
include "config.php";

// only allow logged-in admins to view this page
if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

// ---- DASHBOARD STATS ----

// total members
$r1 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM members");
$total_members = mysqli_fetch_assoc($r1)["total"];

// total trainers
$r2 = mysqli_query($conn, "SELECT COUNT(*) AS total FROM trainers");
$total_trainers = mysqli_fetch_assoc($r2)["total"];

// total ACTIVE members (distinct members with an active subscription that hasn't expired)
$r3 = mysqli_query($conn, "
    SELECT COUNT(DISTINCT member_id) AS total
    FROM subscriptions
    WHERE status = 'active' AND end_date >= CURDATE()
");
$total_active_members = mysqli_fetch_assoc($r3)["total"];

// ---- MEMBER SEARCH ----
$search = "";
$search_results = null;

if (isset($_GET["search"]) && $_GET["search"] != "") {
    $search = mysqli_real_escape_string($conn, $_GET["search"]);
    $sql = "
        SELECT m.member_id, m.name, m.contact, t.name AS trainer_name,
               s.end_date, s.status
        FROM members m
        LEFT JOIN trainers t ON m.trainer_id = t.trainer_id
        LEFT JOIN subscriptions s ON s.member_id = m.member_id
        WHERE m.name LIKE '%$search%'
        ORDER BY s.end_date DESC
    ";
    $search_results = mysqli_query($conn, $sql);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="navbar">
    <span class="brand">🏋️ FitTrack</span>
   
   
    <div class="nav-links">
        
        <a href="admin_dashboard.php">Dashboard</a>
        <a href="manage_members.php">Manage Members</a>
        <a href="manage_trainers.php">Manage Trainers</a>
        <a href="add_member.php">Add Member</a>
        <a href="add_trainer.php">Add Trainer</a>
        <a href="mark_attendance.php">Mark Attendance</a>
    </div>
    <div class="nav-right">
        Logged in as&nbsp;<b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>&nbsp;|
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Admin Dashboard</h2>

    <div class="stats">
        <div class="stat-box">
            <h2><?php echo $total_members; ?></h2>
            <p>Total Members</p>
        </div>
        <div class="stat-box">
            <h2><?php echo $total_trainers; ?></h2>
            <p>Total Trainers</p>
        </div>
        <div class="stat-box">
            <h2><?php echo $total_active_members; ?></h2>
            <p>Total Active Members</p>
        </div>
    </div>

    <h3>🔍 Search Member</h3>
    <form method="GET" action="admin_dashboard.php">
        <input type="text" name="search" placeholder="Search by member name..."
               value="<?php echo htmlspecialchars($search); ?>">
        <input type="submit" value="Search">
    </form>

    <?php if ($search_results !== null): ?>
        <?php if (mysqli_num_rows($search_results) > 0): ?>
            <table>
                <tr>
                    <th>ID</th><th>Name</th><th>Contact</th><th>Trainer</th>
                    <th>Membership Expiry</th><th>Status</th>
                </tr>
                <?php while ($row = mysqli_fetch_assoc($search_results)): ?>
                    <tr>
                        <td><?php echo $row["member_id"]; ?></td>
                        <td><?php echo htmlspecialchars($row["name"]); ?></td>
                        <td><?php echo htmlspecialchars($row["contact"]); ?></td>
                        <td><?php echo htmlspecialchars($row["trainer_name"] ?? "Not assigned"); ?></td>
                        <td><?php echo $row["end_date"] ?? "N/A"; ?></td>
                        <td>
                            <?php if (($row["status"] ?? "") == "active" && $row["end_date"] >= date("Y-m-d")): ?>
                                <span class="badge-active">Active</span>
                            <?php else: ?>
                                <span class="badge-expired">Expired</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No members found matching "<?php echo htmlspecialchars($search); ?>".</p>
        <?php endif; ?>
    <?php endif; ?>

</div>
</body>
</html>
