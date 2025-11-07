<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$api_key = "h0HR3RPipg2i6p7zbIQmNai7XQI36c9N3LmogIwl";

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['personImage']) || !isset($input['garmentImage']) || !isset($input['garmentCategory'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit();
}

// USAR URLs PÚBLICAS EN LUGAR DE ARCHIVOS LOCALES
function convertToPublicUrl($local_url) {
    // Convertir URL local a pública
    if (strpos($local_url, 'localhost') !== false) {
        // Para desarrollo, podrías necesitar un servicio como ngrok
        // o subir las imágenes a un servidor público
        return $local_url; // HUHU.ai no puede acceder a localhost
    }
    return $local_url;
}

function mapCategoryToHUHU($category) {
    $mapping = [
        'fullbody' => 'Top', // Probar con Top primero
        'upperbody' => 'Top',
        'lowerbody' => 'Bottom', 
        'dress' => 'Top'
    ];
    return $mapping[$category] ?? 'Top';
}

$person_image_url = convertToPublicUrl($input['personImage']);
$garment_image_url = convertToPublicUrl($input['garmentImage']);
$huhu_category = mapCategoryToHUHU($input['garmentCategory']);

error_log("🎯 Enviando a HUHU.ai con URLs");
error_log("👤 URL Persona: $person_image_url");
error_log("👕 URL Prenda: $garment_image_url");
error_log("📦 Categoría: $huhu_category");

// Preparar payload JSON con URLs
$payload = [
    'image_garment_url' => $garment_image_url,
    'image_model_url' => $person_image_url,
    'garment_type' => $huhu_category
];

$huhu_api_url = "https://api-service.huhu.ai/tryon/v1";

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $huhu_api_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'x-api-key: ' . $api_key,
    ],
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

error_log("📡 Respuesta HUHU.ai - Status: $http_code");
error_log("📄 Respuesta: " . $response);

$response_data = json_decode($response, true);

if ($http_code === 200 && isset($response_data['job_id'])) {
    error_log("✅ Job creado exitosamente");
    
    $job_id = $response_data['job_id'];
    $result = waitForJobResult($job_id, $api_key);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'image' => $result['image'],
            'message' => 'Virtual Try-On completado exitosamente'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => $result['error']]);
    }
    
} else {
    $error_msg = $response_data['detail'] ?? $response_data['error'] ?? 'Error desconocido';
    echo json_encode([
        'success' => false, 
        'error' => "Error HUHU.ai ($http_code): $error_msg"
    ]);
}

function waitForJobResult($job_id, $api_key, $max_attempts = 40, $delay = 3) {
    // ... (la misma función de arriba)
}
?>