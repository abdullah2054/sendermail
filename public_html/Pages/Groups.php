<?php
// Veritabanı bağlantısı kurma

// Veritabanı bağlantısını oluşturma
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Bağlantıyı kontrol etme
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

$success_message = "";
$error_message = "";

// Mail grubu ekleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_group'])) {
    $group_name = $_POST['group_name'];

    // Grup ekleme sorgusu
    $sql = "INSERT INTO `Groups` (group_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $group_name);
        if ($stmt->execute()) {
            $success_message = "Grup başarıyla oluşturuldu.";
        } else {
            $error_message = "Grup oluşturulurken hata oluştu: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Hazırlama hatası: " . $conn->error;
    }
}

// Mevcut grupları alma işlemi
$sql = "SELECT id, group_name, created_at, updated_at FROM `Groups`";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Grubu Oluşturma ve Listeleme</title>
    <style>
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 80%;
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
        }
        .form-group input, .form-group button {
            width: calc(100% - 10px);
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group button {
            background-color: #6e8efb;
            color: #fff;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 16px;
        }
        .form-group button:hover {
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Mail Grubu Oluşturma</h2>
        <?php
        if (!empty($success_message)) {
            echo "<p class='success-message'>$success_message</p>";
        }
        if (!empty($error_message)) {
            echo "<p class='error-message'>$error_message</p>";
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=Groups" method="post">
            <div class="form-group">
                <label for="group_name">Grup Adı:</label>
                <input type="text" id="group_name" name="group_name" required>
            </div>
            <div class="form-group">
                <button type="submit" name="create_group">Grup Oluştur</button>
            </div>
        </form>

        <h2>Mevcut Gruplar</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Grup Adı</th>
                    <th>Oluşturulma Tarihi</th>
                    <th>Değiştirilme Tarihi</th>
                    <th>Düzenle</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>" . $row['id'] . "</td><td>" . $row['group_name'] . "</td><td>" . $row['created_at'] . "</td><td>" . $row['updated_at'] . "</td><td><a href='?page=editgroup&&id=" . $row['id'] . "'>Düzenle</a></td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>Hiç grup bulunamadı.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
