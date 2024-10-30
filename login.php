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

// Create connection for conference booking database (now pointing to the same database)
$conferenceConn = new mysqli($loginServer, $loginUsername, $loginPassword, $loginDbname); // Changed to $loginDbname

// Check connection for conference booking database
if ($conferenceConn->connect_error) {
    die("Connection failed to conference booking database: " . $conferenceConn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and clean form data
    $Name = $loginConn->real_escape_string(trim($_POST['name']));
    $Email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

    // Validate email format
    if (!filter_var($Email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    $Division = $loginConn->real_escape_string(trim($_POST['division']));
    $Event_Name = $loginConn->real_escape_string(trim($_POST['event-name']));
    $Phone = $loginConn->real_escape_string(trim($_POST['phone']));
    $Number_of_Attendees = (int) $_POST['number-of-attendees']; // Cast to integer for security
    $Food_Requests = $loginConn->real_escape_string(trim($_POST['food-requests']));
    $Audio_Visual = $loginConn->real_escape_string(trim($_POST['audio-visual']));
    $Time_Slot = $loginConn->real_escape_string($_POST['time_slot']);
    $Name_of_Conference_Room = $loginConn->real_escape_string(trim($_POST['Name_of_Conference_Room']));
    $Date = $loginConn->real_escape_string(trim($_POST['date'])); // New date field

    // Check for conflicts with the selected date and time slot
    $conflictQuery = "SELECT * FROM login_database_table 
    WHERE Name_of_Conference_Room = '$Name_of_Conference_Room' 
    AND `Time_Slot` = '$Time_Slot' 
    AND `Date` = '$Date'"; // Updated to include date

    $conflictResult = $loginConn->query($conflictQuery);

    if ($conflictResult->num_rows > 0) {
        echo "<script>alert('The selected conference room is already booked during the chosen date and time slot. Please check the conference room list to choose another room or change your timings.');</script>";
    } else {
        // If no conflict, proceed to book
        $sql = "INSERT INTO login_database_table (Name, Email, Division, Event_Name, Phone, Number_of_Attendees, Food_Requests, Audio_Visual, `Time_Slot`, `Name_of_Conference_Room`, `Date`) 
                VALUES ('$Name', '$Email', '$Division', '$Event_Name', '$Phone', $Number_of_Attendees, '$Food_Requests', '$Audio_Visual', '$Time_Slot', '$Name_of_Conference_Room', '$Date')";
    
        if ($loginConn->query($sql) === TRUE) {
            echo "Booking submitted! Please wait for final approval from GA Section.";
        } else {
            echo "Error: " . $sql . "<br>" . $loginConn->error;
        }
    }
}
// SQL query for conference_rooms_list2 in login_database
$sql = "SELECT S_No, Conference_Meeting_Room, Room_No, Floor, Capacity FROM conference_rooms_list2";
$result = $conferenceConn->query($sql);

if (!$result) {
    die("Query failed: " . $conferenceConn->error); // Error handling
}

// Close the login database connection
$loginConn->close();
?>

<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conference Hall Booking</title>
    <link rel="stylesheet" href="login.css?v=1.1"> <!-- put if css not working ?v=1.1 -->
</head>

<body>
    <nav class="navbar">
        <img src="TRAI_logo_white.png" alt="Logo" class="navbar-logo">
    </nav>

    <div class="container">
        <h1>Conference Hall Booking Form</h1>
        <form action="login.php" method="post" id="bookingForm">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" placeholder="Enter Your Name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter Your TRAI Email ID" required>

            <label for="division">Division:</label>
            <input type="text" id="division" name="division" placeholder="Specify Your Division" required>

            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" placeholder="Enter Your Phone / Extension Number" required>

            <label for="event-name">Event Name:</label>
            <input type="text" id="event-name" name="event-name" placeholder="Specify Event Name" required>

            <label for="number-of-attendees">Number of Attendees:</label>
            <input type="number" id="number-of-attendees" name="number-of-attendees" min="1" required>

            <label for="food-requests">Food Requests:</label>
            <textarea id="food-requests" name="food-requests" rows="4"
                placeholder="Details of food required (e.g., Tea, Biscuit)"></textarea>

            <label for="audio-visual">Audio/Visual Equipment Needed:</label>
            <select id="audio-visual" name="audio-visual" required>
                <option value="" disabled selected>Select an option</option>
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>

            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required> <!-- New date input -->

            <label>Time Slot:</label>
            <div>
                <label><input type="radio" name="time_slot" value="10:00-11:00" required> 10:00 - 11:00</label><br>
                <label><input type="radio" name="time_slot" value="11:15-12:15"> 11:15 - 12:15</label><br>
                <label><input type="radio" name="time_slot" value="12:15-13:15"> 12:15 - 13:15</label><br>
                <label><input type="radio" name="time_slot" value="14:30-15:30"> 14:30 - 15:30</label><br>
                <label><input type="radio" name="time_slot" value="15:30-16:30"> 15:30 - 16:30</label><br>
                <label><input type="radio" name="time_slot" value="16:30-17:30"> 16:30 - 17:30</label>
            </div>

            <label for="Name_of_Conference_Room">Name of Conference Room:</label>
            <select id="Name_of_Conference_Room" name="Name_of_Conference_Room" required>
                <option value="">Select Conference Room</option>
                <option value="GANGA">GANGA</option>
                <option value="YAMUNA">YAMUNA</option>
                <option value="SARASWATI">SARASWATI</option>
                <option value="TAPTI">TAPTI</option>
                <option value="TRAINING ROOM">TRAINING ROOM</option>
                <option value="NARMADA">NARMADA</option>
                <option value="KRISHNA">KRISHNA</option>
                <option value="KAVERI">KAVERI</option>
                <option value="MAHANADI">MAHANADI</option>
                <option value="BHARHMAPUTRA">BHARHMAPUTRA</option>
                <option value="GODAVARI">GODAVARI</option>
            </select>

            <button type="submit">Book</button>
        </form>
    </div>

    <div class="container">
        <h2>Available Conference Rooms</h2>
        <table>
            <tr>
                <th>S.No</th>
                <th>Conference Meeting Room</th>
                <th>Room No</th>
                <th>Floor</th>
                <th>Capacity</th>
            </tr>
            <?php
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['S_No'] . "</td>";
                echo "<td>" . $row['Conference_Meeting_Room'] . "</td>";
                echo "<td>" . $row['Room_No'] . "</td>";
                echo "<td>" . $row['Floor'] . "</td>";
                echo "<td>" . $row['Capacity'] . "</td>";
                echo "</tr>";
            }
            ?>
        </table>
    </div>
</body>

</html>
