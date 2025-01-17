<?php

$servernamefile = "server/servername.txt";

require('db.php');


// 変数の初期化
$current_date = null;
$message_array = array();
$error_message = array();
$pdo = null;
$stmt = null;
$res = null;
$option = null;

$row["userid"] = "";
$row["password"] = "";

$ruserid = "";
$rpassword = "";

$userid = "";
$_SESSION["userid"]="";

$password = null;
$_SESSION["password"]="";


session_start();
// データベースに接続
try {

    $option = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => false
    );
    $pdo = new PDO('mysql:charset=utf8mb4;dbname='.DB_NAME.';host='.DB_HOST , DB_USER, DB_PASS, $option);

} catch(PDOException $e) {

    // 接続エラーのときエラー内容を取得する
    $error_message[] = $e->getMessage();
}

if( !empty($_POST['btn_submit']) ) {


    //$row['userid'] = "daichimarukn";

    $userid = $_POST['userid'];
    $password = $_POST['password'];


    $options = array(
        // SQL実行失敗時に例外をスルー
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // デフォルトフェッチモードを連想配列形式に設定
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // バッファードクエリを使う（一度に結果セットを全て取得し、サーバー負荷を軽減）
        // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );

    $dbh = new PDO('mysql:charset=utf8mb4;dbname='.DB_NAME.';host='.DB_HOST , DB_USER, DB_PASS, $option);


    $result = $dbh->prepare("SELECT userid, password, loginid FROM account WHERE userid = :userid");

    $result->bindValue(':userid', $userid);
    // SQL実行
    $result->execute();



    // ... (前略)
        // IDの入力チェック
	if( empty($userid) ) {
		$error_message[] = 'ユーザーIDを入力してください。';
	} else {

        if( empty($password) ) {
            $error_message[] = 'パスワードを入力してください。';
        } else {

            if($result->rowCount() > 0) {
                $row = $result->fetch(); // ここでデータベースから取得した値を $row に代入する

                if($row["userid"] == $userid){
                    if(password_verify($password,$row["password"])){
                        $_SESSION['admin_login'] = true;

                        $_SESSION['userid'] = $userid;
                        $_SESSION['loginid'] = $row["loginid"];
                        // リダイレクト先のURLへ転送する
                        $url = 'check.php';
                        header('Location: ' . $url, true, 303);

                        // すべての出力を終了
                        exit;
                    }
                    else{
                        $error_message[] = 'IDまたはパスワードが違います'; 
                    }
                }else{
                    $error_message[] = 'IDまたはパスワードが違います'; 
                }
            }
            else {
                $error_message[] = 'IDまたはパスワードが違います';
            }
        }

    }

    // ... (後略)



}

// データベースの接続を閉じる
$pdo = null;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="css/style.css">
<link rel="apple-touch-icon" type="image/png" href="favicon/apple-touch-icon-180x180.png">
<link rel="icon" type="image/png" href="favicon/icon-192x192.png">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ログイン - <?php echo file_get_contents($servernamefile);?></title>
</head>

<script src="js/back.js"></script>
<body>

<div class="leftbox">
    <div class="logo">
        <img src="img/uwuzulogo.svg">
    </div>

    <div class="textbox">
        <h1>ログイン</h1>

        <p>IDとパスワードを入力してください！</p>

            <?php if( !empty($error_message) ): ?>
                <ul class="errmsg">
                    <?php foreach( $error_message as $value ): ?>
                        <p>・ <?php echo $value; ?></p>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <form class="formarea" method="post">
                <!--ユーザーネーム関係-->
                <div>
                    <label for="userid">ユーザーID</label>
                    <input onInput="checkForm(this)" id="userid" class="inbox" type="text" name="userid" value="<?php if( !empty($_SESSION['userid']) ){ echo htmlspecialchars( $_SESSION['userid'], ENT_QUOTES, 'UTF-8'); } ?>">
                </div>
                <!--個人情報関係-->

                <!--アカウント関連-->
                <div>
                    <label for="password">パスワード</label>
                    <input onInput="checkForm(this)" id="password" class="inbox" type="password" name="password" value="<?php if( !empty($_SESSION['password']) ){ echo htmlspecialchars( $_SESSION['password'], ENT_QUOTES, 'UTF-8'); } ?>">
                </div>
                
                <input type="submit" name="btn_submit" class="irobutton" value="ログイン">
            </form>

            <div class="btnbox">
                <a href="index.php" class="sirobutton">戻る</a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

function checkForm($this)
{
    var str=$this.value;
    while(str.match(/[^A-Z^a-z\d\-]/))
    {
        str=str.replace(/[^A-Z^a-z\d\-]/,"");
    }
    $this.value=str;
}


window.onload = function(){
var ele = document.getElementsByTagName("body")[0];
var n = Math.floor(Math.random() * 3); // 3枚の画像がある場合
ele.style.backgroundImage = "url(img/titleimg/"+n+".png)";
}

</script>


</body>
</html>