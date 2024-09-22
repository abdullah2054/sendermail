<?php

if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/setting/config.php')) {
    // Config dosyası yoksa setup sayfasına yönlendir
    header('Location: setting/setup.php');
    exit;
} else {

// Config dosyası varsa dahil et ve veritabanına bağlan
require $_SERVER['DOCUMENT_ROOT'] . '/setting/config.php'; }

// Veritabanı bağlantısını oluşturma
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Bağlantıyı kontrol etme
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

// JSON verisini al
$postData = json_decode(file_get_contents("php://input"), true);

// Geçersiz istek kontrolü
if (!$postData || !isset($postData['islem'])) {
    die("Geçersiz istek");
}

// Seçilen e-posta ID'lerini al
$selectedIds = $postData['selectedIds'];
$islemTuru = $postData['islem'];

// İşlem türüne göre işlem yap
switch ($islemTuru) {
    case 'aktif':
        $sql = "UPDATE EmailAddresses SET status = 1 WHERE id IN (" . implode(",", $selectedIds) . ")";
        break;
    case 'pasif':
        $sql = "UPDATE EmailAddresses SET status = 0 WHERE id IN (" . implode(",", $selectedIds) . ")";
        break;
    case 'sil':
        $sql = "DELETE FROM EmailAddresses WHERE id IN (" . implode(",", $selectedIds) . ")";
        break;
    case 'add_to_group':
        if (!isset($postData['group_id'])) {
            die("Grup ID'si eksik");
        }
        $group_id = $postData['group_id'];

        // Seçilen e-postaları gruba ekleme
        $success_count = 0;
        foreach ($selectedIds as $email_id) {
            // E-postayı gruba ekleme sorgusu
            $sql = "INSERT INTO EmailGroups (email_id, group_id) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $email_id, $group_id);

            if ($stmt->execute()) {
                $success_count++;
            } else {
                $response = [
                    'success' => false,
                    'message' => "E-postalar gruplanırken hata oluştu: " . $conn->error
                ];
                echo json_encode($response);
                $stmt->close();
                $conn->close();
                exit;
            }

            $stmt->close();
        }

        $response = [
            'success' => true,
            'message' => "$success_count e-posta başarıyla gruba eklendi."
        ];
        echo json_encode($response);
        $conn->close();
        exit;
    
    default:
        $response = [
            'success' => false,
            'message' => "Geçersiz işlem türü"
        ];
        echo json_encode($response);
        $conn->close();
        exit;
}

// Sorguyu çalıştırma
if ($conn->query($sql) === TRUE) {
    $response = [
        'success' => true,
        'message' => "Seçilen e-posta adresleri başarıyla işlendi."
    ];
} else {
    $response = [
        'success' => false,
        'message' => "İşlem sırasında hata oluştu: " . $conn->error
    ];
}

// Bağlantıyı kapat
$conn->close();

// JSON yanıtı döndürme
header('Content-Type: application/json');
echo json_encode($response);
?>
