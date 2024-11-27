<?php
require_once __DIR__.'/../db/DBconnection.php';
class AlbumModel {
  private $pdo;

  public function __construct() {
    $this->pdo = getPDOConnection();
  }

  public function getPhotoAlbumsByUserId($userId){
    $query = "
      SELECT 
        a.Album_Id as album_id,
        a.Title as album_title,
        COUNT(*) as num_pictures,
        acc.Description as accessibility
      FROM cst8257project.album a
      INNER JOIN cst8257project.picture p ON a.Album_Id = p.Album_Id
      INNER JOIN cst8257project.accessibility acc ON a.Accessibility_Code = acc.Accessibility_Code
      WHERE a.Owner_Id = :userId
      GROUP BY album_id, album_title, accessibility;
    ";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(':userId', $userId);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }
}