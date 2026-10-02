<?php
include "config.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "member") {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// get this member's profile + trainer info
$sql = "
    SELECT m.*, t.name AS trainer_name, t.specialization, t.contact AS trainer_contact
    FROM members m
    LEFT JOIN trainers t ON m.trainer_id = t.trainer_id
    WHERE m.user_id = '$user_id'
";
$result = mysqli_query($conn, $sql);
$member = mysqli_fetch_assoc($result);
$member_id = $member["member_id"];

// get this member's latest/current subscription
$sub_sql = "
    SELECT s.*, p.plan_name, p.price
    FROM subscriptions s
    JOIN membership_plans p ON s.plan_id = p.plan_id
    WHERE s.member_id = '$member_id'
    ORDER BY s.end_date DESC
    LIMIT 1
";
$sub_result = mysqli_query($conn, $sub_sql);
$subscription = mysqli_fetch_assoc($sub_result);

$is_active = false;
if ($subscription && $subscription["end_date"] >= date("Y-m-d") && $subscription["status"] == "active") {
    $is_active = true;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Member Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="navbar">
    <div class="nav-links">
        <span class="brand">🏋️ FitTrack</span>
        <a href="member_dashboard.php">My Dashboard</a>
    </div>
    <div class="nav-right">
        Logged in as&nbsp;<b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>&nbsp;|
        <a href="logout.php">Logout</a>
    </div>
</div>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($member["name"]); ?> 👋</h2>

    <div class="stats">
        <div class="stat-box">
            <h2><?php echo $is_active ? "Active ✅" : "Expired ❌"; ?></h2>
            <p>Membership Status</p>
        </div>
        <div class="stat-box">
            <h2><?php echo $subscription ? $subscription["end_date"] : "N/A"; ?></h2>
            <p>Membership Expiry Date</p>
        </div>
        <div class="stat-box">
            <h2><?php echo htmlspecialchars($member["trainer_name"] ?? "Not Assigned"); ?></h2>
            <p>My Trainer</p>
        </div>
    </div>

    <h3>My Details</h3>
    <table>
        <tr><th>Contact</th><td><?php echo htmlspecialchars($member["contact"]); ?></td></tr>
        <tr><th>Address</th><td><?php echo htmlspecialchars($member["address"]); ?></td></tr>
        <tr><th>Join Date</th><td><?php echo $member["join_date"]; ?></td></tr>
        <tr><th>Current Plan</th><td><?php echo $subscription ? htmlspecialchars($subscription["plan_name"]) : "N/A"; ?></td></tr>
        <tr><th>Plan Price</th><td>৳<?php echo $subscription ? $subscription["price"] : "N/A"; ?></td></tr>
    </table>

    <h3>My Trainer's Info</h3>
    <table>
        <tr><th>Name</th><td><?php echo htmlspecialchars($member["trainer_name"] ?? "Not assigned yet"); ?></td></tr>
        <tr><th>Specialization</th><td><?php echo htmlspecialchars($member["specialization"] ?? "-"); ?></td></tr>
        <tr><th>Contact</th><td><?php echo htmlspecialchars($member["trainer_contact"] ?? "-"); ?></td></tr>
    </table>
</div>
</body>
</html>
