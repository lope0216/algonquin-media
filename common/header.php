<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$isLoggedIn = $_SESSION['loggedIn'] ?? false;
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
        <a class="nav-link" href="./courseSelection.php">Course Selection</a>
        <a class="nav-link" href="./currentRegistration.php">Current Registration</a>
        <a class="nav-link <?=$isLoggedIn ? 'hidden' : ''?>" href="./login.php">Login</a>
        <a class="nav-link <?=$isLoggedIn ? '' : 'hidden'?>" href="./logout.php">Logout</a>
      </div>
    </div>
  </div>
</nav>
