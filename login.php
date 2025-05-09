<?php
session_start(); // Oturumu başlatır
require_once 'db/connection.php'; // Veritabanı bağlantı dosyasını dahil eder

// Kullanıcı zaten giriş yapmışsa, kullanıcı tipine göre yönlendirme yapılır
if (isset($_SESSION['user_id'])) {
    // Eğer kullanıcı tipi "customer" ise müşteri paneline yönlendir
    if ($_SESSION['user_type'] == 'customer') {
        header("Location: user_dashboard.php");
    } else { // Aksi takdirde sağlayıcı paneline yönlendir
        header("Location: provider_dashboard.php");
    }
    exit(); // Kodun devamının çalışmaması için çıkış yapılır
}

$error = ""; // Hata mesajı değişkeni tanımlanır

// Eğer form POST yöntemiyle gönderildiyse giriş işlemleri başlatılır
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']); // Kullanıcı adı güvenli şekilde alınır
    $password = $_POST['password']; // Şifre alınır (doğrudan, çünkü hash kontrolü yapılacak)
    
    // Kullanıcı adına göre kullanıcıyı veritabanında arar
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    // Eğer kullanıcı bulunduysa
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result); // Kullanıcı bilgileri alınır
        
        // Şifre doğrulaması yapılır (hash ile)
        if (password_verify($password, $user['password'])) {
            // Giriş başarılıysa oturum bilgileri ayarlanır
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['name'] = $user['name'];
            
            // Kullanıcı tipine göre yönlendirme yapılır
            if ($user['user_type'] == 'customer') {
                header("Location: user_dashboard.php");
            } else {
                header("Location: provider_dashboard.php");
            }
            exit(); // Yönlendirme sonrası çıkış yapılır
        } else {
            $error = "Invalid password"; // Şifre uyuşmuyorsa hata mesajı
        }
    } else {
        $error = "Username not found"; // Kullanıcı adı veritabanında bulunamadıysa
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
