<?php
// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' 商品登録編集ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require ('auth.php');

//====================================
// 画面処理
//====================================

//画面表示用データ取得
//====================================
//サイドバーのためにユーザー情報を取得
if(!empty($_SESSION['user_id'])){
  $dbUserData = getUser($_SESSION['user_id']);
  debug('サイドバー用ユーザー情報：'.print_r($dbUserData,true));
}
//DBから都道府県データを取得
$dbPrefectureData = getPrefecture();

//GETデータを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
//DBから商品データを取得
$dbFormData = (!empty($p_id)) ? getProduct($_SESSION['user_id'], $p_id) : '';
//新規登録か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? false : true;
//DBからカテゴリデータを取得
$dbCategoryData = getCategory();
debug('商品ID：'.$p_id);
debug('フォーム用データ：'.print_r($dbFormData,true));
debug('カテゴリデータ：'.print_r($dbCategoryData,true));

//パラメータ改ざんチェック
//====================================
//GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい商品データが取れないのでマイページへ遷移させる
if(!empty($p_id) && empty($dbFormData)){
    debug('GETパラメータの商品IDが違います。マイページへ遷移します。');
    header("Location:mypage.php");
    exit();
}

//POST送信[submit]されていたとき
if(!empty($_POST['submit'])){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST,true));
  debug('FILE情報：'.print_r($_FILES,true));

  //変数定義
  $name = $_POST['name'];
  $category = $_POST['category_id'];
  $comment = $_POST['comment'];
  //画像をアップロードし、パスを格納
  $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'], 'pic1') : '';
  //画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
  $pic1 = (empty($pic1) && !empty($dbFormData['pic1'])) ? $dbFormData['pic1'] : $pic1;
  $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'], 'pic2') : '';
  $pic2 = (empty($pic2) && !empty($dbFormData['pic2'])) ? $dbFormData['pic2'] : $pic2;
  $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'], 'pic3') : '';
  $pic3 = (empty($pic3) && !empty($dbFormData['pic3'])) ? $dbFormData['pic3'] : $pic3;

  //更新の場合はDBの情報と入力情報が異なる場合にバリデーションを行う
  //新規登録の場合
  if(empty($dbFormData)){
      //バリデーション未入力チェック
    validRequired($name, 'name');
    //最大文字数チェック
    validMaxLen($name, 'name');
    //セレクトボックスチェック
    validSelect($category, 'category_id');
    //詳細欄最大文字数チェック
    validMaxLen($comment, 'comment', 500);
  }else{ //更新の場合
    if($dbFormData['name'] !== $name){
      validRequired($name, 'name');
      validMaxLen($name, 'name');
    }
    if($dbFormData['category_id'] !== $category){
      validSelect($category, 'category_id');
    }
    if($dbFormData['comment'] !== $comment){
      validMaxLen($comment, 'commnet', 500);
    }
  }

  if(empty($err_msg)){
    debug('バリデーションOKです');

    //例外処理
    try{
      $dbh = dbConnect();
      //編集画面の場合はUPDATE文、新規登録画面の場合はINSERT文を生成
      if($edit_flg){
        debug('DB更新です');
        $sql = 'UPDATE product SET name = :name, category_id = :category, comment = :comment, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3 WHERE user_id = :u_id AND id = :p_id';
        $data = array(':name' => $name, ':category' => $category, ':comment' => $comment, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, ':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
        }else{
        debug('DB新規登録です');
        $sql = 'INSERT INTO product (name, category_id, comment, pic1, pic2, pic3, user_id, create_date) VALUES (:name, :category, :comment, :pic1, :pic2, :pic3, :u_id, :date)';
        $data = array(':name' => $name, ':category' => $category, ':comment' => $comment, ':pic1'=>$pic1, ':pic2'=>$pic2, ':pic3'=>$pic3, ':u_id'=>$_SESSION['user_id'], ':date'=>date('Y-m-d H:i:s'));
        }
        debug('SQL：'.$sql);
        debug('流し込みデータ：'.print_r($data,true));
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);

        //クリエ成功時
        if($stmt){
          //成功メッセ格納
          $_SESSION['msg_success'] = SUC04;
          debug('マイページへ遷移します');
          header("Location:mypage.php");
          exit();
        }
    }catch (Exception $e){
      error_log('エラー発生：'.$e->getMessage());
      $err_msg['common'] = MSG01;
    }
  }
//POST送信[submit]以外が押された時（削除ボタンが押された時）
}elseif(!empty($_POST['delete'])){
  debug('投稿を削除します');
  //DB接続
  try{
    $dbh = dbConnect();
    $sql = 'UPDATE product SET delete_flg = 1 WHERE user_id = :u_id AND id = :p_id';
    $data = array(':u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      debug('削除しました');
      //成功メッセ格納
      $_SESSION['msg_success'] = SUC06;
      debug('マイページへ遷移します');
      header("Location:mypage.php");
      exit();
    }
  }catch (Exception $e){
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG01;
  }
}

debug('画面表示処理終了 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
?>

<?php
$siteTitle = (!$edit_flg) ? '商品登録' : '商品編集';
require ('head.php');
?>
<body class="page-registProduct page-2colum page-logined">
  <!-- header -->
  <?php
  require ('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <h1 class="page-title"><?php echo (!$edit_flg) ? ' 商品を登録する' : '商品を編集する' ?></h1>
    <!-- Main -->
    <section id="main" >
      <div class="form-container">
        <form action="" method="post" enctype="multipart/form-data" class="form">
          <div class="area-msg">
            <?php
              echo getErrMsg('common');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['name'])) echo 'err'; ?>">
            商品名<span class="required">必須</span>
            <input type="text" name="name" value="<?php echo getFormData('name'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('name');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['category_id'])) echo 'err'; ?>">
            カテゴリ<span class="required">必須</span>
            <select name="category_id" id="">
              <option value="0" <?php if(getFormData('category_id') == 0 ){ echo 'selected';} ?>>選択してください</option>
              <?php
                foreach ($dbCategoryData as $key => $val) {
              ?>
              <option value="<?php echo $val['id'] ?>" <?php if(getFormData('category_id') == $val['id']){ echo 'selected';} ?>>
                  <?php echo $val['name']; ?>
              </option>
              <?php
              }
              ?>
            </select>
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('category_id');
            ?>
          </div>
          <label class="<?php if(!empty($err_msg['comment'])) echo 'err'; ?>">
            詳細
            <textarea name="comment" id="js-count" cols="30" rows="10" style="height:150px;"><?php echo getFormData('comment'); ?></textarea>
          </label>
          <p class="counter-text"><span id="js-count-view">0</span>/500文字</p>
          <div class="area-msg">
            <?php
            echo getErrMsg('comment');
            ?>
          </div>
          <div style="overflow:hidden;">
            <div class="imgDrop-container">
              画像1
              <label class="area-drop <?php if(!empty($err_msg['pic1'])) echo 'err'; ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic1" class="input-file">
                <img src="<?php echo getFormData('pic1') ?>" class="prev-img" alt="商品画像1"
                      style="<?php if(empty(getFormData('pic1'))) echo 'display:none;'; ?>">
                ドラッグ＆ドロップ
              </label>
              <div class="area-msg">
                <?php
                echo getErrMsg('pic1');
                ?>
              </div>
            </div>
            <div class="imgDrop-container">
              画像2
              <label class="area-drop <?php if(!empty($err_msg['pic2'])) echo 'err'; ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic2" class="input-file">
                <img src="<?php echo getFormData('pic2') ?>" class="prev-img" alt="商品画像1"
                      style="<?php if(empty(getFormData('pic2'))) echo 'display:none;'; ?>">
                ドラッグ＆ドロップ
              </label>
              <div class="area-msg">
                <?php
                echo getErrMsg('pic2');
                ?>
              </div>
            </div>
            <div class="imgDrop-container">
              画像3
              <label class="area-drop <?php if(!empty($err_msg['pic3'])) echo 'err'; ?>">
                <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                <input type="file" name="pic3" class="input-file">
                <img src="<?php echo getFormData('pic3') ?>" class="prev-img" alt="商品画像1"
                      style="<?php if(empty(getFormData('pic3'))) echo 'display:none;'; ?>">
                ドラッグ＆ドロップ
              </label>
              <div class="area-msg">
                <?php
                echo getErrMsg('pic3');
                ?>
              </div>
            </div>
          </div>

          <div class="btn-container">
            <input type="submit" name="submit" class="btn btn-mid" value="<?php echo (!$edit_flg) ? '登録する' : '更新する'; ?>">
            <?php if($edit_flg) echo '<input type="submit" name="delete" class="btn btn-mid delete"  value="削除">'; ?>
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