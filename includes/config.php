<?php
// Configuración básica
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tienda_ropa');
define('BASE_URL', 'http://localhost/tienda-ropa');

// Configuración para subida de archivos
define('UPLOAD_DIR_PERFILES', __DIR__ . '/../assets/uploads/perfiles/');
define('UPLOADS_URL_PERFILES', BASE_URL . '/assets/uploads/perfiles/');
define('UPLOAD_DIR_PRENDAS', __DIR__ . '/../assets/uploads/prendas/');
define('UPLOADS_URL_PRENDAS', BASE_URL . '/assets/uploads/prendas/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Iniciar sesión
session_start();
