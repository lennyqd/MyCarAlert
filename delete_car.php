<?php
session_start();
include("connection.php");

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}

if (isset($_GET['VehicleID'])) {
    $vehicleID = (int)$_GET['VehicleID'];
    $userID = $_SESSION['id'];

    // Proceed with deletion without asking for confirmation
    if ($stmt = $conn->prepare("DELETE FROM cars WHERE VehicleID = ? AND UserID = ?")) {
        $stmt->bind_param("ii", $vehicleID, $userID);
        if ($stmt->execute()) {
            // If deletion is successful, redirect with success message
            header("Location: home.php?message=CarDeleted");
            exit();
        } else {
            // If there was a problem executing the statement
            echo "There was an error deleting the car.";
        }
        $stmt->close();
    } else {
        // If there was a problem preparing the statement
        echo "Error preparing statement for car deletion.";
    }
} else {
    // If VehicleID wasn't set in the query string
    header("Location: home.php?error=NoVehicleID");
    exit();
}
