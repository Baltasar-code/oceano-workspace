<?php
$pageTitle   = 'Dashboard';
$currentPage = 'dashboard';
require_once 'includes/header.php';
require_once 'includes/auth_check.php';

$isAdmin = isAdmin();

if ($isAdmin) {
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $total_users = $stmt->fetchColumn();
}

$stmt = $pdo->query("SELECT COUNT(*) FROM events WHERE fecha >= NOW()");
$proximos_eventos = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM monthly_links WHERE user_id = ? AND mes_anio = DATE_FORMAT(NOW(),'%Y-%m')");
$stmt->execute([$_SESSION['user_id']]);
$mis_entregables = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT e.titulo, e.fecha FROM events e WHERE e.fecha >= NOW() ORDER BY e.fecha ASC LIMIT 4");
$eventos = $stmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT ml.descripcion, ml.nombre_archivo, ml.fecha_subida
    FROM monthly_links ml
    WHERE ml.user_id = ?
    ORDER BY ml.fecha_subida DESC LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recientes = $stmt->fetchAll();
?>

<?php require_once 'includes/sidebar.php'; ?>

<main class="main-content flex-1 overflow-y-auto p-8">

    <div class="mb-8">
        <h2 class="text-slate-800 text-2xl font-bold">Hola, <?= htmlspecialchars(explode(' ', $_SESSION['user_nombre'])[0]) ?> 👋</h2>
        <p class="text-slate-400 text-sm mt-1"><?= strftime('%A, %d de %B de %Y') ?? date('d/m/Y') ?></p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <?php if ($isAdmin): ?>
        <a href="admin/usuarios.php" class="card flex items-center gap-4 hover:border-blue-200 hover:shadow-md transition-all cursor-pointer">
            <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                <i data-lucide="users" class="w-6 h-6 text-blue-500"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800"><?= $total_users ?></p>
                <p class="text-slate-500 text-xs font-medium">Usuarios activos</p>
            </div>
        </a>
        <?php endif; ?>
        
        <a href="calendario.php" class="card flex items-center gap-4 hover:border-violet-200 hover:shadow-md transition-all cursor-pointer">
            <div class="w-12 h-12 rounded-xl bg-violet-50 flex items-center justify-center flex-shrink-0">
                <i data-lucide="calendar-check" class="w-6 h-6 text-violet-500"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800"><?= $proximos_eventos ?></p>
                <p class="text-slate-500 text-xs font-medium">Eventos próximos</p>
            </div>
        </a>
        
        <a href="entregables.php" class="card flex items-center gap-4 hover:border-emerald-200 hover:shadow-md transition-all cursor-pointer">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                <i data-lucide="folder-check" class="w-6 h-6 text-emerald-500"></i>
            </div>
            <div>
                <p class="text-2xl font-bold text-slate-800"><?= $mis_entregables ?></p>
                <p class="text-slate-500 text-xs font-medium">Mis entregables este mes</p>
            </div>
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Próximos Eventos -->
        <div class="card cursor-pointer hover:border-blue-200 transition-all" onclick="window.location.href='calendario.php'">
            <h3 class="font-bold text-slate-800 text-sm mb-4 flex items-center gap-2">
                <i data-lucide="calendar" class="w-4 h-4 text-blue-500"></i> Próximos Eventos
            </h3>
            <?php if (empty($eventos)): ?>
                <p class="text-slate-400 text-sm text-center py-6">Sin eventos próximos.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($eventos as $ev): ?>
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition-all">
                        <div class="w-11 h-11 rounded-xl bg-blue-500 flex flex-col items-center justify-center flex-shrink-0">
                            <span class="text-white font-bold text-base leading-none"><?= date('d', strtotime($ev->fecha)) ?></span>
                            <span class="text-blue-200 text-xs uppercase"><?= date('M', strtotime($ev->fecha)) ?></span>
                        </div>
                        <div>
                            <p class="text-slate-700 font-semibold text-sm"><?= htmlspecialchars($ev->titulo) ?></p>
                            <p class="text-slate-400 text-xs"><?= date('H:i', strtotime($ev->fecha)) ?> hrs</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mis Últimos Entregables -->
        <div class="card cursor-pointer hover:border-emerald-200 transition-all" onclick="window.location.href='entregables.php'">
            <h3 class="font-bold text-slate-800 text-sm mb-4 flex items-center gap-2">
                <i data-lucide="folder-open" class="w-4 h-4 text-emerald-500"></i> Mis Últimos Entregables
            </h3>
            <?php if (empty($recientes)): ?>
                <p class="text-slate-400 text-sm text-center py-6">No has subido entregables aún.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recientes as $r): ?>
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-slate-100 transition-all">
                        <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="file-text" class="w-4 h-4 text-emerald-500"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="text-slate-700 font-medium text-sm truncate"><?= htmlspecialchars($r->nombre_archivo) ?></p>
                            <p class="text-slate-400 text-xs"><?= date('d/m/Y', strtotime($r->fecha_subida)) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<script>lucide.createIcons();</script>
</body>
</html>