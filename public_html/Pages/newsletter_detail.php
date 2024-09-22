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




if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Varsayılan değerler
$results_per_page = 100;
if (isset($_GET['results_per_page']) && is_numeric($_GET['results_per_page'])) {
    $results_per_page = (int)$_GET['results_per_page'];
}

$page = 1;
if (isset($_GET['pageno']) && is_numeric($_GET['pageno'])) {
    $page = (int)$_GET['pageno'];
}
$start_from = ($page-1) * $results_per_page;

// Statü filtresi
$status_filter = -1; // Varsayılan olarak "Hepsi" seçeneği
if (isset($_GET['status']) && is_numeric($_GET['status'])) {
    $status_filter = (int)$_GET['status'];
}

if (isset($_GET['newsletter_id']) && is_numeric($_GET['newsletter_id'])) {
   
    $newsletter_id = (int)$_GET['newsletter_id'];

    // Bülten bilgilerini al - prepared statement kullanımı
    $stmt = $conn->prepare("SELECT * FROM Newsletters WHERE id = ?");
    $stmt->bind_param("i", $newsletter_id);
    $stmt->execute();
    $newsletter_result = $stmt->get_result();
    $newsletter = $newsletter_result->fetch_assoc();
    $stmt->close();

    // Görevleri al ve sayfalama uygula - prepared statement kullanımı
    $query = "SELECT * FROM Tasks WHERE newsletter_id = ?";
    
    if ($status_filter != 3) {
        $query .= " AND status = ?";
    }
    $query .= " LIMIT ?, ?";
    
    $stmt = $conn->prepare($query);
    
    if ($status_filter != 3) {
        $stmt->bind_param("iiii", $newsletter_id, $status_filter, $start_from, $results_per_page);
    } else {
        $stmt->bind_param("iii", $newsletter_id, $start_from, $results_per_page);
    }
    
    $stmt->execute();
    $tasks_result = $stmt->get_result();
    $stmt->close();

    // Toplam kayıt sayısını al - prepared statement kullanımı
    $count_query = "SELECT COUNT(id) AS total FROM Tasks WHERE newsletter_id = ?";
    if ($status_filter != 3) {
        $count_query .= " AND status = ?";
    }
    
    $stmt = $conn->prepare($count_query);
    
    if ($status_filter != 3) {
        $stmt->bind_param("ii", $newsletter_id, $status_filter);
    } else {
        $stmt->bind_param("i", $newsletter_id);
    }
    
    $stmt->execute();
    $total_tasks_result = $stmt->get_result();
    $total_tasks_row = $total_tasks_result->fetch_assoc();
    $total_tasks = $total_tasks_row['total'];
    $total_pages = ceil($total_tasks / $results_per_page);
    $stmt->close();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Bülten Detayları</title>
    <!-- Stil kısmı değişmeden kalabilir -->
    <script>
        // Tümünü seç/kaldır fonksiyonu
        function toggleSelectAll(source) {
            checkboxes = document.getElementsByName('selected_tasks[]');
            for(var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
        
        
    function topluislem() {
    var selectedIds = [];
    var checkboxes = document.getElementsByName('selected_tasks[]');
    
    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
            selectedIds.push(checkboxes[i].value);
        }
    }
    
    

    if (selectedIds.length === 0) {
        alert("Lütfen işlem yapmak için en az bir e-posta seçin.");
        return;
    }

    const postData = {
        selectedIds: selectedIds,
        islem: document.getElementById("islem").value
    };

    fetch('Pages/bulk2.php', {
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
}
            
    </script>
</head>
<body>
    <div class="container">
        <h1>Bülten Detayları</h1>
        <?php if (isset($newsletter)) { ?>
            <h2><?php echo htmlspecialchars($newsletter['name']); ?></h2>
            <div class="results-per-page">
                <form method="GET">
                    <label for="results_per_page">Sayfa başına sonuç:</label>
                    <select id="results_per_page" name="results_per_page" onchange="this.form.submit()">
                        <option value="100" <?php if ($results_per_page == 100) echo 'selected'; ?>>100</option>
                        <option value="250" <?php if ($results_per_page == 250) echo 'selected'; ?>>250</option>
                        <option value="500" <?php if ($results_per_page == 500) echo 'selected'; ?>>500</option>
                        <option value="1000" <?php if ($results_per_page == 1000) echo 'selected'; ?>>1000</option>
                    </select>
                    <input type="hidden" name="page" value="newsletter_detail">
                    <input type="hidden" name="pageno" value="1">
                    <input type="hidden" name="newsletter_id" value="<?php echo $newsletter_id; ?>">
                    <label for="status">Durum:</label>
                    <select id="status" name="status" onchange="this.form.submit()">
                        <option value="-1" <?php if ($status_filter == 3) echo 'selected'; ?>>Hepsi</option>
                        <option value="1" <?php if ($status_filter == 1) echo 'selected'; ?>>Başarılı</option>
                        <option value="-1" <?php if ($status_filter == -1) echo 'selected'; ?>>Başarısız</option>
                        <option value="0" <?php if ($status_filter == 0) echo 'selected'; ?>>Beklemede</option>
                        <option value="2" <?php if ($status_filter == 2) echo 'selected'; ?>>Okundu</option>
                    </select>
                </form>
            </div>
         
              <div class="pagination">  
                 <?php
            $prev_page = $page > 1 ? $page - 1 : 1;
            $next_page = $page < $total_pages ? $page + 1 : $total_pages;

            echo "<a href=\"{$_SERVER['PHP_SELF']}?newsletter_id=$newsletter_id&results_per_page=$results_per_page&page=newsletter_detail&pageno=1&status=$status_filter\">İlk</a>";
            echo "<a href=\"{$_SERVER['PHP_SELF']}?newsletter_id=$newsletter_id&results_per_page=$results_per_page&page=newsletter_detail&pageno=$prev_page&status=$status_filter\">Önceki</a>";

            for ($i = max(1, $page - 5); $i <= min($page + 5, $total_pages); $i++) {
                echo "<a href=\"{$_SERVER['PHP_SELF']}?newsletter_id=$newsletter_id&results_per_page=$results_per_page&page=newsletter_detail&pageno=$i&status=$status_filter\"";
                if ($i == $page) echo " class=\"active\"";
                echo ">$i</a>";
            }

            echo "<a href=\"{$_SERVER['PHP_SELF']}?newsletter_id=$newsletter_id&results_per_page=$results_per_page&page=newsletter_detail&pageno=$next_page&status=$status_filter\">Sonraki</a>";
            echo "<a href=\"{$_SERVER['PHP_SELF']}?newsletter_id=$newsletter_id&results_per_page=$results_per_page&page=newsletter_detail&pageno=$total_pages&status=$status_filter\">Son</a>";
            ?>
            </div>


            <!-- Toplu işlem formu -->
            <form  id="form_liste"  name="form_liste">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" onclick="toggleSelectAll(this)"></th> <!-- Tümünü seç/kaldır -->
                            <th>Email</th>
                            <th>Konu</th>
                            <th>Durum</th>
                            <th>Oluşturma Tarihi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                                <select name="islem" id="islem">
                                    <option value="sil">Sil</option>
                                    <option value="iptalet">Gönderimi Durdurt</option>
                                    <option value="tekrarla">Tekrarla</option>
                                </select>
                                <input type="button" onclick="topluislem()" value="Toplu İşlem Uygula" >
                            </tr>
                        <?php while($row = $tasks_result->fetch_assoc()) { ?>
                            <tr>
                                <td><input type="checkbox" name="selected_tasks[]" value="<?php echo $row['id']; ?>"></td> <!-- Satır seçimi -->
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td><?php 
                                if ($row['status'] == 1) {echo  'Başarılı' ;}
                                if ($row['status'] == -1) {echo  'Başarısız' ;}  
                                if ($row['status'] == 0) {echo  'Beklemede' ;}  
                                if ($row['status'] == 2) {echo  'Okundu' ;}  ?></td>
                                <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            </tr>
                            
                            
                        <?php } ?>
                    </tbody>
                </table>

                <!-- Toplu işlem butonları -->
                
                
            </form>
            
        <?php } else { ?>
            <p>Geçersiz bülten ID'si.</p>
        <?php } ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
