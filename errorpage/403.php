<!DOCTYPE html>

<?php
$servernamefile = "../server/servername.txt";

//-------------------------------------

$domain = $_SERVER['HTTP_HOST'];

//------------------------

$contactfile = "../server/contact.txt";

$termsfile = "../server/terms.txt";
$termsdata = file_get_contents($termsfile);

?>

<html lang="ja">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="../css/home.css">
<meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="apple-touch-icon" type="image/png" href="../favicon/apple-touch-icon-180x180.png">
<link rel="icon" type="image/png" href="../favicon/icon-192x192.png">
<title>403 Forbidden - <?php echo file_get_contents($servernamefile);?></title>
</head>

<body>

<div class="topbox">
    <div class="logo">
        <img src="../img/uwuzulogo.svg">
    </div>
</div>

<div class="terms">

    <div class="p3"><?php echo file_get_contents($servernamefile);?></div>
    <div class="p2c"><?php echo $domain;?></div>

    <div class="err404">
        <h1>403 Forbidden</h1>
        <p>...(*￣０￣)ノ< 閲覧権限が無いようです()<br>サービス管理者によって閲覧権限の変更をされた可能性がございます。</p>
    </div>

    <a href="../home/" class="irobutton">ホームへ行く</a>

</div>


</body>

</html>