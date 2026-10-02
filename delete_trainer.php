<?php
include "config.php";

if (!isset($_SESSION["role"]) || $_SESSION["role"] != "admin") {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: manage_trainers.php");
    exit();
}

$trainer_id = intval($_GET["id"]);

// deleting the trainer automatically sets trainer_id to NULL on any
// members who had this trainer (see ON DELETE SET NULL in schema.sql)
mysqli_query($conn, "DELETE FROM trainers WHERE trainer_id = '$trainer_id'");

header("Location: manage_trainers.php?msg=" . urlencode("Trainer deleted successfully."));
exit();
?>
