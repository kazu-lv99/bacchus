<?php
//====================================
// ログイン認証・自動ログアウト
//====================================
// ログインしている場合
if(!empty($_SESSION['login_date'])){
  debug('ログインしています');

  if(($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
    debug('ログイン有効期限切れです');
    // セッションを削除してログイン画面へ
    session_destroy();
    header("Location:login.php");
    exit();
  }else{
    debug('ログイン有効期限内です');
    // 最終ログイン日時を現在日時に更新
    $_SESSION['login_date'] = time();

    // ログイン有効期限なのにログインページへ跳ぼうとしている
    // この場合マイページへ遷移させる
    if(basename($_SERVER['PHP_SELF']) === 'login.php'){
      header("Location:mypage.php");
      exit();
    }
  }
}else{
  debug('未ログインユーザーです');
  //ログインページ以外へ跳ぼうとしているとき
  //ログインページへ遷移させる
  if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
    debug('ログインページ以外への遷移です');
    header("Location:login.php");
    exit();
  }
}
