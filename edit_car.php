<?php
session_start();
include("connection.php");

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}

// Ensure VehicleID is present
if (!isset($_GET['VehicleID'])) {
    echo "No vehicle selected.";
    exit();
}

$vehicleID = $_GET['VehicleID'];
$errorMessage = '';

// Fetch car details
if ($stmt = $conn->prepare("SELECT * FROM cars WHERE VehicleID = ? AND UserID = ?")) {
    $stmt->bind_param("ii", $vehicleID, $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 0) {
        echo "Vehicle not found or you do not have permission to edit it.";
        exit();
    }
    $car = $result->fetch_assoc();
    $stmt->close();
} else {
    echo "Database error.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $make = filter_input(INPUT_POST, 'make', FILTER_SANITIZE_STRING);
    $model = filter_input(INPUT_POST, 'model', FILTER_SANITIZE_STRING);
    $year = filter_input(INPUT_POST, 'year', FILTER_SANITIZE_NUMBER_INT);

    // Update database
    if ($updateStmt = $conn->prepare("UPDATE cars SET Make = ?, Model = ?, Year = ? WHERE VehicleID = ? AND UserID = ?")) {
        $updateStmt->bind_param("ssiii", $make, $model, $year, $vehicleID, $_SESSION['id']);
        if (!$updateStmt->execute()) {
            $errorMessage = "Failed to update car details.";
        } else {
            header("Location: home.php?message=CarUpdated");
            exit();
        }
        $updateStmt->close();
    } else {
        $errorMessage = "Database error.";
    }
}
?>

<head>
    <meta charset="UTF-8">
    <title>Edit Car</title>
    <style>
    .navbar-brand {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        text-shadow: 
            -1px -1px 0 #000,  
             1px -1px 0 #000,
            -1px  1px 0 #000,
             1px  1px 0 #000; /* Outline effect */
        }
    </style>
</head>
<body>
    <h2>Edit Car</h2>
    <?php if ($errorMessage): ?>
        <p><?php echo $errorMessage; ?></p>
    <?php endif; ?>
    <form action="edit_car.php?VehicleID=<?php echo $vehicleID; ?>" method="post">
        <label for="make">Make:</label>
        <input type="text" id="make" name="make" required value="<?php echo htmlspecialchars($car['Make']); ?>">
        
        <label for="model">Model:</label>
        <input type="text" id="model" name="model" required value="<?php echo htmlspecialchars($car['Model']); ?>">
        
        <label for="year">Year:</label>
        <input type="number" id="year" name="year" required value="<?php echo htmlspecialchars($car['Year']); ?>">
        
        <input type="submit" value="Update Car">
    </form>
</body>
</html>