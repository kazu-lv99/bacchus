<header>
  <div class="site-width">
    <h1 class="logo"><a href="index.php">Bacchus</a></h1>
    <nav id="top-nav">
      <ul>
        <?php
        if(empty($_SESSION['user_id'])) {
        ?>
          <li><a href="about.php" class="animoBorderLeftRight">ABOUT</a></li>
          <li><a href="login.php" class="animoBorderLeftRight">LOGIN</a></li>
          <li><a href="signup.php" class="animoBorderLeftRight">SIGNUP</a></li>
        <?php
        }else{
        ?>
        <li><a href="about.php" class="animoBorderLeftRight">ABOUT</a></li>
        <li><a href="logout.php" class="animoBorderLeftRight">LOGOUT</a></li>
        <li><a href="mypage.php" class="animoBorderLeftRight">MYPAGE</a></li>
        <?php
        }
        ?>

      </ul>
    </nav>
  </div>
</header>
