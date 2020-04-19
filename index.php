<?php
// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' トップページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();


//====================================
// 画面処理
//====================================
//サイドバー用にユーザー情報取得
//サイドバーのためにユーザー情報を取得
if(!empty($_SESSION['user_id'])){
    $dbUserData = getUser($_SESSION['user_id']);
    debug('サイドバー用ユーザー情報：'.print_r($dbUserData,true));
    }
  //DBから都道府県データを取得
    $dbPrefectureData = getPrefecture();
//変数定義
$dbNewProductList = ''; //新着投稿を４件まで取得

//画面表示用データ取得

//新着投稿を４件まで取得
$dbNewProductList = getNewProductList();
debug('新着商品情報を取得：'.print_r($dbNewProductList,true));

//人気商品をいいねの総数順でとってくる
$dbPopularProductData = getPopularProduct();
debug('人気商品を取得：'.print_r($dbPopularProductData,true));

debug('画面表示処理終了 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
?>

<?php
$siteTitle = 'HOME';
require ('head.php');
?>
<body class="page-home page-2colum">
    <!-- header -->
    <?php
    require ('header.php');
    ?>
    </header>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
        <!-- Main -->
        <section id="main" >
            <section class="list panel-list">
				<div class="panel_title">
                    <h2 class="panel_title_left">
                    新着商品
                    </h2>
					<a class="panel_title_right animoBorderLeftRight" href="productList.php">商品一覧 &gt;</a>
                </div>
                <!-- 新着投稿一覧 -->
                <div class="panel-list-flex">
                <?php 
                    if(!empty($dbNewProductList)):
                        foreach($dbNewProductList as $key => $val):
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
            <section class="list panel-list">
				<div class="panel_title">
                    <h2 class="panel_title_left">
                        人気商品
                    </h2>
					<a class="panel_title_right animoBorderLeftRight" href="productList.php">商品一覧 &gt;</a>
                </div>
                <!-- 人気商品一覧 -->
                <div class="panel-list-flex">
                <?php 
                    if(!empty($dbPopularProductData)):
                        foreach($dbPopularProductData as $key => $val):
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

    <!-- footer -->
    <?php
    require ('footer.php');
    ?>