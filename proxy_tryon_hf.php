<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// TU API KEY DE HUHU.AI
$api_key = "h0HR3RPipg2i6p7zbIQmNai7XQI36c9N3LmogIwl";

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['personImage']) || !isset($input['garmentImage']) || !isset($input['garmentCategory'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
    exit();
}

// FUNCIÓN PARA CARGAR IMAGEN
function getImageFromLocalPath($image_url, $type) {
    $base_path = 'C:/xampp/htdocs/tienda-ropa/';
    
    if (preg_match('/tienda-ropa\/(.*)$/', $image_url, $matches)) {
        $relative_path = $matches[1];
        $local_path = $base_path . str_replace('/', '\\', $relative_path);
        
        if (!file_exists($local_path)) {
            error_log("❌ Archivo no existe: $local_path");
            return null;
        }
        
        // Verificar que sea una imagen válida
        $image_info = @getimagesize($local_path);
        if (!$image_info) {
            error_log("❌ No es una imagen válida: $local_path");
            return null;
        }
        
        error_log("✅ Imagen $type: $local_path - " . $image_info[0] . "x" . $image_info[1] . " - " . $image_info['mime']);
        return $local_path;
    }
    
    return null;
}

// MAPEAR CATEGORÍAS - VERSIÓN COMPLETA
function mapCategoryToHUHU($category) {
    $mapping = [
        // Categorías principales
        'fullbody' => 'Fullbody',
        'upperbody' => 'Top',
        'lowerbody' => 'Bottom',
        
        // Variaciones comunes
        'top' => 'Top',
        'bottom' => 'Bottom',
        'dress' => 'Fullbody',
        'vestido' => 'Fullbody',
        'short' => 'Bottom',
        'pantalon' => 'Bottom',
        'remera' => 'Top',
        'camiseta' => 'Top',
        'camisa' => 'Top'
    ];
    
    $category_lower = strtolower(trim($category));
    $huhu_category = $mapping[$category_lower] ?? 'Top';
    
    // DEBUG detallado
    error_log("🎯 MAPEO CATEGORÍA:");
    error_log("   - Recibida: '$category'");
    error_log("   - Normalizada: '$category_lower'");
    error_log("   - Mapeada: '$huhu_category'");
    
    return $huhu_category;
}

// Obtener rutas de imágenes
$person_image_path = getImageFromLocalPath($input['personImage'], 'persona');
$garment_image_path = getImageFromLocalPath($input['garmentImage'], 'prenda');
$huhu_category = mapCategoryToHUHU($input['garmentCategory']);

if (!$person_image_path || !$garment_image_path) {
    echo json_encode(['success' => false, 'error' => 'Error cargando imágenes locales']);
    exit();
}

error_log("🎯 Enviando a HUHU.ai - Categoría: $huhu_category");
error_log("📍 Ruta persona: $person_image_path");
error_log("📍 Ruta prenda: $garment_image_path");

// Verificar tamaños de archivo
$person_size = filesize($person_image_path);
$garment_size = filesize($garment_image_path);
error_log("📏 Tamaños - Persona: " . round($person_size/1024, 2) . "KB, Prenda: " . round($garment_size/1024, 2) . "KB");

// ENDPOINT CORRECTO DE HUHU.AI
$huhu_api_url = "https://api-service.huhu.ai/tryon/v1";

// Preparar multipart/form-data
$payload = [
    'garment_type' => $huhu_category,
    'image_garment_file' => new CURLFile($garment_image_path, 'image/jpeg', 'garment.jpg'),
    'image_model_file' => new CURLFile($person_image_path, 'image/jpeg', 'model.jpg')
];

// Configurar cURL
$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $huhu_api_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        'x-api-key: ' . $api_key,
    ],
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

error_log("📡 Respuesta HUHU.ai - Status: $http_code");

if ($curl_error) {
    error_log("❌ Error cURL: $curl_error");
    echo json_encode(['success' => false, 'error' => 'Error de conexión: ' . $curl_error]);
    exit();
}

error_log("📄 Respuesta HUHU.ai: " . $response);

$response_data = json_decode($response, true);

if ($http_code === 200 && isset($response_data['job_id'])) {
    error_log("✅ Job creado exitosamente - Job ID: " . $response_data['job_id']);
    
    // Esperar y consultar el resultado del job
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
    $error_msg = $response_data['detail'] ?? $response_data['error'] ?? $response_data['message'] ?? 'Error desconocido';
    error_log("❌ Error HUHU.ai: $error_msg");
    
    echo json_encode([
        'success' => false, 
        'error' => "Error HUHU.ai ($http_code): $error_msg",
        'debug' => $response_data
    ]);
}

// FUNCIÓN MEJORADA PARA OBTENER DETALLES DEL ERROR
function waitForJobResult($job_id, $api_key, $max_attempts = 40, $delay = 3) {
    error_log("⏳ Esperando resultado del job: $job_id");
    
    $endpoint = "https://api-service.huhu.ai/requests/v1";
    
    for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
        sleep($delay);
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint . "?job_id=" . urlencode($job_id),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'x-api-key: ' . $api_key,
            ],
            CURLOPT_TIMEOUT => 30,
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $job_data = json_decode($response, true);
            $status = $job_data['status'] ?? 'unknown';
            
            error_log("📊 Job $job_id - Intento $attempt: $status");
            
            // OBTENER MÁS DETALLES DEL ERROR
            if ($status === 'failed') {
                $error_msg = $job_data['error_message'] ?? 'Job falló sin mensaje específico';
                $error_details = $job_data['error_details'] ?? $job_data['error'] ?? 'Sin detalles';
                
                error_log("❌ Job falló - Mensaje: $error_msg");
                error_log("❌ Detalles del error: " . print_r($error_details, true));
                error_log("❌ Respuesta completa: " . print_r($job_data, true));
                
                // Mensajes específicos basados en patrones comunes
                if (strpos($error_msg, 'human') !== false || strpos($error_msg, 'person') !== false) {
                    $user_error = "Problema con la imagen de la persona: " . $error_msg;
                } elseif (strpos($error_msg, 'garment') !== false || strpos($error_msg, 'cloth') !== false) {
                    $user_error = "Problema con la imagen de la prenda: " . $error_msg;
                } elseif (strpos($error_msg, 'category') !== false || strpos($error_msg, 'type') !== false) {
                    $user_error = "Problema con la categoría de la prenda: " . $error_msg;
                } else {
                    $user_error = "Error en el procesamiento: " . $error_msg;
                }
                
                return ['success' => false, 'error' => $user_error];
            }
            
            if ($status === 'completed' && isset($job_data['output']) && is_array($job_data['output'])) {
                foreach ($job_data['output'] as $output_item) {
                    if (isset($output_item['image_url'])) {
                        $image_url = $output_item['image_url'];
                        error_log("🎉 Imagen resultante encontrada: $image_url");
                        
                        $image_data = file_get_contents($image_url);
                        if ($image_data !== false) {
                            return [
                                'success' => true,
                                'image' => base64_encode($image_data)
                            ];
                        }
                    }
                }
            }
            
            // Mostrar progreso
            if ($attempt % 5 === 0) {
                error_log("⏰ Esperando... ($attempt/$max_attempts) - Status: $status");
            }
            
        } else {
            error_log("❌ Error consultando job: HTTP $http_code");
        }
    }
    
    return ['success' => false, 'error' => 'Tiempo de espera agotado. El proceso está tomando más de lo esperado.'];
}
?>