<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "aulog";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//FUNCTIONS
function addStudent($studentId, $firstName, $lastName, $email) {
    // Connect to the database
    $conn = new mysqli($servername, $username, $password, $database);

    // Prepare the INSERT statement
    $stmt = $conn->prepare("INSERT INTO student (student_id, first_name, last_name, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $studentId, $firstName, $lastName, $email);

    // Execute the query
    $stmt->execute();

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}

function startChargingSession($studentId) {
    // Connect to the database
    $conn = new mysqli($servername, $username, $password, $database);

    // Get the current timestamp
    $startTime = date("Y-m-d H:i:s");

    // Prepare the INSERT statement
    $stmt = $conn->prepare("INSERT INTO charging_session (student_id, start_time) VALUES (?, ?)");
    $stmt->bind_param("is", $studentId, $startTime);

    // Execute the query
    $stmt->execute();

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}

function endChargingSession($sessionId) {
    // Connect to the database
    $conn = new mysqli($servername, $username, $password, $database);

    // Get the current timestamp
    $endTime = date("Y-m-d H:i:s");

    // Prepare the UPDATE statement
    $stmt = $conn->prepare("UPDATE charging_session SET end_time = ? WHERE session_id = ?");
    $stmt->bind_param("si", $endTime, $sessionId);

    // Execute the query
    $stmt->execute();

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}

function getChargingHistory($studentId) {
    // Connect to the database
    $conn = new mysqli($servername, $username, $password, $database);

    // Prepare the SELECT statement
    $stmt = $conn->prepare("SELECT c.start_time, c.end_time, h.device_name, h.device_type 
                           FROM charging_session AS c
                           INNER JOIN charging_history AS h ON c.session_id = h.session_id
                           WHERE c.student_id = ?");
    $stmt->bind_param("i", $studentId);

    // Execute the query
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();

    // Fetch and return the rows
    $rows = $result->fetch_all(MYSQLI_ASSOC);

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    return $rows;
}



?>
