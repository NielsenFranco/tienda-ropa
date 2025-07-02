<?php
require_once '../includes/config.php';
require_once '../includes/funciones.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $titulo = $_POST['titulo'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $tipo = $_POST['tipo'] ?? '';

    if (!$id || !$titulo || !$descripcion || !$tipo) {
        header('Location: panel_admin.php?error=Faltan datos');
        exit();
    }

    $funciones = new Funciones();

    // Obtener prenda original
    $prenda = $funciones->obtenerPrendaPorId($id);
    if (!$prenda) {
        header('Location: panel_admin.php?error=Prenda no encontrada');
        exit();
    }

    // Subir imagen si se cambió
    $nombre_imagen = $prenda['imagen'];
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $subida = $funciones->subirImagen($_FILES['imagen'], UPLOAD_DIR_PRENDAS);
        if (isset($subida['success'])) {
            // Eliminar imagen anterior
            $ruta_anterior = UPLOAD_DIR_PRENDAS . $prenda['imagen'];
            if (file_exists($ruta_anterior)) {
                unlink($ruta_anterior);
            }
            $nombre_imagen = $subida['success'];
        }
    }

    // Actualizar datos en la base
    $db = $funciones->getDB(); // ✅ se usa correctamente la conexión
    $stmt = $db->prepare("UPDATE prendas SET titulo = ?, descripcion = ?, tipo_prenda = ?, imagen = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $titulo, $descripcion, $tipo, $nombre_imagen, $id);
    $stmt->execute();

    header('Location: panel_admin.php?success=Prenda actualizada');
    exit();
}
?>
