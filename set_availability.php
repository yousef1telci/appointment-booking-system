<?php
session_start();
require_once 'db/connection.php';

// Check if user is logged in and is a service provider
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'provider') {
    header("Location: login.php");
    exit();
}

$provider_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date = mysqli_real_escape_string($conn, $_POST['date']);
    $time_start = mysqli_real_escape_string($conn, $_POST['time_start']);
    $time_end = mysqli_real_escape_string($conn, $_POST['time_end']);
    
    // Validate inputs
    if (empty($date) || empty($time_start) || empty($time_end)) {
        $error = "All fields are required";
    } elseif (strtotime($time_start) >= strtotime($time_end)) {
        $error = "End time must be after start time";
    } elseif (strtotime($date) < strtotime(date('Y-m-d'))) {
        $error = "Date cannot be in the past";
    } else {
        // Check for overlapping time slots
        $check_query = "SELECT * FROM availability 
                        WHERE provider_id = $provider_id 
                        AND date = '$date' 
                        AND ((time_start <= '$time_start' AND time_end > '$time_start') 
                            OR (time_start < '$time_end' AND time_end >= '$time_end') 
                            OR (time_start >= '$time_start' AND time_end <= '$time_end'))";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error = "This time slot overlaps with an existing one";
        } else {
            // Insert new availability
            $insert_query = "INSERT INTO availability (provider_id, date, time_start, time_end) 
                            VALUES ($provider_id, '$date', '$time_start', '$time_end')";
            
            if (mysqli_query($conn, $insert_query)) {
                $success = "Availability slot added successfully!";
            } else {
                $error = "Failed to add availability: " . mysqli_error($conn);
            }
        }
    }
}

// Get provider's current availability
$availability_query = "SELECT id, date, time_start, time_end, is_booked 
                      FROM availability 
                      WHERE provider_id = $provider_id 
                      ORDER BY date ASC, time_start ASC";
$availability_result = mysqli_query($conn, $availability_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Availability - Appointment Booking System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Set Your Availability</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="provider_dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
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
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="time_start">Start Time</label>
                        <input type="time" id="time_start" name="time_start" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="time_end">End Time</label>
                        <input type="time" id="time_end" name="time_end" required>
                    </div>
                    
                    <button type="submit" class="btn">Add Availability</button>
                </form>
            </section>
            
            <section class="availability">
                <h3>Your Current Availability</h3>
                
                <?php if (mysqli_num_rows($availability_result) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($availability_result)): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($row['time_start'])) . ' - ' . date('h:i A', strtotime($row['time_end'])); ?></td>
                                    <td><?php echo $row['is_booked'] ? '<span class="booked">Booked</span>' : '<span class="available">Available</span>'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>You haven't set any availability slots yet.</p>
                <?php endif; ?>
            </section>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Appointment Booking System</p>
        </footer>
    </div>
</body>
</html>