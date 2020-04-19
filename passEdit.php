<?php
// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' パスワード変更ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require ('auth.php');

//====================================
// 画面処理
//====================================
//サイドバー用にユーザー情報取得
if(!empty($_SESSION['user_id'])){
  $dbUserData = getUser($_SESSION['user_id']);
  debug('サイドバー用ユーザー情報：'.print_r($dbUserData,true));
}
//DBから都道府県データを取得
$dbPrefectureData = getPrefecture();

// DBからユーザー情報を取得
$dbFormData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($dbFormData,true));

//POSTされているとき
if(!empty($_POST)){
  debug('POST送信があります');
  //変数定義
  $pass_old = $_POST['pass_old'];
  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];

  //バリデーション
  //未入力チェック
  validRequired($pass_old, 'pass_old');
  validRequired($pass_new, 'pass_new');
  validRequired($pass_new_re, 'pass_new_re');

  if(empty($err_msg)){
    debug('未入力OKです');

    //パスワードチェック
    validPass($pass_old, 'pass_old');
    validPass($pass_new, 'pass_new');
    //新パスワードと再入力があっているかチェック
    validMatch($pass_new, $pass_new_re, 'pass_new_re');

    //古いパスワードとDBのパスワードが一致しているかチェック
    if(!password_verify($pass_old, $dbFormData['password'])){
      $err_msg['pass_old'] = MSG11;
    }

    //新しいパスワードと古いパスワードが同じじゃないかチェック
    if($pass_old === $pass_new){
      $err_msg['pass_new'] = MSG12;
    }
  }
  if(empty($err_msg)){
    debug('バリデーションOKです');
    //例外処理
    try{
      $dbh = dbConnect();
      $sql = 'UPDATE users SET password = :pass WHERE id = :id';
      $data = array(':pass' => password_hash($pass_new, PASSWORD_DEFAULT), ':id' => $_SESSION['user_id']);
      //クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      //クエリ成功の時
      if($stmt){
        //セッションに成功メッセージを入れる
        $_SESSION['msg_success'] = SUC02;

        //メール送信準備
        $username = ($dbFormData['username']);
        $from = 'bacchus@gmail.com';
        $to = $dbFormData['email'];
        $subject = 'パスワード変更通知 | Bacchus';
        //ヒアドキュメント
        $comment = <<<EOT
{$username}さん
パスワードを変更しました。
この通知に身に覚えがない場合は
お問い合わせ下さい。

///////////////////////////////////////////////
Bacchus カスタマーセンター
URL http://localhost:8888/Bacchus/index.php
E-mail info@bacchus.com
//////////////////////////////////////////////
EOT;

        sendMail($from, $to, $subject, $comment);

        //マイページへ遷移
        header("Location:mypage.php");
        exit();
      }
    }catch (Exception $e){
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['common'] = MSG01;
    }
  }
}

debug('画面表示処理終了 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');

?>
<?php
$siteTitle = 'パスワード編集';
require ('head.php');
?>
<body class="page-passEdit page-2colum page-logined">
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
          <h2 class="title">パスワード変更</h2>
          <div class="area-msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['pass_old'])) echo 'err'; ?>">
            古いパスワード
            <input type="password" name="pass_old" value="<?php if(!empty($_POST['pass_old'])){
                echo $_POST['pass_old'];
            } ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('pass_old');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['pass_new'])) echo 'err'; ?>">
            新しいパスワード
            <input type="password" name="pass_new" value="<?php if(!empty($_POST['pass_new'])){
              echo $_POST['pass_new'];
            } ?>">

          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('pass_new');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['pass_new_re'])) echo 'err'; ?>">
            新しいパスワード（再入力）
            <input type="password" name="pass_new_re" value="<?php if(!empty($_POST['pass_new_re'])){
              echo $_POST['pass_new_re'];
            } ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('pass_new_re');
            ?>
          </div>
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="変更する">
          </div>
        </form>
      </div>
    </section>

    <!-- サイドバー-->
    <?php
    require('sidebar.php');
    ?>

  </div>

  <!-- footer -->
<?php
require ('footer.php');
?>