<?php
$pageTitle   = 'Informes PDF';
$currentPage = 'informes';
require_once 'includes/header.php';
require_once 'includes/auth_check.php';

$isAdmin = isAdmin();
$areas = [];
$usuarios = [];
if ($isAdmin) {
    $areas = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre")->fetchAll();
    $usuarios = $pdo->query("SELECT id, nombre FROM users ORDER BY nombre")->fetchAll();
}
?>
<?php require_once 'includes/sidebar.php'; ?>
<main class="main-content flex-1 overflow-y-auto p-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-slate-800 text-2xl font-bold">Informes Corporativos</h2>
            <p class="text-slate-400 text-sm mt-1">Genera reportes oficiales en formato PDF listos para firmar.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl">
        
        <?php if ($isAdmin): ?>
        <!-- Reporte de Usuarios -->
        <div class="card p-6 border border-slate-100 hover:border-blue-200 transition-all flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center mb-4">
                <i data-lucide="users" class="w-8 h-8 text-blue-500"></i>
            </div>
            <h3 class="font-bold text-slate-800 text-lg mb-2">Directorio de Personal</h3>
            <p class="text-slate-500 text-sm mb-6 flex-1">
                Genera un reporte completo con todos los usuarios registrados y sus áreas asignadas.
            </p>
            <button onclick="generarReporte('usuarios')" class="btn-primary w-full flex items-center justify-center gap-2 mt-auto">
                <i data-lucide="download" class="w-4 h-4"></i> Generar PDF
            </button>
        </div>
        <?php endif; ?>

        <!-- Reporte de Entregables -->
        <div class="card p-6 border border-slate-100 hover:border-emerald-200 transition-all flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-2xl bg-emerald-50 flex items-center justify-center mb-4">
                <i data-lucide="folder-check" class="w-8 h-8 text-emerald-500"></i>
            </div>
            <h3 class="font-bold text-slate-800 text-lg mb-2">Reporte de Entregables</h3>
            <p class="text-slate-500 text-sm mb-4">
                Listado de los links y archivos entregados.
            </p>
            
            <div class="w-full space-y-3 mb-4 text-left flex-1">
                <div>
                    <label class="block text-slate-600 text-xs font-semibold uppercase mb-1">Mes</label>
                    <input type="month" id="mesReporte" class="input-field" value="<?= date('Y-m') ?>">
                </div>
                <?php if ($isAdmin): ?>
                <div>
                    <label class="block text-slate-600 text-xs font-semibold uppercase mb-1">Área (Opcional)</label>
                    <select id="areaReporte" class="input-field">
                        <option value="">Todas las áreas</option>
                        <?php foreach($areas as $a): ?>
                            <option value="<?= $a->id ?>"><?= htmlspecialchars($a->nombre) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-600 text-xs font-semibold uppercase mb-1">Usuario (Opcional)</label>
                    <select id="usuarioReporte" class="input-field">
                        <option value="">Todos los usuarios</option>
                        <?php foreach($usuarios as $u): ?>
                            <option value="<?= $u->id ?>"><?= htmlspecialchars($u->nombre) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <button onclick="generarReporte('entregables')" class="btn-primary w-full !bg-emerald-500 hover:!bg-emerald-600 flex items-center justify-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i> Generar PDF
            </button>
        </div>

        <!-- Reporte de Calendario -->
        <div class="card p-6 border border-slate-100 hover:border-purple-200 transition-all flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-2xl bg-purple-50 flex items-center justify-center mb-4">
                <i data-lucide="calendar-days" class="w-8 h-8 text-purple-500"></i>
            </div>
            <h3 class="font-bold text-slate-800 text-lg mb-2">Eventos y Calendario</h3>
            <p class="text-slate-500 text-sm mb-4">
                Genera un reporte de los eventos programados en un rango de fechas.
            </p>
            
            <div class="w-full space-y-3 mb-4 text-left flex-1">
                <div>
                    <label class="block text-slate-600 text-xs font-semibold uppercase mb-1">Fecha Inicio</label>
                    <input type="date" id="fechaInicio" class="input-field" value="<?= date('Y-m-01') ?>">
                </div>
                <div>
                    <label class="block text-slate-600 text-xs font-semibold uppercase mb-1">Fecha Fin</label>
                    <input type="date" id="fechaFin" class="input-field" value="<?= date('Y-m-t') ?>">
                </div>
            </div>

            <button onclick="generarReporte('calendario')" class="btn-primary w-full !bg-purple-500 hover:!bg-purple-600 flex items-center justify-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i> Generar PDF
            </button>
        </div>

    </div>
</main>

<script>
lucide.createIcons();
const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;

function generarReporte(tipo) {
    let url = 'api/reports.php?tipo=' + tipo;
    
    if (tipo === 'entregables') {
        const mes = document.getElementById('mesReporte').value;
        if (!mes) return alert("Selecciona un mes primero.");
        url += '&mes=' + mes;
        
        if (isAdmin) {
            const area = document.getElementById('areaReporte').value;
            const user = document.getElementById('usuarioReporte').value;
            if(area) url += '&area_id=' + area;
            if(user) url += '&user_id=' + user;
        }
    } else if (tipo === 'calendario') {
        const inicio = document.getElementById('fechaInicio').value;
        const fin = document.getElementById('fechaFin').value;
        if (!inicio || !fin) return alert("Selecciona fecha de inicio y fin.");
        url += '&inicio=' + inicio + '&fin=' + fin;
    }
    
    // Abre el PDF en una nueva pestaña
    window.open(url, '_blank');
}
</script>
</body>
</html>