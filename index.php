<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Oceano — Acceso</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>body { background: #0f172a; min-height: 100vh; font-family: 'Inter', sans-serif; }</style>
</head>
<body class="flex items-center justify-center p-4">
    <div class="w-full max-w-sm bg-slate-800/50 p-8 rounded-2xl border border-slate-700 shadow-2xl">
        <h1 class="text-white text-3xl font-bold text-center mb-8">Oceano</h1>
        <form id="loginForm" class="space-y-6">
            <input type="hidden" name="action" value="login">
            <input type="email" name="email" placeholder="admin@oceano.com" class="w-full p-3 rounded-lg bg-slate-900/50 border border-slate-600 text-white outline-none focus:ring-2 focus:ring-blue-500">
            <input type="password" name="password" placeholder="admin123" class="w-full p-3 rounded-lg bg-slate-900/50 border border-slate-600 text-white outline-none focus:ring-2 focus:ring-blue-500">
            <div id="errorMsg" class="hidden text-red-400 text-sm"></div>
            <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-lg font-bold hover:bg-blue-700 transition-all">Ingresar</button>
        </form>
    </div>
    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const res = await fetch('api/auth.php', { method:'POST', body:new FormData(e.target) });
            const data = await res.json();
            if (data.status === 'success') window.location.href = 'dashboard.php';
            else document.getElementById('errorMsg').textContent = data.message;
        });
    </script>
</body>
</html>
