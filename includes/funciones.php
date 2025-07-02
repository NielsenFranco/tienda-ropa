<?php
require_once 'database.php';

class Funciones {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function registrarUsuario($username, $email, $password, $foto_perfil, $rol = 'cliente') {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO usuarios (username, email, password, foto_perfil, rol) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $hashed_password, $foto_perfil, $rol);
        return $stmt->execute();
    }

    public function verificarLogin($email, $password) {
        $stmt = $this->db->prepare("SELECT id, username, password, rol, foto_perfil FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }

    public function obtenerUsuarioPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }

    public function subirImagen($file, $directory) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_type = $file['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            return ['error' => 'Tipo de archivo no permitido. Solo JPG, JPEG y PNG.'];
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            return ['error' => 'El archivo es demasiado grande. Máximo 5MB.'];
        }
        
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_name = uniqid() . '.' . $file_ext;
        $file_path = $directory . $file_name;
        
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            return ['success' => $file_name];
        } else {
            return ['error' => 'Error al subir el archivo.'];
        }
    }

    public function crearPrenda($titulo, $descripcion, $imagen, $id_usuario, $tipo_prenda) {
        $stmt = $this->db->prepare("INSERT INTO prendas (titulo, descripcion, imagen, id_usuario, tipo_prenda) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiss", $titulo, $descripcion, $imagen, $id_usuario, $tipo_prenda);
        return $stmt->execute();
    }

    public function obtenerPrendas() {
        $result = $this->db->query("SELECT * FROM prendas ORDER BY fecha_creacion DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerPrendaPorId($id) {
        $stmt = $this->db->prepare("SELECT * FROM prendas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // ✅ Subir imagen temporalmente a imgbb usando CURL (soluciona errores SSL)
    public function subirImagenTemporalAInternet($ruta_local) {
        $api_key = '73fb389eb94aec2ab38d9a56d81f9d5f'; // Tu API Key de imgbb

        if (!file_exists($ruta_local)) {
            return ['error' => 'Archivo no encontrado: ' . $ruta_local];
        }

        $image_data = base64_encode(file_get_contents($ruta_local));

        $postFields = http_build_query([
            'key' => $api_key,
            'image' => $image_data
        ]);

        $ch = curl_init('https://api.imgbb.com/1/upload');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['error' => 'cURL error: ' . $error];
        }

        $result = json_decode($response, true);

        if (isset($result['data']['url'])) {
            return ['success' => $result['data']['url']];
        } else {
            return ['error' => 'Error al subir imagen a imgbb', 'debug' => $result];
        }
    }



    // ✅ Verifica si un usuario está logueado
    public function estaLogueado() {
        return isset($_SESSION['usuario']);
    }

    public function getDB() {
        return $this->db;
    }
}
?>
