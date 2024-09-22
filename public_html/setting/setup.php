<?php

session_start();



if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/setting/config.php')) {
    
    
require $_SERVER['DOCUMENT_ROOT'] . '/setting/config.php'; // Config dosyasını dahil et
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

$result = mysqli_query($conn, "SELECT COUNT(*) AS count FROM admin");
$row = mysqli_fetch_assoc($result);
    
    if ($row['count'] >= 1) {
        // Kullanıcı varsa, kurulum başarılı
        echo "Kurulum başarılı! Ana sayfaya yönlendiriliyorsunuz...";
        header('Location: ../index.php');
        exit;
    } 
}

    
    $_SESSION['admin'] = "";
    $setup_step = 1; // Kurulum aşamasını takip etmek için bir değişken

    // İlk aşamada veritabanı ayarları alınıyor
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['add_admin'])) {
        $db_host = $_POST['db_host'];
        $db_user = $_POST['db_user'];
        $db_pass = $_POST['db_pass'];
        $db_name = $_POST['db_name'];

        // Veritabanı bağlantısını test et
        $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
        if (!$conn) {
            die("Veritabanına bağlanılamadı: " . mysqli_connect_error());
        } else {
            // Config dosyasını oluştur
            $config_content = "<?php
            \$db_host = '$db_host';
            \$db_user = '$db_user';
            \$db_pass = '$db_pass';
            \$db_name = '$db_name';
            ?>";

            // SQL dosyasını yükle
            $sql_file = 'database.sql';
            $sql_content = file_get_contents($sql_file);

            // SQL sorgularını çalıştır
            if (mysqli_multi_query($conn, $sql_content)) {
                echo "Veritabanı başarıyla yüklendi.";
                $setup_step = 2; // İkinci aşamaya geçiş
            } else {
                echo "Hata: " . mysqli_error($conn);
            }

            // Config dosyasını oluştur
            file_put_contents('config.php', $config_content);
 }
    }

    // Admin kullanıcı ekleme işlemi
    if (isset($_POST['add_admin'])) {
        $admin_email = $_POST['admin_email'];
        $admin_password = $_POST['admin_password'];
        $admin_password_confirm = $_POST['admin_password_confirm'];

        // Şifrelerin eşleşip eşleşmediğini kontrol et
        if ($admin_password !== $admin_password_confirm) {
            die("Şifreler eşleşmiyor.");
        }

        // Şifreyi güvenli bir şekilde hashle
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

        // Veritabanı bağlantısını tekrar aç
        // Config dosyasını dahil et
        $conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
        
        // Admin kullanıcısını ekle
        $insert_admin = "INSERT INTO admin (mail, password) VALUES ('$admin_email', '$hashed_password')";
        if (mysqli_query($conn, $insert_admin)) {
            echo "Admin kullanıcısı başarıyla eklendi.";
            // Başarılı eklemeden sonra yönlendirme yapabilirsin
            header('Location: index.php');
            exit;
        } else {
            echo "Hata: " . mysqli_error($conn);
        }
    }

?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Kurulumu</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e0f7fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        h1 {
            text-align: center;
            color: #00796b;
            font-size: 24px;
            margin-bottom: 20px;
        }

        form {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }

        label {
            font-size: 14px;
            color: #333333;
            margin-bottom: 5px;
            display: block;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            margin-bottom: 20px;
            border: 2px solid #00796b;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            width: 100%;
            background-color: #00796b;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #004d40;
        }

        .form-container {
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
        }

        @media screen and (max-width: 600px) {
            form {
                padding: 20px;
            }

            h1 {
                font-size: 20px;
            }

            input[type="text"], input[type="password"], input[type="submit"] {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <?php if ($setup_step == 1): ?>
            <h1>Veritabanı Ayarlarını Girin</h1>
            <form action="setup.php" method="post">
                <label for="db_host">Veritabanı Sunucusu:</label>
                <input type="text" name="db_host" required><br>

                <label for="db_user">Veritabanı Kullanıcı Adı:</label>
                <input type="text" name="db_user" required><br>

                <label for="db_pass">Veritabanı Şifresi:</label>
                <input type="password" name="db_pass" required><br>

                <label for="db_name">Veritabanı Adı:</label>
                <input type="text" name="db_name" required><br>

                <input type="submit" value="Kurulumu Tamamla">
            </form>
        <?php elseif ($setup_step == 2): ?>
            <h2>Admin Kullanıcı Bilgilerini Girin</h2>
            <form action="setup.php" method="post">
                <label for="admin_email">E-posta:</label>
                <input type="text" name="admin_email" required><br>
                <label for="admin_password">Şifre:</label>
                <input type="password" name="admin_password" required><br>
                <label for="admin_password_confirm">Şifreyi Tekrar Girin:</label>
                <input type="password" name="admin_password_confirm" required><br>
                <input type="submit" name="add_admin" value="Kullanıcı Ekle">
            </form>
        <?php endif; ?>
    </div>
</body>
</html>


