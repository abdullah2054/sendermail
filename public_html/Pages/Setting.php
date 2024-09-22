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

// Veritabanı bağlantısı
$servername = "localhost";
$username_db = "u929469444_phplist";
$password_db = "Yildirim.88";
$dbname = "u929469444_phplist";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Varsayılan ayarları yükle
$settings = array(
    'use_smtp' => false,
    'smtp_host' => '',
    'smtp_port' => '',
    'smtp_username' => '',
    'smtp_password' => '',
    'status' => 0,
    'user_name' => '',
    'user_mail' => '',
    'mail_limit' => 20 // Varsayılan değer
);

// Veritabanından ayarları oku
$sql = "SELECT * FROM settings LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $settings['use_smtp'] = $row['use_smtp'];
    $settings['smtp_host'] = $row['smtp_host'];
    $settings['smtp_port'] = $row['smtp_port'];
    $settings['smtp_username'] = $row['smtp_username'];
    $settings['smtp_password'] = $row['smtp_password'];
    $settings['status'] = $row['status'];
    $settings['user_name'] = $row['user_name'];
    $settings['user_mail'] = $row['user_mail'];
    $settings['mail_limit'] = $row['mail_limit'];
}

// Form gönderildiğinde ayarları kaydet
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $settings['use_smtp'] = ($_POST['mail_method'] === 'smtp') ? 1 : 0;
    $settings['smtp_host'] = $_POST['smtp_host'];
    $settings['smtp_port'] = $_POST['smtp_port'];
    $settings['smtp_username'] = $_POST['smtp_username'];
    $settings['smtp_password'] = $_POST['smtp_password'];
    $settings['status'] = isset($_POST['status']) ? 1 : 0;
    $settings['user_name'] = $_POST['user_name'];
    $settings['user_mail'] = $_POST['user_mail'];
    $settings['mail_limit'] = intval($_POST['mail_limit']);

    $sql = "UPDATE settings SET 
            use_smtp = '".$settings['use_smtp']."',
            smtp_host = '".$settings['smtp_host']."',
            smtp_port = '".$settings['smtp_port']."',
            smtp_username = '".$settings['smtp_username']."',
            smtp_password = '".$settings['smtp_password']."',
            status = '".$settings['status']."',
            user_name = '".$settings['user_name']."',
            user_mail = '".$settings['user_mail']."',
            mail_limit = '".$settings['mail_limit']."'
            ";

    if ($conn->query($sql) === TRUE) {
        echo "<p class='success-message'>Ayarlar başarıyla kaydedildi.</p>";
    } else {
        echo "<p class='error-message'>Ayarlar kaydedilirken hata oluştu: " . $conn->error . "</p>";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .sidebar {
            width: 200px;
            background-color: #333;
            color: white;
            height: 100vh;
            padding-top: 20px;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #575757;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
        }
        .header {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: right;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
        }
        .form-group input, .form-group select {
            width: calc(100% - 10px);
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #6e8efb;
        }
        .form-group .btn-save {
            width: 100%;
            padding: 10px;
            background-color: #6e8efb;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-group .btn-save:hover {
            background-color: #556cd6;
        }
        .success-message {
            color: green;
            margin-top: 10px;
        }
        .error-message {
            color: red;
            margin-top: 10px;
        }
        .hidden {
            display: none;
        }
        .warning-message {
            color: orange;
            margin-top: 10px;
        }
        .range-input-container {
            display: flex;
            align-items: center;
        }
        .range-input-container input[type="range"] {
            flex-grow: 1;
        }
        .range-input-container span {
            margin-left: 10px;
        }
    </style>
    <script>
        function toggleSMTPSettings() {
            var smtpSettings = document.getElementById('smtp-settings');
            var warningMessage = document.getElementById('warning-message');
            var phpMailSettings = document.getElementById('php-mail-settings');
            var mailMethod = document.querySelector('input[name="mail_method"]:checked').value;
            if (mailMethod === 'smtp') {
                smtpSettings.classList.remove('hidden');
                warningMessage.classList.add('hidden');
                phpMailSettings.classList.add('hidden');
            } else {
                smtpSettings.classList.add('hidden');
                warningMessage.classList.remove('hidden');
                phpMailSettings.classList.remove('hidden');
            }
        }

        function updateMailLimitValue(val) {
            document.getElementById('mail_limit_value').textContent = val;
        }
    </script>
</head>
<body>
    <div class="content">
        <h2>Ayarlar</h2>
        <form action="<?php echo $_SERVER['PHP_SELF'] . '?page=Setting'; ?>" method="post">
            <div class="form-group">
                <label for="status">Mail Gönderimi Aktif:</label>
                <input type="checkbox" id="status" name="status" <?php echo $settings['status'] ? 'checked' : ''; ?>>
            </div>
            <div class="form-group">
                <label for="mail_method">Mail Gönderim Yöntemi Seçiniz:</label>
                <div style="
                    background-color: aliceblue;
                    padding: 10px;
                    border-radius: 10px;
                    margin: 5px;
                    " class="smtpdiv">
                    <input style="width: min-content;" type="radio" id="mail_smtp" name="mail_method" value="smtp" <?php echo $settings['use_smtp'] ? 'checked' : ''; ?> onchange="toggleSMTPSettings()">
                    SMTP (Önerilen)
                    <div id="smtp-settings" class="<?php echo $settings['use_smtp'] ? '' : 'hidden'; ?>">
                        <div class="form-group">
                            <label for="smtp_host">SMTP Host:</label>
                            <input type="text" id="smtp_host" name="smtp_host" value="<?php echo $settings['smtp_host']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="smtp_port">SMTP Port:</label>
                            <input type="text" id="smtp_port" name="smtp_port" value="<?php echo $settings['smtp_port']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="smtp_username">SMTP Kullanıcı Adı:</label>
                            <input type="text" id="smtp_username" name="smtp_username" value="<?php echo $settings['smtp_username']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="smtp_password">SMTP Şifre:</label>
                            <input type="password" id="smtp_password" name="smtp_password" value="<?php echo $settings['smtp_password']; ?>">
                        </div>
                    </div>
                </div>
                <div style="
                    background-color: aliceblue;
                    padding: 10px;
                    border-radius: 10px;
                    margin: 5px;
                    "class="maildiv">
                    <input style="width: min-content;" type="radio" id="mail_php" name="mail_method" value="php" <?php echo !$settings['use_smtp'] ? 'checked' : ''; ?> onchange="toggleSMTPSettings()">
                    Mail()
                    <div id="php-mail-settings" class="<?php echo !$settings['use_smtp'] ? '' : 'hidden'; ?>">
                        <div class="form-group">
                            <label for="user_name">Gönderen Adı:</label>
                            <input type="text" id="user_name" name="user_name" value="<?php echo $settings['user_name']; ?>">
                        </div>
                        <div class="form-group">
                            <label for="user_mail">Gönderen E-posta:</label>
                            <input type="email" id="user_mail" name="user_mail" value="<?php echo $settings['user_mail']; ?>">
                        </div>
                    </div>
                    <br>
                    <div id="warning-message" class="warning-message <?php echo !$settings['use_smtp'] ? '' : 'hidden'; ?>">
                        <p>mail() fonksiyonunu kullanmanız halinde:</p>
                        <ul>
                            <li>İleti tesliminde garanti yoktur.</li>
                            <li>Genellikle spam olarak işaretlenir.</li>
                            <li>Yüksek miktarda e-posta gönderiminde kısıtlamalar vardır.</li>
                            <li>SMTP ayarlarının eksikliği nedeniyle sınırlı yapılandırma seçenekleri sunar.</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="mail_limit">Her adımda gönderilecek mail sayısı:</label>
                <div class="range-input-container">
                    <input type="range" id="mail_limit" name="mail_limit" value="<?php echo $settings['mail_limit']; ?>" min="1" max="300" onchange="updateMailLimitValue(this.value)">
                    <span id="mail_limit_value"><?php echo $settings['mail_limit']; ?></span>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn-save">Kaydet</button>
            </div>
        </form>
    </div>
        <form id="emailForm" >
        <label for="test_email">Test E-posta Adresi:</label>
        <input type="email" id="test_email" name="test_email" value="" required>
        <button type="button" onclick="f_testmailgonder()">Gönder</button>
    </form>
    <div id="response"></div>
    <script>
        // Sayfa yüklendiğinde doğru ayarları göster
        toggleSMTPSettings();
        
                function isValidEmail(email) {
            // Basit bir e-posta doğrulama regex'i
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailPattern.test(email);
        }
             
             
                function f_testmailgonder() {
                    
                test_emailadres=document.getElementById("test_email").value
               if (isValidEmail(test_emailadres)) {
              
              const postData = {
                test_email: test_emailadres
            };

            fetch('Pages/testmail.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(postData)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('İşlem başarılı:', data);
                alert(data.message);
                location.reload(); // Sayfayı yenile
            })
            .catch(error => {
                console.error('İşlem sırasında hata oluştu:', error);
            });
                } else {alert("Girdiğiniz mail adresi yanlış görünüyor, Girdiğiniz mail adresini kontrol ediniz")}}
         
    </script>
</body>
</html>
