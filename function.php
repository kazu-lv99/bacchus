<?php
//====================================
// ログ
//====================================
//E_STRICTレベル以外のエラーを報告する
error_reporting(E_ALL);
//画面にエラーを表示させるか
ini_set('display_errors','On');
//ログを取るか
ini_set('log_errors','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');

//====================================
// デバッグ
//====================================
// デバッグフラグ
$debug_flg = true;
// デバッグログ関数
function debug($str){
    global $debug_flg;
    if(!empty($debug_flg)){
        error_log('デバッグ：'.$str);
    }
}

//====================================
// セッション準備・セッション有効期限を延ばす
//====================================
// セッションファイルの置き場を変更する（/var/tmp/以下に置くと30日は削除されない）
session_save_path("/var/tmp/");
// ガーベージコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ100分の1の確率で削除）
ini_set('session.gc_maxlifetime', 60*60*24*30);
// ブラウザを閉じても削除されないようにクッキー自体の有効期限を延ばす
ini_set('session.cookie_lifetime',60*60*24*30);
// セッションを使う
session_start();
// 現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

//====================================
// 画面表示処理開始ログ吐き出し関数
//====================================
function debugLogStart(){
    debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
    debug('セッションID：'.session_id());
    debug('現在日時タイムスタンプ：'.time());
    if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
        debug('ログイン期限日時タイムスタンプ：'.($_SESSION['login_date'] + $_SESSION['login_limit']));
    }
}

//====================================
// 定数
//====================================
// エラーメッセージを定数に設定
define('MSG01','エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG02','入力必須です');
define('MSG03','Email形式で入力してください');
define('MSG04','255文字以内で入力してください');
define('MSG05','そのメールアドレスは既に登録されています');
define('MSG06','半角英数字のみご利用いただけます');
define('MSG07','6文字以上入力してください');
define('MSG08','パスワード（再入力）があっていません');
define('MSG09','メールアドレスまたはパスワードが違います');
define('MSG10','正しくありません');
define('MSG11','古いパスワードが違います');
define('MSG12','古いパスワードと同じです');
define('MSG13','文字で入力してください');
define('MSG14','正しくありません');
define('MSG15','有効期限が切れています');
//成功メッセ
define('SUC01','プロフィールを変更しました');
define('SUC02','パスワードを変更しました');
define('SUC03','メールを送信しました');
define('SUC04','登録しました');
define('SUC05','コメントしました');
define('SUC06','商品を削除しました');

//====================================
// グローバル変数
//====================================
// エラーメッセージ格納用の配列
$err_msg = array();

//====================================
// バリデーション関数
//====================================
// バリデーション未入力チェック
function validRequired($str, $key){
    if($str === ''){
        global $err_msg;
        $err_msg[$key] = MSG02;
    }
}
// バリデーションEmail型式チェック
function validEmail($str, $key){
  if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}
// バリデーション最大文字数チェック
function validMaxLen($str, $key, $max = 255){
  if(mb_strlen($str) > $max){
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}
// バリデーションEmail重複チェック
function validEmailDup($email){
  global $err_msg;
  // 例外処理
  try{
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    // クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    // array_shift関数は配列の先頭を取り出す関数です。クエリ結果は配列形式で入っているので、array_shiftで１つ目だけ取り出して判定します
    if(!empty(array_shift($result))){
      $err_msg['email'] = MSG05;
    }
  } catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
    $err_msg['common'] = MSG01;
  }
}
// バリデーション半角チェック
function validHalf($str, $key){
  if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG06;
  }
}
// バリデーション最小文字数チェック
function validMinLen($str, $key, $min = 6){
  if(mb_strlen($str) < $min){
    global $err_msg;
    $err_msg[$key] = MSG07;
  }
}
// バリデーション同値チェック
function validMatch($str1, $str2, $key){
  if($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = MSG08;
  }
}
// パスワードチェック
function validPass($str, $key){
  // バリデーション半角チェック
  validHalf($str,$key);
  // バリデーション最大文字数チェック
  validMaxLen($str,$key);
  // バリデーション最小文字数チェック
  validMinLen($str,$key);
}
//桁数チェック
function validLength($str, $key, $length = 8){
  if(mb_strlen($str) !== $length){
    global $err_msg;
    $err_msg[$key] = $length.MSG13;
  }
}
//セレクトボックスチェック
function validSelect($str, $key){
  if(!preg_match("/^[0-9]+$/",$str)){
    global $err_msg;
    $err_msg[$key] = MSG10;
  }
}
// エラーメッセージ表示
function getErrMsg($key){
    global $err_msg;
    if(!empty($err_msg[$key])){
        return $err_msg[$key];
    }
}

//================================
// ログイン認証
//================================
function isLogin(){
  // ログインしている場合
  if( !empty($_SESSION['login_date']) ){
    debug('ログイン済みユーザーです。');

    // 現在日時が最終ログイン日時＋有効期限を超えていた場合
    if( ($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
      debug('ログイン有効期限オーバーです。');

      // セッションを削除（ログアウトする）
      session_destroy();
      return false;
    }else{
      debug('ログイン有効期限以内です。');
      return true;
    }

  }else{
    debug('未ログインユーザーです。');
    return false;
  }
}

//====================================
// データベース
//====================================
// DB接続関数
function dbConnect(){
    // DBへの接続準備
    $dsn = 'mysql:dbname=Bacchus;host=localhost;charset=utf8';
    $user = 'root';
    $password = 'root';
    $options = array(
        // SQL実行失敗時にはエラーコードのみ設定
        PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
        // デフォルトフェッチモードを連想配列型式に設定
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // バッファードクエリを使う（一度に結果セットをすべて取得し、サーバー負荷を軽減）
        // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );
    // PDOオブジェクト生成（DBへ接続）
    $dbh = new PDO($dsn, $user, $password, $options);
    return $dbh;
}
// SQL実行関数
function queryPost($dbh, $spl, $data){
    // クエリー作成
    $stmt = $dbh->prepare($spl);
    // プレースホルダに値をセットし、SQL文を実行
    if(!$stmt->execute($data)){
        debug('クエリに失敗しました。');
        debug('失敗したSQL：'.print_r($stmt,true));
        debug('SQLエラー：'.print_r($stmt->errorInfo(),true));
        $err_msg['common'] = MSG01;
        return false;
    }
    debug('クエリ成功。');
    return $stmt;
}
function getUser($u_id){
  debug('ユーザー情報を取得します');
  //例外処理
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM users WHERE id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    //クエリ結果のデータを1レコード返却
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  }catch (Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getProduct($u_id, $p_id){
  debug('商品情報を取得します。');
  debug('ユーザーID：'.$u_id);
  debug('商品ID：'.$p_id);
  //例外処理
  try{
    $dbh = dbConnect();
    $sql ='SELECT * FROM product WHERE user_id = :u_id AND id = :p_id AND delete_flg = 0 ';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      //クエリ結果のデータを1レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  }catch (Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getProductList($currentMinNum = 1, $category, $sort, $span = 20){
  debug('商品一覧を取得します');
  //例外処理
  try{
    $dbh = dbConnect();
    //件数表示用のSQL文作成
    $sql = 'SELECT id FROM product WHERE delete_flg = 0';
    //検索とソート関係
    if(!empty($category)){
      $sql .= ' AND category_id = '.$category;
    }
    if(!empty($sort)){
      switch ($sort){
        //投稿新しい順
        case 1:
          $sql .= ' ORDER BY create_date DESC ';
        break;
        //投稿古い順
        case 2:
          $sql .= ' ORDER BY create_date ASC ';
        break;
      }
    }
    $data = array();
    debug('SQL文：'.$sql);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    //総レコード数をカウント
    $rst['total'] = $stmt->rowCount();
    //総ページ数を繰り上げで取得
    $rst['total_page'] = ceil($rst['total']/$span);
    if(!$stmt){
      return false;
    }

    //ページング用のSQL文作成
    $sql = 'SELECT * FROM product WHERE delete_flg = 0';
    if(!empty($category)){
      $sql .= ' AND category_id = '.$category;
    }
    if(!empty($sort)){
      switch ($sort){
        //投稿新しい順
        case 1:
          $sql .= ' ORDER BY create_date DESC ';
        break;
        //投稿古い順
        case 2:
          $sql .= ' ORDER BY create_date ASC ';
        break;
      }
    }
    //その後ろにSQL文をつなげる
    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array();
    debug('SQL文：'.$sql);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      //全レコードを格納する
      $rst['data'] = $stmt->fetchAll();
      debug('$rstの中身：'.print_r($rst, true));
      return $rst;
    }else{
      return false;
    }
  } catch(Exeption $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getNewProductList(){
  debug('新着商品情報を取得します');
  //例外処理
  try{
    $dbh = dbConnect();
    //新着順で４つの商品をとってくる
    $sql = 'SELECT * FROM product WHERE delete_flg = 0 ORDER BY create_date DESC LIMIT 4';
    $data = array();
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch(Exeption $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
//人気商品一覧取得
//いいねの総数順
function getPopularProduct(){
  debug('人気商品を取得します');
  //DB接続
  try{
    $dbh = dbConnect();
    $sql = 'SELECT p.id, p.name, p.pic1, p.delete_flg FROM product AS p INNER JOIN good AS g ON p.id = g.product_id WHERE p.delete_flg = 0 GROUP BY g.product_id ORDER BY count(g.product_id) DESC LIMIT 4';
    $data = array();
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        if ($stmt) {
            //全レコードを格納する
            $result = $stmt -> fetchAll();
            return $result;
        } else {
            return false;
        }
  }catch (Exception $e) {
    debug('エラー発生: '.$e->getMessage());
    $err_msg['common'] = MSG01;
  }
}
function getProductDetail($p_id){
  debug('商品詳細を取得します');
  debug('商品ID：'.$p_id);
  //例外処理
  try{
    $dbh = dbConnect();
    $sql = 'SELECT p.id, p.name, p.comment, p.pic1, p.pic2, p.pic3, p.user_id, p.create_date, p.update_date, c.name AS category FROM product AS p LEFT JOIN category AS c ON p.category_id = c.id WHERE p.id = :p_id AND p.delete_flg = 0';
    $data = array(':p_id' => $p_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getComment($p_id){
	debug('コメントを取得します。');
	try{
		$dbh = dbConnect();
		$sql = 'SELECT * FROM comment WHERE product_id = :p_id AND delete_flg = 0 ORDER BY send_date DESC';
		$data = array(':p_id' => $p_id);
		// クエリ実行
		$stmt = queryPost($dbh, $sql, $data);

		if($stmt){
			return $stmt->fetchAll();
		}else{
			return false;
		}
	}catch(Exception $e){
		error_log('エラー発生：'.$e->getMessage());
	}
}
function getGood($p_id){
	debug(' いいねを取得します');
	try {
		$dbh = dbConnect();
		$sql = 'SELECT * FROM good WHERE product_id = :p_id';
		$data = array(':p_id' => $p_id);
		// クエリ実行
		$stmt = queryPost($dbh, $sql, $data);

		if($stmt){
			return $stmt->fetchAll();
		}else{
			return false;
		}
	} catch (Exception $e) {
		error_log('エラー発生：'.$e->getMessage());
	}
}
function isGood($u_id, $p_id){
	debug('いいねした情報があるか確認');
	debug('ユーザーID：'.$u_id);
	debug('商品ID：'.$p_id);

	try {
		$dbh = dbConnect();
		$sql = 'SELECT * FROM good WHERE product_id = :p_id AND user_id = :u_id';
		$data = array(':u_id' => $u_id, ':p_id' => $p_id);
		// クエリ実行
		$stmt = queryPost($dbh, $sql, $data);

		if($stmt->rowCount()){
			debug('お気に入りです');
			return true;
		}else{
			debug('特に気に入ってません');
			return false;
		}

	} catch (Exception $e) {
		error_log('エラー発生:' . $e->getMessage());
	}
}
function getMyProducts($u_id){
	debug('My投稿情報を取得します。');
	try{
		$dbh = dbConnect();
		$sql = 'SELECT * FROM product WHERE user_id = :u_id AND delete_flg = 0 ORDER BY create_date DESC'; 
		$data = array(':u_id' => $u_id);
		// クエリ実行
		$stmt = queryPost($dbh, $sql, $data);

		if($stmt){
			return $stmt->fetchAll();
		}else{
			return false;
		}
	}catch(Exception $e){
		error_log('エラー発生：'.$e->getMessage());
	}
}
function getPostProducts($u_id){
	debug('投稿した全ての情報を取得します。');
	try{
		$dbh = dbConnect();
		$sql = 'SELECT * FROM product WHERE user_id = :u_id AND delete_flg = 0 ORDER BY create_date DESC'; 
		$data = array(':u_id' => $u_id);
		// クエリ実行
		$stmt = queryPost($dbh, $sql, $data);

		if($stmt){
			return $stmt->fetchAll();
		}else{
			return false;
		}
	}catch(Exception $e){
		error_log('エラー発生：'.$e->getMessage());
	}
}
function getMyGood($u_id){
	debug(' 自分のいいねした投稿を取得します');
	try {
		$dbh = dbConnect();
		$sql = 'SELECT p.id, p.name, p.category_id, p.comment, p.pic1, p.pic2, p.pic3, p.user_id, p.create_date, p.delete_flg FROM product AS p INNER JOIN good AS g ON p.id = g.product_id WHERE g.user_id = :u_id AND p.delete_flg = 0 ORDER BY create_date DESC';
		$data = array(':u_id' => $u_id);
		// クエリ実行
		$stmt = queryPost($dbh, $sql, $data);

		if($stmt){
			return $stmt->fetchAll();
		}else{
			return false;
		}
	} catch (Exception $e) {
		error_log('エラー発生：'.$e->getMessage());
	}
}
function getPrefecture(){
  debug('都道府県データを取得します');
  //例外処理
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM prefecture';
    $data = array();
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      //クエリ結果の全データを返却
      return $stmt->fetchAll();
    }else{
      return false;
    }
  } catch (Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
function getCategory(){
  debug('カテゴリー情報を取得します。');
  //例外処理
  try{
    $dbh = dbConnect();
    $sql ='SELECT * FROM category';
    $data = array();
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      //クエリ結果の全データを返却
      return $stmt->fetchAll();
      debug('$rstの中身：'.print_r($rst, true));
    }else{
      return false;
    }
  }catch (Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
//====================================
// メール送信
//====================================
function sendMail($from, $to, $subject, $comment){
  if(!empty($to) && !empty($subject) && !empty($comment)){
    //文字化けしないように設定（お決まりパターン）
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");

    //メールを送信（送信結果はtrueかfalseで帰ってくる）
    $result = mb_send_mail($to, $subject, $comment,"From: ".$from);
    //送信結果を判定
    if($result){
      debug('メールを送信しました。');
    }else{
      debug('エラー発生：メールの送信に失敗しました。');
    }
  }
}
//====================================
// その他
//====================================
// サニタイズ
function sanitize($str){
  return htmlspecialchars($str, ENT_QUOTES);
}
//フォーム入力保持
function getFormData($str, $flg = false){
  if($flg){
    $method = $_GET;
  }else{
    $method = $_POST;
  }
  global $dbFormData;
  global $err_msg;
  // ユーザーデータがある場合
  if(!empty($dbFormData)){
    // フォームのエラーがある場合
    if(!empty($err_msg[$str])){
      // POSTにデータがある場合
      if(isset($method[$str])){ //金額や郵便番号などのフォームで数字や数値の0が入っている場合もあるので、issetを使うこと
        return sanitize($method[$str]);
      } else {
        // ない場合（フォームにエラーがある=POSTされてるはずなので、まずありえないが）はDBの情報を表示
        return sanitize($dbFormData[$str]);
      }
    } else {
      // POSTにデータがあり、DBの情報と違う場合（このフォームも変更していてエラーはないが、他のフォームでひっかかっている状態）
      if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]) {
        return sanitize($method[$str]);
      }else { //そもそも変更していない
        return sanitize($dbFormData[$str]);
      }
    }
  } else {
    if(isset($method[$str])) {
      return sanitize($method[$str]);
    }
  }
}
//セッションを1回だけ取得する関数
function getSessionFlash($key){
  if(!empty($_SESSION[$key])){
    $data = $_SESSION[$key];
    //セッションを空にしないとマイページにいくと何回もでてくる
    $_SESSION[$key] = '';
    return $data;
  }
}
//8桁の認証キー作成
function makeRandKey($length = 8){
  //半角英数字を用意（この中からランダムに8文字選ばれたものが認証キーと仮パスワードになる）
  $chars = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  //空の変数を用意
  $str = '';
  //for文で8文字作成
  for($i = 0; $i < $length; ++$i){
    //$str = $str.$chars[mt_rand(0, 61)]
    $str .= $chars[mt_rand(0, 61)];
  }
  return $str;
}
//画像処理
function uploadImg($file, $key){
  debug('画像アップロード処理開始');
  debug('FILE情報：'.print_r($file,true));

  if(isset($file['error']) && is_int($file['error'])){
    try{
      //バリデーション
      //$file['error']の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として０や１などの数値が入っている。
      switch ($file['error']){
        case UPLOAD_ERR_OK: //OK
          break;
        case UPLOAD_ERR_NO_FILE: //ファイル未選択の場合
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE: //php.ini定義の最大サイズが超過した場合
        case UPLOAD_ERR_FORM_SIZE: //フォーム定義の最大サイズ超過した場合
          throw new RuntimeException('ファイルサイズが大きすぎます');
        default: //その他の場合
          throw new RuntimeException('その他のエラーが発生しました');
      }

      //$file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
      //exif_imagetype関数は[IMAGETYPE_GIF][IMAGETYPE_JPEG]などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      if(!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)){
        //第3引数にtrueを設定すると厳密にチェックしてくれるので必ずつける
        throw new RuntimeException('画像形式が未対応です');
      }

      //ファイルデータからSHA-1ハッシュをとってファイル名を決定し、ファイルを保存する
      //ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      //DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      //img_type_to_extension関数はファイル拡張子を取得するもの
      $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
      if(!move_uploaded_file($file['tmp_name'], $path)){ //ファイルを移動する
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      //保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：'.$path);
      return $path;

    } catch (RuntimeException $e){

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}
//ページング
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination($currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
  // 現在のページが、総ページ数と同じ かつ 総ページ数が表示項目数以上なら、左にリンク４個出す
  if($currentPageNum == $totalPageNum && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum-4;
    $maxPageNum = $currentPageNum;
    // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
  }elseif($currentPageNum == ($totalPageNum-1) && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
    // 現ページが２の場合は左にリンク１個、右にリンク３個出す。
  }elseif($currentPageNum == 2 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
    // 現ページが１の場合は左に何も出さない。右に５個出す。
  }elseif($currentPageNum == 1 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
    // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax,ループのMinを１に設定
  }elseif($totalPageNum < $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
    // それ以外は左に２個出す。
  }else{
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }

  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
      if($currentPageNum != 1){
        echo '<li class="list-item"><a href="?p=1'.$link.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="list-item ';
        if($currentPageNum == $i){ echo 'active'; }
        echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
      }
      if($currentPageNum != $maxPageNum && $maxPageNum > 1){
        echo '<li class="list-item"><a href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
      }
  echo '</ul>';
  echo '</div>';
}
// 画像表示用関数
function showImg($path){
  if(empty($path)){
    return 'img/sample-img.png';
  }else{
    return $path;
  }
}
//GETパラメータ付与
// $del_key : 付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array()){
  if(!empty($_GET)){
    $str = '?';
    foreach($_GET as $key => $val){
      if(!in_array($key,$arr_del_key,true)){ //取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
        $str .= $key.'='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}