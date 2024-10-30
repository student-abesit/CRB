<?php
// Connection to login_database
$loginServer = "localhost";
$loginUsername = "root";
$loginPassword = "";
$loginDbname = "login_database";

// Create connection for login database
$loginConn = new mysqli($loginServer, $loginUsername, $loginPassword, $loginDbname);

// Check connection for login database
if ($loginConn->connect_error) {
    die("Connection failed to login database: " . $loginConn->connect_error);
}

// Handle booking deletion only when the delete parameter is set in the URL
if (isset($_GET['delete'])) {
    $NameToDelete = $loginConn->real_escape_string($_GET['delete']);
    $deleteQuery = "DELETE FROM login_database_table WHERE Name = '$NameToDelete'";
    if ($loginConn->query($deleteQuery) === TRUE) {
        echo "<script>alert('Booking deleted successfully.');</script>";
    } else {
        echo "<script>alert('Error deleting booking: " . $loginConn->error . "');</script>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Filter bookings based on search criteria
$searchQuery = "SELECT * FROM login_database_table";
$conditions = [];

if (!empty($_POST['search_name'])) {
    $searchName = $loginConn->real_escape_string(trim($_POST['search_name']));
    $conditions[] = "Name LIKE '%$searchName%'";
}

if (!empty($_POST['search_room'])) {
    $searchRoom = $loginConn->real_escape_string(trim($_POST['search_room']));
    $conditions[] = "Name_of_Conference_Room = '$searchRoom'";
}

if (count($conditions) > 0) {
    $searchQuery .= " WHERE " . implode(' AND ', $conditions);
}

$result = $loginConn->query($searchQuery);

// Fetch booking counts for each conference room
$roomBookingCounts = [];
$roomQuery = "SELECT Name_of_Conference_Room, COUNT(*) AS count FROM login_database_table GROUP BY Name_of_Conference_Room";
$roomResult = $loginConn->query($roomQuery);

if ($roomResult->num_rows > 0) {
    while ($row = $roomResult->fetch_assoc()) {
        $roomBookingCounts[$row['Name_of_Conference_Room']] = $row['count'];
    }
} else {
    $roomBookingCounts = []; // No bookings found
}

// Convert PHP array to JSON for use in JavaScript
$roomNames = json_encode(array_keys($roomBookingCounts));
$bookingCounts = json_encode(array_values($roomBookingCounts));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Conference Hall Booking</title>
    <link rel="stylesheet" href="admin.css"> <!-- CSS file link -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js -->
</head>
<body>
    <nav class="navbar">
        <img src="TRAI_logo_white.png" alt="Logo" class="navbar-logo">
    </nav>

    <div class="container">
        <h1>Admin Panel - Conference Hall Bookings</h1>
        <form method="post" action="">
            <label for="search_name">Search by Name:</label>
            <input type="text" id="search_name" name="search_name" placeholder="Enter name">

            <label for="search_room">Search by Room:</label>
            <input type="text" id="search_room" name="search_room" placeholder="Enter room name">

            <button type="submit">Search</button>
        </form>

        <h2>Booking List</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Division</th>
                    <th>Event Name</th>
                    <th>Phone</th>
                    <th>Number of Attendees</th>
                    <th>Food Requests</th>
                    <th>Audio/Visual</th>
                    <th>Booking Date</th> <!-- Updated column for Booking Date -->
                    <th>Time Slot</th> <!-- Updated column for Time Slot -->
                    <th>Conference Room</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["Name"] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($row["Email"] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($row["Division"] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($row["Event_Name"] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($row["Phone"] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($row["Number_of_Attendees"] ?? '0') . "</td>";
                        echo "<td>" . htmlspecialchars($row["Food_Requests"] ?? 'None') . "</td>";
                        echo "<td>" . htmlspecialchars($row["Audio_Visual"] ?? 'No') . "</td>";
                        echo "<td>" . htmlspecialchars($row["Date"] ?? 'N/A') . "</td>"; // Display Booking Date
                        echo "<td>" . htmlspecialchars($row["Time_Slot"] ?? 'N/A') . "</td>"; // Display Time Slot
                        echo "<td><a href='?delete=" . htmlspecialchars($row["Name"]) . "' onclick=\"return confirm('Are you sure you want to delete this booking?');\">Delete</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='12'>No bookings found.</td></tr>"; // Adjusted colspan
                }
                ?>
            </tbody>
        </table>

        <h2>Booking Counts by Conference Room</h2>
        <canvas id="roomChart" width="400" height="200"></canvas>
    </div>

    <script>
        const roomNames = <?php echo $roomNames; ?>;
        const bookingCounts = <?php echo $bookingCounts; ?>;

        const ctx = document.getElementById('roomChart').getContext('2d');
        const roomChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: roomNames,
                datasets: [{
                    label: 'Bookings per Room',
                    data: bookingCounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
