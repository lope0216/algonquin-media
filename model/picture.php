<?php
require_once __DIR__.'/../db/DBconnection.php';

class PictureModel {
  private $pdo;

  public function __construct() {
    $this->pdo = getPDOConnection();
  }

  public function deletePicturesFromAlbum($albumId) {
    $query = "
    DELETE FROM cst8257project.picture p
    WHERE Album_Id = :albumId 
    ";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(':albumId', $albumId);
    if ($stmt->execute()) {
      return $stmt->rowCount() > 0; // Returns true if rows were affected, false otherwise
    }
    return false; // Returns false if execution failed
  }
}