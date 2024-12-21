<?php
declare(strict_types=1);
require_once("../includes/session.inc.php");
require_once("../includes/dbh.inc.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $reason = $_POST["reason"];
        $unit = $_POST["unit"];

        $errors = [];

        if (empty($reason) || $unit == null) {
            $errors["request_empty"] = "Fill all fields!";
        }
        if ($unit && $unit < 0) {
            $errors["request_negative"] = "Blood units cannot be negative!";
        }

        if ($errors) {
            $_SESSION["patient_error_request"] = $errors;
            header("Location:dashboard.php?request_blood=1");
            die();
        }

        $query = "SELECT blood FROM patient WHERE username = :current_username;";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":current_username", $_SESSION['patient']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $blood = $result["blood"];

        $query = "SELECT id FROM patient WHERE username = :current_username;";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":current_username", $_SESSION['patient']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $patient_id = $result["id"];

        $query = "INSERT INTO request (username, patient_id, reason, blood, unit) VALUES (:current_username, :id, :reason, :blood, :unit);";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":current_username", $_SESSION["patient"]);
        $stmt->bindParam(":reason", $reason);
        $stmt->bindParam(":blood", $blood);
        $stmt->bindParam(":id", $patient_id);
        $stmt->bindParam(":unit", $unit);
        $stmt->execute();

        // Admin notification
        $admin_id = 1; // Replace with the actual admin ID
        $message = "New blood request from patient ID $patient_id: $reason ($unit unit(s) of $blood).";
        $query = "INSERT INTO notifications (admin_id, patient_id, message) VALUES (:admin_id, :patient_id, :message);";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(":admin_id", $admin_id, PDO::PARAM_INT);
        $stmt->bindParam(":patient_id", $patient_id, PDO::PARAM_INT);
        $stmt->bindParam(":message", $message, PDO::PARAM_STR);
        $stmt->execute();

        header("Location:dashboard.php?requests_history=1");

        $pdo = null;
        $stmt = null;
        die();
    } catch (PDOException $e) {
        echo $e;
    }
} else {
    header("Location:dashboard.php");
    die();
}
?>
