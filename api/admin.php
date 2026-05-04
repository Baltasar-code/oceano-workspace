<?php
require_once '../config/config.php';
require_once '../includes/auth_check.php';
header('Content-Type: application/json');
requireAdmin();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
switch ($action) {
    case 'list_users':
        $stmt = $pdo->query("SELECT u.*, a.nombre as area_nombre FROM users u LEFT JOIN areas a ON u.area_id = a.id");
        echo json_encode($stmt->fetchAll());
        break;
    case 'create_user':
        $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nombre, email, password_hash, rol, area_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['nombre'], $_POST['email'], $pass, $_POST['rol'], $_POST['area_id']]);
        echo json_encode(['status' => 'success']);
        break;
    case 'delete_user':
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$_POST['id']]);
        echo json_encode(['status' => 'success']);
        break;
    case 'list_areas':
        $stmt = $pdo->query("SELECT * FROM areas");
        echo json_encode($stmt->fetchAll());
        break;
}
