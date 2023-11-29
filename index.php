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

// Function to insert a new student into the database
function addStudent($conn, $registrationNumber, $name, $grade, $classroomId) {
    $registrationNumber = mysqli_real_escape_string($conn, $registrationNumber);
    $name = mysqli_real_escape_string($conn, $name);
    $grade = floatval($grade);

    $sql = "INSERT INTO students (registration_number, name, grade, classroom_id)
            VALUES ('$registrationNumber', '$name', $grade, $classroomId)";

    if (mysqli_query($conn, $sql)) {
        return true;
    } else {
        // Error handling: Display the SQL error message
        echo "Error: " . mysqli_error($conn);
        return false;
    }
}

// Function to retrieve all students from the database
function getAllStudents($conn) {
    $students = array();
    $sql = "SELECT students.*, classrooms.name AS classroom_name
            FROM students
            LEFT JOIN classrooms ON students.classroom_id = classrooms.id";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $students[] = $row;
        }
    }

    return $students;
}


// Function to update a student in the database
function updateStudent($conn, $registrationNumber, $name, $grade, $classroomId) {
    $registrationNumber = mysqli_real_escape_string($conn, $registrationNumber);
    $name = mysqli_real_escape_string($conn, $name);
    $grade = floatval($grade);

    $sql = "UPDATE students 
            SET name = '$name', grade = $grade, classroom_id = $classroomId
            WHERE registration_number = '$registrationNumber'";

    if (mysqli_query($conn, $sql)) {
        return true;
    } else {
        // Error handling: Display the SQL error message
        echo "Error: " . mysqli_error($conn);
        return false;
    }
}


// Function to retrieve all classrooms from the database
function getAllClassrooms($conn) {
    $classrooms = array();
    $sql = "SELECT * FROM classrooms";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $classrooms[] = $row;
        }
    }

    return $classrooms;
}

// Function to delete a student from the database
function deleteStudent($conn, $registrationNumber) {
    $registrationNumber = mysqli_real_escape_string($conn, $registrationNumber);
    $sql = "DELETE FROM students WHERE registration_number = '$registrationNumber'";
    return mysqli_query($conn, $sql);
}

$errors = [];

// Handle adding a new student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registration_number'])) {
    $registrationNumber = filter_input(INPUT_POST, 'registration_number', FILTER_SANITIZE_STRING);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $grade = filter_input(INPUT_POST, 'grade', FILTER_VALIDATE_FLOAT);
    $classroomId = filter_input(INPUT_POST, 'classroom', FILTER_VALIDATE_INT);

    if (empty($registrationNumber) || empty($name) || $grade === false || $classroomId === false) {
        $errors[] = "Error: All fields are required and grade must be a number!";
    } else {
        if (addStudent($conn, $registrationNumber, $name, $grade, $classroomId)) {
            echo "<div class=\"success-message\">Student added successfully!</div>";
        } else {
            echo "Error: Failed to add student data!";
        }
    }
}

// Handle updating a student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_registration_number'])) {
    $registrationNumber = filter_input(INPUT_POST, 'edit_registration_number', FILTER_SANITIZE_STRING);
    $name = filter_input(INPUT_POST, 'edit_name', FILTER_SANITIZE_STRING);
    $grade = filter_input(INPUT_POST, 'edit_grade', FILTER_VALIDATE_FLOAT);
    $classroomId = filter_input(INPUT_POST, 'edit_classroom', FILTER_VALIDATE_INT);

    if (empty($registrationNumber) || empty($name) || $grade === false || $classroomId === false) {
        $errors[] = "Error: All fields are required and grade must be a number!";
    } else {
        if (updateStudent($conn, $registrationNumber, $name, $grade, $classroomId)) {
            echo "<div class=\"success-message\">Student updated successfully!</div>";
        } else {
            echo "Error: Failed to update student data!";
        }
    }
}

// Handle deleting a student
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['registration_number'])) {
    $registrationNumber = filter_input(INPUT_GET, 'registration_number', FILTER_SANITIZE_STRING);
    if (deleteStudent($conn, $registrationNumber)) {
        echo "<div class=\"success-message\">Student deleted successfully!</div>";
    } else {
        echo "Error: Failed to delete student data!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Management System</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<h1>Student Management System</h1>

<!-- Logout button -->
<form method="post" action="login.php" class="logout-button">
    <button type="submit" class="button">Logout</button>
</form>

<!-- Student form -->
<form method="post" action="index.php">
    <label for="registration_number">Registration Number:</label>
    <input type="text" id="registration_number" name="registration_number" required>

    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required>

    <label for="grade">Grade:</label>
    <input type="number" id="grade" name="grade" min="0" max="10" required>

    <label for="classroom">Classroom:</label>
    <select id="classroom" name="classroom" required>
        <?php
        // Retrieve and display existing classrooms from the database
        $classrooms = getAllClassrooms($conn);
        foreach ($classrooms as $classroom) {
            $classroomId = $classroom['id'];
            $classroomName = $classroom['name'];
            echo "<option value=\"$classroomId\">$classroomName</option>";
        }
        ?>
    </select>

    <button type="submit" class="button">Add Student</button>
</form>

<!-- Student table -->
<table>
    <tr>
        <th>Registration Number</th>
        <th>Name</th>
        <th>Grade</th>
        <th>Classroom</th>
        <th>Actions</th>
    </tr>
    <?php
    // Retrieve and display existing students from the database
    $students = getAllStudents($conn);

    foreach ($students as $student) {
        $registrationNumber = $student['registration_number'];
        $name = $student['name'];
        $grade = $student['grade'];
        $classroomName = $student['classroom_name'];
        echo "<tr>";
        echo "<td>$registrationNumber</td>";
        echo "<td>$name</td>";
        echo "<td>$grade</td>";
        echo "<td>$classroomName</td>";
        echo "<td>
                <a href=\"javascript:void(0);\" onclick=\"editStudent('$registrationNumber', '$name', '$grade', '$classroomId')\" class=\"button\">Edit</a>
                <a href=\"index.php?action=delete&registration_number=$registrationNumber\" class=\"button\">Delete</a>
            </td>";
        echo "</tr>";
    }
    ?>
</table>
<!-- Classroom managment button -->
<form method="post" action="classrooms.php">
    <button type="submit" name="classroom_management" class="button">Classroom Management</button>
</form>
<!-- Classroom report button -->
<form method="post" action="classroom_report.php">
    <button type="submit" name="classroom_report" class="button">Classroom report</button>
</form>

<!-- Edit student form -->
<div id="edit-form-container" style="display: none;">
    <form id="edit-form" method="post" action="index.php" onsubmit="return validateEditForm()">
        <h2>Edit Student</h2>
        <input type="hidden" id="edit_registration_number" name="edit_registration_number">
        <label for="edit_name">Name:</label>
        <input type="text" id="edit_name" name="edit_name" required>
        <label for="edit_grade">Grade:</label>
        <input type="number" id="edit_grade" name="edit_grade" min="0" max="10" required>
        <label for="edit_classroom">Classroom:</label>
        <select id="edit_classroom" name="edit_classroom" required>
            <?php
            // Retrieve and display existing classrooms from the database
            $classrooms = getAllClassrooms($conn);
            foreach ($classrooms as $classroom) {
                $classroomId = $classroom['id'];
                $classroomName = $classroom['name'];
                echo "<option value=\"$classroomId\">$classroomName</option>";
            }
            ?>
        </select>
        <button type="submit" class="button">Save</button>
        <button type="button" onclick="cancelEdit()" class="button">Cancel</button>
    </form>
</div>

<!-- JavaScript functions for editing students -->
<script>
    function editStudent(registrationNumber, name, grade, classroomId) {
        document.getElementById("edit_registration_number").value = registrationNumber;
        document.getElementById("edit_name").value = name;
        document.getElementById("edit_grade").value = grade;
        document.getElementById("edit_classroom").value = classroomId;
        document.getElementById("edit-form-container").style.display = "block";
    }

    function cancelEdit() {
        document.getElementById("edit_registration_number").value = "";
        document.getElementById("edit_name").value = "";
        document.getElementById("edit_grade").value = "";
        document.getElementById("edit_classroom").value = "";
        document.getElementById("edit-form-container").style.display = "none";
    }

    function validateEditForm() {
        var name = document.getElementById("edit_name").value;
        var grade = document.getElementById("edit_grade").value;
        var classroom = document.getElementById("edit_classroom").value;

        if (name === "" || grade === "" || classroom === "") {
            alert("Error: All fields are required!");
            return false;
        }

        if (isNaN(grade) || grade < 0 || grade > 10) {
            alert("Error: Invalid grade value!");
            return false;
        }

        return true;
    }
</script>

</body>
</html>
