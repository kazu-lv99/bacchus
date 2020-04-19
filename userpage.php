<?php
// 共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' ユーザーページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証
require ('auth.php');
//====================================
// 画面処理
//====================================

//変数定義
$u_id = ''; //投稿者ユーザーID
$dbPostProducts = '';//投稿した商品情報
$dbPostUserInfo = ''; //投稿者情報
$dbPrefectureData = ''; //出身地情報

//画面表示用データ取得
// 投稿者ユーザーIDのGETパラメータを取得
$u_id = (!empty($_GET['u_id'])) ? $_GET['u_id'] : '';
debug('ユーザーID：'.$u_id);

//投稿者とセッションのユーザーIDが同じならマイページへ遷移
if($_SESSION['user_id'] == $u_id){
    debug('マイページへ遷移します');
    header("Location:mypage.php");
    exit();
}
//DBから投稿した商品データを取得
$dbPostProducts = getPostProducts($u_id);
debug('投稿商品情報：'.print_r($dbPostProducts,true));
//投稿者情報
$dbPostUserInfo = getUser($u_id);
debug('投稿者情報：'.print_r($dbPostUserInfo,true));
//DBから都道府県データを取得
$dbPrefectureData = getPrefecture();
debug('都道府県情報：'.print_r($dbPrefectureData,true));




debug('画面表示処理終了 >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>');

?>

<?php
$siteTitle = 'ユーザーページ';
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
                    <a class="panel_title_right" href="productList.php">商品一覧 &gt;</a>
                </div>

                <div class="panel-list-flex">
                <?php 
                    if(!empty($dbPostProducts)):
                        foreach($dbPostProducts as $key => $val):
                ?>
                    <a href="productDetail.php?p_id=<?php echo sanitize($val['id']); ?>" class="panel">
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
        </section>

        <!-- サイドバー-->
        <aside class="sidebar logined">
            <div class="prof_img">
                <?php
                if(!empty($dbPostUserInfo['prof_img'])){
                echo '<img src="'.$dbPostUserInfo['prof_img'].'" alt="プロフィール画像">';
                }else{
                echo '<i class="far fa-user-circle"></i>';
                }
                ?>
            </div>
            <p>
                <?php if(!empty($dbPostUserInfo['username'])){
                echo $dbPostUserInfo['username'];
                }?>さん
            </p>
            <p>出身地：<?php if(isset($dbPostUserInfo['prefecture_id'])){
                echo $dbPrefectureData[$dbPostUserInfo['prefecture_id']]['name'];
                }?></p>
        </aside>


    </div>

<?php
require ('footer.php');
?>