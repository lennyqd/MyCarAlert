<?php
session_start();
include("connection.php");

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}

$successMsg = '';
$errorMsg = '';

// Get the AlertID from the URL
$alertID = isset($_GET['AlertID']) ? (int)$_GET['AlertID'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alertDate'])) {
    $alertDate = $_POST['alertDate'];

    if ($stmt = $conn->prepare("UPDATE MaintenanceAlert SET AlertDate = ? WHERE AlertID = ?")) {
        $stmt->bind_param("si", $alertDate, $alertID);
        if ($stmt->execute()) {
            $successMsg = "Maintenance alert updated successfully!";
        } else {
            $errorMsg = "Error updating alert.";
        }
        $stmt->close();
    }
}

// Fetch the existing alert to pre-fill the form
if ($stmt = $conn->prepare("SELECT AlertDate FROM MaintenanceAlert WHERE AlertID = ?")) {
    $stmt->bind_param("i", $alertID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($alert = $result->fetch_assoc()) {
        $alertDate = $alert
        ['AlertDate'];
    } else {
        $errorMsg = "Alert not found.";
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Alert Date</title>
</head>
<body>
    <h2>Edit Maintenance Alert Date</h2>
    <?php if ($successMsg) echo "<p>$successMsg</p>"; ?>
    <?php if ($errorMsg) echo "<p>$errorMsg</p>"; ?>

    <form action="edit_alert_date.php?AlertID=<?php echo $alertID; ?>" method="post">
        <label for="alertDate">Alert Date:</label>
        <input type="date" id="alertDate" name="alertDate" value="<?php echo isset($alertDate) ? $alertDate : ''; ?>" required>

        <input type="submit" value="Update Alert Date">
    </form>

    <button onclick="window.location.href='home.php';">Go Back</button>
</body>
</html>
