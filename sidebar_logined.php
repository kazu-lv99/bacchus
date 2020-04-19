<aside class="sidebar logined">
  <div class="prof_img">
    <?php
    if(!empty($dbUserData['prof_img'])){
      echo '<img src="'.$dbUserData['prof_img'].'" alt="プロフィール画像">';
    }else{
      echo '<i class="far fa-user-circle"></i>';
    }
    ?>
  </div>
  <p>ようこそ</br>
    <?php if(!empty($dbUserData['username'])){
    echo $dbUserData['username'];
    }?>さん</p>
    <p>出身地：<?php if(isset($dbUserData['prefecture_id'])){
                echo $dbPrefectureData[$dbUserData['prefecture_id']]['name'];
                }?></p>
  <div class="sidemenu">
    <ul>
      <li><a href="registProduct.php" class="animoBorderLeftRight">商品を登録する</a></li>
      <li><a href="profEdit.php" class="animoBorderLeftRight">プロフィール編集</a></li>
      <li><a href="passEdit.php" class="animoBorderLeftRight">パスワード変更</a></li>
      <li><a href="withdraw.php" class="animoBorderLeftRight">退会</a></li>
    </ul>
  </div>
</aside><?php
