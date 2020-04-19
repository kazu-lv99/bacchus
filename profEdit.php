<?php
// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' プロフィール編集ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require ('auth.php');

//====================================
// 画面処理
//====================================
//サイドバーのためにユーザー情報を取得
$dbUserData = getUser($_SESSION['user_id']);
//DBからユーザー情報を取得
$dbFormData = getUser($_SESSION['user_id']);
//DBから都道府県データを取得
$dbPrefectureData = getPrefecture();

debug('取得したユーザー情報：'.print_r($dbFormData,true));
//debug('取得した都道府県情報：'.print_r($dbPrefectureData,true));

//POST送信されていたとき
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));
    debug('FILE情報：'.print_r($_FILES,true));

    //変数にユーザー情報を代入
  $username = $_POST['username'];
  $email = $_POST['email'];
  $prefecture = $_POST['prefecture_id'];
  //画像をアップロードし、パスを格納
  $profImg = (!empty($_FILES['prof_img']['name'])) ? uploadImg($_FILES['prof_img'],'prof_img') : '';
  //画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
  $profImg = (empty($profImg) && !empty($dbFormData['prof_img'])) ? $dbFormData['prof_img'] : $profImg;

  //DBとPOSTの情報が異なればバリデーション
  //ユーザー名
  if($dbFormData['username'] !== $username){
    validRequired($username, 'username');
    validMaxLen($username, 'username');
  }
  //メールアドレス
  if($dbFormData['email'] !== $email){
    validRequired($email, 'email');
    validEmail($email, 'email');
    validMaxLen($email, 'email');
    validEmailDup($email);
  }
  //出身地
  if($dbFormData['prefecture_id'] !== $prefecture){
    validSelect($prefecture, 'prefecture_id');
  }

  if(empty($err_msg)){
    debug('バリデーションOKです');
    //例外処理
    try{
      $dbh = dbConnect();
      $sql = 'UPDATE users SET username = :username, email = :email, prof_img = :prof_img, prefecture_id = :prefecture_id WHERE id = :u_id';
      $data = array(':username' => $username, ':email' => $email, ':prof_img' => $profImg, ':prefecture_id' => $prefecture, ':u_id' => $dbFormData['id']);
      // クエリ実行
      $stmt = queryPost($dbh, $sql, $data);

      //クエリ成功時
      if($stmt){
        debug('更新成功');
        $_SESSION['msg_success'] = SUC01;
        debug('マイページへ遷移します');
        header("Location:mypage.php");
      }
    } catch (Exception $e){
      error_log('エラー発生：'.$e->getMessage());
      $err_msg['common'] = MSG01;
    }

  }
}

debug('画面表示処理終了 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
?>
<?php
$siteTitle = 'プロフィール編集';
require ('head.php');
?>
<body class="page-profEdit page-2colum page-logined">
  <!-- header -->
  <?php
  require ('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <h1 class="page-title">プロフィール編集</h1>
    <!-- Main -->
    <section id="main" >
      <div class="form-container">
        <form action="" method="post" class="form" enctype="multipart/form-data">
          <div class="area-msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
          プロフィール画像
          <div class="imgProfDrop-container">
            <label class=""<?php if(!empty($err_msg['prof_img'])) echo 'err'; ?>"">
              <div class="area-drop area-drop_round">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="prof_img" class="input-file input-file_round">
                <img src="<?php echo getFormData('prof_img'); ?>" class="prof_img prof_img_round" alt="プロフ画像" style="<?php if(empty(getFormData('prof_img'))) echo 'display:none;' ?>">
                <p <?php if (!empty(getFormData('prof_img'))) {
                  echo 'style="display:none"';
                } ?>>ドラッグ＆ドロップ</p>
              </div>

            </label>
            <div class="area-msg">
              <?php
              echo getErrMsg('prof_img');
              ?>
            </div>
          </div>
          <label class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
            ユーザー名<span class="required">必須</span>
            <input type="text" name="username" value="<?php echo getFormData('username'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('username');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
            メールアドレス<span class="required">必須</span>
            <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('email');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['prefecture_id'])) echo 'err'; ?>">
            出身地
            <select name="prefecture_id" id="">
              <option value="0" <?php if(getFormData('prefecture_id') == 0){ echo 'selected'; } ?>>選択してください</option>
              <?php
                foreach ($dbPrefectureData as $key => $val) {
                  ?>
                  <option value="<?php echo $val['id'] ?>" <?php if (getFormData('prefecture_id') == $val['id']) {
                    echo 'selected';
                  } ?>>
                    <?php echo $val['name']; ?>
                  </option>
                  <?php
                    }
                  ?>
            </select>
          </label>
          

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

<?php
require ('footer.php');
?>