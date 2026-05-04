<?php
$pageTitle   = 'Gestión de Áreas';
$currentPage = 'areas';
require_once '../includes/header.php';
requireAdmin();
?>
<?php require_once '../includes/sidebar.php'; ?>
<main class="main-content flex-1 overflow-y-auto p-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-slate-800 text-2xl font-bold">Gestión de Áreas</h2>
            <p class="text-slate-400 text-sm mt-1">Crea y administra los departamentos de la empresa.</p>
        </div>
        <button onclick="openModal()" class="btn-primary flex items-center gap-2">
            <i data-lucide="building-2" class="w-4 h-4"></i> Nueva Área
        </button>
    </div>
    <div class="card overflow-hidden max-w-3xl">
        <table class="table-oceano">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del Área</th>
                    <th class="w-24 text-center">Acción</th>
                </tr>
            </thead>
            <tbody id="areaTableBody"></tbody>
        </table>
    </div>
</main>
<div id="modalOverlay" class="modal-overlay" onclick="closeModal(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <h3 class="font-bold text-slate-800 text-lg mb-6">Crear Nueva Área</h3>
        <form id="formArea" class="space-y-4">
            <div>
                <label class="block text-slate-600 text-xs font-semibold mb-1.5 uppercase">Nombre del Área</label>
                <input type="text" name="nombre" id="inputNombre" class="input-field" placeholder="Ej. Finanzas" required>
            </div>
            <div id="modalError" class="hidden alert alert-error text-red-500 text-sm"></div>
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeModal()" class="btn-secondary flex-1">Cancelar</button>
                <button type="submit" class="btn-primary flex-1">Guardar Área</button>
            </div>
        </form>
    </div>
</div>
<script>
lucide.createIcons();
async function loadData() {
    try {
        const res = await fetch('../api/admin.php?action=list_areas');
        const areas = await res.json();
        const tbody = document.getElementById('areaTableBody');
        if (areas.length === 0) {
            tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4 text-slate-400">No hay áreas registradas.</td></tr>';
            return;
        }
        tbody.innerHTML = areas.map(a => `
            <tr>
                <td class="text-slate-400 font-mono text-sm">#${a.id}</td>
                <td class="font-bold text-slate-800">${a.nombre}</td>
                <td class="text-center">
                    <button onclick="deleteArea(${a.id})" class="text-red-400 hover:text-red-600 transition-all p-1.5 rounded-lg hover:bg-red-50">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </td>
            </tr>`).join('');
        lucide.createIcons();
    } catch (e) {
        console.error("Error loading areas:", e);
    }
}
document.getElementById('formArea').addEventListener('submit', async (e) => {
    e.preventDefault();
    const errorDiv = document.getElementById('modalError');
    errorDiv.classList.add('hidden');
    const fd = new FormData(e.target);
    fd.append('action', 'create_area');
    try {
        const res = await fetch('../api/admin.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.status === 'success') {
            e.target.reset(); closeModal(); loadData();
        } else {
            errorDiv.textContent = data.message || "Error al crear área"; errorDiv.classList.remove('hidden');
        }
    } catch (e) {
        errorDiv.textContent = "Error de red."; errorDiv.classList.remove('hidden');
    }
});
async function deleteArea(id) {
    if(!confirm('¿Estás seguro de eliminar esta área? Los usuarios asociados quedarán sin área asignada.')) return;
    const fd = new FormData(); fd.append('action', 'delete_area'); fd.append('id', id);
    await fetch('../api/admin.php', { method: 'POST', body: fd }); loadData();
}
function openModal() { 
    document.getElementById('formArea').reset();
    document.getElementById('modalError').classList.add('hidden');
    document.getElementById('modalOverlay').classList.add('open'); 
    setTimeout(() => document.getElementById('inputNombre').focus(), 100);
}
function closeModal(e) { 
    if (!e || e.target === document.getElementById('modalOverlay')) {
        document.getElementById('modalOverlay').classList.remove('open'); 
    }
}
loadData();
</script>
</body>
</html>