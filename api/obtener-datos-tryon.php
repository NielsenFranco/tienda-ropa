<?php
// api/obtener-datos-tryon.php

// LIMPIAR CUALQUIER OUTPUT ANTES DE EMPEZAR
if (ob_get_length()) ob_clean();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Evitar cualquier output accidental
error_reporting(0);
ini_set('display_errors', 0);

require_once '../includes/config.php';

try {
    $prendaId = $_GET['prenda_id'] ?? null;
    $usuarioId = $_GET['usuario_id'] ?? null;
    
    if (!$prendaId || !$usuarioId) {
        throw new Exception('Datos incompletos: se requieren prenda_id y usuario_id');
    }

    // Conexión a la base de datos
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Obtener datos del USUARIO
    $stmt = $pdo->prepare("SELECT id, foto_perfil FROM usuarios WHERE id = ?");
    $stmt->execute([$usuarioId]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        throw new Exception('Usuario no encontrado');
    }

    // 2. Obtener datos de la PRENDA
    $stmt = $pdo->prepare("SELECT id, titulo, imagen, categoria FROM prendas WHERE id = ?");
    $stmt->execute([$prendaId]);
    $prenda = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$prenda) {
        throw new Exception('Prenda no encontrada');
    }

    // 3. Categoría ya está en formato correcto
    $categoriaTryon = $prenda['categoria'];

    // 4. Construir URLs completas
    $imagenUsuario = UPLOADS_URL_PERFILES . $usuario['foto_perfil'];
    $imagenPrenda = UPLOADS_URL_PRENDAS . $prenda['imagen'];

    // 5. Verificar que las imágenes existen
    if (!file_exists(UPLOAD_DIR_PERFILES . $usuario['foto_perfil'])) {
        throw new Exception('La imagen de perfil del usuario no existe: ' . $usuario['foto_perfil']);
    }
    
    if (!file_exists(UPLOAD_DIR_PRENDAS . $prenda['imagen'])) {
        throw new Exception('La imagen de la prenda no existe: ' . $prenda['imagen']);
    }

    // 6. Preparar respuesta SIN debug_info para hacerla más simple
    $response = [
        'success' => true,
        'person_image_url' => $imagenUsuario,
        'garment_image_url' => $imagenPrenda,
        'garment_category' => $categoriaTryon
    ];

    // LIMPIAR CUALQUIER OUTPUT ANTES DE ENVIAR JSON
    if (ob_get_length()) ob_clean();
    
    echo json_encode([
    'success' => true,
    'person_image_url' => $imagenUsuario,
    'garment_image_url' => $imagenPrenda,
    'garment_category' => $categoriaTryon // ← Esto es importante
    ]);
    exit;

} catch (Exception $e) {
    // LIMPIAR CUALQUIER OUTPUT ANTES DEL ERROR
    if (ob_get_length()) ob_clean();
    
    error_log("Error en obtener-datos-tryon: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
    exit;
}
?>