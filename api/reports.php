<?php
require_once '../config/config.php';
require_once '../includes/auth_check.php';
require_once '../vendor/dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;
requireAdmin();
$tipo = $_GET['tipo'] ?? '';
$html = '';
$tituloDocumento = 'Reporte Corporativo';
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
        .firma-section { margin-top: 60px; text-align: right; page-break-inside: avoid; }
        .firma-linea { border-top: 1px solid #000; width: 250px; display: inline-block; margin-bottom: 5px; }
        .firma-texto { font-size: 12px; font-weight: bold; color: #0f172a; }
        .firma-nombre { font-size: 12px; color: #64748b; }
    </style>
";
if ($tipo === 'usuarios') {
    $tituloDocumento = 'Directorio de Personal Oceano';
    $stmt = $pdo->query("SELECT u.nombre, u.email, u.rol, a.nombre as area FROM users u LEFT JOIN areas a ON u.area_id = a.id ORDER BY u.nombre ASC");
    $usuarios = $stmt->fetchAll();
    $html = $css . "<div class='header'><h1>$tituloDocumento</h1><p>Generado el: " . date('d/m/Y H:i') . "</p></div>
        <table><thead><tr><th>Nombre Completo</th><th>Email Corporativo</th><th>Área / Departamento</th><th>Rol en Sistema</th></tr></thead><tbody>";
    foreach ($usuarios as $u) {
        $html .= "<tr><td>" . htmlspecialchars($u->nombre) . "</td><td>" . htmlspecialchars($u->email) . "</td><td>" . htmlspecialchars($u->area ?? 'Sin Asignar') . "</td><td>" . htmlspecialchars($u->rol) . "</td></tr>";
    }
    $html .= "</tbody></table>";
} elseif ($tipo === 'entregables') {
    $mes = $_GET['mes'] ?? date('Y-m');
    $tituloDocumento = 'Reporte de Entregables - ' . $mes;
    $stmt = $pdo->prepare("SELECT m.nombre_archivo, m.descripcion, m.fecha_subida, u.nombre as autor, a.nombre as area FROM monthly_links m JOIN users u ON m.user_id = u.id LEFT JOIN areas a ON u.area_id = a.id WHERE m.mes_anio = ? ORDER BY m.fecha_subida DESC");
    $stmt->execute([$mes]);
    $entregables = $stmt->fetchAll();
    $html = $css . "<div class='header'><h1>$tituloDocumento</h1><p>Generado el: " . date('d/m/Y H:i') . "</p></div>
        <table><thead><tr><th>Fecha</th><th>Autor</th><th>Área</th><th>Archivo / Documento</th><th>Descripción</th></tr></thead><tbody>";
    if (count($entregables) === 0) {
        $html .= "<tr><td colspan='5' style='text-align:center;'>No hay entregables registrados en este mes.</td></tr>";
    } else {
        foreach ($entregables as $e) {
            $html .= "<tr><td>" . date('d/m/Y', strtotime($e->fecha_subida)) . "</td><td>" . htmlspecialchars($e->autor) . "</td><td>" . htmlspecialchars($e->area ?? 'N/A') . "</td><td>" . htmlspecialchars($e->nombre_archivo) . "</td><td>" . htmlspecialchars($e->descripcion) . "</td></tr>";
        }
    }
    $html .= "</tbody></table>";
} else {
    die("Tipo de reporte inválido.");
}
$adminName = htmlspecialchars($_SESSION['user_nombre']);
$html .= "<div class='firma-section'><div class='firma-linea'></div><br><span class='firma-texto'>Aprobado y Validado por</span><br><span class='firma-nombre'>$adminName (Administrador)</span></div>";
$html .= "<div class='footer'>Documento interno oficial - Plataforma Oceano Workspace</div>";
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();
$dompdf->stream("Reporte_Oceano_" . date('Ymd_Hi') . ".pdf", array("Attachment" => false));
