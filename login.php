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

// Process login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    // Validate login credentials
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['name'] = $user['name'];
            
            // Redirect based on user type
            if ($user['user_type'] == 'customer') {
                header("Location: user_dashboard.php");
            } else {
                header("Location: provider_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid password";
        }
    } else {
        $error = "Username not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Appointment Booking System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Login</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="register.php">Register</a>
            </nav>
        </header>
        
        <main>
            <section class="form-container">
                <?php if (!empty($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn">Login</button>
                </form>
                
                <p class="form-footer">Don't have an account? <a href="register.php">Register here</a></p>
            </section>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Appointment Booking System</p>
        </footer>
    </div>
</body>
</html>
