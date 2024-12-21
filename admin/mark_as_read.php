<?php
declare(strict_types=1);
require_once("../includes/dbh.inc.php");

if (isset($_GET['notification_id'])) {
    $notification_id = (int)$_GET['notification_id'];
    $query = "UPDATE notifications SET is_read = 1 WHERE id = :id;";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(":id", $notification_id, PDO::PARAM_INT);
    $stmt->execute();
    header("Location:dashboard.php");
    die();
}
?>
