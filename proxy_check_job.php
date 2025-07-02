<?php
require_once 'includes/config.php';
header('Content-Type: application/json');
if (!isset($_GET['job_id'])) { http_response_code(400); echo json_encode(['error'=>'job_id faltante']); exit; }
$job = $_GET['job_id'];
$apiKey = 'TU_API_KEY_DE_HUHU';
$ch = curl_init("https://api-service.huhu.ai/requests/v1?job_id={$job}");
curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch, CURLOPT_HTTPHEADER,['x-api-key: '.$apiKey]);
$res = curl_exec($ch);
$code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
curl_close($ch);
http_response_code($code);
echo $res;
