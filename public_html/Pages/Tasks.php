<?php


// Veritabanı bağlantısını kurma
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Form gönderildiyse işlemleri gerçekleştir
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['newsletter_name'])) {
        $newsletter_name = $_POST['newsletter_name'];
        $draft_id = $_POST['draft_id'];
        $group_id = $_POST['group_id'];

        // Bülten adını Newsletters tablosuna ekle veya mevcut olanı kullan
        $newsletter_query = "INSERT INTO Newsletters (name) VALUES ('$newsletter_name')
                             ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)";
        $conn->query($newsletter_query);
        $newsletter_id = $conn->insert_id;

        // Seçilen taslak bilgilerini al
        $draft_query = "SELECT * FROM EmailDrafts WHERE id = $draft_id";
        $draft_result = $conn->query($draft_query);
        $draft = $draft_result->fetch_assoc();

        $subject = $draft['subject'];
        $content = $draft['content'];

        // Seçilen grup bilgilerini al
        $group_query = "SELECT * FROM Groups WHERE id = $group_id";
        $group_result = $conn->query($group_query);
        $group = $group_result->fetch_assoc();

        $group_name = $group['group_name'];

        // Seçilen grup üyelerinin email adreslerini al
        $email_query = "SELECT ea.email FROM EmailAddresses ea
                        JOIN EmailGroups eg ON ea.id = eg.email_id
                        WHERE eg.group_id = $group_id AND ea.status = '1'";
        $email_result = $conn->query($email_query);

        // Her email adresi için görev oluştur
        if ($email_result->num_rows > 0) {
            while($row = $email_result->fetch_assoc()) {
                $email = $row['email'];
                $insert_task = "INSERT INTO Tasks (email, group_id, group_name, subject, content, status, created_at, newsletter_id)
                                VALUES ('$email', $group_id, '$group_name', '$subject', '$content', 0, NOW(), $newsletter_id)";
                $conn->query($insert_task);
            }
        }

        echo "Bülten başarıyla oluşturuldu ve e-postalar gönderilmeye başlandı!";
    } 
    
   
}

 if (isset($_GET['islem'])) {
        $newsletter_id = $_GET['newsletter_id'];
        // İlgili bültenin tüm görevlerini iptal et
        $cancel_tasks_query = "UPDATE Tasks SET status = -1 WHERE newsletter_id = $newsletter_id AND status = 0";
        echo "UPDATE Tasks SET status = -1 WHERE newsletter_id = $newsletter_id AND status = 0";
        $conn->query($cancel_tasks_query);

        echo "Bülten iptal edildi!";
    }
    



    
    
// Taslak ve grup bilgilerini al
$drafts_query = "SELECT * FROM EmailDrafts";
$drafts_result = $conn->query($drafts_query);

$groups_query = "SELECT * FROM Groups";
$groups_result = $conn->query($groups_query);

// Görevleri (bültenleri) al
$tasks_query = "SELECT newsletter_id, n.name as newsletter_name, COUNT(t.id) as total_tasks,
                SUM(CASE WHEN t.status = 1 THEN 1 ELSE 0 END) AS success_count,
                SUM(CASE WHEN t.status = -1 THEN 1 ELSE 0 END) AS failure_count,
                SUM(CASE WHEN t.status = 0 THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN t.status = 2 THEN 1 ELSE 0 END) AS open_count,
                MAX(t.created_at) as last_created
                FROM Tasks t
                JOIN Newsletters n ON t.newsletter_id = n.id
                GROUP BY newsletter_id, newsletter_name
                ORDER BY last_created DESC";
$tasks_result = $conn->query($tasks_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bülten Oluştur</title>
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
        .form-group input, .form-group select, .form-group button {
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
        .cancel-button {
            background-color: #ff4d4d;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .cancel-button:hover {
            background-color: #ff0000;
        }
        .details-button {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .details-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Bülten Oluştur</h1>
        <form method="POST">
            <div class="form-group">
                <label for="newsletter_name">Bülten Adı:</label><br>
                <input type="text" id="newsletter_name" name="newsletter_name" required>
            </div>
            <div class="form-group">
                <label for="draft_id">Taslak Seçin:</label><br>
                <select id="draft_id" name="draft_id" required>
                    <?php while($row = $drafts_result->fetch_assoc()) { ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['title']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <label for="group_id">Grup Seçin:</label><br>
                <select id="group_id" name="group_id" required>
                    <?php while($row = $groups_result->fetch_assoc()) { ?>
                        <option value="<?php echo $row['id']; ?>"><?php echo $row['group_name']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="form-group">
                <button type="submit">Bülten Oluştur</button>
            </div>
        </form>

        <h2>Önceden Yayınlanan Bültenler</h2>
        <table>
            <thead>
                <tr>
                    <th>Bülten Adı</th>
                    <th>Oluşturma Tarihi</th>
                    <th>Gönderilen</th>
                    <th>Gönderilemeyen</th>
                    <th>Bekleyenler</th>
                    <th>Okunanlar</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($tasks_result->num_rows > 0) {
                    while($row = $tasks_result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['newsletter_name']}</td>
                                <td>{$row['last_created']}</td>
                                <td>{$row['success_count']}</td>
                                <td>{$row['failure_count']}</td>
                                <td>{$row['pending_count']}</td>
                                <td>{$row['open_count']}</td>
                                <td>
                                    <form method='GET' action='' style='display:inline;'>
                                        <input type='hidden' name='page' value='newsletter_detail'>
                                        <input type='hidden' name='newsletter_id' value='{$row['newsletter_id']}'>
                                        <button type='submit' name='view_details' class='details-button'>Detayları Gör</button>
                                    </form>
                                    <form method='GET' style='display:inline;'>
                                        <input type='hidden' name='page' value='{$page}'>
                                        <input type='hidden' name='newsletter_id' value='{$row['newsletter_id']}'>
                                        <input type='hidden' name='islem' value='cancel_newsletter'>
                                        <button type='submit' name='cancel_newsletter' class='cancel-button'>İptal Et</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>Henüz oluşturulmuş bir bülten yok.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
