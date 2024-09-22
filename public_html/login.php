


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Girişi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 300px;
            text-align: center;
        }
        .login-container h2 {
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        .form-group input {
            width: calc(100% - 10px);
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #6e8efb;
        }
        .form-group .btn-login {
            width: 100%;
            padding: 10px;
            background-color: #6e8efb;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-group .btn-login:hover {
            background-color: #556cd6;
        }
        .error-message {
            color: red;
            margin-top: 10px;
        }
        .success-message {
            color: green;
            margin-top: 10px;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
   
    <?php if(empty($_SESSION['admin'])): ?>
    
    <div class="login-container">
        <h2>Kullanıcı Girişi</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <div class="form-group">
                <label for="username">Kullanıcı Adı:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Şifre:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn-login">Giriş Yap</button>
            </div>
        </form>

<?php
// Formdan veri geldiğinde çalışacak kodlar
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Veritabanı bağlantısı kurma (örnek olarak)
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    // Bağlantıyı kontrol etme
    if ($conn->connect_error) {
        die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
    }

    // POST verilerini al
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Kullanıcı bilgilerini veritabanından sorgulama
    $sql = "SELECT password FROM admin WHERE mail='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Kullanıcı bulundu, şifreyi kontrol et
        $user = $result->fetch_assoc();
        $hashed_password = $user['password'];

        if (password_verify($password, $hashed_password)) {
            // Şifre doğru, oturumu başlat
            session_start();
            $_SESSION['admin'] = $username;
            // Giriş başarılı, ana sayfaya yönlendir
            header("Location: index.php");
            exit;
        } else {
            // Şifre hatalı
            echo "<p class='error-message'>Kullanıcı adı veya şifre hatalı.</p>";
        }
    } else {
        // Kullanıcı bulunamadı
        echo "<p class='error-message'>Kullanıcı adı veya şifre hatalı.</p>";
    }

    // Veritabanı bağlantısını kapatma
    $conn->close();
}
?>


    </div>
    <?php else: ?>
    <div class="welcome-container">
        
        <!-- Buraya kullanıcıya özel içerikler veya yönlendirmeler eklenebilir -->
        <p><a href="index.php">Çıkış Yap</a></p>
    </div>
    <?php endif; ?>
</body>
</html>
