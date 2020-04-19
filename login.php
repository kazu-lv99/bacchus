<?php
// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' ログインページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require ('auth.php');

//====================================
// 画面処理
//====================================
//変数定義
$email = '';
$pass = '';
$pass_save = '';
// post送信されていた場合
if(!empty($_POST)){
  //変数定義
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_save = (!empty($_POST['pass_save'])) ? true : false;


  //バリデーション
  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');

  if(empty($err_msg)){
    debug('未入力チェックOK');
    //Email型式チェック
    validEmail($email, 'email');
    validMaxLen($email, 'email');

    //パスワードチェック
    validPass($pass, 'pass');

    if(empty($err_msg)){
      debug('バリデーションOKです');
      //DB接続
      try{
        $dbh = dbConnect();
        $sql = 'SELECT password, id FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email'=>$email);

        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        debug('クエリ結果の中身：'.print_r($result,true));

        //パスワード照合password_verify関数はパスワード（第一引数）がハッシュ化されたパスワード（第二引数）とマッチするか
        //パスワードと照合
        if(!empty($result) && password_verify($pass, array_shift($result))){
          debug('パスワードがマッチしました。');

          //ログイン有効期限（デフォルトを1時間）
          $sesLimit = 60*60;
          //最終ログインを現在のタイムスタンプに
          $_SESSION['login_date'] = time();

          //ログイン保持の有無
          if($pass_save){
            debug('ログイン保持チェックあり');
            //有効期限を1ヶ月に
            $_SESSION['login_limit'] = $sesLimit*24*30;
          }else{
            debug('ログイン保持チェックなし');
            $_SESSION['login_limit'] = $sesLimit;
          }
          //ユーザーIDを格納
          $_SESSION['user_id'] = $result['id'];
          debug('セッション変数の中身：'.print_r($_SESSION,true));
          debug('マイページへ遷移します。');
          header("Location:mypage.php");
          exit();
        }else{
          debug('パスワードが不一致です');
          $err_msg['common'] = MSG09;
        }
      }catch (Exception $e){
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['common'] = MSG01;
      }
    }
  }
}
debug('画面表示処理終了 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');

?>
<?php
$siteTitle = 'ログイン';
require ('head.php');
?>
<body class="page-login page-1colum">
  <!-- header -->
  <?php
  require ('header.php');
  ?>

  <!-- メッセージを表示 -->
  <p id="js-show-msg" style="display:none;" class="msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <!-- Main -->
    <section id="main">
      <div class="form-container">
        <form action="" method="post" class="form">
          <h2 class="title">ログイン</h2>
          <div class="area-msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
            メールアドレス
            <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo sanitize($_POST['email']); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('email');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
            パスワード
            <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo sanitize($_POST['pass']); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('pass');
            ?>
          </div>
          <label>
            <input type="checkbox" name="pass_save">次回ログインを省略する
          </label>
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="LogIn">
          </div>
          パスワードを忘れた方は<a href="passRemindsend.php">コチラ</a>
        </form>
      </div>
    </section>
  </div>

  <!-- footer -->
<?php
require ('footer.php');
?>