<?php
require_once '../config/config.php';
header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';
switch ($action) {
    case 'list':
        $stmt = $pdo->query("SELECT id, titulo as title, fecha as start FROM events");
        echo json_encode($stmt->fetchAll()); break;
    case 'create':
        $stmt = $pdo->prepare("INSERT INTO events (titulo, fecha, descripcion, creado_por) VALUES (?,?,?,?)");
        $stmt->execute([$_POST['titulo'], $_POST['fecha'], $_POST['descripcion'], $_SESSION['user_id']]);
        echo json_encode(['status'=>'success']); break;
}
