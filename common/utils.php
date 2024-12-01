<?php

startSession();

function startSession() {
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
}

function isLoggedIn() {
  return isset($_SESSION['UserId']) && !empty($_SESSION['UserId']);
}

function unauthorizedAccess() {
  header('location: login.php');
  exit();
}

function redirectToHome() {
  header('location: myAlbums.php');
  exit();
}