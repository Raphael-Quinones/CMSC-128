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

    // Prepare the UPDATE statement to end the charging session
    $stmt = $conn->prepare("UPDATE charging_session SET end_time = ? WHERE session_id = ?");
    $stmt->bind_param("si", $endTime, $sessionId);

    // Execute the query to end the charging session
    $stmt->execute();

    // Get the student_id associated with the session_id
    $stmt = $conn->prepare("SELECT student_id FROM charging_session WHERE session_id = ?");
    $stmt->bind_param("i", $sessionId);
    $stmt->execute();
    $stmt->bind_result($studentId);
    $stmt->fetch();
    $stmt->close();

    // Calculate the total charge time for the student
    $stmt = $conn->prepare("SELECT SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) FROM charging_session WHERE student_id = ?");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $stmt->bind_result($totalChargeTime);
    $stmt->fetch();
    $stmt->close();

    // Update the total_charge_time in the student table
    $stmt = $conn->prepare("UPDATE student SET total_charge_time = ? WHERE student_id = ?");
    $stmt->bind_param("ii", $totalChargeTime, $studentId);
    $stmt->execute();
    $stmt->close();

    // Close the connection
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

function getStudentEmail($studentId) {
    // Connect to the database
    $conn = new mysqli($servername, $username, $password, $database);

    // Prepare the SELECT statement
    $stmt = $conn->prepare("SELECT email FROM student WHERE student_id = ?");
    $stmt->bind_param("i", $studentId);

    // Execute the query
    $stmt->execute();

    // Bind the result to a variable
    $stmt->bind_result($email);

    // Fetch the result
    $stmt->fetch();

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Return the student's email
    return $email;
}

function getRemainingTime($studentId) {
    // Connect to the database
    $conn = new mysqli($servername, $username, $password, $database);

    // Prepare the SELECT statement
    $stmt = $conn->prepare("SELECT total_charge_time FROM student WHERE student_id = ?");
    $stmt->bind_param("i", $studentId);

    // Execute the query
    $stmt->execute();

    // Bind the result to a variable
    $stmt->bind_result($totalChargeTime);

    // Fetch the result
    $stmt->fetch();

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Calculate remaining time based on total_charge_time logic
    $remainingTime = 120 - $totalChargeTime; // Assuming 120 minutes is the maximum charging time

    // Return the remaining time
    return $remainingTime;
}

function sendEmailToStudent($studentEmail, $remainingTime) {
    $apiKey = 'mailgun_api_key';
    $domain = 'mailgun_domain';
    $fromEmail = 'sender_email@example.com';
    $fromName = 'Library yowzzzz';

    $subject = 'Remaining Charging Time';
    $message = "Dear student,\n\nYou have $remainingTime minutes remaining for your charging session.\n\nBest regards,\nYour Library";

    // Prepare the email parameters
    $params = array(
        'from' => "$fromName <$fromEmail>",
        'to' => $studentEmail,
        'subject' => $subject,
        'text' => $message
    );

    // Send the email using Mailgun API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.mailgun.net/v3/$domain/messages");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "api:$apiKey");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}


?>
