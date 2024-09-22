<?php

// Veritabanı bağlantısını oluşturma
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Bağlantıyı kontrol etme
if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

// Form gönderildiğinde çalışacak kod
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_email'])) {
        $email = $_POST['email'];
        $name = $_POST['name'];
        $status = $_POST['status'];

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $sql_check = "SELECT id FROM EmailAddresses WHERE email = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows == 0) {
                $sql = "INSERT INTO EmailAddresses (email, name, status) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $email, $name, $status);

                if ($stmt->execute()) {
                    $success_message = "Yeni email başarıyla eklendi.";
                } else {
                    $error_message = "Email eklenirken hata oluştu: " . $conn->error;
                }

                $stmt->close();
            } else {
                $error_message = "Bu email adresi zaten mevcut: $email";
            }

            $stmt_check->close();
        } else {
            $error_message = "Geçersiz email adresi: $email";
        }
    } elseif (isset($_POST['upload_csv'])) {
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
            $csv_file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($csv_file, "r");

            if ($handle) {
                $invalid_emails = [];
                $existing_emails = [];
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $email = $data[0];
                    $name = $data[1];
                    $status = $data[2];

                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $sql_check = "SELECT id FROM EmailAddresses WHERE email = ?";
                        $stmt_check = $conn->prepare($sql_check);
                        $stmt_check->bind_param("s", $email);
                        $stmt_check->execute();
                        $stmt_check->store_result();

                        if ($stmt_check->num_rows == 0) {
                            $sql = "INSERT INTO EmailAddresses (email, name, status) VALUES (?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("sss", $email, $name, $status);

                            if (!$stmt->execute()) {
                                $error_message = "Bazı e-postalar eklenirken hata oluştu: " . $conn->error;
                                break;
                            }
                        } else {
                            $existing_emails[] = $email;
                        }

                        $stmt_check->close();
                    } else {
                        $invalid_emails[] = $email;
                    }
                }
                fclose($handle);
                if (!isset($error_message)) {
                    $success_message = "CSV dosyasındaki e-postalar başarıyla eklendi.";
                    if (!empty($invalid_emails)) {
                        $success_message .= " Geçersiz e-postalar atlandı: " . implode(", ", $invalid_emails);
                    }
                    if (!empty($existing_emails)) {
                        $success_message .= " Zaten mevcut olan e-postalar atlandı: " . implode(", ", $existing_emails);
                    }
                }
            } else {
                $error_message = "CSV dosyası okunamadı.";
            }
        } else {
            $error_message = "CSV dosyası yüklenirken hata oluştu.";
        }
    }
}

// Arama ve listeleme işlemleri
$search_query = isset($_POST['search']) ? $_POST['search'] : "";
$search_term = "%" . $search_query . "%";
$limit = isset($_POST['limit']) ? intval($_POST['limit']) : 100;
$page = isset($_GET['page_num']) ? intval($_GET['page_num']) : 1;
$offset = ($page - 1) * $limit;

if ($search_query != "") {
    $sql_count = "SELECT COUNT(*) AS total FROM EmailAddresses WHERE email LIKE ?";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->bind_param("s", $search_term);
    $stmt_count->execute();
    $result_count = $stmt_count->get_result();
    $total_results = $result_count->fetch_assoc()['total'];

    $total_pages = ceil($total_results / $limit);

    $sql = "SELECT id, name, email, status, date FROM EmailAddresses WHERE email LIKE ? ORDER BY id DESC LIMIT ?, ?";
} else {
    $sql_count = "SELECT COUNT(*) AS total FROM EmailAddresses";
    $result_count = $conn->query($sql_count);
    $total_results = $result_count->fetch_assoc()['total'];

    $total_pages = ceil($total_results / $limit);

    $sql = "SELECT id, name, email, status, date FROM EmailAddresses ORDER BY id DESC LIMIT ?, ?";
}

$stmt = $conn->prepare($sql);

if ($search_query != "") {
    $stmt->bind_param("sii", $search_term, $offset, $limit);
} else {
    $stmt->bind_param("ii", $offset, $limit);
}

$stmt->execute();
$result = $stmt->get_result();

$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mail Adresleri Yönetimi</title>
    <style>
        .success-message { color: green; }
        .error-message { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Mail Adresleri Yönetimi</h2>
        <?php
        if (!empty($success_message)) {
            echo "<p class='success-message'>$success_message</p>";
        }
        if (!empty($error_message)) {
            echo "<p class='error-message'>$error_message</p>";
        }
        ?>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?page=Contact_Adress" method="post">
            <div class="form-group">
                <label for="name">Ad:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email Adresi:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="status">Durum:</label>
                <select id="status" name="status" required>
                    <option value="1">Aktif</option>
                    <option value="0">Pasif</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" name="add_email">Ekle</button>
            </div>
        </form>
        
        <h3>CSV Dosyasından Toplu E-posta Ekle</h3>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?page=Contact_Adress" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="csv_file">CSV Dosyası:</label>
                <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
            </div>
            <div class="form-group">
                <button type="submit" name="upload_csv">Yükle ve Ekle</button>
            </div>
        </form>

        <div class="pagination">
            <?php
            $prev_page = $page > 1 ? $page - 1 : 1;
            $next_page = $page < $total_pages ? $page + 1 : $total_pages;

            echo "<a href=\"{$_SERVER['PHP_SELF']}?page=Contact_Adress&page_num=1\">İlk</a>";
            echo "<a href=\"{$_SERVER['PHP_SELF']}?page=Contact_Adress&page_num=$prev_page\">Önceki</a>";

            for ($i = max(1, $page - 5); $i <= min($page + 5, $total_pages); $i++) {
                echo "<a href=\"{$_SERVER['PHP_SELF']}?page=Contact_Adress&page_num=$i\"";
                if ($i == $page) echo " class=\"active\"";
                echo ">$i</a>";
            }

            echo "<a href=\"{$_SERVER['PHP_SELF']}?page=Contact_Adress&page_num=$next_page\">Sonraki</a>";
            echo "<a href=\"{$_SERVER['PHP_SELF']}?page=Contact_Adress&page_num=$total_pages\">Son</a>";
            ?>
        </div>
        
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>?page=Contact_Adress" method="post" id="form_liste">
            <table border="0" width="100%">
                <tr align='right'>
                    <td>
                        <label for="limit">Her sayfa </label>
                        <select id="limit" name="limit" onchange="this.form.submit()">
                            <option value="100" <?php if ($limit == 100) echo 'selected'; ?>>100</option>
                            <option value="250" <?php if ($limit == 250) echo 'selected'; ?>>250</option>
                            <option value="500" <?php if ($limit == 500) echo 'selected'; ?>>500</option>
                            <option value="1000" <?php if ($limit == 1000) echo 'selected'; ?>>1000</option>
                        </select>
                        <label for="limit"> Sonuç</label>
                    </td>
                    <td>
                        <form style="width: max-content;" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=Contact_Adress" method="post">
                            <input style="width: max-content;" type="text" id="search" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
                            <button type="submit">Ara</button>
                        </form>
                    </td>
                </tr>
            </table>
            <table>
                <thead>
                    <tr>
                        <th colspan="6" align="center">
                            <form id="add-to-group-form2">
                                <label for="group_id">Seçilileri</label>
                                <select id="group_id" name="group_id" required>
                                    <?php
                                    // Grupları veritabanından çekme
                                    $sql_groups = "SELECT id, group_name FROM Groups";
                                    $result_groups = $conn->query($sql_groups);

                                    if ($result_groups->num_rows > 0) {
                                        while ($row = $result_groups->fetch_assoc()) {
                                            echo "<option value='" . $row['id'] . "'>" . $row['group_name'] . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <button type="button" onclick="addToGroup()">grubuna Ekle</button>
                            </form>
                            <button type="button" onclick="topluIslem('aktif')">Seçilileri Aktif Yap</button>
                            <button type="button" onclick="topluIslem('pasif')">Seçilileri Pasif Yap</button>
                            <button type="button" onclick="topluIslem('sil')">Seçilileri Sil</button>
                        </th>
                    </tr>
                    <tr>
                        <td><input type="checkbox" id="selectAll" name="selectAll" onclick="selectAllChanged()"></td>
                        <th>ID</th>
                        <th>Mail</th>
                        <th>Ad</th>
                        <th>Tarih</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><input class='emailCheckbox' type="checkbox" name="selected_emails[]" value="<?php echo $row['id']; ?>"></td>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo $row['status'] == 1 ? 'Aktif' : 'Pasif'; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </form>
        
    </div>

    <script>
        document.getElementById('select_all').onclick = function() {
            var checkboxes = document.querySelectorAll('input[type="checkbox"]');
            for (var checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        }

        function islem(aydi, islemTuru, status) {
            const postData = {
                islemaydi: aydi,
                islem: islemTuru,
                statu: status
            };

            fetch('Pages/islem.php', {
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

        function selectAllChanged() {
            var checkboxes = document.getElementsByClassName('emailCheckbox');
            var selectAllCheckbox = document.getElementById('selectAll');

            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = selectAllCheckbox.checked;
            }
        }

        function topluIslem(islemTuru) {
            var checkboxes = document.querySelectorAll('input[name="selected_emails[]"]:checked');
            var selectedIds = [];
            checkboxes.forEach(function(checkbox) {
                selectedIds.push(checkbox.value);
            });

            if (selectedIds.length === 0) {
                alert("Lütfen işlem yapmak için en az bir e-posta seçin.");
                return;
            }

            const postData = {
                selectedIds: selectedIds,
                islem: islemTuru
            };

            fetch('Pages/islem.php', {
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

        function addToGroup() {
            var checkboxes = document.querySelectorAll('input[name="selected_emails[]"]:checked');
            var selectedIds = [];
            checkboxes.forEach(function(checkbox) {
                selectedIds.push(checkbox.value);
            });

            if (selectedIds.length === 0) {
                alert("Lütfen işlem yapmak için en az bir e-posta seçin.");
                return;
            }

            var group_id = document.getElementById('group_id').value;

            const postData = {
                selectedIds: selectedIds,
                group_id: group_id,
                islem: 'add_to_group'
            };

            fetch('Pages/islem.php', {
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
</body>
</html>

<?php $conn->close(); ?>
