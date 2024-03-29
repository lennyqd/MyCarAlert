<?php
session_start();
include("connection.php");

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}

$userID = $_SESSION['id'];

$vehicleID = isset($_GET['VehicleID']) ? (int)$_GET['VehicleID'] : 0;
$successMsg = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['serviceDate'], $_POST['serviceType'], $_POST['cost'])) {
    // Sanitize and validate inputs here as needed
    $serviceDate = $_POST['serviceDate'];
    $serviceType = $_POST['serviceType'];
    $cost = $_POST['cost'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert service record
        if ($stmt = $conn->prepare("INSERT INTO ServiceRecord (VehicleID, ServiceDate, ServiceType, Cost) VALUES (?, ?, ?, ?)")) {
            $stmt->bind_param("isss", $vehicleID, $serviceDate, $serviceType, $cost);
            $stmt->execute();
            $stmt->close();

            // Insert maintenance alert
            if ($alertStmt = $conn->prepare("INSERT INTO MaintenanceAlert (VehicleID, AlertDate) VALUES (?, ?)")) {
                $alertStmt->bind_param("is", $vehicleID, $serviceDate);
                $alertStmt->execute();
                $alertStmt->close();

                // Define the folder name
                $logFolder = "MaintenanceLogFolder";
                
                // Check if the folder exists, if not, create it
                if (!file_exists($logFolder)) {
                    mkdir($logFolder, 0777, true); // The third parameter "true" allows the creation of nested directories as needed
                }

                // Log this action
                $dateTimeNow = date('Y-m-d_H-i-s');
                $logFilename = $logFolder . "/MaintenanceAlert_$dateTimeNow.log";
                $logContent = "Alert added for VehicleID: $vehicleID on $serviceDate about $serviceType\n";
                file_put_contents($logFilename, $logContent);

                $successMsg = "Service record and maintenance alert added successfully!";
            }

        }

        // Commit transaction
        $conn->commit();
    } catch (Exception $e) {
        // An error occurred, roll back the transaction
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Record Service</title>
</head>
<body>
    <?php if (!empty($successMsg)) echo "<p>$successMsg</p>"; ?>
    <form action="record_car.php?VehicleID=<?php echo $vehicleID; ?>" method="post">
        <label for="serviceDate">Service Date:</label>
        <input type="date" id="serviceDate" name="serviceDate" required>

        <label for="serviceType">Service Type:</label>
        <input type="text" id="serviceType" name="serviceType" required>

        <label for="cost">Cost:</label>
        <input type="number" step="0.01" id="cost" name="cost" required>

        <input type="submit" value="Add Record">
        
        <input type="button" value="Reset" onclick="window.location='record_car.php?VehicleID=<?php echo $vehicleID; ?>';">
        <input type="button" value="Go Back" onclick="window.location='home.php';">
    </form>
</body>
</html>
