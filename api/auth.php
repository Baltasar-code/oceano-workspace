<?php
require_once '../config/config.php';
header('Content-Type: application/json');
$action = $_POST['action'] ?? '';
if ($action === 'login') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch();
    if ($user && password_verify($_POST['password'], $user->password_hash)) {
        $_SESSION['user_id'] = $user->id; $_SESSION['user_nombre'] = $user->nombre; $_SESSION['user_rol'] = $user->rol;
        echo json_encode(['status' => 'success']);
    } else echo json_encode(['status' => 'error', 'message' => 'Credenciales incorrectas']);
}
