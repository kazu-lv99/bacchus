<?php
if(empty($_SESSION['user_id'])){
  //未ログイン用サイドバー表示
  require('sidebar_index.php');
}else{
  //ログイン用のサイドバー表示
  require('sidebar_logined.php');
}