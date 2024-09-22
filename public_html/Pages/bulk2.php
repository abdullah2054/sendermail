<?php
// Veritabanı bağlantısı kurma

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/setting/config.php')) {
    
    
   require $_SERVER['DOCUMENT_ROOT'] . '/setting/config.php'; 



// Config dosyası varsa dahil et ve veritabanına bağlan

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
    case 'tekrarla':
        $sql = "UPDATE Tasks SET status = 0 WHERE id IN (" . implode(",", $selectedIds) . ") and status!=0";
        break;
    case 'iptalet':
        $sql = "UPDATE Tasks SET status =-1 WHERE id IN (" . implode(",", $selectedIds) . ")and status>-1";
        break;
    case 'sil':
        $sql = "DELETE FROM Tasks WHERE id IN (" . implode(",", $selectedIds) . ")";
        break;

}

// Sorguyu çalıştırma
if ($conn->query($sql) === TRUE) {
    $response = [
        'success' => true,
        'message' => "Seçilen görevler  başarıyla işlendi."
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

} 
?>
