<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php?error=Please login first");
    exit();
}

// Database connection configuration
class Database {
    private $host = "localhost";
    private $username = "your_db_username";
    private $password = "your_db_password";
    private $database = "smart";
    private $conn;
    
    public function connect() {
        try {
            $this->conn = new PDO(
                "mysql:host=$this->host;dbname=$this->database",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->conn;
        } catch(PDOException $e) {
            error_log("Connection failed: " . $e->getMessage());
            return false;
        }
    }
}

class ArtworkManager {
    private $db;
    private $jsonFile = 'art_data.json';
    private $jsonData;
    
    public function __construct($db) {
        $this->db = $db;
        $this->loadJsonData();
    }
    
    private function loadJsonData() {
        if (file_exists($this->jsonFile)) {
            $jsonContent = file_get_contents($this->jsonFile);
            $this->jsonData = json_decode($jsonContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON decode error: " . json_last_error_msg());
                throw new Exception("Error processing art data");
            }
        } else {
            error_log("Art data file not found");
            throw new Exception("Art data file not found");
        }
    }
    
    public function searchArtwork($title) {
        foreach ($this->jsonData as $artwork) {
            if (strcasecmp($artwork['list_title'], $title) === 0) {
                return $artwork;
            }
        }
        return null;
    }
    
    public function addArtwork($userId, $artwork) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO user_artwork (user_id, title, image_url, latitude, longitude) 
                 VALUES (:user_id, :title, :image_url, :latitude, :longitude)"
            );
            
            return $stmt->execute([
                ':user_id' => $userId,
                ':title' => $artwork['list_title'],
                ':image_url' => $artwork['photo_webs'],
                ':latitude' => $artwork['latitude'],
                ':longitude' => $artwork['longitude']
            ]);
        } catch(PDOException $e) {
            error_log("Error adding artwork: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserArtwork($userId) {
        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM user_artwork WHERE user_id = :user_id ORDER BY time_added DESC"
            );
            $stmt->execute([':user_id' => $userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error retrieving artwork: " . $e->getMessage());
            return [];
        }
    }
}

// Initialize database and artwork manager
$db = new Database();
$conn = $db->connect();

if (!$conn) {
    die("Database connection failed. Please try again later.");
}

$artworkManager = new ArtworkManager($conn);
$errorMessage = '';
$successMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['artwork_title'])) {
    try {
        $title = trim(htmlspecialchars($_POST['artwork_title']));
        
        if (empty($title)) {
            $errorMessage = "Please enter an artwork title";
        } else {
            $artwork = $artworkManager->searchArtwork($title);
            
            if ($artwork) {
                if ($artworkManager->addArtwork($_SESSION['userid'], $artwork)) {
                    $successMessage = "Artwork added successfully!";
                } else {
                    $errorMessage = "Failed to add artwork to your collection";
                }
            } else {
                $errorMessage = "Artwork not found";
            }
        }
    } catch (Exception $e) {
        error_log("Error processing request: " . $e->getMessage());
        $errorMessage = "An error occurred. Please try again later.";
    }
}

// Get user's artwork collection
$userArtwork = $artworkManager->getUserArtwork($_SESSION['userid']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Art Gallery - <?php echo htmlspecialchars($_SESSION['username']); ?>'s Collection</title>
    <style>
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .artwork-card {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 8px;
        }
        .artwork-card img {
            max-width: 100%;
            height: auto;
        }
        .error { color: red; }
        .success { color: green; }
        .search-form {
            margin: 20px;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    
    <div class="search-form">
        <h2>Add New Artwork</h2>
        <?php if ($errorMessage): ?>
            <p class="error"><?php echo $errorMessage; ?></p>
        <?php endif; ?>
        <?php if ($successMessage): ?>
            <p class="success"><?php echo $successMessage; ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <label for="artwork_title">Artwork Title:</label>
            <input type="text" id="artwork_title" name="artwork_title" required>
            <button type="submit">Search & Add</button>
        </form>
    </div>
    
    <div class="gallery">
        <?php foreach ($userArtwork as $artwork): ?>
            <div class="artwork-card">
                <img src="<?php echo htmlspecialchars($artwork['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($artwork['title']); ?>">
                <h3><?php echo htmlspecialchars($artwork['title']); ?></h3>
                <p>Location: <?php echo htmlspecialchars($artwork['latitude']); ?>, 
                            <?php echo htmlspecialchars($artwork['longitude']); ?></p>
                <p>Added: <?php echo htmlspecialchars($artwork['time_added']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>