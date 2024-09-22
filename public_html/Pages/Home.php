<?php


// Veritabanı bağlantısını kurma
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Genel sayıları al
$email_count_query = "SELECT COUNT(*) AS count FROM EmailAddresses";
$email_count_result = $conn->query($email_count_query);
$email_count = $email_count_result->fetch_assoc()['count'];

$group_count_query = "SELECT COUNT(*) AS count FROM Groups";
$group_count_result = $conn->query($group_count_query);
$group_count = $group_count_result->fetch_assoc()['count'];

$draft_count_query = "SELECT COUNT(*) AS count FROM EmailDrafts";
$draft_count_result = $conn->query($draft_count_query);
$draft_count = $draft_count_result->fetch_assoc()['count'];

$newsletter_count_query = "SELECT COUNT(*) AS count FROM Newsletters";
$newsletter_count_result = $conn->query($newsletter_count_query);
$newsletter_count = $newsletter_count_result->fetch_assoc()['count'];

// Görev durumlarını al
$pending_tasks_query = "SELECT COUNT(*) AS count FROM Tasks WHERE status = 0";
$pending_tasks_result = $conn->query($pending_tasks_query);
$pending_tasks_count = $pending_tasks_result->fetch_assoc()['count'];

$successful_tasks_query = "SELECT COUNT(*) AS count FROM Tasks WHERE status = 1";
$successful_tasks_result = $conn->query($successful_tasks_query);
$successful_tasks_count = $successful_tasks_result->fetch_assoc()['count'];

$failed_tasks_query = "SELECT COUNT(*) AS count FROM Tasks WHERE status = -1";
$failed_tasks_result = $conn->query($failed_tasks_query);
$failed_tasks_count = $failed_tasks_result->fetch_assoc()['count'];

// Görev durumlarını al
$open_tasks_query = "SELECT COUNT(*) AS count FROM Tasks WHERE status = 2";
$open_tasks_result = $conn->query($open_tasks_query);
$open_tasks_count = $open_tasks_result->fetch_assoc()['count'];


// Ayarları al
$settings_query = "SELECT * FROM settings";
$settings_result = $conn->query($settings_query);
$settings = $settings_result->fetch_assoc();
$mailing_enabled = $settings['status'];
$last_sent_time = $settings['last_sent_time'];

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Genel Durum</title>
    <style>
     
        .container {
            max-width: 1200px;
            margin: auto;
        }
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin: 20px 0;
        }
        .card h2 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .card p {
            margin: 10px 0;
            font-size: 18px;
            color: #666;
        }
        .status {
            display: flex;
            justify-content: space-between;
        }
        .status div {
            width: 30%;
            text-align: center;
        }
        .status div h3 {
            margin: 0;
            font-size: 22px;
            color: #555;
        }
        .status div p {
            margin: 5px 0;
            font-size: 18px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2>Genel Durum</h2>
            <p>Toplam Mail: <?php echo $email_count; ?></p>
            <p>Toplam Mail Grubu: <?php echo $group_count; ?></p>
            <p>Toplam E-posta Taslağı: <?php echo $draft_count; ?></p>
            <p>Toplam Bülten: <?php echo $newsletter_count; ?></p>
        </div>
        <div class="card">
            <h2>Görev Durumları</h2>
            <div class="status">
                <div>
                    <h3>Bekleyen Görevler</h3>
                    <p><?php echo $pending_tasks_count; ?></p>
                </div>
                <div>
                    <h3>Başarılı Görevler</h3>
                    <p><?php echo $successful_tasks_count; ?></p>
                </div>
                <div>
                    <h3>Açılan Mailler</h3>
                    <p><?php echo $open_tasks_count; ?></p>
                </div>
                <div>
                    <h3>Başarısız Görevler</h3>
                    <p><?php echo $failed_tasks_count; ?></p>
                </div>
            </div>
        </div>
        <div class="card">
            <h2>Ayarlar</h2>
            <p>Gönderim Açık mı: <?php echo $mailing_enabled ? 'Evet' : 'Hayır'; ?></p>
            <p>En Son Gönderim Zamanı: <?php echo $last_sent_time; ?></p>
        </div>
    </div>
</body>
</html>
