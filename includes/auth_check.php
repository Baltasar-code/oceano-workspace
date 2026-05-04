<?php
require_once __DIR__ . '/../config/config.php';
if (!isset($_SESSION['user_id'])) { header('Location: /oceano/index.php'); exit; }
function isAdmin() { return $_SESSION['user_rol'] === 'Admin'; }
function requireAdmin() { if (!isAdmin()) { header('Location: /oceano/dashboard.php'); exit; } }
