<?php
session_start();
require_once 'db/connection.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on user type
    if ($_SESSION['user_type'] == 'customer') {
        header("Location: user_dashboard.php");
    } else {
        header("Location: provider_dashboard.php");
    }
    exit();
}

$error = "";
$success = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $user_type = $_POST['user_type'];
    
    // Validate inputs
    if (empty($username) || empty($password) || empty($email) || empty($name)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if username already exists
        $check_query = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "Username or email already exists";
        } else {
            // Hash password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $insert_query = "INSERT INTO users (username, password, email, user_type, name) 
                            VALUES ('$username', '$hashed_password', '$email', '$user_type', '$name')";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Registration failed: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Appointment Booking System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Register</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="login.php">Login</a>
            </nav>
        </header>
        
        <main>
            <section class="form-container">
                <?php if (!empty($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="user_type">Register as</label>
                        <select id="user_type" name="user_type" required>
                            <option value="customer">Customer</option>
                            <option value="provider">Service Provider</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Register</button>
                </form>
                
                <p class="form-footer">Already have an account? <a href="login.php">Login here</a></p>
            </section>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Appointment Booking System</p>
        </footer>
    </div>
</body>
</html>
