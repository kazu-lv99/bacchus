$(function(){
  // フッターを最下部に固定
  var $ftr = $('#footer');
  if( window.innerHeight > $ftr.offset().top + $ftr.outerHeight() ){
    $ftr.attr({'style': 'position:fixed; top:' + (window.innerHeight - $ftr.outerHeight()) +'px;' });
  }
  // メッセージ表示
  var $jsShowMsg = $('#js-show-msg');
  var msg = $jsShowMsg.text();
  if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){
    $jsShowMsg.slideToggle('slow');
    setTimeout(function(){ $jsShowMsg.slideToggle('slow'); }, 5000);
  }

  // 画像ライブプレビュー
  var $dropArea = $('.area-drop');
  var $fileInput = $('.input-file');
  $dropArea.on('dragover', function(e){
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', '3px #ccc dashed');
  });
  $dropArea.on('dragleave', function(e){
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', 'none');
  });
  $fileInput.on('change', function(e){
    $dropArea.css('border', 'none');
    var file = this.files[0],            // 2. files配列にファイルが入っています
        $img = $(this).siblings('.prev-img'), // 3. jQueryのsiblingsメソッドで兄弟のimgを取得
        fileReader = new FileReader();   // 4. ファイルを読み込むFileReaderオブジェクト

    // 5. 読み込みが完了した際のイベントハンドラ。imgのsrcにデータをセット
    fileReader.onload = function(event) {
      // 読み込んだデータをimgに設定
      $img.attr('src', event.target.result).show();
    };

    // 6. 画像読み込み
    fileReader.readAsDataURL(file);

  });
  // プロフィール画像登録用ライブプレビュー
  var $dropAreaRound = $('.area-drop_round');
  var $fileInputRound = $('.input-file_round');
  $dropAreaRound.on('dragover', function(e){
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', '3px #ccc dashed');
  });
  $dropAreaRound.on('dragleave', function(e){
    e.stopPropagation();
    e.preventDefault();
    $(this).css('border', 'none');
  });
  $fileInputRound.on('change', function(e){
    $dropAreaRound.css('border', 'none');
    var file = this.files[0],            // 2. files配列にファイルが入っています
        $img = $(this).siblings('.prof_img'), // 3. jQueryのsiblingsメソッドで兄弟のimgを取得
        fileReader = new FileReader();   // 4. ファイルを読み込むFileReaderオブジェクト

    // 5. 読み込みが完了した際のイベントハンドラ。imgのsrcにデータをセット
    fileReader.onload = function(event) {
      // 読み込んだデータをimgに設定
      $img.attr('src', event.target.result).show();
    };

    // 6. 画像読み込み
    fileReader.readAsDataURL(file);

  });

  // テキストエリアカウント
  var $countUp = $('#js-count'),
      $countView = $('#js-count-view');
  $countUp.on('keyup', function(e){
    $countView.html($(this).val().length);
  });

  //カテゴリ・ソートを選択するとGET送信
  $('.submit-select').on('change', function() {
    $('#submit-form').submit();
  });

  //画面切替
  var $switchImgSubs = $('.js-switch-img-sub');
  var $switchImgMain = $('#js-switch-img-main');
  $switchImgSubs.on('click',function(e){
    $switchImgMain.attr('src',$(this).attr('src'));
  });


  // お気に入り登録・削除
  var $good,
  goodProductId;
$good = $('.js-click-good') || null; //nullというのはnull値という値で、「変数の中身は空ですよ」と明示するためにつかう値
goodProductId = $good.data('productid') || null;
// 数値の0はfalseと判定されてしまう。product_idが0の場合もありえるので、0もtrueとする場合にはundefinedとnullを判定する
if(goodProductId !== undefined && goodProductId !== null){
$good.on('click',function(){
  var $this = $(this);
  $.ajax({
    type: "POST",
    url: "ajaxGood.php",
    data: { productId : goodProductId}
  }).done(function( data ){
    console.log('Ajax Success');
    // いいねの総数を表示
    //.nextは隣の要素を指定する
    $this.next('span').html(data);
    // クラス属性をtoggleでつけ外しする
    $this.toggleClass('active');
  }).fail(function( msg ) {
    console.log('Ajax Error');
  });
});
}

});



