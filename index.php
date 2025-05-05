<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Booking System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Online Appointment Booking System</h1>
            <nav>
                <a href="browse_providers.php">Browse Providers</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_type'] == 'customer'): ?>
                        <a href="user_dashboard.php">Dashboard</a>
                    <?php else: ?>
                        <a href="provider_dashboard.php">Dashboard</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </header>
        
        <main>
            <section class="welcome">
                <h2>Welcome to our Appointment Booking System</h2>
                <p>A simple and efficient way to book appointments with service providers.</p>
                
                <?php if (!isset($_SESSION['user_id'])): ?>
                    <div class="cta">
                        <a href="login.php" class="btn">Login</a>
                        <a href="register.php" class="btn">Register Now</a>
                    </div>
                <?php endif; ?>
            </section>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <section class="quick-access">
                    <?php if ($_SESSION['user_type'] == 'customer'): ?>
                        <a href="book_appointment.php" class="btn">Book an Appointment</a>
                    <?php else: ?>
                        <a href="set_availability.php" class="btn">Set Your Availability</a>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Appointment Booking System</p>
        </footer>
    </div>
</body>
</html>