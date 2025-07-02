<?php
require_once 'includes/config.php';
require_once 'includes/funciones.php';

header('Content-Type: application/json');

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Validar sesión y datos
if (!isset($_POST['id_prenda'], $_SESSION['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos']);
    exit;
}

$id_prenda = intval($_POST['id_prenda']);
$id_usuario = intval($_SESSION['user_id']);
$tipo_prenda = $_POST['categoria'] ?? 'Top';

$funciones = new Funciones();
$prenda = $funciones->obtenerPrendaPorId($id_prenda);
$usuario = $funciones->obtenerUsuarioPorId($id_usuario);

if (!$prenda || !$usuario || empty($prenda['imagen']) || empty($usuario['foto_perfil'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos de imagen incompletos']);
    exit;
}

// Rutas locales
$ruta_prenda = realpath(UPLOAD_DIR_PRENDAS . $prenda['imagen']);
$ruta_usuario = realpath(UPLOAD_DIR_PERFILES . $usuario['foto_perfil']);

if (!file_exists($ruta_prenda) || !file_exists($ruta_usuario)) {
    http_response_code(404);
    echo json_encode(['error' => 'Imagen no encontrada']);
    exit;
}

// Enviar a IA local
$ch = curl_init('http://127.0.0.1:5000/predict');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
    'image_model_file' => new CURLFile($ruta_usuario),
    'image_garment_file' => new CURLFile($ruta_prenda),
    'garment_type' => $tipo_prenda
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300 && $response) {
    echo $response;
} else {
    http_response_code($httpCode ?: 500);
    echo json_encode(['error' => 'Error al contactar la IA local', 'debug' => $response]);
}
