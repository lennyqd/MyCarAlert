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

$userId = $_SESSION['id'];

$query = "SELECT sr.* 
          FROM servicerecord sr 
          JOIN cars c ON sr.VehicleID = c.VehicleID 
          WHERE c.UserID = ? 
          ORDER BY sr.ServiceDate ASC";

$statement = $conn->prepare($query);
$statement->bind_param("i", $userId); // Assuming UserID is an integer type
$statement->execute();
$result = $statement->get_result(); // Get the result set from the prepared statement
$rows = $result->fetch_all(MYSQLI_ASSOC); // Fetch all rows as an associative array

// Calculate statistics
$totalCost = 0;
$numRecords = count($rows);
$lowestCost = PHP_INT_MAX;
$highestCost = 0;

foreach ($rows as $row) {
    $totalCost += $row['Cost'];
    if ($row['Cost'] < $lowestCost) {
        $lowestCost = $row['Cost'];
    }
    if ($row['Cost'] > $highestCost) {
        $highestCost = $row['Cost'];
    }
}

$averageCost = $numRecords > 0 ? $totalCost / $numRecords : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <script src="js/jquery.js"></script>
    <script src="js/timeline.min.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css"/>
    <ink rel="stylesheet" href="css/timeline.min.css"/>
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
            text-shadow: -1px -1px 0 #000, 1px -1px 0 #000, -1px 1px 0 #000, 1px 1px 0 #000;
            /* Outline effect */
        }

        .countdown-timer {
            text-align: center;
            margin-top: 20px;
        }

        #countdown {
            font-size: 24px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <header class="navbar-brand">MyCarAlert</header>
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

        <!-- Statistics table -->
        <?php if ($numRecords > 0): ?>
            <div>
                <h3>Statistics</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Total Cost</th>
                            <th>Average Cost</th>
                            <th>Lowest Cost</th>
                            <th>Highest Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo "$" . number_format($totalCost, 2); ?></td>
                            <td><?php echo "$" . number_format($averageCost, 2); ?></td>
                            <td><?php echo "$" . number_format($lowestCost, 2); ?></td>
                            <td><?php echo "$" . number_format($highestCost, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <!-- Countdown Timer -->
        <div class="countdown-timer">
            <h2>Countdown Timer Til Next Maintenance</h2>
            <div id="countdown"></div>
        </div>

<!-- Timeline -->
<div class="timeline">
    <div class="timeline__wrap">
        <div class="timeline__items">
            <?php
            foreach ($rows as $row) {
            ?>
                <div class="timeline__item">
                    <div class="timeline__content">
                        <h2><?php echo $row["ServiceDate"]; ?></h2>
                        <p><?php echo $row["ServiceType"]; ?></p>
                        <p><?php echo $row["Cost"]; ?></p>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>
    </div>
</div>

        <!-- Car Form -->
        <div class="form-box box">
            <form action="home.php" method="post">
                <label for="make">Make:</label>
                <input type="text" id="make" name="make" required>
                <label for="model">Model:</label>
                <input type="text" id="model" name="model" required>
                <label for="year">Year:</label>
                <input type="number" id="year" name="year" required>
                <input type="submit" value="Add Car">
            </form>
        </div>

        <!-- Car List -->
        <div class="car-list">
            <h3>Your Cars:</h3>
            <ul>
                <?php
                $userid = $_SESSION['id'];
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
            </ul>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm"
        crossorigin="anonymous">
    </script>
<script>
    <?php if ($numRecords > 0): ?>
        // Fetch the soonest service date from PHP and convert it to milliseconds
        var soonestServiceDate = new Date("<?php echo $rows[0]['ServiceDate']; ?>").getTime();

        // Add 21 hours (in milliseconds) to the soonest service date
        soonestServiceDate += 21 * 60 * 60 * 1000; // 21 hours * 60 minutes * 60 seconds * 1000 milliseconds
    <?php else: ?>
        var soonestServiceDate = null; // No upcoming service date
    <?php endif; ?>

    // Update the countdown every 1 second
    var x = setInterval(function () {

        // Get the current date and time in milliseconds
        var now = new Date().getTime();

        if (soonestServiceDate !== null) {
            // Calculate the remaining time in milliseconds
            var distance = soonestServiceDate - now;

            // Ensure the distance is positive
            if (distance >= 0) {
                // Calculate days, hours, minutes, and seconds
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                // Convert hours to 12-hour format
                hours = hours % 12;
                hours = hours ? hours : 12; // Handle midnight

                // Determine AM/PM indicator
                var ampm = hours >= 12 ? '' : '';

                // Display the countdown
                document.getElementById("countdown").innerHTML = days + "d " + hours + "h " +
                    minutes + "m " + seconds + "s " + ampm;
            } else {
                // If the countdown is finished, display a message
                clearInterval(x);
                document.getElementById("countdown").innerHTML = "Next maintenance is due!";
            }
        } else {
            // No upcoming service date
            document.getElementById("countdown").innerHTML = "No upcoming maintenance";
        }
    }, 1000);
</script>


<script>
    $(document).ready(function () {
        jQuery('.timeline').timeline({
            mode: 'horizontal',
            visibleItems: 4
        });
    });
</script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>
