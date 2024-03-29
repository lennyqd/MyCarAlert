<?php
session_start();
include("connection.php");

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}

if (isset($_GET['AlertID'])) {
    $alertID = (int)$_GET['AlertID'];

    if ($stmt = $conn->prepare("DELETE FROM MaintenanceAlert WHERE AlertID = ?")) {
        $stmt->bind_param("i", $alertID);
        if ($stmt->execute()) {
            // If deletion is successful, redirect with a success message
            header("Location: home.php?alertMessage=AlertDeleted");
            exit();
        } else {
            // If there was a problem executing the deletion
            echo "There was an error deleting the alert.";
        }
        $stmt->close();
    } else {
        // If there was a problem preparing the deletion statement
        echo "Error preparing statement for alert deletion.";
    }
} else {
    // If AlertID wasn't set in the query string
    header("Location: home.php?alertError=NoAlertID");
    exit();
}
?>
