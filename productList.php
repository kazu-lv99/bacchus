<?php
// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' 商品一覧ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証はなし

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

//変数定義
$currentPageNum = ''; //現在のページ
$dbcategory = ''; //カテゴリー情報
$sort = ''; //ソート順
$dbProductData = ''; //商品情報
$dbProductGoodNum = ''; //いいねの数


//画面表示用データ取得
// 商品IDのGETパラメータを取得
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
//カレントページのGETパラメータを取得
//現在のページ
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトページは１
// カテゴリー
$category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
// ソート順
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';
//ページ数をいじった時はトップページへ
if(!is_int((int)$currentPageNum)){
	error_log('エラー発生：指定ページに不正な値が入りました');
	header("Location:index.php");
	exit();
}

//表示件数 20件
$listSpan = 20;
//このページで最初に表示する記事は何番目か
//1ページ目なら(1-1)*20=0,2ページ目なら(2-1)*20=20
$currentMinNum = (($currentPageNum - 1) * $listSpan);
//DBからカテゴリー情報を取得
$dbCategoryData = getCategory();
//DBから商品リストを取得
$dbProductData = getProductList($currentMinNum, $category, $sort);

debug('現在のページ：'.$currentPageNum);


debug('画面表示処理終了 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');
?>

<?php
$siteTitle = '商品一覧';
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

			<div class="panel_title">
                <h2 class="panel_title_left">
                商品一覧
                </h2>
                <div class="search-right">
                <span class="num"><?php echo (!empty($dbProductData['data'])) ? $currentMinNum+1 : 0; ?></span> - <span class="num"><?php echo $currentMinNum+count($dbProductData['data']); ?></span>件 / <span class="num"><?php echo sanitize($dbProductData['total']); ?></span>件中
                </div>
            </div>
			<!-- カテゴリー順 -->
			<form action="" method="get" class="selectform" id="submit-form">
				<div class="selectbox">
					<span class="icn_select"></span>
					<select name="c_id" id="" class="submit-select">
						<option value="0" <?php if(getFormData('c_id', true) == 0){ echo 'selected';} ?>>選択してください</option>
						<?php
							foreach($dbCategoryData as $key => $val):
						?>
						<option value="<?php echo $val['id']; ?>" <?php if(getFormData('c_id',true) == $val['id']){ echo 'selected';} ?>><?php echo $val['name']; ?></option>
							<?php endforeach; ?>
					</select>
				</div>
                <!-- ソート順  -->
				<div class="selectbox">
					<select name="sort" id="" class="submit-select">
						<option value="0" <?php if(getFormData('sort',true) == 0){ echo 'selected';} ?>>選択してください</option>
						<option value="1" <?php if(getFormData('sort',true) == 1){ echo 'selected';} ?>>新しい順</option>
						<option value="2" <?php if(getFormData('sort',true) == 2){ echo 'selected';} ?>>古い順</option>
					</select>
				</div>
			</form>
			<!-- パネルリストフレックスボックス -->
			<div class="panel-list-flex">
			<?php   
				foreach($dbProductData['data'] as $key => $val):
			?>
				<a href="productDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="panel">
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
			?>
			</div>
			<!-- ページネーション -->
			<?php pagination($currentPageNum, $dbProductData['total_page'], '&c_id='.$category.'&sort='.$sort); ?>

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