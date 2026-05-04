<?php
require_once '../config/config.php';
require_once '../includes/auth_check.php';

// Cargar la librería Dompdf
require_once '../vendor/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Seguridad (revisión por tipo de reporte)
$me = (int)($_SESSION['user_id'] ?? 0);
$isAdmin = isAdmin();

$tipo = $_GET['tipo'] ?? '';
$html = '';
$tituloDocumento = 'Reporte Corporativo';

// Estilos base para el PDF (Limpios y profesionales)
$css = "
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #0f172a; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #64748b; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 40px; font-size: 12px; }
        th { background-color: #f1f5f9; color: #0f172a; font-weight: bold; padding: 10px; text-align: left; border-bottom: 1px solid #cbd5e1; }
        td { padding: 10px; border-bottom: 1px solid #e2e8f0; color: #475569; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        
        /* Espacio para firma */
        .firma-section { margin-top: 60px; text-align: right; page-break-inside: avoid; }
        .firma-linea { border-top: 1px solid #000; width: 250px; display: inline-block; margin-bottom: 5px; }
        .firma-texto { font-size: 12px; font-weight: bold; color: #0f172a; }
        .firma-nombre { font-size: 12px; color: #64748b; }
    </style>
";

if ($tipo === 'usuarios') {
    if (!$isAdmin) die("No autorizado.");
    $tituloDocumento = 'Directorio de Personal Oceano';
    
    $stmt = $pdo->query("SELECT u.nombre, u.email, u.rol, a.nombre as area FROM users u LEFT JOIN areas a ON u.area_id = a.id ORDER BY u.nombre ASC");
    $usuarios = $stmt->fetchAll();

    $html = $css . "
        <div class='header'>
            <h1>$tituloDocumento</h1>
            <p>Generado el: " . date('d/m/Y H:i') . "</p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Nombre Completo</th>
                    <th>Email Corporativo</th>
                    <th>Área / Departamento</th>
                    <th>Rol en Sistema</th>
                </tr>
            </thead>
            <tbody>
    ";
    foreach ($usuarios as $u) {
        $html .= "<tr>
            <td>" . htmlspecialchars($u->nombre) . "</td>
            <td>" . htmlspecialchars($u->email) . "</td>
            <td>" . htmlspecialchars($u->area ?? 'Sin Asignar') . "</td>
            <td>" . htmlspecialchars($u->rol) . "</td>
        </tr>";
    }
    $html .= "</tbody></table>";

} elseif ($tipo === 'entregables') {
    $mes = $_GET['mes'] ?? date('Y-m');
    $tituloDocumento = 'Reporte de Entregables - ' . $mes;
    
    $params = [$mes];
    $where = "m.mes_anio = ?";
    
    if ($isAdmin) {
        if (!empty($_GET['area_id'])) {
            $where .= " AND u.area_id = ?";
            $params[] = (int)$_GET['area_id'];
        }
        if (!empty($_GET['user_id'])) {
            $where .= " AND m.user_id = ?";
            $params[] = (int)$_GET['user_id'];
        }
    } else {
        $where .= " AND m.user_id = ?";
        $params[] = $me;
    }
    
    $stmt = $pdo->prepare("SELECT m.nombre_archivo, m.descripcion, m.fecha_subida, u.nombre as autor, a.nombre as area 
                           FROM monthly_links m 
                           JOIN users u ON m.user_id = u.id 
                           LEFT JOIN areas a ON u.area_id = a.id 
                           WHERE $where 
                           ORDER BY m.fecha_subida DESC");
    $stmt->execute($params);
    $entregables = $stmt->fetchAll();

    $html = $css . "
        <div class='header'>
            <h1>$tituloDocumento</h1>
            <p>Generado el: " . date('d/m/Y H:i') . "</p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Autor</th>
                    <th>Área</th>
                    <th>Archivo / Documento</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
    ";
    if (count($entregables) === 0) {
        $html .= "<tr><td colspan='5' style='text-align:center;'>No hay entregables registrados.</td></tr>";
    } else {
        foreach ($entregables as $e) {
            $html .= "<tr>
                <td>" . date('d/m/Y', strtotime($e->fecha_subida)) . "</td>
                <td>" . htmlspecialchars($e->autor) . "</td>
                <td>" . htmlspecialchars($e->area ?? 'N/A') . "</td>
                <td>" . htmlspecialchars($e->nombre_archivo) . "</td>
                <td>" . htmlspecialchars($e->descripcion) . "</td>
            </tr>";
        }
    }
    $html .= "</tbody></table>";

} elseif ($tipo === 'calendario') {
    $inicio = $_GET['inicio'] ?? date('Y-m-01');
    $fin = $_GET['fin'] ?? date('Y-m-t');
    $tituloDocumento = 'Reporte de Eventos (' . date('d/m/Y', strtotime($inicio)) . ' al ' . date('d/m/Y', strtotime($fin)) . ')';
    
    $stmt = $pdo->prepare("SELECT titulo, descripcion, fecha_inicio, fecha_fin, tipo 
                           FROM events 
                           WHERE fecha_inicio >= ? AND fecha_inicio <= ? 
                           ORDER BY fecha_inicio ASC");
    $stmt->execute([$inicio . ' 00:00:00', $fin . ' 23:59:59']);
    $eventos = $stmt->fetchAll();

    $html = $css . "
        <div class='header'>
            <h1>$tituloDocumento</h1>
            <p>Generado el: " . date('d/m/Y H:i') . "</p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Tipo</th>
                    <th>Evento</th>
                    <th>Descripción</th>
                </tr>
            </thead>
            <tbody>
    ";
    if (count($eventos) === 0) {
        $html .= "<tr><td colspan='5' style='text-align:center;'>No hay eventos programados en este rango.</td></tr>";
    } else {
        foreach ($eventos as $e) {
            $html .= "<tr>
                <td>" . date('d/m/Y H:i', strtotime($e->fecha_inicio)) . "</td>
                <td>" . ($e->fecha_fin ? date('d/m/Y H:i', strtotime($e->fecha_fin)) : '—') . "</td>
                <td>" . htmlspecialchars($e->tipo) . "</td>
                <td>" . htmlspecialchars($e->titulo) . "</td>
                <td>" . htmlspecialchars($e->descripcion) . "</td>
            </tr>";
        }
    }
    $html .= "</tbody></table>";

} else {
    die("Tipo de reporte inválido.");
}

// Agregar el espacio de firma al final de todos los reportes
$adminName = htmlspecialchars($_SESSION['user_nombre']);
$html .= "
    <div class='firma-section'>
        <div class='firma-linea'></div><br>
        <span class='firma-texto'>Aprobado y Validado por</span><br>
        <span class='firma-nombre'>$adminName (Administrador)</span>
    </div>
";

// Pie de página estandarizado
$html .= "
    <div class='footer'>
        Documento interno oficial - Plataforma Oceano Workspace
    </div>
";

// Configurar Dompdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Útil si en el futuro añades imágenes por URL absoluta

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

// Formato carta
$dompdf->setPaper('letter', 'portrait');

// Renderizar PDF (primer paso para crear el archivo)
$dompdf->render();

// Enviar el PDF al navegador para forzar descarga o visualización
$dompdf->stream("Reporte_Oceano_" . date('Ymd_Hi') . ".pdf", array("Attachment" => false)); // Attachment = false lo abre en el navegador, true lo descarga