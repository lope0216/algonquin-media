<?php

include_once __DIR__ . '/../model/album.php';
include_once __DIR__ . '/../model/picture.php';

session_start();
// if (!$_SESSION['loggedIn']) {
//   header("Location: index.php");
//   exit();
// }

$albumModel = new AlbumModel();
$pictureModel = new PictureModel();
$user = ['name' => 'Gio']; //$_SESSION['student'];
$accessibilityModes = $albumModel->getAccessibilityModes();

if (isFromSubmit()) {
  $type = $_POST['type'];
  $albumId = $_POST['album_id'];
  if ($type == 'delete') {
    $pictureModel->deletePicturesFromAlbum($albumId);
    $albumModel->deleteUserAlbumById($albumId, 'U0001');
  }

  if ($type == 'save') {
    $mode = $_POST['accessibility'];
    $albumModel->updateAccessByAlbumId($albumId, $mode);
  }
}

$albums = $albumModel->getPhotoAlbumsByUserId('U0001'); //getAlbumsByUserId($student['UserId']);

function isFromSubmit() {
  return $_SERVER['REQUEST_METHOD'] === 'POST';
}
?>


<!DOCTYPE html>
<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta charset="UTF-8">

  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link rel="stylesheet" href="<?= dirname($_SERVER['PHP_SELF']) . '/../public/css/global.css' ?>" />

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <title>Algonquin Media</title>
</head>

<body class="body-layout">
  <?php include("../common/header.php"); ?>


  <main class="container">

    <h1 class="text-primary text-center my-3">My Albums</h1>
    <p>Welcome <b><?= $user['name'] ?></b>! (not you? change user <a href="/pages/logout.php">here</a>)</p>
    <a href="addAlbum.php" class="btn btn-primary mb-3">Create a new album</a>

    <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
      <input type="hidden" name="album_id" id="album_id" value="">
      <input type="hidden" name="accessibility" id="accessibility" value="">
      <input type="hidden" name="type" id="submitType" value="">
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
            <tr data-album-id="<?= $album['album_id'] ?>" data-album-title="<?= $album['album_title'] ?>">
              <td>
                <a href="myPictures.php?album=<?= $album['album_id'] ?>"><?= $album['album_title'] ?></a>
              </td>
              <td><?= $album['num_pictures'] ?></td>
              <td>
                <select class="form-select" >
                  <?php foreach ($accessibilityModes as $mode): ?>
                    <option value="<?= $mode['code'] ?>" <?= $mode['code'] == $album['accessibility'] ? 'selected' : '' ?>>
                      <?= $mode['name'] ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td>
                <button type="submit" class="btn">
                  <i class="material-icons text-primary save-btn">save</i>
                </button>
                <button type="submit" class="btn">
                  <i class="material-icons text-danger delete-btn">delete</i>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
  
        </tbody>
      </table>
    </form>
  </main>

  <?php
  // put your code here
  ?>

  <?php include("../common/footer.php"); ?>

  <script src="<?= dirname($_SERVER['PHP_SELF']) . '/../public/js/myAlbums.js' ?>" type="module"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous"></script>
</body>

</html>