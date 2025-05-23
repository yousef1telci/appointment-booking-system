<?php
session_start(); // Oturumu başlat
require_once 'db/connection.php'; // Veritabanı bağlantısını dahil et

// Eğer kullanıcı zaten giriş yapmışsa, kullanıcı tipine göre yönlendir
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] == 'customer') {
        header("Location: user_dashboard.php"); // Müşteri paneline yönlendir
    } else {
        header("Location: provider_dashboard.php"); // Sağlayıcı paneline yönlendir
    }
    exit();
}

$error = "";   // Hata mesajı için değişken
$success = ""; // Başarı mesajı için değişken

// Form gönderilmişse işle
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form verilerini al
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $user_type = $_POST['user_type']; // 'customer' veya 'provider'

    // Eğer kullanıcı türü 'provider' ise, hizmet kategorisini al
    $service_category_id = null;
    if ($user_type == 'provider' && isset($_POST['service_category'])) {
        $service_category_id = (int)$_POST['service_category'];
    }

    // Girdi doğrulaması
    if (empty($username) || empty($password) || empty($email) || empty($name)) {
        $error = "Tüm alanlar zorunludur";
    } elseif ($password !== $confirm_password) {
        $error = "Şifreler uyuşmuyor";
    } elseif ($user_type == 'provider' && empty($service_category_id)) {
        $error = "Hizmet sağlayıcılar bir kategori seçmelidir";
    } else {
        // Kullanıcı adı veya e-posta daha önce alınmış mı kontrol et
        $check_query = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $error = "Kullanıcı adı veya e-posta zaten kullanılıyor";
        } else {
            // Şifreyi güvenli hale getir (hash)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Kullanıcıyı veritabanına ekle
            if ($user_type == 'provider') {
                $insert_query = "INSERT INTO users (username, password, email, user_type, name, service_category_id) 
                                VALUES ('$username', '$hashed_password', '$email', '$user_type', '$name', $service_category_id)";
            } else {
                $insert_query = "INSERT INTO users (username, password, email, user_type, name) 
                                VALUES ('$username', '$hashed_password', '$email', '$user_type', '$name')";
            }

            // Kayıt işlemi başarılıysa
            if (mysqli_query($conn, $insert_query)) {
                $success = "Kayıt başarılı! Giriş yapabilirsiniz.";
            } else {
                $error = "Kayıt başarısız oldu: " . mysqli_error($conn);
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
                        <select id="user_type" name="user_type" required onchange="toggleCategoryField()">
                            <option value="customer">Customer</option>
                            <option value="provider">Service Provider</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="service-category-group" style="display: none;">
                        <label for="service_category">Service Category</label>
                        <select id="service_category" name="service_category">
                            <option value="">-- Select Category --</option>
                            <?php
                            // Fetch service categories from database
                            $category_query = "SELECT id, name FROM service_categories ORDER BY name ASC";
                            $category_result = mysqli_query($conn, $category_query);
                            
                            while ($category = mysqli_fetch_assoc($category_result)) {
                                echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">Register</button>
                    
                    <script>
                        function toggleCategoryField() {
                            var userType = document.getElementById('user_type').value;
                            var categoryGroup = document.getElementById('service-category-group');
                            
                            if (userType === 'provider') {
                                categoryGroup.style.display = 'block';
                                document.getElementById('service_category').setAttribute('required', 'required');
                            } else {
                                categoryGroup.style.display = 'none';
                                document.getElementById('service_category').removeAttribute('required');
                            }
                        }
                        
                        // Call the function initially to set the correct state
                        document.addEventListener('DOMContentLoaded', toggleCategoryField);
                    </script>
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