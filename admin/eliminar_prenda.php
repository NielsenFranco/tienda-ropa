<?php
require_once '../includes/config.php';
require_once '../includes/funciones.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: panel_admin.php?error=ID inválido');
    exit();
}

$id = intval($_GET['id']);
$funciones = new Funciones();
$prenda = $funciones->obtenerPrendaPorId($id);

if (!$prenda) {
    header('Location: panel_admin.php?error=Prenda no encontrada');
    exit();
}

// Eliminar imagen asociada
$ruta = UPLOAD_DIR_PRENDAS . $prenda['imagen'];
if (file_exists($ruta)) {
    unlink($ruta);
}

// Eliminar de la base de datos
$db = $funciones->getDB();
$stmt = $db->prepare("DELETE FROM prendas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header('Location: panel_admin.php?success=Prenda eliminada');
exit();
?>
