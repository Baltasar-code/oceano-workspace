<?php
$pageTitle   = 'Entregables Mensuales';
$currentPage = 'entregables';
require_once 'includes/header.php';
require_once 'includes/auth_check.php';

// Obtener datos para los filtros si es admin
$areas = [];
$usuarios = [];
if (isAdmin()) {
    $areas = $pdo->query("SELECT id, nombre FROM areas ORDER BY nombre")->fetchAll();
    $usuarios = $pdo->query("SELECT id, nombre FROM users ORDER BY nombre")->fetchAll();
}
?>
<?php require_once 'includes/sidebar.php'; ?>

<main class="main-content flex-1 overflow-y-auto p-8">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-slate-800 text-2xl font-bold">Entregables Mensuales</h2>
            <p class="text-slate-400 text-sm mt-1">Registra tus archivos y links de trabajo por mes.</p>
        </div>
        <button onclick="openModal()" class="btn-primary flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i> Nuevo Entregable
        </button>
    </div>

    <!-- Filtros -->
    <div class="card mb-6 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2">
            <label class="text-slate-600 text-sm font-semibold">Mes:</label>
            <input type="month" id="filtroMes" value="<?= date('Y-m') ?>" class="input-field w-40" onchange="loadEntregables()">
        </div>

        <?php if (isAdmin()): ?>
        <div class="flex items-center gap-2">
            <label class="text-slate-600 text-sm font-semibold">Área:</label>
            <select id="filtroArea" class="input-field w-40" onchange="loadEntregables()">
                <option value="">Todas</option>
                <?php foreach($areas as $a): ?>
                    <option value="<?= $a->id ?>"><?= htmlspecialchars($a->nombre) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <label class="text-slate-600 text-sm font-semibold">Usuario:</label>
            <select id="filtroUsuario" class="input-field w-48" onchange="loadEntregables()">
                <option value="">Todos</option>
                <?php foreach($usuarios as $u): ?>
                    <option value="<?= $u->id ?>"><?= htmlspecialchars($u->nombre) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <span id="totalLabel" class="text-slate-400 text-sm ml-auto"></span>
    </div>

    <!-- Tabla de entregables -->
    <div class="card">
        <div id="loadingMsg" class="text-center py-8 text-slate-400 text-sm">Cargando...</div>
        <table class="table-oceano hidden" id="tablaEntregables">
            <thead>
                <tr>
                    <th>Archivo</th>
                    <th>Descripción</th>
                    <?php if (isAdmin()): ?>
                    <th>Autor / Área</th>
                    <?php endif; ?>
                    <th>Mes</th>
                    <th>Fecha Subida</th>
                    <th>Link</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="tbodyEntregables"></tbody>
        </table>
        <div id="emptyMsg" class="hidden text-center py-10 text-slate-400">
            <i data-lucide="folder-open" class="w-10 h-10 mx-auto mb-2 opacity-50"></i>
            <p class="text-sm">No hay entregables para este filtro.</p>
        </div>
    </div>
</main>

<!-- Modal Entregable -->
<div id="modalOverlay" class="modal-overlay" onclick="closeModal(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-bold text-slate-800 text-lg" id="modalTitle">Nuevo Entregable</h3>
            <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition-all">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="formEntregable" class="space-y-4">
            <input type="hidden" name="id" id="entregableId" value="">
            <input type="hidden" name="action" id="formAction" value="create">

            <div>
                <label class="block text-slate-600 text-xs font-semibold uppercase tracking-wider mb-1.5">Mes</label>
                <input type="month" name="mes_anio" id="inputMes" class="input-field" value="<?= date('Y-m') ?>" required>
            </div>
            <div>
                <label class="block text-slate-600 text-xs font-semibold uppercase tracking-wider mb-1.5">Nombre del Archivo</label>
                <input type="text" name="nombre_archivo" id="inputArchivo" class="input-field" placeholder="Informe_Abril_2024.pdf" required>
            </div>
            <div>
                <label class="block text-slate-600 text-xs font-semibold uppercase tracking-wider mb-1.5">Link de Google Drive <span class="text-red-400">*</span></label>
                <input type="url" name="drive_link" id="inputLink" class="input-field" placeholder="https://drive.google.com/..." required>
            </div>
            <div>
                <label class="block text-slate-600 text-xs font-semibold uppercase tracking-wider mb-1.5">Descripción</label>
                <textarea name="descripcion" id="inputDesc" rows="3" class="input-field resize-none" placeholder="Descripción breve del contenido..."></textarea>
            </div>

            <div id="modalError" class="hidden alert alert-error"></div>
            <div id="modalSuccess" class="hidden alert alert-success"></div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal()" class="btn-secondary flex-1">Cancelar</button>
                <button type="submit" id="btnGuardar" class="btn-primary flex-1">Guardar Entregable</button>
            </div>
        </form>
    </div>
</div>

<script>
lucide.createIcons();

const isAdmin = <?= isAdmin() ? 'true' : 'false' ?>;
let currentData = [];

async function loadEntregables() {
    const mes = document.getElementById('filtroMes').value;
    let url = `api/entregables.php?action=list&mes=${mes}`;

    if (isAdmin) {
        const area = document.getElementById('filtroArea').value;
        const user = document.getElementById('filtroUsuario').value;
        if(area) url += `&area_id=${area}`;
        if(user) url += `&user_id=${user}`;
    }

    document.getElementById('loadingMsg').classList.remove('hidden');
    document.getElementById('tablaEntregables').classList.add('hidden');
    document.getElementById('emptyMsg').classList.add('hidden');

    const res  = await fetch(url);
    const data = await res.json();
    currentData = data.data || [];

    document.getElementById('loadingMsg').classList.add('hidden');

    if (currentData.length === 0) {
        document.getElementById('emptyMsg').classList.remove('hidden');
        document.getElementById('totalLabel').textContent = '0 registros';
        return;
    }

    document.getElementById('totalLabel').textContent = `${currentData.length} registro(s)`;
    const tbody = document.getElementById('tbodyEntregables');
    tbody.innerHTML = '';

    currentData.forEach(e => {
        const tr = document.createElement('tr');
        
        let autorHtml = '';
        if (isAdmin) {
            autorHtml = `<td>
                <div class="font-bold text-slate-800">${e.usuario || 'Desconocido'}</div>
                <div class="text-xs text-slate-400">${e.area_nombre || 'Sin área'}</div>
            </td>`;
        }

        tr.innerHTML = `
            <td>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                        <i data-lucide="file-text" class="w-4 h-4 text-emerald-500"></i>
                    </div>
                    <span class="font-medium text-slate-700">${e.nombre_archivo || '—'}</span>
                </div>
            </td>
            <td class="text-slate-500 max-w-xs truncate" title="${e.descripcion || ''}">${e.descripcion || '—'}</td>
            ${autorHtml}
            <td><span class="bg-blue-50 text-blue-600 text-xs font-bold px-2 py-1 rounded-full">${e.mes_anio}</span></td>
            <td class="text-slate-400 text-xs">${new Date(e.fecha_subida).toLocaleDateString('es-VE')}</td>
            <td>
                <a href="${e.drive_link}" target="_blank"
                   class="text-blue-500 hover:text-blue-700 font-medium text-sm flex items-center gap-1 transition-all">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i> Abrir
                </a>
            </td>
            <td>
                <div class="flex items-center gap-1">
                    <button onclick="editEntregable(${e.id})"
                        class="text-amber-500 hover:text-amber-700 transition-all p-1.5 rounded-lg hover:bg-amber-50" title="Editar">
                        <i data-lucide="pencil" class="w-4 h-4"></i>
                    </button>
                    <button onclick="deleteEntregable(${e.id})"
                        class="text-red-400 hover:text-red-600 transition-all p-1.5 rounded-lg hover:bg-red-50" title="Eliminar">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </td>`;
        tbody.appendChild(tr);
    });

    document.getElementById('tablaEntregables').classList.remove('hidden');
    lucide.createIcons();
}

function editEntregable(id) {
    const entregable = currentData.find(e => e.id == id);
    if(!entregable) return;

    document.getElementById('modalTitle').textContent = 'Editar Entregable';
    document.getElementById('formAction').value = 'update';
    document.getElementById('entregableId').value = entregable.id;
    document.getElementById('inputMes').value = entregable.mes_anio;
    document.getElementById('inputArchivo').value = entregable.nombre_archivo;
    document.getElementById('inputLink').value = entregable.drive_link;
    document.getElementById('inputDesc').value = entregable.descripcion;
    
    document.getElementById('modalError').classList.add('hidden');
    document.getElementById('modalSuccess').classList.add('hidden');
    document.getElementById('modalOverlay').classList.add('open');
}

async function deleteEntregable(id) {
    if (!confirm('¿Eliminar este entregable permanentemente?')) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    const res  = await fetch('api/entregables.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.status === 'success') loadEntregables();
    else alert(data.message);
}

document.getElementById('formEntregable').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn  = document.getElementById('btnGuardar');
    const errD = document.getElementById('modalError');
    const okD  = document.getElementById('modalSuccess');
    btn.disabled = true;
    btn.textContent = 'Guardando...';
    errD.classList.add('hidden');
    okD.classList.add('hidden');

    const fd = new FormData(e.target);
    const res  = await fetch('api/entregables.php', { method:'POST', body:fd });
    const data = await res.json();

    if (data.status === 'success') {
        okD.textContent = data.message;
        okD.classList.remove('hidden');
        loadEntregables();
        setTimeout(closeModal, 1500);
    } else {
        errD.textContent = data.message;
        errD.classList.remove('hidden');
    }
    btn.disabled = false;
    btn.textContent = 'Guardar Entregable';
});

function openModal()  { 
    document.getElementById('formEntregable').reset();
    document.getElementById('formAction').value = 'create';
    document.getElementById('modalTitle').textContent = 'Nuevo Entregable';
    document.getElementById('modalError').classList.add('hidden');
    document.getElementById('modalSuccess').classList.add('hidden');
    document.getElementById('modalOverlay').classList.add('open'); 
}
function closeModal(e) {
    if (!e || e.target === document.getElementById('modalOverlay')) {
        document.getElementById('modalOverlay').classList.remove('open');
    }
}

loadEntregables();
</script>
</body>
</html>