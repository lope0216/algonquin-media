<?php

include_once __DIR__ .'/utils.php';

startSession();

?>

<nav class="navbar navbar-expand-lg bg-dark" data-bs-theme="dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="http://www.algonquincollege.com">
        <img src="../public/img/AC.png" 
             alt="Algonquin College" style="max-width:80%; max-height:80%;"/>
    </a>  
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <div class="navbar-nav">
        <a class="nav-link" href="./Index.php">Home</a>
        <a class="nav-link" href="./myFriends.php">My Friends</a>
        <a class="nav-link" href="./myAlbums.php">My Albums</a>
        <a class="nav-link" href="./myPictures.php">My Pictures</a>
        <a class="nav-link" href="./uploadPictures.php">Upload Pictures</a>
        <a class="nav-link <?=isLoggedIn() ? 'd-none' : ''?>" href="./login.php">Login</a>
        <a class="nav-link <?=isLoggedIn() ? '' : 'd-none'?>" href="./logout.php">Logout</a>
      </div>
    </div>
  </div>
</nav>
