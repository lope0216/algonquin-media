<?php

include_once __DIR__.'/../model/album.php';

session_start();
// if (!$_SESSION['loggedIn']) {
//   header("Location: index.php");
//   exit();
// }

$albumModel = new AlbumModel();

$user = ['name' => 'Gio']; //$_SESSION['student'];
$albums = $albumModel->getPhotoAlbumsByUserId('U0001'); //getAlbumsByUserId($student['UserId']);


?>


<!DOCTYPE html>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta charset="UTF-8">

  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= dirname($_SERVER['PHP_SELF']) . '/../public/css/global.css' ?>" />

  <title>Algonquin Media</title>
</head>

<body class="body-layout">
  <?php include("../common/header.php"); ?>


  <main class="container">

    <h1 class="text-primary text-center my-3">My Albums</h1>
    <p>Welcome <b><?=$user['name']?></b>! (not you? change user <a href="/pages/logout.php">here</a>)</p>
    <button class="btn btn-primary mb-3">Create a new album</button>
  
    <table class="table">
      <thead>
        <tr>
          <th>Title</th>
          <th>Number of Pictures</th>
          <th>Accessibility</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
  
        <?php foreach ($albums as $album): ?>
          <tr>
            <td><?= $album['album_title'] ?></td>
            <td><?= $album['num_pictures'] ?></td>
            <td><?= $album['accessibility'] ?></td>
            <td><button class="btn"><i class="material-icons text-danger">delete</i></button></td>
          </tr>
        <?php endforeach; ?>
  
      </tbody>
    </table>
  </main>

  <?php
  // put your code here
  ?>

  <?php include("../common/footer.php"); ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
</body>

</html>