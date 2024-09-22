<?php
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/setting/config.php')) {
    // Config dosyası yoksa setup sayfasına yönlendir
    header('Location: setting/setup.php');
    exit;
} else {

// Config dosyası varsa dahil et ve veritabanına bağlan
require $_SERVER['DOCUMENT_ROOT'] . '/setting/config.php'; }

// Veritabanı bağlantısını oluşturma
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    echo json_encode(['message' => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Girdi verisini almak
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($data['test_email'])) {
    $test_email = $data['test_email'];

    // SMTP ayarlarını veritabanından çekme
    $settings_query = "SELECT * FROM settings LIMIT 1";
    $settings_result = $conn->query($settings_query);

    if (!$settings_result) {
        echo json_encode(['message' => "Error retrieving settings: " . $conn->error]);
        exit();
    }

    $settings = $settings_result->fetch_assoc();

    if (!$settings) {
        echo json_encode(['message' => "No settings found."]);
        exit();
    }

    // SMTP kullanıp kullanmama durumu
    $use_smtp = $settings['use_smtp'];

    // E-posta gönderim fonksiyonu
    function sendEmail($to, $subject, $content, $settings) {
        if ($settings['use_smtp'] == 1) {
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
            // PHP mail() fonksiyonu ile gönderim
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: ' . $settings['user_mail'] . "\r\n";
            return mail($to, $subject, $content, $headers);
        }
    }

    $subject = "Test için gönderildi";
    $content = "Bu mail test için gönderilmiştir";

    if ($settings['status'] == 1) {
        // E-posta gönder
        if (sendEmail($test_email, $subject, $content, $settings)) {
            echo json_encode(['message' => "Test Mail gönderimi başarılı"]);
        } else {
            echo json_encode(['message' => "Test Mail gönderimi başarısız"]);
        }
    } else {
        echo json_encode(['message' => "Mail gönderimi şu an ayarlardan kapalı durumda"]);
    }
}

$conn->close();
?>
