<?php
session_start(); // Oturumu başlatır
require_once 'db/connection.php'; // Veritabanı bağlantısını dahil eder

// Kullanıcının giriş yapıp yapmadığını ve sağlayıcı (provider) olup olmadığını kontrol eder
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'provider') {
    header("Location: login.php"); // Giriş sayfasına yönlendir
    exit(); // Kodun devamını çalıştırmadan çık
}

$provider_id = $_SESSION['user_id']; // Oturumdan sağlayıcı ID'si alınır

// Randevu durumu güncelleme işlemi yapılacaksa
if (isset($_POST['action']) && isset($_POST['appointment_id'])) {
    $appointment_id = mysqli_real_escape_string($conn, $_POST['appointment_id']); // Güvenli şekilde randevu ID'si alınır
    $status = ''; // Durum değişkeni tanımlanır
    
    // Gelen aksiyona göre durum belirlenir
    if ($_POST['action'] == 'accept') {
        $status = 'accepted'; // Kabul edildi
    } elseif ($_POST['action'] == 'reject') {
        $status = 'rejected'; // Reddedildi
    }
    
    // Eğer geçerli bir durum varsa, randevunun durumu güncellenir
    if (!empty($status)) {
        $update_query = "UPDATE appointments SET status = '$status' WHERE id = $appointment_id";
        mysqli_query($conn, $update_query); // Veritabanında güncelleme yapılır
        
        // Formun yeniden gönderilmesini önlemek için sayfa yenilenir
        header("Location: provider_dashboard.php");
        exit();
    }
}

// Sağlayıcının hizmet kategorisi bilgisi alınır
$category_query = "SELECT c.name as category_name 
                  FROM users u
                  JOIN service_categories c ON u.service_category_id = c.id
                  WHERE u.id = $provider_id";
$category_result = mysqli_query($conn, $category_query);
$category_info = mysqli_fetch_assoc($category_result); // Tek bir satır alınır

// Sağlayıcının randevuları çekilir
$appointments_query = "SELECT a.id, u.name as customer_name, v.date, v.time_start, v.time_end, 
                       a.booking_date, a.notes, a.status 
                       FROM appointments a 
                       JOIN availability v ON a.availability_id = v.id 
                       JOIN users u ON a.customer_id = u.id 
                       WHERE v.provider_id = $provider_id 
                       ORDER BY v.date ASC, v.time_start ASC";
$appointments_result = mysqli_query($conn, $appointments_query);

// Sağlayıcının uygunluk zaman dilimleri (availability) çekilir
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
    <title>Provider Dashboard - Appointment Booking System</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .status-pending { color: #f39c12; font-weight: bold; }
        .status-accepted { color: #27ae60; font-weight: bold; }
        .status-rejected { color: #e74c3c; font-weight: bold; }
        .action-buttons form { display: inline; }
        .action-buttons button { 
            padding: 5px 10px;
            margin: 0 2px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .accept-btn { background-color: #27ae60; color: white; }
        .reject-btn { background-color: #e74c3c; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Provider Dashboard</h1>
            <nav>
                <a href="index.php">Home</a>
                <a href="set_availability.php">Set Availability</a>
                <a href="edit_profile.php">Edit Profile</a>
                <a href="logout.php">Logout</a>
            </nav>
            
        </header>
        
        <main>
        <section class="welcome-user">
             <h2>Welcome, <?php echo $_SESSION['name']; ?>!</h2>
            <p class="provider-category">
                <span>Service Category: </span>
                <strong class="category-name"><?php echo $category_info['category_name']; ?></strong>
            </p>
        </section>

            
            <section class="appointments">
                <h3>Your Booked Appointments</h3>
                
                <?php if (mysqli_num_rows($appointments_result) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Booking Date</th>
                                <th>Notes</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($appointments_result)): ?>
                                <tr>
                                    <td><?php echo $row['customer_name']; ?></td>
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
                                    <td class="action-buttons">
                                        <?php if ($status == 'pending'): ?>
                                            <form method="post" action="">
                                                <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="action" value="accept">
                                                <button type="submit" class="accept-btn">Accept</button>
                                            </form>
                                            <form method="post" action="">
                                                <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="reject-btn">Reject</button>
                                            </form>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>You have no booked appointments.</p>
                <?php endif; ?>
            </section>
            
            <section class="availability">
                <h3>Your Availability</h3>
                
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
                    <p>You have not set any availability slots. <a href="set_availability.php">Set some now</a>.</p>
                <?php endif; ?>
            </section>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Appointment Booking System</p>
        </footer>
    </div>
</body>
</html>