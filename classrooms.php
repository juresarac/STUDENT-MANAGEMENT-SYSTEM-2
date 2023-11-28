<!-- classrooms.php -->
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

// Function to retrieve all classrooms
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

// Handle adding a new classroom
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_classroom'])) {
    $classroomName = filter_input(INPUT_POST, 'classroom_name', FILTER_SANITIZE_STRING);
    if (!empty($classroomName)) {
        // Insert the new classroom into the database
        $sql = "INSERT INTO classrooms (name) VALUES ('$classroomName')";
        if (mysqli_query($conn, $sql)) {
            // Redirect to the same page after adding
            header("Location: classrooms.php");
            exit;
        } else {
            // Handle database error
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        // Handle validation errors
    }
}

// Handle editing a classroom
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_classroom'])) {
    $classroomID = filter_input(INPUT_POST, 'edit_classroom_id', FILTER_VALIDATE_INT);
    $classroomName = filter_input(INPUT_POST, 'edit_classroom_name', FILTER_SANITIZE_STRING);
    if (!empty($classroomID) && !empty($classroomName)) {
        // Update the classroom in the database
        $sql = "UPDATE classrooms SET name='$classroomName' WHERE id=$classroomID";
        if (mysqli_query($conn, $sql)) {
            // Redirect to the same page after editing
            header("Location: classrooms.php");
            exit;
        } else {
            // Handle database error
            echo "Error: " . mysqli_error($conn);
        }
    } else {
        // Handle validation errors
    }
}

// Handle deleting a classroom
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['classroom_id'])) {
    $classroomID = filter_input(INPUT_GET, 'classroom_id', FILTER_VALIDATE_INT);
    if (!empty($classroomID)) {
        // Delete the classroom from the database
        $sql = "DELETE FROM classrooms WHERE id=$classroomID";
        if (mysqli_query($conn, $sql)) {
            // Redirect to the same page after deleting
            header("Location: classrooms.php");
            exit;
        } else {
            // Handle database error
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Classroom Management</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        /* Add some spacing between classroom entries */
        ul.classroom-list {
            list-style-type: none;
            padding: 0;
        }
        ul.classroom-list li {
            margin-bottom: 20px; /* Adjust the margin as needed */
        }
    </style>
</head>
<body>
<h1>Classroom Management</h1>

<!-- Add Classroom Form -->
<h2>Add Classroom</h2>
<form method="post" action="classrooms.php">
    <div class="form-group">
        <label for="classroom_name">Classroom Name:</label>
        <input type="text" id="classroom_name" name="classroom_name" required>
    </div>
    <button type="submit" name="add_classroom" class="button">Add Classroom</button>
</form>

<!-- List of Classrooms -->
<h2>Classrooms</h2>
<ul class="classroom-list">
    <!-- Display the list of classrooms here -->
    <?php
    $classrooms = getAllClassrooms($conn);
    foreach ($classrooms as $classroom) {
        $classroomID = $classroom['id'];
        $classroomName = $classroom['name'];
        echo "<li>
                    $classroomName
                    <a href=\"javascript:void(0);\" onclick=\"editClassroom($classroomID, '$classroomName')\" class=\"button\">Edit</a>
                    <a href=\"classrooms.php?action=delete&classroom_id=$classroomID\" class=\"button\">Delete</a>
                  </li>";
    }
    ?>
</ul>
<!-- Index button -->
<form method="post" action="index.php">
    <button type="submit" name="student_management" class="button">Student Management</button>
</form>

<!-- Edit Classroom Form (hidden by default) -->
<div id="edit-classroom-form" style="display: none;">
    <h2>Edit Classroom</h2>
    <form method="post" action="classrooms.php">
        <input type="hidden" id="edit_classroom_id" name="edit_classroom_id">
        <div class="form-group">
            <label for="edit_classroom_name">Classroom Name:</label>
            <input type="text" id="edit_classroom_name" name="edit_classroom_name" required>
        </div>
        <button type="submit" name="edit_classroom" class="button">Save</button>
        <button type="button" onclick="cancelEdit()" class="button">Cancel</button>
    </form>
</div>

<!-- JavaScript functions for editing classrooms -->
<script>
    function editClassroom(classroomID, classroomName) {
        document.getElementById("edit_classroom_id").value = classroomID;
        document.getElementById("edit_classroom_name").value = classroomName;
        document.getElementById("edit-classroom-form").style.display = "block";
    }

    function cancelEdit() {
        document.getElementById("edit_classroom_id").value = "";
        document.getElementById("edit_classroom_name").value = "";
        document.getElementById("edit-classroom-form").style.display = "none";
    }
</script>

</body>
</html>
