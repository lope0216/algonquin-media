<?php
require_once __DIR__.'/../db/DBconnection.php';

class AlbumModel {
  private $pdo;

  public function __construct() {
    $this->pdo = getPDOConnection(); // Initialize the PDO connection
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

  public function getAlbumAccesibillityOptions() {
    $accessibilityOptions = [];
    try {
        // Use $this->pdo instead of $pdo to refer to the PDO connection
        $stmt = $this->pdo->query("SELECT Accessibility_Code, Description FROM accessibility"); 
        $accessibilityOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching accessibility options: " . $e->getMessage());
    }
    return $accessibilityOptions;
  }

  public function createAlbum($title, $accessibility, $description, $albumId = "001", $ownerId = "001") {
    if (!empty($title) && !empty($accessibility) && !empty($description)) {
        try {
            // Prepare the SQL statement
            $stmt = $this->pdo->prepare("
                INSERT INTO album (Album_Id, Title, Description, Owner_Id, Accessibility_Code) 
                VALUES (:Album_Id, :Title, :Description, :Owner_Id, :Accessibility_Code)
            ");
  
            // Execute the statement with bound parameters
            $stmt->execute([
                ':Album_Id' => $albumId,
                ':Title' => $title,
                ':Description' => $description,
                ':Owner_Id' => $ownerId,
                ':Accessibility_Code' => $accessibility,
            ]);
  
            return "Album created successfully!";
        } catch (PDOException $e) {
            return "Error creating album: " . $e->getMessage();
        }
    } else {
        return "Please fill out all fields.";
    }
  }
}
