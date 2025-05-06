<?php
session_start();
require_once 'db/connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Form verilerini al
$name = trim($_POST['name']);
$username = trim($_POST['username']);
$email = trim($_POST['email']);

// Basit doğrulama
if (empty($name) || empty($username) || empty($email)) {
    echo "Tüm alanlar doldurulmalıdır.";
    exit();
}

// Aynı kullanıcı adı veya e-posta başka kullanıcı tarafından kullanılıyor mu kontrol et
$sql_check = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ssi", $username, $email, $user_id);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    echo "Bu kullanıcı adı veya e-posta zaten başka biri tarafından kullanılıyor.";
    exit();
}

// Güncelleme işlemi
$sql = "UPDATE users SET name = ?, username = ?, email = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $name, $username, $email, $user_id);

if ($stmt->execute()) {

      // تحديث بيانات الجلسة بعد نجاح التحديث في قاعدة البيانات
      $_SESSION['name'] = $name;
      $_SESSION['username'] = $username;
      $_SESSION['email'] = $email;
    echo "Profil başarıyla güncellendi.";
    
    // İsteğe bağlı: yönlendirme
    header("Location: " . ($_SESSION['user_type'] == 'customer' ? 'user_dashboard.php' : 'provider_dashboard.php'));
    exit();
} else {
    echo "Bir hata oluştu. Lütfen tekrar deneyin.";
}
?>
