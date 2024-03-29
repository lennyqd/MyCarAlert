<?php
session_start();
include("connection.php");

if (!isset($_SESSION['username'])) {
    header("location:login.php");
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['make'], $_POST['model'], $_POST['year'])) {
    $make = filter_input(INPUT_POST, 'make', FILTER_SANITIZE_STRING);
    $model = filter_input(INPUT_POST, 'model', FILTER_SANITIZE_STRING);
    $year = filter_input(INPUT_POST, 'year', FILTER_SANITIZE_NUMBER_INT);
    $userid = $_SESSION['id']; // Sanitize this if needed

    // Prepare SQL statement to insert new car
    if ($stmt = $conn->prepare("INSERT INTO cars (UserID, Make, Model, Year) VALUES (?, ?, ?, ?)")) {
        $stmt->bind_param("issi", $userid, $make, $model, $year);
        if (!$stmt->execute()) {
            // Handle error - Insert failed
            echo "<script>alert('Error adding car. Please try again.');</script>";
        }
        $stmt->close();
    } else {
        // Handle error - Preparation failed
        echo "<script>alert('Error preparing to add car. Please try again.');</script>";
    }
}

if (isset($_GET['alertMessage']) && $_GET['alertMessage'] === 'AlertDeleted') {
    echo "<p>The alert has been successfully deleted.</p>";
}
if (isset($_GET['alertError']) && $_GET['alertError'] === 'NoAlertID') {
    echo "<p>No alert specified for deletion.</p>";
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
    <div class="container">
    <header class="navbar-brand"></i>MyCarAlert</header>
    <div class="form-box box">
        <?php
        ?>
        <header class="navbar-section">
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid navigationEdit">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link" aria-current="page" href="#home">Home</a>
                        </li>
                        <li class="nav-item">
                            <div class="dropdown">
                                <a class='nav-link dropdown-toggle' href='edit.php?id=$res_id' id='dropdownMenuLink'
                                    data-bs-toggle='dropdown' aria-expanded='false'>
                                    <i class='bi bi-person'></i>
                                </a>
                                <ul class="dropdown-menu mt-2 mr-0" aria-labelledby="dropdownMenuLink">
                                    <li>
                                        <?php
                                        $id = $_SESSION['id'];
                                        $query = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
                                        while ($result = mysqli_fetch_assoc($query)) {
                                            $res_username = $result['username'];
                                            $res_email = $result['email'];
                                            $res_id = $result['id'];
                                        }
                                        echo "<a class='dropdown-item' href='edit.php?id=$res_id'>Change Info</a>";
                                        ?>
                                    </li>
                                    <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
<hr>
        <div class="name">
            <center>Welcome
                <?php
                // echo $_SESSION['valid'];
                echo $_SESSION['username'];
                ?>
                !
            </center>
        </div> 

        <form action="home.php" method="post">
        <label for="make">Make:</label>
        <input type="text" id="make" name="make" required>
        <label for="model">Model:</label>
        <input type="text" id="model" name="model" required>
        <label for="year">Year:</label>
        <input type="number" id="year" name="year" required>
        <input type="submit" value="Add Car">
        </form>

        <div class="car-list">
    <h3>Your Cars:</h3>
    <ul>
    <?php
        $userid = $_SESSION['id']; // Assuming you store user ID in session
        $query = mysqli_query($conn, "SELECT * FROM cars WHERE UserID = $userid");
        while ($car = mysqli_fetch_assoc($query)) {
            echo "<li><strong><u>" . $car['Make'] . " " . $car['Model'] . " - " . $car['Year'] . "</u></strong>";
            echo " <a href='edit_car.php?VehicleID=" . $car['VehicleID'] . "'>Edit</a>";
            echo " <a href='delete_car.php?VehicleID=" . $car['VehicleID'] . "' onclick='return confirm(\"Are you sure you want to delete?\");'>Delete</a>";

            // Service Records for each car
            echo "<ul class='service-records'>";
            $queryRecords = mysqli_query($conn, "SELECT * FROM ServiceRecord WHERE VehicleID = " . $car['VehicleID']);
            while ($record = mysqli_fetch_assoc($queryRecords)) {
                echo "<li>Service Date: " . $record['ServiceDate'] . ", Type: " . $record['ServiceType'] . ", Cost: $" . $record['Cost'];
                echo " <a href='edit_service_record.php?RecordID=" . $record['RecordID'] . "'>Edit Record</a>";
                echo " <a href='delete_record.php?RecordID=" . $record['RecordID'] . "' onclick='return confirm(\"Are you sure you want to delete this record?\");'>Delete Record</a>";
                echo "</li>";
            }
            echo "<li><a href='record_car.php?VehicleID=" . $car['VehicleID'] . "'>Add Record</a></li>";
            echo "</ul>"; // End of Service Records

            // Maintenance Alerts for each car
            echo "<ul class='maintenance-alerts'>";
            $alertQuery = mysqli_query($conn, "SELECT * FROM MaintenanceAlert WHERE VehicleID = " . $car['VehicleID']);
            while ($alert = mysqli_fetch_assoc($alertQuery)) {
                echo "<li>Alert Date: " . $alert['AlertDate'];
                echo " <a href='edit_alert_date.php?AlertID=" . $alert['AlertID'] . "'>Edit Alert Date</a>";
                echo " <a href='delete_alert_date.php?AlertID=" . $alert['AlertID'] . "' onclick='return confirm(\"Are you sure you want to delete this alert?\");'>Delete Alert</a>";
                echo "</li>";
            }
            echo "</ul>"; // End of Maintenance Alerts

            echo "</li>"; // End of Car
        }
        ?>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm"
        crossorigin="anonymous">
</script>
</body>

</html>
