<?php
session_start();
require_once 'db/connection.php';

// Check if user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'customer') {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['user_id'];
$error = "";
$success = "";

// Process booking form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_appointment'])) {
    $availability_id = mysqli_real_escape_string($conn, $_POST['availability_id']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);
    
    // Check if slot is still available
    $check_query = "SELECT * FROM availability WHERE id = $availability_id AND is_booked = 0";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $error = "This time slot is no longer available";
    } else {
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Insert appointment
            $insert_query = "INSERT INTO appointments (availability_id, customer_id, notes) 
                            VALUES ($availability_id, $customer_id, '$notes')";
            $insert_result = mysqli_query($conn, $insert_query);
            
            // Update availability status
            $update_query = "UPDATE availability SET is_booked = 1 WHERE id = $availability_id";
            $update_result = mysqli_query($conn, $update_query);
            
            if ($insert_result && $update_result) {
                mysqli_commit($conn);
                $success = "Appointment booked successfully!";
            } else {
                throw new Exception("Booking failed");
            }
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = "Failed to book appointment: " . $e->getMessage();
        }
    }
}

// Get list of service providers
$providers_query = "SELECT id, name FROM users WHERE user_type = 'provider' ORDER BY name ASC";
$providers_result = mysqli_query($conn, $providers_query);

// Get available time slots if provider is selected
$available_slots = array();
if (isset($_GET['provider_id']) && !empty($_GET['provider_id'])) {
    $provider_id = mysqli_real_escape_string($conn, $_GET['provider_id']);
    
    $slots_query = "SELECT a.id, a.date, a.time_start, a.time_end, u.name as provider_name 
                   FROM availability a 
                   JOIN users u ON a.provider_id = u.id 
                   WHERE a.provider_id = $provider_id 
                   AND a.is_booked = 0 
                   AND a.date >= CURDATE() 
                   ORDER BY a.date ASC, a.time_start ASC";
    $slots_result = mysqli_query($conn, $slots_query);
    
    while ($row = mysqli_fetch_assoc($slots_result)) {
        $available_slots[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment - Appointment Booking System</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Book an Appointment</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="user_dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>
        
        <main>
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <section class="select-provider">
                <h3>Select a Service Provider</h3>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
                    <div class="form-group">
                        <label for="provider_id">Provider</label>
                        <select id="provider_id" name="provider_id" required>
                            <option value="">-- Select Provider --</option>
                            <?php while ($provider = mysqli_fetch_assoc($providers_result)): ?>
                                <option value="<?php echo $provider['id']; ?>" <?php echo (isset($_GET['provider_id']) && $_GET['provider_id'] == $provider['id']) ? 'selected' : ''; ?>>
                                    <?php echo $provider['name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">View Available Slots</button>
                </form>
            </section>
            
            <?php if (!empty($available_slots)): ?>
                <section class="available-slots">
                    <h3>Available Time Slots</h3>
                    
                    <?php if (count($available_slots) > 0): ?>
                        <div class="slots-grid">
                            <?php foreach ($available_slots as $slot): ?>
                                <div class="slot-card">
                                    <div class="slot-details">
                                        <h4><?php echo $slot['provider_name']; ?></h4>
                                        <p>Date: <?php echo date('M d, Y', strtotime($slot['date'])); ?></p>
                                        <p>Time: <?php echo date('h:i A', strtotime($slot['time_start'])) . ' - ' . date('h:i A', strtotime($slot['time_end'])); ?></p>
                                    </div>
                                    
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                        <input type="hidden" name="availability_id" value="<?php echo $slot['id']; ?>">
                                        <div class="form-group">
                                            <label for="notes-<?php echo $slot['id']; ?>">Notes (Optional)</label>
                                            <textarea id="notes-<?php echo $slot['id']; ?>" name="notes" rows="2"></textarea>
                                        </div>
                                        <button type="submit" name="book_appointment" class="btn btn-book">Book Now</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No available time slots for this provider. Please select another provider.</p>
                    <?php endif; ?>
                </section>
            <?php elseif (isset($_GET['provider_id']) && !empty($_GET['provider_id'])): ?>
                <p>No available time slots for this provider. Please select another provider.</p>
            <?php endif; ?>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Appointment Booking System</p>
        </footer>
    </div>
</body>
</html>
