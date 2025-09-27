<?php
// send_message.php
session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(403);
    exit('Non autorizzato');
}

// Configurazione
$uploadDir = 'uploads/';
$maxFileSize = 10 * 1024 * 1024; // 10MB
$allowedTypes = [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
    'application/pdf', 'text/plain', 'application/msword', 
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/zip', 'application/x-rar-compressed',
    'audio/mpeg', 'audio/wav', 'video/mp4', 'video/mpeg'
];

// Crea directory uploads se non esiste
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$username = $_SESSION['username'];
$message = trim($_POST['message'] ?? '');
$fileHtml = '';

// Gestione upload file
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['file'];
    $fileName = basename($file['name']);
    $fileSize = $file['size'];
    $fileTmpPath = $file['tmp_name'];
    $fileType = mime_content_type($fileTmpPath);
    
    // Validazioni
    if ($fileSize > $maxFileSize) {
        http_response_code(400);
        exit('File troppo grande. Dimensione massima: 10MB');
    }
    
    // Controllo tipo file (opzionale ma consigliato)
    if (!in_array($fileType, $allowedTypes)) {
        http_response_code(400);
        exit('Tipo di file non supportato');
    }
    
    // Sanitizza nome file
    $safeFileName = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $fileName);
    $ext = strtolower(pathinfo($safeFileName, PATHINFO_EXTENSION));
    $newFileName = uniqid('file_') . '.' . $ext;
    $destPath = $uploadDir . $newFileName;
    
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        $safeName = htmlspecialchars($fileName, ENT_QUOTES, 'UTF-8');
        
        // Genera HTML appropriato per il tipo di file
        if (strpos($fileType, 'image/') === 0) {
            $fileHtml = '<br><div class="file-attachment"><img src="' . htmlspecialchars($destPath) . '" alt="' . $safeName . '" style="max-width: 300px; max-height: 200px; border-radius: 8px; margin-top: 8px;" /></div>';
        } elseif (strpos($fileType, 'audio/') === 0) {
            $fileHtml = '<br><div class="file-attachment"><audio controls style="margin-top: 8px;"><source src="' . htmlspecialchars($destPath) . '" type="' . $fileType . '">Il tuo browser non supporta l\'audio.</audio><br><a href="' . htmlspecialchars($destPath) . '" download="' . $safeName . '">📎 ' . $safeName . '</a></div>';
        } elseif (strpos($fileType, 'video/') === 0) {
            $fileHtml = '<br><div class="file-attachment"><video controls style="max-width: 300px; margin-top: 8px;"><source src="' . htmlspecialchars($destPath) . '" type="' . $fileType . '">Il tuo browser non supporta il video.</video><br><a href="' . htmlspecialchars($destPath) . '" download="' . $safeName . '">📎 ' . $safeName . '</a></div>';
        } else {
            $fileHtml = '<br><div class="file-attachment"><a href="' . htmlspecialchars($destPath) . '" download="' . $safeName . '" style="color: #2d3748; text-decoration: underline;">📎 ' . $safeName . ' (' . formatFileSize($fileSize) . ')</a></div>';
        }
    }
}

// Connessione DB
try {
    $mysqli = new mysqli('localhost', 'root', '', 'chat_lan');
    
    if ($mysqli->connect_error) {
        throw new Exception('Errore DB');
    }
    
    $mysqli->set_charset("utf8mb4");
    
    $content = $message . $fileHtml;
    $stmt = $mysqli->prepare("INSERT INTO messages (username, message) VALUES (?, ?)");
    $stmt->bind_param('ss', $username, $content);
    
    if (!$stmt->execute()) {
        throw new Exception('Errore nell\'inserimento');
    }
    
    $stmt->close();
    $mysqli->close();
    
    echo 'OK';
    
} catch (Exception $e) {
    http_response_code(500);
    exit('Errore nell\'invio del messaggio');
}

// Funzione per formattare la dimensione del file
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, 2) . ' ' . $units[$pow];
}