<?php
echo "Başlatıldı";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';




use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



// Config dosyası varsa dahil et ve veritabanına bağlan

 $db_host = 'localhost';
 $db_user = 'u929469444_phplist';
 $db_pass = 'Yildirim.88';
 $db_name = 'u929469444_phplist';

// Veritabanı bağlantısını oluşturma
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SMTP ayarlarını veritabanından çekme
$settings_query = "SELECT * FROM settings LIMIT 1";
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


// E-posta gönderim fonksiyonu
function sendEmail($to, $subject, $content, $settings) {
    if ($settings['use_smtp'] == 1) {
        
        echo $settings['use_smtp'];
        // PHPMailer ile SMTP üzerinden gönderim
          $mail = new PHPMailer(true);
        try {
            // Sunucu ayarları
            $mail->isSMTP();
            $mail->Host = $settings['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $settings['smtp_username'];
            $mail->Password = $settings['smtp_password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $settings['smtp_port'];

            // Alıcı ve Gönderen bilgileri
            $mail->setFrom($settings['smtp_username'], 'Dijital Market');
            $mail->addAddress($to);

            // İçerik
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $content;

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
      
    } else {
        
        echo $settings['use_smtp'];
        // PHP mail() fonksiyonu ile gönderim
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: ' . $settings['user_mail'] . "\r\n";
        return mail($to, $subject, $content, $headers);
    }
}

if ($settings['status']==1) {

$setting_maillimit=$settings['mail_limit'];

// Pending durumundaki ilk 20 görevi al (oluşturulma tarihine göre sıralı)
$query = "SELECT * FROM Tasks WHERE status = '0' ORDER BY created_at ASC LIMIT $setting_maillimit";
$result = $conn->query($query);

if (!$result) {
    die("Error retrieving tasks: " . $conn->error . " Query: " . $query);
}

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $taskId = $row['id'];
        $email = $row['email'];
        $subject = $row['subject'];
        $content = $row['content'];
        
        
        $content=$content."<br><br><img src='https://bilgiyarismasioyunlari.com/Pages/okundu.php?gorevid=$taskId' width='1' height='1'>";
   

        // E-posta gönder
        if (sendEmail($email, $subject, $content, $settings)) {
            echo "<br>Görev durumunu gönderildi güncellendi";
            $updateQuery = "UPDATE Tasks SET status = '1' WHERE id = $taskId";
        } else {
            echo  "<br>Görev durumunu 'failed' olarak güncelle";
            $updateQuery = "UPDATE Tasks SET status = '-1' WHERE id = $taskId";
        }
        $conn->query($updateQuery);
    }
}} else {
    
     echo "<br>Mail gönderimi şuan ayarlardan kapalı durumda";
    
    
}

$conn->close();
?>
