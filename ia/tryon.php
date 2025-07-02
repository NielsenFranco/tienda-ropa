<?php
require_once __DIR__ . '/../includes/config.php';


header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido', 405);
    }

    if (!isset($_FILES['image_garment']) || !isset($_FILES['image_model'])) {
        throw new Exception('Faltan imágenes', 400);
    }

    // Preparar archivos temporales
    $garmentTmpPath = $_FILES['image_garment']['tmp_name'];
    $modelTmpPath = $_FILES['image_model']['tmp_name'];

    // Validación opcional
    if (!file_exists($garmentTmpPath) || !file_exists($modelTmpPath)) {
        throw new Exception("Archivo no encontrado", 400);
    }

    // Preparar CURL
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "http://127.0.0.1:8081/tryon/v1",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["accept: application/json"],
        CURLOPT_POSTFIELDS => [
            "image_garment_file" => new CURLFile($garmentTmpPath, $_FILES['image_garment']['type'], $_FILES['image_garment']['name']),
            "image_model_file" => new CURLFile($modelTmpPath, $_FILES['image_model']['type'], $_FILES['image_model']['name']),
            "garment_type" => "upper", // O según tipo
            "model_type" => "male",    // O según sexo usuario
        ]
    ]);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        throw new Exception("Error CURL: $error");
    } elseif ($http_code !== 200) {
        throw new Exception("Error HTTP $http_code: $response", $http_code);
    }

    $json = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error al decodificar JSON: $response");
    }

    echo json_encode([
        "success" => true,
        "job_id" => $json["job_id"],
        "status" => $json["status"]
    ]);

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
