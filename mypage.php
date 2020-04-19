<?php
// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' マイページ ');
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
// debug('都道府県情報：'.print_r($dbPrefectureData,true));

//画面表示用データ取得
$u_id = $_SESSION['user_id'];
//DBから自分の投稿した商品データを取得
$dbmyProductData = getMyProducts($u_id);
debug('My投稿情報：'.print_r($dbmyProductData,true));
$goodData = getMyGood($u_id);
debug('Myいいね情報：'.print_r($goodData,true));

debug('画面表示処理終了 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');

?>

<?php
$siteTitle = 'マイページ';
require ('head.php');
?>
<body class="page-mypage page-2colum page-logined">
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
        <section id="main" >

            <!-- 投稿一覧 -->
            <section class="list panel-list">
                <div class="panel_title">
                    <h2 class="panel_title_left">
                    登録商品一覧
                    </h2>
                    <a class="panel_title_right animoBorderLeftRight" href="productList.php">商品一覧 &gt;</a>
                </div>

                <div class="panel-list-flex">
                <?php 
                    if(!empty($dbmyProductData)):
                        foreach($dbmyProductData as $key => $val):
                ?>
                    <a href="registProduct.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="panel">
                        <div class="panel-head">
                            <img class="thumbnail150" src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['name']); ?>">
                        </div>
                        <div class="panel-body">
                            <p class="product-title"><?php echo sanitize($val['name']); ?></p>
                            <!-- いいねボタン -->
                            <div class="good_btn_container">
                                <i class="fa fa-heart icn-good <?php if(isGood($_SESSION['user_id'], $val['id'])){ echo 'active'; } ?>"></i>
                                <span class="good_num"><?php echo count(getGood($val['id'])); ?></span>
                            </div>
                        </div>
                    </a>
                <?php
                        endforeach;
                    endif;
                ?>
                </div>
            </section>

            <!-- お気に入り一覧 -->
            <section class="list panel-list">
				<div class="panel_title">
                    <h2 class="panel_title_left">
                        お気に入り一覧
                    </h2>
					<a class="panel_title_right animoBorderLeftRight" href="productList.php">商品一覧 &gt;</a>
                </div>

                <div class="panel-list-flex">

                <?php
                    if(!empty($goodData)):
                        foreach($goodData as $key => $val):
                ?>
                <a href="ProductDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="panel">
                    <div class="panel-head">
                        <img class="thumbnail150" src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['name']); ?>">
                    </div>
                    <div class="panel-body">
                        <p class="product-title"><?= sanitize($val['name']); ?></p>
                        <!-- いいねボタン -->
                        <div class="good_btn_container">
                            <i class="fa fa-heart icn-good <?php if(isGood($_SESSION['user_id'], $val['id'])){ echo 'active'; } ?>"></i>
                            <span class="good_num"><?php echo count(getGood($val['id'])); ?></span>
                        </div>
                    </div>
                </a>
                <?php
                        endforeach;
                    endif;
                ?>
                </div>
            </section>
        </section>

        <!-- サイドバー-->
        <?php
        require('sidebar.php');
        ?>

    </div>

<?php
require ('footer.php');
?>