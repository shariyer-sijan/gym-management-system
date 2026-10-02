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

// find the linked user_id first
$result = mysqli_query($conn, "SELECT user_id FROM members WHERE member_id = '$member_id'");
$row = mysqli_fetch_assoc($result);

if ($row) {
    $user_id = $row["user_id"];

    // deleting the USER row cascades and removes the member row too
    // (and subscriptions/payments/attendance cascade from there)
    mysqli_query($conn, "DELETE FROM users WHERE user_id = '$user_id'");

    header("Location: manage_members.php?msg=" . urlencode("Member deleted successfully."));
    exit();
} else {
    header("Location: manage_members.php?msg=" . urlencode("Member not found."));
    exit();
}
?>
