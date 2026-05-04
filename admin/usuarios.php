<?php
require_once '../includes/header.php';
requireAdmin();
?>
<?php require_once '../includes/sidebar.php'; ?>
<main class="main-content flex-1 overflow-y-auto p-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-slate-800 text-2xl font-bold">Gestión de Usuarios</h2>
            <p class="text-slate-400 text-sm mt-1">Crea y administra las cuentas corporativas.</p>
        </div>
        <button onclick="openModal()" class="btn-primary flex items-center gap-2">
            <i data-lucide="user-plus" class="w-4 h-4"></i> Nuevo Usuario
        </button>
    </div>
    <div class="card overflow-hidden">
        <table class="table-oceano">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Área</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="userTableBody"></tbody>
        </table>
    </div>
</main>
<div id="modalOverlay" class="modal-overlay" onclick="closeModal(event)">
    <div class="modal-box" onclick="event.stopPropagation()">
        <h3 class="font-bold text-slate-800 text-lg mb-6">Nuevo Usuario</h3>
        <form id="formUser" class="space-y-4">
            <input type="text" name="nombre" class="input-field" placeholder="Nombre completo" required>
            <input type="email" name="email" class="input-field" placeholder="correo@empresa.com" required>
            <input type="password" name="password" class="input-field" placeholder="Contraseña temporal" required>
            <select name="rol" class="input-field">
                <option value="Standard">Standard</option>
                <option value="Admin">Administrador</option>
            </select>
            <select name="area_id" id="areaSelect" class="input-field"></select>
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeModal()" class="btn-secondary flex-1">Cancelar</button>
                <button type="submit" class="btn-primary flex-1">Crear Cuenta</button>
            </div>
        </form>
    </div>
</div>
<script>
lucide.createIcons();
async function loadData() {
    const resA = await fetch('../api/admin.php?action=list_areas');
    const areas = await resA.json();
    document.getElementById('areaSelect').innerHTML = areas.map(a => `<option value="${a.id}">${a.nombre}</option>`).join('');
    const resU = await fetch('../api/admin.php?action=list_users');
    const users = await resU.json();
    document.getElementById('userTableBody').innerHTML = users.map(u => `
        <tr>
            <td class="font-bold text-slate-800">${u.nombre}</td>
            <td class="text-slate-600">${u.email}</td>
            <td><span class="px-2 py-1 rounded-full text-xs font-bold ${u.rol==='Admin'?'bg-purple-100 text-purple-600':'bg-slate-100 text-slate-600'}">${u.rol}</span></td>
            <td class="text-slate-600">${u.area_nombre || 'N/A'}</td>
            <td><button onclick="deleteUser(${u.id})" class="text-red-400 hover:text-red-600 transition-all"><i data-lucide="trash-2" class="w-4 h-4"></i></button></td>
        </tr>`).join('');
    lucide.createIcons();
}
document.getElementById('formUser').addEventListener('submit', async (e) => {
    e.preventDefault();
    await fetch('../api/admin.php', { method: 'POST', body: new FormData(e.target) });
    closeModal(); loadData();
});
async function deleteUser(id) {
    if(!confirm('¿Eliminar usuario?')) return;
    const fd = new FormData(); fd.append('action', 'delete_user'); fd.append('id', id);
    await fetch('../api/admin.php', { method: 'POST', body: fd }); loadData();
}
function openModal() { document.getElementById('modalOverlay').classList.add('open'); }
function closeModal() { document.getElementById('modalOverlay').classList.remove('open'); }
loadData();
</script>
</body>
</html>
