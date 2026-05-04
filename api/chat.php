<?php
require_once '../config/config.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) exit;
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$me = $_SESSION['user_id'];
switch ($action) {
    case 'get_users':
        $stmt = $pdo->prepare("SELECT u.id, u.nombre, a.nombre as area FROM users u LEFT JOIN areas a ON u.area_id = a.id WHERE u.id != ?");
        $stmt->execute([$me]); echo json_encode(['status'=>'success','data'=>$stmt->fetchAll()]); break;
    case 'get_messages':
        $with = $_GET['with'];
        $stmt = $pdo->prepare("SELECT * FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY timestamp ASC");
        $stmt->execute([$me, $with, $with, $me]); echo json_encode(['status'=>'success','data'=>$stmt->fetchAll()]); break;
    case 'send_message':
        $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, contenido) VALUES (?,?,?)");
        $stmt->execute([$me, $_POST['receiver_id'], $_POST['contenido']]); echo json_encode(['status'=>'success']); break;
}
