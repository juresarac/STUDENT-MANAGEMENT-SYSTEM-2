<?php
session_start();

$db_host = 'localhost';  // Replace with your MySQL server host
$db_username = 'root';  // Replace with your MySQL username
$db_password = '';      // Replace with your MySQL password
$db_name = 'student_management';  // Replace with your database name

// Create a connection to the MySQL database
$conn = mysqli_connect($db_host, $db_username, $db_password, $db_name);

// Check if the connection was successful
if (!$conn) {
    die('Connection failed: ' . mysqli_connect_error());
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        // Login logic
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

        // Validate username
        if (empty($username)) {
            $errors['username'] = 'Username is required';
        }

        // Validate password
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }

        if (empty($errors)) {
            // Check username in the database
            $sql = "SELECT * FROM users WHERE username = '$username'";
            $result = mysqli_query($conn, $sql);

            if ($result && mysqli_num_rows($result) === 1) {
                $user = mysqli_fetch_assoc($result);
                // Verify the password using password_verify
                if (password_verify($password, $user['password'])) {
                    // Login successful, set a session variable
                    $_SESSION['loggedin'] = true;
                    header('Location: index.php');
                    exit();
                } else {
                    $errors['password'] = 'Invalid password';
                }
            } else {
                $errors['username'] = 'Invalid username';
            }
        }
    } elseif (isset($_POST['register'])) {
        // Registration logic
        $newUsername = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING); // Use the same name 'username' for input field
        $newPassword = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING); // Use the same name 'password' for input field

        // Validate new username
        if (empty($newUsername)) {
            $errors['username'] = 'New username is required';
        }

        // Validate new password
        if (empty($newPassword)) {
            $errors['password'] = 'New password is required';
        }

        if (empty($errors)) {
            // Check if the username already exists in the database
            $checkExistingUsername = "SELECT * FROM users WHERE username = '$newUsername'";
            $existingResult = mysqli_query($conn, $checkExistingUsername);

            if ($existingResult && mysqli_num_rows($existingResult) > 0) {
                $errors['username'] = 'Username already exists';
            } else {
                // Hash the password before storing it
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                // Insert the new user into the database
                $insertNewUser = "INSERT INTO users (username, password) VALUES ('$newUsername', '$hashedPassword')";
                if (mysqli_query($conn, $insertNewUser)) {
                    $_SESSION['loggedin'] = true;
                    header('Location: index.php');
                    exit();
                } else {
                    $errors['registration'] = 'Registration failed';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login & Register</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<h1>Login & Register</h1>

<h2>Login / Register</h2>
<form method="post" action="login.php">
    <div class="form-group">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
        <?php if (isset($errors['username'])): ?>
            <p class="error"><?php echo $errors['username']; ?></p>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <?php if (isset($errors['password'])): ?>
            <p class="error"><?php echo $errors['password']; ?></p>
        <?php endif; ?>
    </div>

    <button type="submit" name="login" class="button">Login</button>

    <!-- Use a different name 'register' for the registration button -->
    <button type="submit" name="register" class="button">Register</button>
</form>
</body>
</html>
