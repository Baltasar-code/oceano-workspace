<?php
// api/entregables.php
require_once '../config/config.php';
require_once '../includes/auth_check.php'; // SOLUCIÓN AL BUG CRÍTICO
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autorizado.']); exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$me     = (int) $_SESSION['user_id'];

switch ($action) {

    case 'list':
        $mes = $_GET['mes'] ?? date('Y-m');
        $params = [$mes];
        $whereClause = "ml.mes_anio = ?";

        if (isAdmin()) {
            // Filtros opcionales para administradores
            if (!empty($_GET['area_id'])) {
                $whereClause .= " AND u.area_id = ?";
                $params[] = (int)$_GET['area_id'];
            }
            if (!empty($_GET['user_id'])) {
                $whereClause .= " AND ml.user_id = ?";
                $params[] = (int)$_GET['user_id'];
            }
        } else {
            // Usuario estándar solo ve lo suyo
            $whereClause .= " AND ml.user_id = ?";
            $params[] = $me;
        }

        $stmt = $pdo->prepare("
            SELECT ml.*, u.nombre AS usuario, a.nombre AS area_nombre
            FROM monthly_links ml
            JOIN users u ON ml.user_id = u.id
            LEFT JOIN areas a ON u.area_id = a.id
            WHERE $whereClause
            ORDER BY ml.fecha_subida DESC
        ");
        $stmt->execute($params);
        echo json_encode(['status' => 'success', 'data' => $stmt->fetchAll()]);
        break;

    case 'create':
        $mes      = trim($_POST['mes_anio']    ?? date('Y-m'));
        $desc     = trim($_POST['descripcion'] ?? '');
        $archivo  = trim($_POST['nombre_archivo'] ?? '');
        $link     = trim($_POST['drive_link']  ?? '');

        if (empty($link)) {
            echo json_encode(['status' => 'error', 'message' => 'El link de Drive es obligatorio.']); exit;
        }
        $pdo->prepare("INSERT INTO monthly_links (user_id, mes_anio, descripcion, nombre_archivo, drive_link) VALUES (?,?,?,?,?)")
            ->execute([$me, $mes, htmlspecialchars($desc), htmlspecialchars($archivo), htmlspecialchars($link)]);
        echo json_encode(['status' => 'success', 'message' => 'Entregable registrado correctamente.']);
        break;

    case 'update':
        $id       = (int)($_POST['id'] ?? 0);
        $mes      = trim($_POST['mes_anio']    ?? date('Y-m'));
        $desc     = trim($_POST['descripcion'] ?? '');
        $archivo  = trim($_POST['nombre_archivo'] ?? '');
        $link     = trim($_POST['drive_link']  ?? '');

        if (empty($link)) {
            echo json_encode(['status' => 'error', 'message' => 'El link de Drive es obligatorio.']); exit;
        }

        // Check ownership or admin
        $check = $pdo->prepare("SELECT user_id FROM monthly_links WHERE id = ?");
        $check->execute([$id]);
        $row = $check->fetch();
        if (!$row || ($row->user_id !== $me && !isAdmin())) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso para editar.']); exit;
        }

        $pdo->prepare("UPDATE monthly_links SET mes_anio=?, descripcion=?, nombre_archivo=?, drive_link=? WHERE id=?")
            ->execute([$mes, htmlspecialchars($desc), htmlspecialchars($archivo), htmlspecialchars($link), $id]);
        echo json_encode(['status' => 'success', 'message' => 'Entregable actualizado correctamente.']);
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        // Solo puede borrar si es suyo o admin
        $check = $pdo->prepare("SELECT user_id FROM monthly_links WHERE id = ?");
        $check->execute([$id]);
        $row = $check->fetch();
        if (!$row || ($row->user_id !== $me && !isAdmin())) {
            echo json_encode(['status' => 'error', 'message' => 'Sin permiso.']); exit;
        }
        $pdo->prepare("DELETE FROM monthly_links WHERE id = ?")->execute([$id]);
        echo json_encode(['status' => 'success', 'message' => 'Entregable eliminado.']);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida.']);
}