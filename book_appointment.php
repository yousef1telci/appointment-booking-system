<?php
session_start(); // Oturumu başlat
require_once 'db/connection.php'; // Veritabanı bağlantı dosyasını dahil et

// Kullanıcının giriş yapıp yapmadığını ve müşteri olup olmadığını kontrol et
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'customer') {
    header("Location: login.php"); // Giriş sayfasına yönlendir
    exit();
}

$customer_id = $_SESSION['user_id']; // Giriş yapan müşterinin ID'si
$error = "";
$success = "";

// Randevu formu gönderildiğinde işlemleri başlat
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_appointment'])) {
    $availability_id = mysqli_real_escape_string($conn, $_POST['availability_id']); // Seçilen zaman dilimi ID'si
    $notes = mysqli_real_escape_string($conn, $_POST['notes']); // Kullanıcının notları
    
    // Zaman diliminin hâlâ uygun olup olmadığını kontrol et
    $check_query = "SELECT * FROM availability WHERE id = $availability_id AND is_booked = 0";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $error = "Bu zaman dilimi artık uygun değil";
    } else {
        // Veritabanı işlemi başlat (transaction)
        mysqli_begin_transaction($conn);
        
        try {
            // Yeni randevuyu veritabanına ekle
            $insert_query = "INSERT INTO appointments (availability_id, customer_id, notes) 
                            VALUES ($availability_id, $customer_id, '$notes')";
            $insert_result = mysqli_query($conn, $insert_query);
            
            // Seçilen zaman dilimini 'rezerve edilmiş' olarak güncelle
            $update_query = "UPDATE availability SET is_booked = 1 WHERE id = $availability_id";
            $update_result = mysqli_query($conn, $update_query);
            
            if ($insert_result && $update_result) {
                mysqli_commit($conn); // İşlemleri onayla
                $success = "Randevu başarıyla oluşturuldu!";
            } else {
                throw new Exception("Randevu oluşturulamadı");
            }
        } catch (Exception $e) {
            mysqli_rollback($conn); // İşlemleri geri al
            $error = "Randevu oluşturulamadı: " . $e->getMessage();
        }
    }
}

// Hizmet sağlayıcıları ve kategorilerini veritabanından al
$providers_query = "SELECT u.id, u.name, c.name as category_name 
                   FROM users u
                   LEFT JOIN service_categories c ON u.service_category_id = c.id
                   WHERE u.user_type = 'provider' 
                   ORDER BY u.name ASC";
$providers_result = mysqli_query($conn, $providers_query);

// Hizmet sağlayıcı seçildiyse, uygun zaman dilimlerini al
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
        $available_slots[] = $row; // Uygun zaman dilimlerini diziye ekle
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Karakter kodlaması UTF-8 olarak ayarlandı -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Mobil uyumluluk için viewport ayarı -->
    <title>Book Appointment - Appointment Booking System</title> <!-- Sayfa başlığı -->
    <link rel="stylesheet" href="assets/style.css"> <!-- CSS dosyası bağlantısı -->
</head>
<body>
    <div class="container">
        <header>
            <h1>Book an Appointment</h1> <!-- Sayfa başlığı -->
            <nav> <!-- Navigasyon menüsü -->
                <a href="index.php">Home</a>
                <a href="user_dashboard.php">Dashboard</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>
        
        <main>
            <!-- Hata mesajı varsa göster -->
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <!-- Başarı mesajı varsa göster -->
            <?php if (!empty($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <section class="select-provider">
                <h3>Select a Service Provider</h3> <!-- Hizmet sağlayıcı seçme başlığı -->
                
                <!-- Sağlayıcı seçme formu -->
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get">
                    <div class="form-group">
                        <label for="provider_id">Provider</label>
                        <select id="provider_id" name="provider_id" required>
                            <option value="">-- Select Provider --</option>
                            <!-- Sağlayıcı listesi döngü ile dolduruluyor -->
                            <?php while ($provider = mysqli_fetch_assoc($providers_result)): ?>
                                <option value="<?php echo $provider['id']; ?>" <?php echo (isset($_GET['provider_id']) && $_GET['provider_id'] == $provider['id']) ? 'selected' : ''; ?>>
                                    <?php echo $provider['name']; ?> (<?php echo $provider['category_name']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">View Available Slots</button> <!-- Zaman dilimlerini göster butonu -->
                </form>
            </section>
            
            <!-- Eğer uygun zaman dilimleri varsa, onları göster -->
            <?php if (!empty($available_slots)): ?>
                <section class="available-slots">
                    <h3>Available Time Slots</h3> <!-- Uygun zaman dilimi başlığı -->
                    
                    <?php if (count($available_slots) > 0): ?>
                        <div class="slots-grid">
                            <!-- Her bir zaman dilimi kartı için döngü -->
                            <?php foreach ($available_slots as $slot): ?>
                                <div class="slot-card">
                                    <div class="slot-details">
                                        <h4><?php echo $slot['provider_name']; ?></h4>
                                        <p>Date: <?php echo date('M d, Y', strtotime($slot['date'])); ?></p>
                                        <p>Time: <?php echo date('h:i A', strtotime($slot['time_start'])) . ' - ' . date('h:i A', strtotime($slot['time_end'])); ?></p>
                                    </div>
                                    
                                    <!-- Randevu alma formu -->
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                        <input type="hidden" name="availability_id" value="<?php echo $slot['id']; ?>">
                                        <div class="form-group">
                                            <label for="notes-<?php echo $slot['id']; ?>">Notes (Optional)</label>
                                            <textarea id="notes-<?php echo $slot['id']; ?>" name="notes" rows="2"></textarea>
                                        </div>
                                        <button type="submit" name="book_appointment" class="btn btn-book">Book Now</button> <!-- Randevu al butonu -->
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- Zaman dilimi yoksa bilgi mesajı -->
                        <p>No available time slots for this provider. Please select another provider.</p>
                    <?php endif; ?>
                </section>
            <?php elseif (isset($_GET['provider_id']) && !empty($_GET['provider_id'])): ?>
                <!-- Sağlayıcı seçildi ama zaman dilimi yoksa gösterilecek mesaj -->
                <p>No available time slots for this provider. Please select another provider.</p>
            <?php endif; ?>
        </main>
        
        <footer>
            <p>&copy; <?php echo date("Y"); ?> Online Appointment Booking System</p> <!-- Footer kısmı -->
        </footer>
    </div>
</body>
</html>
