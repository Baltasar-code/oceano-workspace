<?php
// includes/sidebar.php
$stmt_unread = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND leido = 0");
$stmt_unread->execute([$_SESSION['user_id']]);
$unread = (int) $stmt_unread->fetchColumn();

$stmt_foto = $pdo->prepare("SELECT foto_perfil, nombre FROM users WHERE id = ?");
$stmt_foto->execute([$_SESSION['user_id']]);
$userData = $stmt_foto->fetch();
$fotoUrl  = !empty($userData->foto_perfil) ? BASE_URL . $userData->foto_perfil : null;
$initials = strtoupper(substr($_SESSION['user_nombre'], 0, 2));
?>
<aside class="sidebar w-64 flex-shrink-0 flex flex-col h-full">

    <div class="p-6 border-b border-white/5">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center shadow-lg shadow-blue-500/30">
                <i data-lucide="waves" class="w-5 h-5 text-white"></i>
            </div>
            <div>
                <h1 class="text-white font-bold text-lg leading-none">Oceano</h1>
                <p class="text-slate-500 text-xs">Workspace</p>
            </div>
        </div>
    </div>

    <div class="px-4 py-4 border-b border-white/5">
        <a href="<?= BASE_URL ?>perfil.php" class="flex items-center gap-3 bg-white/5 rounded-xl p-3 hover:bg-white/10 transition-all">
            <?php if ($fotoUrl): ?>
                <img src="<?= htmlspecialchars($fotoUrl) ?>" alt="Foto"
                     class="w-9 h-9 rounded-full object-cover flex-shrink-0 ring-2 ring-blue-500/40">
            <?php else: ?>
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    <?= $initials ?>
                </div>
            <?php endif; ?>
            <div class="min-w-0">
                <p class="text-white text-sm font-semibold truncate"><?= htmlspecialchars($_SESSION['user_nombre']) ?></p>
                <p class="text-slate-400 text-xs"><?= $_SESSION['user_rol'] ?> · <span class="text-blue-400">Ver perfil</span></p>
            </div>
        </a>
    </div>

    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
        <p class="text-slate-600 text-xs font-semibold uppercase tracking-wider px-3 mb-3">Principal</p>

        <a href="<?= BASE_URL ?>dashboard.php"
           class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
            <span class="text-sm font-medium">Dashboard</span>
        </a>

        <a href="<?= BASE_URL ?>chat.php"
           class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 <?= ($currentPage ?? '') === 'chat' ? 'active' : '' ?>">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            <span class="text-sm font-medium flex-1">Mensajes</span>
            <?php if ($unread > 0): ?>
                <span id="globalBadge" class="badge-pulse bg-blue-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $unread ?></span>
            <?php else: ?>
                <span id="globalBadge" class="hidden bg-blue-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"></span>
            <?php endif; ?>
        </a>

        <a href="<?= BASE_URL ?>entregables.php"
           class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 <?= ($currentPage ?? '') === 'entregables' ? 'active' : '' ?>">
            <i data-lucide="folder-open" class="w-4 h-4"></i>
            <span class="text-sm font-medium">Entregables</span>
        </a>

        <a href="<?= BASE_URL ?>calendario.php"
           class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 <?= ($currentPage ?? '') === 'calendario' ? 'active' : '' ?>">
            <i data-lucide="calendar" class="w-4 h-4"></i>
            <span class="text-sm font-medium">Calendario</span>
        </a>

        <a href="<?= BASE_URL ?>informes.php"
           class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 <?= ($currentPage ?? '') === 'informes' ? 'active' : '' ?>">
            <i data-lucide="file-bar-chart" class="w-4 h-4"></i>
            <span class="text-sm font-medium">Informes PDF</span>
        </a>

        <a href="<?= BASE_URL ?>perfil.php"
           class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 <?= ($currentPage ?? '') === 'perfil' ? 'active' : '' ?>">
            <i data-lucide="user-circle" class="w-4 h-4"></i>
            <span class="text-sm font-medium">Mi Perfil</span>
        </a>

        <?php if (isAdmin()): ?>
        <div class="pt-4">
            <p class="text-slate-600 text-xs font-semibold uppercase tracking-wider px-3 mb-3">Administración</p>

            <a href="<?= BASE_URL ?>admin/usuarios.php"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 <?= ($currentPage ?? '') === 'usuarios' ? 'active' : '' ?>">
                <i data-lucide="users" class="w-4 h-4"></i>
                <span class="text-sm font-medium">Usuarios</span>
            </a>

            <a href="<?= BASE_URL ?>admin/areas.php"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 <?= ($currentPage ?? '') === 'areas' ? 'active' : '' ?>">
                <i data-lucide="building-2" class="w-4 h-4"></i>
                <span class="text-sm font-medium">Áreas</span>
            </a>
        </div>
        <?php endif; ?>
    </nav>

    <div class="p-4 border-t border-white/5">
        <a href="<?= BASE_URL ?>logout.php"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-500 hover:text-red-400 hover:bg-red-400/10 transition-all">
            <i data-lucide="log-out" class="w-4 h-4"></i>
            <span class="text-sm font-medium">Cerrar Sesión</span>
        </a>
    </div>
</aside>
