<?php
session_start();
include("connection.php");

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}

if (isset($_GET['RecordID'])) {
    $recordID = (int)$_GET['RecordID'];

    // Start transaction to ensure both operations are done together
    $conn->begin_transaction();
    try {
        // First, find VehicleID and ServiceDate for the given RecordID to delete the correct MaintenanceAlert
        $findStmt = $conn->prepare("SELECT VehicleID, ServiceDate FROM ServiceRecord WHERE RecordID = ?");
        $findStmt->bind_param("i", $recordID);
        $findStmt->execute();
        $result = $findStmt->get_result();
        if ($serviceRecord = $result->fetch_assoc()) {
            // Delete MaintenanceAlert using VehicleID and ServiceDate
            $alertStmt = $conn->prepare("DELETE FROM MaintenanceAlert WHERE VehicleID = ? AND AlertDate = ?");
            $alertStmt->bind_param("is", $serviceRecord['VehicleID'], $serviceRecord['ServiceDate']);
            $alertStmt->execute();
            $alertStmt->close();

            // Now, delete the ServiceRecord
            $stmt = $conn->prepare("DELETE FROM ServiceRecord WHERE RecordID = ?");
            $stmt->bind_param("i", $recordID);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            echo "Service record and associated maintenance alert successfully deleted.";
        } else {
            echo "Service record not found.";
            $conn->rollback();
        }
        $findStmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        echo "An error occurred: " . $e->getMessage();
    }
} else {
    echo "No record ID specified.";
}
header("Location: home.php");
exit();
?>
