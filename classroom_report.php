<?php
// Initialize the session, if not already initialized
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is not logged in, then redirect to the login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Database configuration
$db_host = 'localhost';
$db_username = 'root';
$db_password = '';
$db_name = 'student_management';

// Create a connection to the MySQL database
$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);

// Check if the connection was successful
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

// Retrieve classroom and student information for classrooms with students
$sql = "SELECT classrooms.name AS classroom_name, students.registration_number, students.name AS student_name
        FROM classrooms
        LEFT JOIN students ON classrooms.id = students.classroom_id
        WHERE students.registration_number IS NOT NULL
        ORDER BY classrooms.name, students.name";

$result = mysqli_query($conn, $sql);

// Initialize variables for tracking classroom changes
$current_classroom = null;

?>

<!DOCTYPE html>
<html>
<head>
    <title>Classroom Report</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<h1>Classroom Report</h1>
<!-- Index button -->
<form method="post" action="index.php">
    <button type="submit" name="student_management" class="button">Student Management</button>
</form>

<!-- Classroom report table -->
<table>
    <tr>
        <th>Classroom</th>
        <th>Student Registration Number</th>
        <th>Student Name</th>
    </tr>
    <?php
    while ($row = mysqli_fetch_assoc($result)) {
        $classroom_name = $row['classroom_name'];
        $student_registration_number = $row['registration_number'];
        $student_name = $row['student_name'];

        // Check if the classroom has changed
        if ($current_classroom !== $classroom_name) {
            $current_classroom = $classroom_name;
        } else {
            $classroom_name = ''; // Clear the classroom name for subsequent rows in the same classroom
        }

        echo "<tr>";
        echo "<td>$classroom_name</td>";
        echo "<td>$student_registration_number</td>";
        echo "<td>$student_name</td>";
        echo "</tr>";
    }
    ?>
</table>

</body>
</html>
