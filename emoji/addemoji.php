<?php

$servernamefile = "../server/servername.txt";
function createUniqId(){
    list($msec, $sec) = explode(" ", microtime());
    $hashCreateTime = $sec.floor($msec*1000000);
    
    $hashCreateTime = strrev($hashCreateTime);

    return base_convert($hashCreateTime,10,36);
}

require('../db.php');

// 変数の初期化
$datetime = array();
$user_name = null;
$message = array();
$message_data = null;
$error_message = array();
$pdo = null;
$stmt = null;
$res = null;
$option = null;

session_start();

$userid = $_SESSION['userid'];
$username = $_SESSION['username'];

try {

    $option = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_MULTI_STATEMENTS => false
    );
    $pdo = new PDO('mysql:charset=UTF8;dbname='.DB_NAME.';host='.DB_HOST , DB_USER, DB_PASS, $option);

} catch(PDOException $e) {

    // 接続エラーのときエラー内容を取得する
    $error_message[] = $e->getMessage();
}

if(isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true) {

	$passQuery = $pdo->prepare("SELECT username,userid,loginid,admin FROM account WHERE userid = :userid");
	$passQuery->bindValue(':userid', $_SESSION['userid']);
	$passQuery->execute();
	$res = $passQuery->fetch();
	if(empty($res["userid"])){
		header("Location: ../login.php");
		exit;
	}elseif($_SESSION['loginid'] === $res["loginid"]){
	// セッションに値をセット
	$userid = $_SESSION['userid']; // セッションに格納されている値をそのままセット
	$username = $_SESSION['username']; // セッションに格納されている値をそのままセット
	$_SESSION['admin_login'] = true;
	$_SESSION['userid'] = $userid;
	$_SESSION['username'] = $username;
	$_SESSION['loginid'] = $res["loginid"];
	setcookie('userid', $userid, time() + 60 * 60 * 24 * 14);
	setcookie('username', $username, time() + 60 * 60 * 24 * 14);
	setcookie('loginid', $res["loginid"], time() + 60 * 60 * 24 * 14);
	setcookie('admin_login', true, time() + 60 * 60 * 24 * 14);
	}else{
		header("Location: ../login.php");
		exit;
	}

		
} elseif (isset($_COOKIE['admin_login']) && $_COOKIE['admin_login'] == true) {

	$passQuery = $pdo->prepare("SELECT username,userid,loginid,admin FROM account WHERE userid = :userid");
	$passQuery->bindValue(':userid', $_COOKIE['userid']);
	$passQuery->execute();
	$res = $passQuery->fetch();
	if(empty($res["userid"])){
		header("Location: ../login.php");
		exit;
	}elseif($_COOKIE['loginid'] === $res["loginid"]){
	// セッションに値をセット
	$userid = $_COOKIE['userid']; // クッキーから取得した値をセット
	$username = $_COOKIE['username']; // クッキーから取得した値をセット
	$_SESSION['admin_login'] = true;
	$_SESSION['userid'] = $userid;
	$_SESSION['username'] = $username;
	$_SESSION['loginid'] = $res["loginid"];
	setcookie('userid', $userid, time() + 60 * 60 * 24 * 14);
	setcookie('username', $username, time() + 60 * 60 * 24 * 14);
	setcookie('loginid', $res["loginid"], time() + 60 * 60 * 24 * 14);
	setcookie('admin_login', true, time() + 60 * 60 * 24 * 14);
	}else{
		header("Location: ../login.php");
		exit;
	}


} else {
	// ログインが許可されていない場合、ログインページにリダイレクト
	header("Location: ../login.php");
	exit;
}
if(empty($userid)){
	header("Location: ../login.php");
	exit;
} 
if(empty($username)){
	header("Location: ../login.php");
	exit;
} 

if(!($res["admin"] === "yes")){
	header("Location: ../login.php");
	exit;
}

if( !empty($pdo) ) {
	
	// データベース接続の設定
	$dbh = new PDO('mysql:charset=UTF8;dbname='.DB_NAME.';host='.DB_HOST, DB_USER, DB_PASS, array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
	));

	$userQuery = $dbh->prepare("SELECT username, userid, profile, role FROM account WHERE userid = :userid");
	$userQuery->bindValue(':userid', $userid);
	$userQuery->execute();
	$userData = $userQuery->fetch();

	$role = $userData["role"];

	$dbh = new PDO('mysql:charset=UTF8;dbname='.DB_NAME.';host='.DB_HOST , DB_USER, DB_PASS, $option);

	$rerole = $dbh->prepare("SELECT username, userid, password, mailadds, profile, iconname, iconcontent, icontype, iconsize, headname, headcontent, headtype, headsize, role, datetime FROM account WHERE userid = :userid");

    $rerole->bindValue(':userid', $userid);
    // SQL実行
    $rerole->execute();

    $userdata = $rerole->fetch(); // ここでデータベースから取得した値を $role に代入する

	
}



if( !empty($_POST['btn_submit']) ) {
	$emojiname = $_POST['emojiname'];
    $emojiinfo = $_POST['emojiinfo'];

    if (!empty($_FILES['image']['name'])) {
        $img = $_FILES['image'];
    }else{
        $error_message[] = '画像を選択してください～';
    }



    $options = array(
        // SQL実行失敗時に例外をスルー
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // デフォルトフェッチモードを連想配列形式に設定
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // バッファードクエリを使う（一度に結果セットを全て取得し、サーバー負荷を軽減）
        // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );

    $dbh = new PDO('mysql:charset=UTF8;dbname='.DB_NAME.';host='.DB_HOST , DB_USER, DB_PASS, $option);


    $query = $dbh->prepare('SELECT * FROM emoji WHERE emojiname = :emojiname limit 1');

    $query->execute(array(':emojiname' => $emojiname));

    $result = $query->fetch();

    // IDの入力チェック
	if( empty($emojiname) ) {
		$error_message[] = '絵文字IDを入力してください！';
	} else {

        // 文字数を確認
        if( 20 < mb_strlen($emojiname, 'UTF-8') ) {
			$error_message[] = 'IDは20文字以内で入力してください。';
		}

        if($result > 0){
            $error_message[] = 'このID('.$emojiname.')は既に使用されています。他のIDを作成してください。'; //このE-mailは既に使用されています。
        }

    }

	if( empty($error_message) ) {
		
		// 書き込み日時を取得
        $datetime = date("Y-m-d H:i:s");

        // トランザクション開始
        $pdo->beginTransaction();

        try {

            // SQL作成
            $stmt = $pdo->prepare("INSERT INTO emoji (emojifile, emojitype, emojicontent, emojisize, emojiname, emojiinfo, emojidate) VALUES ( :emojifile, :emojitype, :emojicontent, :emojisize, :emojiname, :emojiinfo, :emojidate)");


            
            $name = $img['name'];
            $type = $img['type'];
            $content = file_get_contents($img['tmp_name']);
            $size = $img['size'];
    
            $stmt->bindValue(':emojifile', $name, PDO::PARAM_STR);
            $stmt->bindValue(':emojitype', $type, PDO::PARAM_STR);
            $stmt->bindValue(':emojicontent', $content, PDO::PARAM_STR);
            $stmt->bindValue(':emojisize', $size, PDO::PARAM_INT);

            // 値をセット
            $stmt->bindParam( ':emojiname', $emojiname, PDO::PARAM_STR);
            $stmt->bindParam( ':emojiinfo', $emojiinfo, PDO::PARAM_STR);
            
            $stmt->bindParam( ':emojidate', $datetime, PDO::PARAM_STR);

            // SQLクエリの実行
            $res = $stmt->execute();

            // コミット
            $res = $pdo->commit();

        } catch(Exception $e) {

            // エラーが発生した時はロールバック
            $pdo->rollBack();
        }

        if( $res ) {
            $url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header("Location:".$url."");
            exit;  
        } else {
            $error_message[] = '登録に失敗しました。';
        }

        // プリペアドステートメントを削除
        $stmt = null;


	}
   
}


if( !empty($_POST['logout']) ) {
	if (isset($_SERVER['HTTP_COOKIE'])) {
		$cookies = explode(';', $_SERVER['HTTP_COOKIE']);
		foreach($cookies as $cookie) {
			$parts = explode('=', $cookie);
			$name = trim($parts[0]);
			setcookie($name, '', time()-1000);
			setcookie($name, '', time()-1000, '/');
		}
	}
	// リダイレクト先のURLへ転送する
    $url = '../index.php';
    header('Location: ' . $url, true, 303);

    // すべての出力を終了
    exit;
}



// データベースの接続を閉じる
$pdo = null;

?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="../css/home.css">
<title>絵文字登録 - <?php echo file_get_contents($servernamefile);?></title>

</head>

<body>
<?php require('../require/leftbox.php');?>
	<main>

            <?php if( !empty($error_message) ): ?>
                <ul class="errmsg">
                    <?php foreach( $error_message as $value ): ?>
                        <p>・ <?php echo $value; ?></p>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
                
        <form class="formarea" enctype="multipart/form-data" method="post">

		<h1>絵文字登録</h1>

        <p>絵文字登録です。</p>

        <div id="wrap">

            <label class="irobutton" for="file_upload">ファイル選択
            <input type="file" id="file_upload" name="image" >
            </label>
        </div>

            <!--ユーザーネーム関係-->
            <div>
                <p>EmojiID</p>
                <input id="username" placeholder="kusa" class="inbox" type="text" name="emojiname" value="<?php if( !empty($_SESSION['emojiname']) ){ echo htmlspecialchars( $_SESSION['emojiname'], ENT_QUOTES, 'UTF-8'); } ?>">
            </div>

            <div>
                <p>この絵文字について</p>
                <input id="username" placeholder="くさデス" class="inbox" type="text" name="emojiinfo" value="<?php if( !empty($_SESSION['emojiinfo']) ){ echo htmlspecialchars( $_SESSION['emojiinfo'], ENT_QUOTES, 'UTF-8'); } ?>">
            </div>

            <div>
                
            <input type="submit" class = "irobutton" name="btn_submit" value="登録">
            </div>

        </form>

        </div>
	</main>

	<?php require('../require/rightbox.php');?>
    <?php require('../require/botbox.php');?>
</body>

<script type="text/javascript">

window.addEventListener('DOMContentLoaded', function(){

// ファイルが選択されたら実行
document.getElementById("file_upload").addEventListener('change', function(e){

  var file_reader = new FileReader();

  // ファイルの読み込みを行ったら実行
  file_reader.addEventListener('load', function(e) {
    console.log(e.target.result);
        const element = document.querySelector('#wrap');
        const createElement = '<p>画像を選択しました。</p>';
        element.insertAdjacentHTML('afterend', createElement);
  });

  file_reader.readAsText(e.target.files[0]);
});
});
</script>
</html>