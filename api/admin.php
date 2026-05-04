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
        $nombre = trim($_POST['nombre'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $pass   = password_hash($_POST['password'] ?? '123456', PASSWORD_DEFAULT);
        $rol    = $_POST['rol'] ?? 'Standard';
        $area   = (int)($_POST['area_id'] ?? 1);
        if (empty($nombre) || empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Nombre y Email son obligatorios']); exit;
        }
        $stmt = $pdo->prepare("INSERT INTO users (nombre, email, password_hash, rol, area_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $email, $pass, $rol, $area]);
        echo json_encode(['status' => 'success']);
        break;
    case 'delete_user':
        $id = (int)($_POST['id'] ?? 0);
        if ($id == $_SESSION['user_id']) { 
            echo json_encode(['status' => 'error', 'message' => 'No puedes borrarte a ti mismo']); exit; 
        }
        $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
        echo json_encode(['status' => 'success']);
        break;
    case 'list_areas':
        $stmt = $pdo->query("SELECT * FROM areas");
        echo json_encode($stmt->fetchAll());
        break;
    case 'create_area':
        $nombre = trim($_POST['nombre'] ?? '');
        if (empty($nombre)) {
            echo json_encode(['status' => 'error', 'message' => 'El nombre del área es obligatorio']); exit;
        }
        try {
            $stmt = $pdo->prepare("INSERT INTO areas (nombre) VALUES (?)");
            $stmt->execute([$nombre]);
            echo json_encode(['status' => 'success']);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'El área ya existe o ocurrió un error.']);
        }
        break;
    case 'delete_area':
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM areas WHERE id = ?")->execute([$id]);
        echo json_encode(['status' => 'success']);
        break;
}