<?php
require_once 'includes/config.php';
require_once 'includes/funciones.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$funciones = new Funciones();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $tipo_prenda = $_POST['tipo_prenda']; // ✅ Capturar tipo de prenda
    $id_usuario = $_SESSION['user_id'];

    // Validar campos
    if ($titulo === '' || $descripcion === '' || empty($_FILES['imagen']['name'])) {
        $error = 'Completa todos los campos.';
    } else {
        // Subir imagen
        $upload_result = $funciones->subirImagen($_FILES['imagen'], 'assets/uploads/prendas/');
        if (isset($upload_result['success'])) {
            $imagen = $upload_result['success'];

            // Crear prenda en base de datos
            if ($funciones->crearPrenda($titulo, $descripcion, $imagen, $id_usuario, $tipo_prenda)) {
                $success = '✅ Prenda cargada exitosamente.';
            } else {
                $error = '❌ Error al guardar en la base de datos.';
            }
        } else {
            $error = $upload_result['error'];
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="form-container">
    <h1>Cargar nueva prenda</h1>

    <?php if ($success): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php elseif ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="titulo">Título:</label>
            <input type="text" id="titulo" name="titulo" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
        </div>

        <div class="form-group">
            <label for="imagen">Imagen de la prenda:</label>
            <input type="file" id="imagen" name="imagen" accept="image/*" required>
        </div>

        <!-- ✅ Selector del tipo de prenda -->
        <div class="form-group">
            <label for="tipo_prenda">Tipo de prenda:</label>
            <select id="tipo_prenda" name="tipo_prenda" required>
                <option value="Top">Top</option>
                <option value="Bottom">Bottom</option>
                <option value="Fullbody">Fullbody</option>
            </select>
        </div>

        <button type="submit" class="btn">Cargar prenda</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
