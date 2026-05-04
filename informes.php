<?php
$pageTitle   = 'Informes PDF';
$currentPage = 'informes';
require_once 'includes/header.php';
requireAdmin(); // Sólo para administradores
?>
<?php require_once 'includes/sidebar.php'; ?>
<main class="main-content flex-1 overflow-y-auto p-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-slate-800 text-2xl font-bold">Informes Corporativos</h2>
            <p class="text-slate-400 text-sm mt-1">Genera reportes oficiales en formato PDF listos para firmar.</p>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl">
        <div class="card p-6 border border-slate-100 hover:border-blue-200 transition-all flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center mb-4">
                <i data-lucide="users" class="w-8 h-8 text-blue-500"></i>
            </div>
            <h3 class="font-bold text-slate-800 text-lg mb-2">Directorio de Personal</h3>
            <p class="text-slate-500 text-sm mb-6 flex-1">
                Genera un reporte completo con todos los usuarios registrados y sus áreas asignadas.
            </p>
            <button onclick="generarReporte('usuarios')" class="btn-primary w-full flex items-center justify-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i> Generar PDF
            </button>
        </div>
        <div class="card p-6 border border-slate-100 hover:border-emerald-200 transition-all flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center mb-4">
                <i data-lucide="folder-check" class="w-8 h-8 text-emerald-500"></i>
            </div>
            <h3 class="font-bold text-slate-800 text-lg mb-2">Reporte de Entregables</h3>
            <p class="text-slate-500 text-sm mb-4">
                Genera un listado de los links y archivos entregados durante un mes específico.
            </p>
            <div class="w-full mb-4 text-left">
                <label class="block text-slate-600 text-xs font-semibold uppercase mb-1">Seleccionar Mes</label>
                <input type="month" id="mesReporte" class="input-field" value="<?= date('Y-m') ?>">
            </div>
            <button onclick="generarReporte('entregables')" class="btn-primary w-full !bg-emerald-500 hover:!bg-emerald-600 flex items-center justify-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i> Generar PDF
            </button>
        </div>
    </div>
</main>
<script>
lucide.createIcons();
function generarReporte(tipo) {
    let url = 'api/reports.php?tipo=' + tipo;
    if (tipo === 'entregables') {
        const mes = document.getElementById('mesReporte').value;
        if (!mes) {
            alert("Selecciona un mes primero.");
            return;
        }
        url += '&mes=' + mes;
    }
    window.open(url, '_blank');
}
</script>
</body>
</html>