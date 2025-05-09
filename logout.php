<?php
session_start(); // Oturumu başlatır (aktif oturumu yönetmek için gerekir)

// Tüm oturum değişkenlerini temizler
$_SESSION = array();

// Oturumu tamamen sonlandırır
session_destroy();

// Anasayfaya yönlendirir
header("Location: index.php");
exit(); // Kodun devamının çalışmaması için çıkış yapılır
?>
