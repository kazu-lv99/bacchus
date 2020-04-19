<?php
// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' 認証キー入力ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証はなし（ログインできない人のためのページのため）

//SESSIONに認証キーがあるか確認、なければ認証キー発行ページへリダイレクト
if(empty($_SESSION['auth_key'])){
  header("Location:passRemindsend.php");
  exit();
}

//====================================
// 画面処理
//====================================
// post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST, true));

  //変数に認証キーを代入
  $auth_key = $_POST['auth_key'];

  //バリデーション未入力チェック
  validRequired($auth_key, 'auth_key');

  if(empty($err_msg)){
    debug('未入力チェックOK');
    //桁数チェック
    validLength($auth_key, 'auth_key');
    //半角英数字チェック
    validHalf($auth_key, 'auth_key');

    if(empty($err_msg)){
      debug('バリデーションOKです');

      //認証キーがあってるかチェック
      if($auth_key !== $_SESSION['auth_key']){
        $err_msg['common'] = MSG14;
      }
      //認証キーの有効期限が切れてないかチェック
      if($_SESSION['auth_key_limit'] < time()){
        $err_msg['common'] = MSG15;
      }

      if(empty($err_msg)){
        debug('認証OK');

        //パスワード作成
        $pass = makeRandKey();

        //例外処理
        try{
          $dbh = dbConnect();
          $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
          $data = array(':pass' => password_hash($pass, PASSWORD_DEFAULT), ':email' => $_SESSION['auth_email']);
          //クエリ実行
          $stmt = queryPost($dbh, $sql, $data);

          //クエリ成功時
          if($stmt){
            //メール送信
            $from = 'bacchus@gmail.com';
            $to = $_SESSION['auth_email'];
            $subject = 'パスワード再発行しました | Bacchus';
            //ヒアドキュメント
            $comment = <<<EOT
パスワードを再発行しました。
下記のパスワードでログイン後、パスワード編集画面から
パスワードを変更してください。

ログインページ：http://localhost:8888:/Bacchus/login.php
パスワード：{$pass}

この通知に身に覚えがない場合は
お問い合わせ下さい。

///////////////////////////////////////////////
Bacchus カスタマーセンター
URL http://bacchus.com/
E-mail info@bacchus.com
//////////////////////////////////////////////
EOT;
            sendMail($from, $to, $subject, $comment);

            //セッション削除
            session_unset();
            //セッションに成功メッセを格納
            $_SESSION['msg_success'] = SUC03;
            debug('セッション変数の中身：'.print_r($_SESSION,true));

            //ログインページへ遷移
            header("Location:login.php");
            exit();
          }
        }catch (Exception $e){
          error_log('エラー発生：'.$e->getMessage());
          $err_msg['common'] = MSG01;
        }
      }
    }
  }
}

debug('画面表示処理終了 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');

?>

<?php
$siteTitle = '認証キー入力';
require ('head.php');
?>
<body class="page-passRemindRecieve page-1colum">
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
          <p>ご指定のメールアドレスお送りした【パスワード再発行認証メール】内にある「認証キー」をご入力ください。
          </p>
          <div class="area-msg">
            <?php
              echo getErrMsg('common');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['auth_key'])) echo 'err'; ?>">
            認証キー
            <input type="text" name="auth_key">
          </label>
          <div class="area-msg">
            <?php
              echo getErrMsg('auth_key');
            ?>
          </div>
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="変更画面へ">
          </div>
        </form>
      </div>
      <a href="passRemindSend.php">&lt; パスワード再発行メールを再度送信する</a>
    </section>
  </div>

  <!-- footer -->
<?php
require ('footer.php');
?>