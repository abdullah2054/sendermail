
<?php
session_start();


if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/setting/config.php')) {
    // Config dosyası yoksa setup sayfasına yönlendir
    header('Location: setting/setup.php');
    exit;
} else {

// Config dosyası varsa dahil et ve veritabanına bağlan
require $_SERVER['DOCUMENT_ROOT'] . '/setting/config.php'; }


 



//$_SESSION['admin']="";

if (!isset($_SESSION['admin']) || empty($_SESSION['admin'])) {
    // Eğer admin oturumu yoksa veya boşsa, login.php sayfasına yönlendirin veya içeriği dahil edin
    
    include("login.php");
   
} else {
 
?>  <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <style>
    
       .container {
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    width: 80%;
    margin-top: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

table, th, td {
    border: 1px solid #ccc;
}

th, td {
    padding: 10px;
    text-align: left;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
}

.form-group input,
.form-group select,
.form-group button {
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

.pagination {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}

.pagination a {
    margin: 0 5px;
    padding: 10px 20px;
    text-decoration: none;
    background-color: #6e8efb;
    color: white;
    border-radius: 4px;
}

.pagination a:hover {
    background-color: #556cd6;
}

.pagination .active {
    background-color: #556cd6;
    pointer-events: none;
}

body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
}

.sidebar {
    width: 200px;
    background-color: #333;
    color: white;
    height: 100vh;
    padding-top: 20px;
}

.sidebar a {
    display: block;
    color: white;
    padding: 15px;
    text-decoration: none;
}

.sidebar a:hover {
    background-color: #575757;
}

.content {
    flex-grow: 1;
    padding: 20px;
}

.header {
    background-color: #333;
    color: white;
    padding: 10px;
    text-align: right;
}

    </style>
</head>
<body>
   
    <div class="sidebar">
        <h2>Menü</h2>
        <a href="?page=Home">Anasayfa</a>
        <a href="?page=Contact_Adress">Aboneler</a>
        <a href="?page=Groups">Gruplar</a>
        <a href="?page=Mail_Drafts">Taslakları</a>
        <a href="?page=Tasks">Bültenler</a>
        <a href="?page=Setting">Ayarlar</a>
        <a href="?page=Logout">Çıkış Yap</a>
    </div>
     
    <div class="content">
        <div class="header">
            Hoş geldiniz, <?php echo $_SESSION['admin']; ?>
            
     </div>
        
                          <?php
   $page=$_GET["page"];
   
   if ($page=="") {$page="Home";}
   include("./Pages/".$page.".php");
  
   
  
?>
      

        
        
    </div>
</body>
</html>
 <?php
   
   
}
?>


