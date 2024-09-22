<?php


// Veritabanı bağlantısını oluşturma
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Bağlantıyı kontrol etme
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

$success_message = "";
$error_message = "";

// Mail taslağı oluşturma veya güncelleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['create_draft'])) {
        $title = $_POST['title'];
        $subject = $_POST['subject'];
        $content = $_POST['content'];

        // Taslak oluşturma sorgusu
        $sql = "INSERT INTO EmailDrafts (title, subject, content, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("sss", $title, $subject, $content);
            if ($stmt->execute()) {
                $success_message = "Mail taslağı başarıyla oluşturuldu.";
            } else {
                $error_message = "Mail taslağı oluşturulurken hata oluştu: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error_message = "Hazırlama hatası: " . $conn->error;
        }
    } elseif (isset($_POST['edit_draft'])) {
        $draft_id = $_POST['draft_id'];
        $title = $_POST['title'];
        $subject = $_POST['subject'];
        $content = $_POST['content'];

        // Taslak güncelleme sorgusu
        $sql_update = "UPDATE EmailDrafts SET title=?, subject=?, content=?, updated_at=NOW() WHERE id=?";
        $stmt_update = $conn->prepare($sql_update);
        if ($stmt_update) {
            $stmt_update->bind_param("sssi", $title, $subject, $content, $draft_id);
            if ($stmt_update->execute()) {
                $success_message = "Mail taslağı başarıyla güncellendi.";
            } else {
                $error_message = "Mail taslağı güncellenirken hata oluştu: " . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
            $error_message = "Hazırlama hatası: " . $conn->error;
        }
    }
}

// Mevcut taslakları alma işlemi
$sql = "SELECT id, title, subject, content, created_at, updated_at FROM EmailDrafts";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Taslakları Yönetimi</title>
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
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
        <h2>Mail Taslakları Yönetimi</h2>
        <?php
        if (!empty($success_message)) {
            echo "<p class='success-message'>$success_message</p>";
        }
        if (!empty($error_message)) {
            echo "<p class='error-message'>$error_message</p>";
        }
        ?>
        <div class="form-container">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?page=Mail_Drafts" method="post">
                <div class="form-group">
                    <label for="title">Taslak Başlığı:</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="subject">Konu:</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="form-group">
                    <label for="content">İçerik:</label>
                    <textarea id="content" name="content" required></textarea>
                </div>
                <div class="form-group">
                    <input type="hidden" id="draft_id" name="draft_id">
                    <button type="submit" name="create_draft">Taslak Oluştur</button>
                </div>
            </form>
        </div>
        <script>
            CKEDITOR.replace('content');
        </script>

        <h2>Mevcut Taslaklar</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Taslak Başlığı</th>
                    <th>Konu</th>
                    <th>Oluşturma Tarihi</th>
                    <th>Güncelleme Tarihi</th>
                    <th>Düzenle</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        // İçeriği JSON formatında encode ediyoruz
                        $content = json_encode($row['content']);
                        echo "<tr><td>" . $row['id'] . "</td><td>" . $row['title'] . "</td><td>" . $row['subject'] . "</td><td>" . $row['created_at'] . "</td><td>" . $row['updated_at'] . "</td><td><button onclick='editDraft(" . $row['id'] . ", \"" . addslashes($row['title']) . "\", \"" . addslashes($row['subject']) . "\", " . $content . ")'>Düzenle</button></td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Hiç taslak bulunamadı.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        function editDraft(id, title, subject, content) {
            document.getElementById('draft_id').value = id;
            document.getElementById('title').value = title;
            document.getElementById('subject').value = subject;
            
            // CKEditor instance'ını güncelle
            CKEDITOR.instances.content.setData(content);

            // Form submit buttonunu güncelleme moduna geçir
            var submitButton = document.querySelector('button[type="submit"]');
            submitButton.innerHTML = 'Taslak Güncelle';
            submitButton.setAttribute('name', 'edit_draft');
        }
    </script>
</body>
</html>
