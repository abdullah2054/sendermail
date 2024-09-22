<?php
//okundu.php?mail=gidenmail@domain.com
//1x1 lik resim
$resim = imagecreate(1,1);
header("Content-type: image/jpeg");
imagejpeg($resim);
imagedestroy($resim);
//Log tut


$gorevkodu=$_GET["gorevid"];



// Config dosyası varsa dahil et ve veritabanına bağlan
require $_SERVER['DOCUMENT_ROOT'] . '/setting/config.php'; 

// Veritabanı bağlantısını oluşturma
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SMTP ayarlarını veritabanından çekme
$settings_query = "UPDATE Tasks set status=2 where id=$gorevkodu";
$settings_result = $conn->query($settings_query);

if (!$settings_result) {
    die("Error retrieving settings: " . $conn->error . " Query: " . $settings_query);
}

$settings = $settings_result->fetch_assoc();

if (!$settings) {
    die("No settings found.");
}

// SMTP kullanıp kullanmama durumu
$use_smtp = $settings['use_smtp'];


?>