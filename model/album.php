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
        COUNT(p.Picture_Id) as num_pictures,
        acc.Accessibility_Code as accessibility
      FROM cst8257project.album a
      LEFT JOIN cst8257project.picture p ON a.Album_Id = p.Album_Id
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

  public function createAlbum($title, $accessibility, $description, $ownerId = "001") {
    if (!empty($title) && !empty($accessibility) && !empty($description)) {
        try {
            // Prepare the SQL statement
            $stmt = $this->pdo->prepare("
                INSERT INTO album ( Title, Description, Owner_Id, Accessibility_Code) 
                VALUES ( :Title, :Description, :Owner_Id, :Accessibility_Code)
            ");
  
            // Execute the statement with bound parameters
            $stmt->execute([
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

  public function deleteUserAlbumById($albumId, $userId) {
    $query = "
    DELETE FROM cst8257project.album a
    WHERE Album_Id = :albumId 
    AND Owner_Id = :userId;
    ";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(':albumId', $albumId);
    $stmt->bindParam(':userId', $userId);
    if ($stmt->execute()) {
      return $stmt->rowCount() > 0; // Returns true if rows were affected, false otherwise
    }
    return false; // Returns false if execution failed
  }

  public function updateAccessByAlbumId($albumId, $mode) {
    $query = "
    UPDATE cst8257project.album a
    SET Accessibility_Code = :mode
    WHERE Album_Id = :albumId;
    ";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindParam(':albumId', $albumId);
    $stmt->bindParam(':mode', $mode);
    if ($stmt->execute()) {
      return $stmt->rowCount() > 0; // Returns true if rows were affected, false otherwise
    }
    return false; // Returns false if execution failed
  }

  public function getAccessibilityModes() {
    $query = "
    SELECT
      Accessibility_Code as code,
      Description as name
    FROM cst8257project.accessibility;
    ";
    $stmt = $this->pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC);
  }
}
