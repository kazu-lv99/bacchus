<?php

//共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' 商品詳細ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//================================
// 画面処理
//================================


//変数定義
$p_id = ''; //商品ID
$dbProductDetail = ''; //商品詳細データ
$dbPostUserInfo = ''; //投稿者情報
$dbCommentList = ''; //コメント情報
$dbGoodNum = ''; //いいねの数

// 画面表示用データ取得
//================================
// 商品IDのGETパラメータを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
//DBから商品データを取得
$dbProductDetail = getProductDetail($p_id);
debug('商品情報：'.print_r($dbProductDetail,true));
//投稿者情報
$dbPostUserInfo = getUser($dbProductDetail['user_id']);
debug('投稿者情報：'.print_r($dbPostUserInfo,true));

// DBからコメントを取得
$dbCommentList = getComment($p_id);
debug('コメント情報：'.print_r($dbCommentList,true));

// DBからいいねの数を取得
$dbProductGoodNum = count(getGood($p_id));

//パラメータに不正な値が入っているかチェック
if(empty($dbProductDetail)){
  error_log('エラー発生：指定ページに不正な値が入りました');
  header("Location:index.php");
  exit();
}

//POSTされたとき
if(!empty($_POST)){
  debug('POST送信されました');
	debug('post情報'.print_r($_POST,true));

	$comment = $_POST['comment'];
	// 未入力チェック
	validRequired($comment, 'comment');
	// 最大文字数チェック
	validMaxLen($comment, 'comment');
	if(empty($err_msg)){
		debug('バリデーションOK!');

		try{
			$dbh = dbConnect();
			$sql = 'INSERT INTO comment (product_id, user_id, send_date, comment, create_date) VALUES (:p_id, :u_id, :send_date, :comment, :date)';
			$date = array(':p_id' => $p_id, ':u_id' => $_SESSION['user_id'], ':send_date' => date('Y-m-d H:i:s'), ':comment' => $comment, ':date' => date('Y-m-d H:i:s'));
			// クエリ実行
			$stmt = queryPost($dbh,$sql,$date);

			if($stmt){
        $_SESSION['msg_success'] = SUC05;
        debug('セッション変数の中身: '.print_r($_SESSION, true));
        header("Location:productDetail.php?p_id=".$p_id);
        exit();
			}
		}catch(Exception $e){
			error_log('エラー発生：'.$e->getMessage());
			$err_msg['common'] = MSG01;
		}
	}
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$siteTitle = '商品詳細';
require('head.php'); 
?>

  <body class="page-productDetail page-1colum">

    <!-- メニュー -->
    <?php
      require('header.php'); 
    ?>

    <!-- メッセージを表示 -->
    <p id="js-show-msg" style="display:none;" class="msg-slide">
      <?php echo getSessionFlash('msg_success'); ?>
    </p>
    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <!-- Main -->
      <section id="main" >
        <div class="send_user_container">
          <a href="userpage.php?u_id=<?php echo sanitize($dbPostUserInfo['id']); ?>">
            <img src="<?php echo showImg(sanitize($dbPostUserInfo['prof_img'])); ?>" alt="投稿者画像">
          </a>
          <p><?php echo sanitize($dbPostUserInfo['username']); ?></p>

          <!-- いいねボタン -->
          <div class="good_btn_container line_hight_44">
            <i class="fa fa-heart icn-good js-click-good <?php if(isGood($_SESSION['user_id'], $dbProductDetail['id'])){ echo 'active'; } ?>" aria-hidden="true" data-productid="<?php echo sanitize($dbProductDetail['id']); ?>"></i>
            <span class="good_num"><?php echo $dbProductGoodNum; ?></span>
          </div>

        </div>
        <div class="product-img-container">
          <div class="img-main">
            <img src="<?php echo showImg(sanitize($dbProductDetail['pic1'])); ?>" alt="メイン画像：<?php echo sanitize($dbProductDetail['name']); ?>" id="js-switch-img-main">
          </div>
          <div class="img-sub">
            <img src="<?php echo showImg(sanitize($dbProductDetail['pic1'])); ?>" alt="サブ画像1：<?php echo sanitize($dbProductDetail['name']); ?>" class="js-switch-img-sub">
            <img src="<?php echo showImg(sanitize($dbProductDetail['pic2'])); ?>" alt="サブ画像2：<?php echo sanitize($dbProductDetail['name']); ?>" class="js-switch-img-sub">
            <img src="<?php echo showImg(sanitize($dbProductDetail['pic3'])); ?>" alt="サブ画像3：<?php echo sanitize($dbProductDetail['name']); ?>" class="js-switch-img-sub">
          </div>
        </div>

        <div class="product_name">
          <span class="badge"><?php echo sanitize($dbProductDetail['category']); ?></span>
          <?php echo sanitize($dbProductDetail['name']); ?>
        </div>
        <div class="product-detail">
          <p>
            <?php echo nl2br(sanitize($dbProductDetail['comment'])); ?>
          </p>
        </div>

        <!-- amazon、楽天検索ボタン -->
        <div class="product_search">
          <a class="shop_button  amazon" href="https://www.amazon.co.jp/s?k=<?php echo sanitize($dbProductDetail['name']); ?>" target=”_blank”>Amazonで探す</a>
          <a class="shop_button rakuten" href="https://search.rakuten.co.jp/search/mall/<?php echo sanitize($dbProductDetail['name']); ?>" target=”_blank”>楽天市場で探す</a>
        </div>

        <!-- コメント一覧 -->
        <div class="comment_area">
        <?php
						foreach ($dbCommentList as $key => $val):
							$dbCommentUserId = $dbCommentList[$key]['user_id'];
							$dbCommentUserInfo = getUser($dbCommentUserId);
					?>
          <div class="comment_send_user">
            <img src="<?php echo showImg(sanitize($dbCommentUserInfo['prof_img']));?>" alt="投稿者画像">
            <p><?php echo sanitize($dbCommentUserInfo['username']); ?></p>
            <time><?php echo date('Y/m/d H:i:s',strtotime(sanitize($val['send_date']))); ?></time>
          </div>
          <p class="comment"><?php echo sanitize($val['comment']); ?></p>
          <?php
            endforeach;
          ?>
        </div>

        <!-- コメント送信 -->
        <div class="product_comment_send">
          <form action="" method="post" class="comment_form">
            <h2>コメントする</h2>
            <div class="err_msg">
            </div>

            <label>
              <textarea id="js-count" name="comment" cols=63 rows=20 style="height:100px;"></textarea>
            </label>
            <p class="counter-text"><span id="js-count-view">0</span>/200</p>
            <div class="err_msg">
            </div>
            <div class="item-left">
              <a href="productList.php<?php echo appendGetParam(array('p_id')); ?>">&lt; 商品一覧に戻る</a>
            </div>
  
            <div class="item-right">
              <input type="submit" class="btn-primary" value="送信">
            </div>
          </div>
          </form>

      </section>

    </div>

    <!-- footer -->
    <?php
      require('footer.php'); 
    ?>

