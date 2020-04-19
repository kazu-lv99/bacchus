<?php
// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' ABOUTページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

?>
<?php
$siteTitle = 'ABOUT';
require ('head.php');
?>
<body class="page-about page-1colum">
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
          <h2 class="title">このサイトについて</h2>
          <p class="about_p">Bacchusとはお酒限定の投稿サービスです。</br>
            自分のおすすめのお酒を投稿して、みんなに</br>
            紹介しましょう！
          </p>
          <div class="btn-container">
            <a href="signup.php" class="btn btn-center">SignUp</a>
          </div>
          <p class="about_p mt-32">既に会員の方はこちらから</p>
          <div class="btn-container">
            <a href="login.php" class="btn btn-center">LogIn</a>
          </div>
        </form>
      </div>
    </section>
  </div>

<?php
require ('footer.php');
?>