<?php
// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' パスワード再発行メール送信ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証はなし（ログインできない人のためのページのため）

//====================================
// 画面処理
//====================================
// post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります');
  debug('POST情報：'.print_r($_POST,true));

  //変数定義
  $email = $_POST['email'];

  //バリデーション未入力チェック
  validRequired($email, 'email');

  if(empty($err_msg)){
    debug('未入力OKです');
    //メール型式チェック
    validEmail($email, 'email');
    //メール最大文字数
    validMaxLen($email, 'email');

    if(empty($err_msg)){
      debug('バリデーションOKです');

      //例外処理
      try{
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        //1レコードとってくる
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        //$resultがtrueかつ$resultに何か入ってればEmailが登録されている（array_shiftで先頭の値を取り出す）
        if($result && array_shift($result)){
          debug('DB登録あり');
          //セッションに成功メッセージを格納
          $_SESSION['msg_success'] = SUC03;

          //認証キーを作成
          $auth_key = makeRandKey();

          //メールを送信
          $from = 'bacchus@gmail.com';
          $to = $email;
          $subject = 'パスワード再発行認証キー | Bacchus';
          //ヒアドキュメント
          $comment = <<<EOT
パスワード再発行に必要な認証キーを送信しました。
下記のページで認証を入力してください。

パスワード再発行認証キー入力ページ：http://localhost:8888:/Bacchus/passRemindRecieve.php
認証キー：{$auth_key}
※認証キーの有効期限は30分です。

認証キーを再発行する場合は下記ページより再度お手続きをお願いします。
http://localhost:8888:/Bacchus/passRemindsend.php

この通知に身に覚えがない場合は
お問い合わせ下さい。

///////////////////////////////////////////////
Bacchus カスタマーセンター
URL http://bacchus.com/
E-mail info@bacchus.com
//////////////////////////////////////////////

EOT;
          sendMail($from, $to, $subject, $comment);

          //認証に必要な情報をセッションに保存
          $_SESSION['auth_key'] = $auth_key;
          $_SESSION['auth_email'] = $email;
          //認証キーの有効期限を30分に設定
          $_SESSION['auth_key_limit'] = time() + (60*30);
          debug('セッション変数の中身：'.print_r($_SESSION,true));
          //認証キー入力ページへ遷移
          header("Location:passRemindRecieve.php");
          exit();
        }else{
          debug('クエリに失敗したかDBに登録のないEmailが入力されました');
          $err_msg['common'] = MSG01;
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
$siteTitle = 'パスワード再発行手続き';
require ('head.php');
?>
<body class="page-passRemindsend page-1colum">
  <!-- header -->
  <?php
  require ('header.php');
  ?>


  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <!-- Main -->
    <section id="main">
      <div class="form-container">
        <form action="" method="post" class="form">
          <p>ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送り致します。
          </p>
          <div class="area-msg">
            <?php
              echo getErrMsg('common');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
            メールアドレス
            <input type="text" name="email">
          </label>
          <div class="area-msg">
            <?php
              echo getErrMsg('email');
            ?>
          </div>
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="送信する">
          </div>
        </form>
      </div>
    </section>
  </div>

  <!-- footer -->
<?php
require ('footer.php');
?>