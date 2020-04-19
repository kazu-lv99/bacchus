<?php

// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' 退会ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require ('auth.php');

//====================================
// 画面処理
//====================================
//post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
  //例外処理
  try{
    $dbh = dbConnect();
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :us_id';
    $sql2 = 'UPDATE product SET delete_flg = 1 WHERE user_id = :us_id';
    $sql3 = 'UPDATE favorite SET delete_flg = 1 WHERE user_id = :us_id';
    $data = array(':us_id' => $_SESSION['user_id']);
    //クエリ実行
    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    $stmt3 = queryPost($dbh, $sql3, $data);

    //クエリ成功時(今回は最悪userテーブルのみ削除成功していればよしとする)
    // ちゃんとする場合if($stmt1 && $stmt2)にする
    if($stmt1 && $stmt2){
      //セッション削除
      session_destroy();
      debug('セッション変数の中身：'.print_r($_SESSION,true));
      debug('トップページへ遷移します');
      header("Location:index.php");
      exit();
    }else{
      debug('クエリが失敗しました');
      $err_msg['common'] = MSG01;
    }
  }catch (Exception $e){
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG01;
  }
}

debug('画面表示処理終了 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
?>

<?php
$siteTitle = '退会';
require ('head.php');
?>

<body class="page-withdraw page-1colum">
  <!-- header -->
  <?php
  require ('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <!-- Main -->
    <section id="main">
      <div class="form-container">
        <form action=""  method="post" class="form">
          <h2 class="title">退会</h2>
          <div class="area-msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
          <p>退会処理を行います。</p>
          <p>これまでのご利用ありがとうございました。</p>
          <div class="btn-container">
            <input type="submit" class="btn btn-center" value="退会する" name="submit">
          </div>
        </form>
      </div>
      <a href="mypage.php">&lt; マイページに戻る</a>
    </section>
  </div>

<?php
require ('footer.php');
?>