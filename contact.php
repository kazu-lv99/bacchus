<?php
// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' お問い合わせページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証なし

//====================================
// 画面処理
//====================================
//post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります');

  //変数にお問い合わせ情報を代入
  $name = $_POST['name'];
  $email = $_POST['email'];
  $subject = $_POST['subject'];
  $comment = $_POST['comment'];

  //未入力チェック
  validRequired($name, 'name');
  validRequired($email, 'email');
  validRequired($subject, 'subject');
  validRequired($comment, 'comment');

  if(empty($err_msg)){
    // Email型式チェック
    validEmail($email, 'email');
    // 最大文字数チェック
    validMaxLen($email, 'email');
    validMaxLen($subject, 'subject');

    if(empty($err_msg)){
      debug('バリデーションOKです');
    }
  }
}


debug('画面表示処理終了 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
?>
<?php
$siteTitle = 'お問い合わせ';
require ('head.php');
?>
<body class="page-contact page-1colum">
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
          <h2 class="title">CONTACT</h2>
          <div class="area-msg">
            <?php
              echo getErrMsg('common');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['name'])) echo 'err'; ?>">
            お名前<span class="required">必須</span>
            <input type="text" name="name" value="<?php if(!empty($_POST['name'])) echo sanitize($_POST['name']); ?>">
          </label>
          <div class="area-msg">
            <?php
              echo getErrMsg('name');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
            メールアドレス<span class="required">必須</span>
            <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo sanitize($_POST['email']); ?>">
          </label>
          <div class="area-msg">
            <?php
              echo getErrMsg('email');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['subject'])) echo 'err'; ?>">
            件名<span class="required">必須</span>
            <input type="text" name="subject" value="<?php if(!empty($_POST['subject'])) echo sanitize($_POST['subject']); ?>">
          </label>
          <div class="area-msg">
            <?php
              echo getErrMsg('subject');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['comment'])) echo 'err'; ?>">
            お問い合わせ内容<span class="required">必須</span>
            <textarea name="comment" id="contact_comment" cols="30" rows="10"><?php if(!empty($_POST['comment'])) echo $_POST['comment']; ?></textarea>
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('comment');
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