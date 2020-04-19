<?php

// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' ユーザー登録ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


//====================================
// 画面処理
//====================================

// post送信されていた場合
if(!empty($_POST)){
    debug('POST送信があります。');

    // 変数にユーザー情報を代入
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_re = $_POST['pass_re'];

    // 未入力チェック
    validRequired($email, 'email');
    validRequired($pass, 'pass');
    validRequired($pass_re, 'pass_re');

    if(empty(($err_msg))){
      // Email型式チェック
      validEmail($email, 'email');
      // 最大文字数チェック
      validMaxLen($email, 'email');
      // Email重複チェック
      validEmailDup($email);

      // パスワードチェック
      validPass($pass, 'pass');

      if(empty($err_msg)){
        // パスワードとパスワード再入力があっているかチェック
        validMatch($pass, $pass_re, 'pass_re');

        if(empty(($err_msg))){
            debug('バリデーションOKです');
          // 例外処理
          try{
             // DB接続
            $dbh = dbConnect();
            // SQL文作成
            $sql = 'INSERT INTO users (email, password, login_time, create_date) VALUES (:email, :pass, :login_time, :create_date)';
            $data = array(':email'=>$email, ':pass'=>password_hash($pass, PASSWORD_DEFAULT),
              ':login_time'=>date('Y-m-d H:i:s'),':create_date'=>date('Y-m-d H:i:s'));
            // クエリ実行
            $stmt = queryPost($dbh, $sql, $data);

            // クエリ成功の場合
            if($stmt){
                debug('データ挿入成功');
                // ログイン有効期限（デフォルトを1時間とする）
              $sesLimit = 60*60;
              // 最終ログイン日時を現在日時に
              $_SESSION['login_date'] = time();
              $_SESSION['login_limit'] = $sesLimit;
              // ユーザーIDを格納
              $_SESSION['user_id'] = $dbh->lastInsertId();

              debug('セッション変数の中身：'.print_r($_SESSION,true));

              header("Location:mypage.php"); //マイページへ
              exit();
            }
          } catch (Exception $e){
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
    $siteTitle = 'ユーザー登録';
    require ('head.php');
?>
<body class="page-signup page-1colum">
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
          <h2 class="title">ユーザー登録</h2>
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
            パスワード<span>※英数字6文字以上</span>
            <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo sanitize($_POST['pass']); ?>">
          </label>
          <div class="area-msg">
              <?php
                echo getErrMsg('pass');
              ?>
          </div>
          <label class="<?php if(!empty($err_msg['pass_re'])) echo 'err'; ?>">
            パスワード（再入力）
            <input type="password" name="pass_re" value="<?php if(!empty($_POST['pass_re'])) echo sanitize($_POST['pass_re']); ?>">
          </label>
          <div class="area-msg">
              <?php
                echo getErrMsg('pass_re');
              ?>
          </div>
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="SignUp">
          </div>
        </form>
      </div>
    </section>
  </div>

 <?php
    require ('footer.php');
?>