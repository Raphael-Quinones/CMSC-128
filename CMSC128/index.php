<?php


//Test functions
$student_id = 14;
$first_name = "raphael";
$last_name = "Quinones";
$email = "rpquinones@up.edu.ph";

function addStudent($studentId, $firstName, $lastName, $email) {
    // Connect to the database
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
    echo "Connected successfully";

    // Prepare the INSERT statement
    $stmt = $conn->prepare("INSERT INTO student (student_id, first_name, last_name, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $studentId, $firstName, $lastName, $email);

    // Execute the query
    $stmt->execute();

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    echo "addStudent finished executing";
}

//test
echo "testste";
addStudent($student_id, $first_name, $last_name, $email);

echo "added student";
?>
