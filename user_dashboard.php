<?php
session_start();
require_once 'db/connection.php';

// Check if user is logged in and is a customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'customer') {
    header("Location: login.php");
    exit();
}

// Get user's appointments
$user_id = $_SESSION['user_id'];
$query = "SELECT a.id, u.name as provider_name, v.date, v.time_start, v.time_end, 
          a.booking_date, a.notes, a.status 
          FROM appointments a 
          JOIN availability v ON a.availability_id = v.id 
          JOIN users u ON v.provider_id = u.id 
          WHERE a.customer_id = $user_id 
          ORDER BY v.date ASC, v.time_start ASC";
$result = mysqli_query($conn, $query);

// Get user's canceled appointments, if wanted to display separately
$canceled_query = "SELECT a.id, u.name as provider_name, v.date, v.time_start, v.time_end, 
                  a.booking_date, a.notes 
                  FROM appointments a 
                  JOIN availability v ON a.availability_id = v.id 
                  JOIN users u ON v.provider_id = u.id 
                  WHERE a.customer_id = $user_id AND a.status = 'rejected' 
                  ORDER BY v.date ASC, v.time_start ASC";
$canceled_result = mysqli_query($conn, $canceled_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Appointment Booking System</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .status-pending { color: #f39c12; font-weight: bold; }
        .status-accepted { color: #27ae60; font-weight: bold; }
        .status-rejected { color: #e74c3c; font-weight: bold; }
        .appointment-status {
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>User Dashboard</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="book_appointment.php">Book Appointment</a>
                <a href="edit_profile.php">Edit Profile</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>
        
        <main>
            <section class="welcome-user">
                <h2>Welcome, <?php echo $_SESSION['name']; ?>!</h2>
            </section>
            
            <section class="appointments">
                <h3>Your Appointments</h3>
                
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Service Provider</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Booking Date</th>
                                <th>Notes</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td><?php echo $row['provider_name']; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                    <td><?php echo date('h:i A', strtotime($row['time_start'])) . ' - ' . date('h:i A', strtotime($row['time_end'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['booking_date'])); ?></td>
                                    <td><?php echo $row['notes']; ?></td>
                                    <td>
                                        <?php 
                                        $status = isset($row['status']) ? $row['status'] : 'pending';
                                        $status_class = 'status-' . $status;
                                        echo '<span class="' . $status_class . '">' . ucfirst($status) . '</span>';
                                        ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>You have no appointments. <a href="book_appointment.php">Book one now</a>.</p>
                <?php endif; ?>
            </section>
            
            <?php if (mysqli_num_rows($canceled_result) > 0): ?>
            <!-- Uncomment this section if you want to display rejected appointments separately
            <section class="rejected-appointments">
                <h3>Rejected Appointments</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Service Provider</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Booking Date</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($canceled_result)): ?>
                            <tr>
                                <td><?php echo $row['provider_name']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($row['time_start'])) . ' - ' . date('h:i A', strtotime($row['time_end'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($row['booking_date'])); ?></td>
                                <td><?php echo $row['notes']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
            -->
            <?php endif; ?>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Appointment Booking System</p>
        </footer>
    </div>
</body>
</html>