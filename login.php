<?php
include "config.php";

$error = "";
$selected_role = isset($_POST["role"]) ? $_POST["role"] : "member";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST["username"]);
    $password = $_POST["password"];
    $role     = $_POST["role"]; // which tab they picked: admin / member / trainer

    // only allow the 3 known roles (protects against tampered form data)
    $allowed_roles = ["admin", "member", "trainer"];
    if (!in_array($role, $allowed_roles)) {
        $role = "member";
    }

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user["password"])) {

            // THE KEY CHECK: the account's real role must match the tab they chose.
            // An admin account can only log in through the Admin tab, etc.
            if ($user["role"] !== $role) {
                $error = "These credentials aren't registered as a \"" . ucfirst($role) . "\". Please select the correct login type.";
            } else {
                $_SESSION["user_id"] = $user["user_id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                if ($user["role"] == "admin") {
                    header("Location: admin_dashboard.php");
                } elseif ($user["role"] == "trainer") {
                    header("Location: trainer_dashboard.php");
                } else {
                    header("Location: member_dashboard.php");
                }
                exit();
            }
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No account found with that username.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gym Management System - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page" >
    <div class="login-card">
        <h2>🏋️ FitTrack Gym</h2>
        <p class="login-sub">Sign in to continue</p>

        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <div class="role-tabs">
                <input type="radio" name="role" id="role-admin" value="admin" <?php if ($selected_role=="admin") echo "checked"; ?>>
                <label for="role-admin">Admin</label>

                <input type="radio" name="role" id="role-member" value="member" <?php if ($selected_role=="member" || $selected_role=="") echo "checked"; ?>>
                <label for="role-member">Member</label>

                <input type="radio" name="role" id="role-trainer" value="trainer" <?php if ($selected_role=="trainer") echo "checked"; ?>>
                <label for="role-trainer">Trainer</label>
            </div>

            <label>Username</label>
            <input type="text" name="username" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
