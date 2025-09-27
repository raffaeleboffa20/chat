<?php
session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(403);
    exit('Non autorizzato');
}

$username = $_SESSION['username'];
$message = trim($_POST['message'] ?? '');
$imagePath = null;

// Se è stata caricata un'immagine
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['image']['tmp_name'];
    $fileName = basename($_FILES['image']['name']);
    $fileSize = $_FILES['image']['size'];
    $fileType = $_FILES['image']['type'];

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (in_array($fileType, $allowedTypes)) {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = uniqid('img_') . '.' . $ext;
        $uploadDir = 'uploads/';
        $destPath = $uploadDir . $newFileName;

        if (move_uploaded_file($fileTmpPath, $destPath)) {
            $imagePath = $destPath;
        }
    }
}

// Connessione DB
$mysqli = new mysqli('localhost', 'root', '', 'chat_lan');
if ($mysqli->connect_error) {
    http_response_code(500);
    exit('Errore DB');
}

$stmt = $mysqli->prepare("INSERT INTO messages (username, message) VALUES (?, ?)");
$content = $message;

if ($imagePath) {
    // Se c'è un'immagine, aggiungila al contenuto del messaggio
    $imgTag = '<img src="' . htmlspecialchars($imagePath, ENT_QUOTES) . '" style="max-width: 100%; border-radius: 8px; margin-top: 8px;" />';
    $content .= $imgTag;
}

$stmt->bind_param('ss', $username, $content);
$stmt->execute();
$stmt->close();
$mysqli->close();

echo 'OK';
