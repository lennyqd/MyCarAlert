<?php
session_start();
include("connection.php");

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}

$successMsg = '';
$errorMsg = '';

// Get the RecordID from the URL
$recordID = isset($_GET['RecordID']) ? (int)$_GET['RecordID'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['serviceDate'], $_POST['serviceType'], $_POST['cost'])) {
    $serviceDate = $_POST['serviceDate'];
    $serviceType = $_POST['serviceType'];
    $cost = $_POST['cost'];

    if ($stmt = $conn->prepare("UPDATE ServiceRecord SET ServiceDate = ?, ServiceType = ?, Cost = ? WHERE RecordID = ?")) {
        $stmt->bind_param("ssdi", $serviceDate, $serviceType, $cost, $recordID);
        if ($stmt->execute()) {
            $successMsg = "Service record updated successfully!";
        } else {
            $errorMsg = "Error updating record.";
        }
        $stmt->close();
    }
}

// Fetch the existing record to pre-fill the form
if ($stmt = $conn->prepare("SELECT ServiceDate, ServiceType, Cost FROM ServiceRecord WHERE RecordID = ?")) {
    $stmt->bind_param("i", $recordID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($record = $result->fetch_assoc()) {
        $serviceDate = $record['ServiceDate'];
        $serviceType = $record['ServiceType'];
        $cost = $record['Cost'];
    } else {
        $errorMsg = "Record not found.";
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Service Record</title>
</head>
<body>
    <h2>Edit Service Record</h2>
    <?php if ($successMsg) echo "<p>$successMsg</p>"; ?>
    <?php if ($errorMsg) echo "<p>$errorMsg</p>"; ?>

    <form action="edit_service_record.php?RecordID=<?php echo $recordID; ?>" method="post">
        <label for="serviceDate">Service Date:</label>
        <input type="date" id="serviceDate" name="serviceDate" value="<?php echo $serviceDate; ?>" required>

        <label for="serviceType">Service Type:</label>
        <input type="text" id="serviceType" name="serviceType" value="<?php echo $serviceType; ?>" required>

        <label for="cost">Cost:</label>
        <input type="number" step="0.01" id="cost" name="cost" value="<?php echo $cost; ?>" required>

        <input type="submit" value="Update Record">
    </form>

    <button onclick="window.location.href='home.php';">Go Back</button>
</body>
</html>
