<?php
session_start();
if (!isset($_SESSION['username'])) {
    http_response_code(403);
    exit('Non autorizzato');
}

// Connessione DB
$mysqli = new mysqli('localhost', 'root', '', 'chat_lan');
if ($mysqli->connect_error) {
    http_response_code(500);
    exit('Errore DB');
}

// Prendi ultimi 50 messaggi ordinati per tempo
$result = $mysqli->query("SELECT username, message, DATE_FORMAT(created_at, '%H:%i:%s') as created_at FROM messages ORDER BY id DESC LIMIT 50");

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$mysqli->close();

// Ordina per id crescente per mostrare dal più vecchio al più recente
$messages = array_reverse($messages);

header('Content-Type: application/json');
echo json_encode($messages);
