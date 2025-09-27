<?php
// get_messages.php
session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(403);
    exit('Non autorizzato');
}

// Configurazione DB
$db_config = [
    'host' => 'localhost',
    'user' => 'root',
    'password' => '',
    'database' => 'chat_lan'
];

// Connessione DB con gestione errori migliorata
try {
    $mysqli = new mysqli($db_config['host'], $db_config['user'], $db_config['password'], $db_config['database']);
    
    if ($mysqli->connect_error) {
        throw new Exception('Errore connessione DB: ' . $mysqli->connect_error);
    }
    
    $mysqli->set_charset("utf8mb4");
    
    // Prendi ultimi 50 messaggi
    $stmt = $mysqli->prepare("SELECT username, message, DATE_FORMAT(created_at, '%H:%i:%s') as created_at FROM messages ORDER BY id DESC LIMIT 50");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    $stmt->close();
    $mysqli->close();
    
    // Ordina per mostrare dal più vecchio al più recente
    $messages = array_reverse($messages);
    
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($messages, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    exit('Errore nel recupero dei messaggi');
}