<?php
include "config.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

$message = "";

// get trainers list for the dropdown
$trainers = mysqli_query($conn, "SELECT * FROM trainers");

// get membership plans list for the dropdown
$plans = mysqli_query($conn, "SELECT * FROM membership_plans");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ---- 1. Read form data ----
    $username   = mysqli_real_escape_string($conn, $_POST["username"]);
    $password   = $_POST["password"];
    $name       = mysqli_real_escape_string($conn, $_POST["name"]);
    $dob        = $_POST["dob"];
    $gender     = $_POST["gender"];
    $contact    = mysqli_real_escape_string($conn, $_POST["contact"]);
    $address    = mysqli_real_escape_string($conn, $_POST["address"]);
    $trainer_id = $_POST["trainer_id"];
    $plan_id    = $_POST["plan_id"];
    $start_date = $_POST["start_date"];
    $payment_mode = $_POST["payment_mode"];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $join_date = date("Y-m-d");

    // ---- 2. Insert into users table (login account) ----
    $sql1 = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', 'member')";

    if (mysqli_query($conn, $sql1)) {
        $user_id = mysqli_insert_id($conn);

        // ---- 3. Insert into members table ----
        $sql2 = "INSERT INTO members (user_id, name, dob, gender, contact, address, join_date, trainer_id)
                 VALUES ('$user_id', '$name', '$dob', '$gender', '$contact', '$address', '$join_date', '$trainer_id')";
        mysqli_query($conn, $sql2);
        $member_id = mysqli_insert_id($conn);

        // ---- 4. Get plan duration + price to calculate end_date ----
        $plan_result = mysqli_query($conn, "SELECT * FROM membership_plans WHERE plan_id = '$plan_id'");
        $plan = mysqli_fetch_assoc($plan_result);
        $end_date = date("Y-m-d", strtotime($start_date . " + " . $plan["duration_days"] . " days"));

        // ---- 5. Insert subscription ----
        $sql3 = "INSERT INTO subscriptions (member_id, plan_id, start_date, end_date, status)
                 VALUES ('$member_id', '$plan_id', '$start_date', '$end_date', 'active')";
        mysqli_query($conn, $sql3);
        $subscription_id = mysqli_insert_id($conn);

        // ---- 6. Insert payment record ----
        $sql4 = "INSERT INTO payments (subscription_id, amount, payment_date, payment_mode)
                 VALUES ('$subscription_id', '{$plan['price']}', '$start_date', '$payment_mode')";
        mysqli_query($conn, $sql4);

        $message = "<p class='success'>Member added successfully! Give the member this login -> Username: $username</p>";
    } else {
        $message = "<p class='error'>Error: Username may already be taken.</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Member</title>
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
    <h2>Add New Member</h2>
    <?php echo $message; ?>

    <form method="POST" action="add_member.php">
        <h3>Login Details</h3>
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <h3>Personal Details</h3>
        <label>Full Name</label>
        <input type="text" name="name" required>

        <label>Date of Birth</label>
        <input type="date" name="dob" required>

        <label>Gender</label>
        <select name="gender" required>
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>

        <label>Contact Number</label>
        <input type="text" name="contact" required>

        <label>Address</label>
        <input type="text" name="address">

        <h3>Trainer Assignment</h3>
        <label>Assign Trainer</label>
        <select name="trainer_id" required>
            <?php while ($t = mysqli_fetch_assoc($trainers)): ?>
                <option value="<?php echo $t['trainer_id']; ?>"><?php echo htmlspecialchars($t['name']); ?> (<?php echo htmlspecialchars($t['specialization']); ?>)</option>
            <?php endwhile; ?>
        </select>

        <h3>Membership & Payment</h3>
        <label>Membership Plan</label>
        <select name="plan_id" required>
            <?php while ($p = mysqli_fetch_assoc($plans)): ?>
                <option value="<?php echo $p['plan_id']; ?>">
                    <?php echo htmlspecialchars($p['plan_name']); ?> - <?php echo $p['duration_days']; ?> days - ৳<?php echo $p['price']; ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label>Membership Start Date</label>
        <input type="date" name="start_date" value="<?php echo date('Y-m-d'); ?>" required>

        <label>Payment Mode</label>
        <select name="payment_mode" required>
            <option value="Cash">Cash</option>
            <option value="Card">Card</option>
            <option value="Mobile Banking">Mobile Banking</option>
        </select>

        <input type="submit" value="Add Member">
    </form>
</div>
</body>
</html>
