<?php
require_once '../config/config.php';
header('Content-Type: application/json');
$action = $_GET['action'] ?? $_POST['action'] ?? '';
switch ($action) {
    case 'list':
        $stmt = $pdo->prepare("SELECT * FROM monthly_links WHERE user_id = ? AND mes_anio = ?");
        $stmt->execute([$_SESSION['user_id'], $_GET['mes']]); echo json_encode(['status'=>'success','data'=>$stmt->fetchAll()]); break;
    case 'create':
        $stmt = $pdo->prepare("INSERT INTO monthly_links (user_id, mes_anio, descripcion, nombre_archivo, drive_link) VALUES (?,?,?,?,?)");
        $stmt->execute([$_SESSION['user_id'], $_POST['mes_anio'], $_POST['descripcion'], $_POST['nombre_archivo'], $_POST['drive_link']]);
        echo json_encode(['status'=>'success']); break;
}
