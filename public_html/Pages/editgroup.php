<?php

// Veritabanı bağlantısını oluşturma
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Bağlantıyı kontrol etme
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

$success_message = "";
$error_message = "";
$group_name = "";

// Grup ID'sini alma
if (isset($_GET['id'])) {
    $group_id = $_GET['id'];

    // Grup bilgilerini alma sorgusu
    $sql = "SELECT group_name FROM `Groups` WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $group_id);
    $stmt->execute();
    $stmt->bind_result($group_name);
    $stmt->fetch();
    $stmt->close();
}

// Grup güncelleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_group'])) {
    $group_id = $_POST['group_id'];
    $group_name = $_POST['group_name'];

    // Grup güncelleme sorgusu
    $sql = "UPDATE `Groups` SET group_name = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("si", $group_name, $group_id);
        if ($stmt->execute()) {
            $success_message = "Grup başarıyla güncellendi.";
        } else {
            $error_message = "Grup güncellenirken hata oluştu: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = "Hazırlama hatası: " . $conn->error;
    }
}

// Grup silme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_group'])) {
    $group_id = $_POST['group_id'];

    // Grup silme ve ilişkili e-postaları silme sorgusu
    $sql = "DELETE FROM `Groups` WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $group_id);
    if ($stmt->execute()) {
        $sql_delete_emails = "DELETE FROM `EmailGroups` WHERE group_id = ?";
        $stmt_delete_emails = $conn->prepare($sql_delete_emails);
        $stmt_delete_emails->bind_param("i", $group_id);
        $stmt_delete_emails->execute();
        $stmt_delete_emails->close();
        $success_message = "Grup ve ilişkili e-postalar başarıyla silindi.";
    } else {
        $error_message = "Grup silinirken hata oluştu: " . $stmt->error;
    }
    $stmt->close();
}

// Toplu işlem
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $selected_emails = $_POST['selected_emails'];

    if ($action == 'remove') {
        foreach ($selected_emails as $email_id) {
            $delete_group_sql = "DELETE FROM EmailGroups WHERE email_id = ? AND group_id = ?";
            $stmt_group = $conn->prepare($delete_group_sql);
            $stmt_group->bind_param("ii", $email_id, $group_id);
            if (!$stmt_group->execute()) {
                $error_message .= "E-posta ID'si $email_id gruptan çıkarılırken hata oluştu: " . $stmt_group->error . "<br>";
            
                
                echo "$email_id";
            }
            $stmt_group->close();
        }
        if (empty($error_message)) {
            $success_message = "Seçili e-postalar gruptan başarıyla çıkarıldı.";
        }
    }
}

// El ile E-posta Ekleme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_email'])) {
    $email = $_POST['email'];
    $name = $_POST['name'];

    // EmailAddresses tablosunda kontrol et
    $check_email_sql = "SELECT id FROM EmailAddresses WHERE email = ?";
    $stmt = $conn->prepare($check_email_sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        // E-posta yoksa ekle
        $insert_email_sql = "INSERT INTO EmailAddresses (email, name) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($insert_email_sql);
        $stmt_insert->bind_param("ss", $email, $name);
        $stmt_insert->execute();
        $email_id = $stmt_insert->insert_id;
    } else {
        // E-posta varsa id'sini al
        $stmt->bind_result($email_id);
        $stmt->fetch();
    }
    $stmt->close();

    // EmailGroups tablosuna ekle
    $insert_group_sql = "INSERT INTO EmailGroups (email_id, group_id) VALUES (?, ?)";
    $stmt_group = $conn->prepare($insert_group_sql);
    $stmt_group->bind_param("ii", $email_id, $group_id);
    if ($stmt_group->execute()) {
        $success_message = "E-posta başarıyla gruba eklendi.";
    } else {
        $error_message = "E-posta gruba eklenirken hata oluştu: " . $stmt_group->error;
    }
    $stmt_group->close();
}

// CSV Dosyası ile E-posta Ekleme
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_csv'])) {
    $csv_file = $_FILES['csv_file']['tmp_name'];

    if (($handle = fopen($csv_file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $email = $data[0];
            $name = $data[1];

            // EmailAddresses tablosunda kontrol et
            $check_email_sql = "SELECT id FROM EmailAddresses WHERE email = ?";
            $stmt = $conn->prepare($check_email_sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 0) {
                // E-posta yoksa ekle
                $insert_email_sql = "INSERT INTO EmailAddresses (email, name) VALUES (?, ?)";
                $stmt_insert = $conn->prepare($insert_email_sql);
                $stmt_insert->bind_param("ss", $email, $name);
                $stmt_insert->execute();
                $email_id = $stmt_insert->insert_id;
            } else {
                // E-posta varsa id'sini al
                $stmt->bind_result($email_id);
                $stmt->fetch();
            }
            $stmt->close();

            // EmailGroups tablosuna ekle
            $insert_group_sql = "INSERT INTO EmailGroups (email_id, group_id) VALUES (?, ?)";
            $stmt_group = $conn->prepare($insert_group_sql);
            $stmt_group->bind_param("ii", $email_id, $group_id);
            $stmt_group->execute();
            $stmt_group->close();
        }
        fclose($handle);
        $success_message = "CSV dosyasındaki e-postalar başarıyla gruba eklendi.";
    } else {
        $error_message = "CSV dosyası yüklenirken hata oluştu.";
    }
}

// Gruba eklenmiş e-postaları listeleyen sorgu
$sql_emails = "SELECT ea.id, ea.email, ea.name, ea.date
              FROM EmailAddresses ea
              INNER JOIN EmailGroups eg ON ea.id = eg.email_id
              WHERE eg.group_id = ?";
$stmt_emails = $conn->prepare($sql_emails);
$stmt_emails->bind_param("i", $group_id);
$stmt_emails->execute();
$result_emails = $stmt_emails->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grup Düzenleme ve E-posta Yönetimi</title>
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
        .form-group input, .form-group textarea, .form-group button {
            width: calc(100% - 10px);
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-group textarea {
            height: 150px;
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
        <h2>Grup Düzenleme ve E-posta Yönetimi - <?php echo htmlspecialchars($group_name); ?></h2>
        <?php
        if (!empty($success_message)) {
            echo "<p class='success-message'>$success_message</p>";
        }
        if (!empty($error_message)) {
            echo "<p class='error-message'>$error_message</p>";
        }
        ?>
        <form action="?page=editgroup&id=<?php echo $group_id; ?>" method="post">
            <div class="form-group">
                <label for="email">E-posta:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="name">İsim:</label>
                <input type="text" id="name" name="name">
            </div>
            <div class="form-group">
                <button type="submit" name="add_email">E-posta Ekle</button>
            </div>
        </form>

        <form action="?page=editgroup&id=<?php echo $group_id; ?>" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="csv_file">CSV Dosyası:</label>
                <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
            </div>
            <div class="form-group">
                <button type="submit" name="upload_csv">CSV Dosyasını Yükle</button>
            </div>
        </form>

        <form action="?page=editgroup&id=<?php echo $group_id; ?>" method="post">
            <div class="form-group">
                <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                <label for="group_name">Grup Adı:</label>
                <input type="text" id="group_name" name="group_name" value="<?php echo $group_name; ?>" required>
            </div>
            <div class="form-group">
                <button type="submit" name="update_group">Grubu Güncelle</button>
            </div>
            <div class="form-group">
                <button type="submit" name="delete_group" onclick="return confirm('Bu grubu silmek istediğinize emin misiniz?')">Grubu Sil</button>
            </div>
        </form>

        <h2>Gruba Eklenmiş E-postalar</h2>
        <form action="?page=editgroup&id=<?php echo $group_id; ?>" method="post" id="email_form">
            <div class="form-group">
                <button type="button" onclick="performBulkAction('remove')">Seçilileri Gruptan Çıkar</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select_all" onclick="toggleSelectAll(this)"></th>
                        <th>ID</th>
                        <th>E-posta</th>
                        <th>İsim</th>
                        <th>Tarih</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_emails->num_rows > 0) {
                        while ($row = $result_emails->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><input type='checkbox' name='selected_emails[]' value='" . htmlspecialchars($row['id']) . "'></td>";
                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>Bu gruba henüz e-posta eklenmemiş.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            
        </form>
    </div>

    <script>
        function toggleSelectAll(source) {
            checkboxes = document.getElementsByName('selected_emails[]');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }

        function performBulkAction(action) {
            var form = document.getElementById('email_form');
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'bulk_action';
            input.value = action;
            form.appendChild(input);
            form.submit();
        }
    </script>
</body>
</html>
