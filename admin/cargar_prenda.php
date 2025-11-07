<?php
require_once '../includes/config.php';
require_once '../includes/funciones.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$funciones = new Funciones();
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $categoria_input = $_POST['categoria']; // Valor del formulario
    $id_usuario = $_SESSION['user_id'];

    // 🔁 Mapear categorías amigables a las del modelo Huhu-Tryon
    $categoria_map = [
        'superior' => 'top',
        'inferior' => 'bottom', 
        'cuerpo_completo' => 'fullbody'
    ];
    
    // Obtener el valor para la base de datos
    $categoria_db = $categoria_map[$categoria_input] ?? 'top';

    // Validar campos
    if ($titulo === '' || $descripcion === '' || empty($_FILES['imagen']['name'])) {
        $error = 'Completa todos los campos.';
    } else {
        // Subir imagen
        $upload_result = $funciones->subirImagen($_FILES['imagen'], '../assets/uploads/prendas/');
        if (isset($upload_result['success'])) {
            $imagen = $upload_result['success'];

            // Crear prenda en base de datos
            if ($funciones->crearPrenda($titulo, $descripcion, $imagen, $id_usuario, $categoria_db)) {
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

<?php include '../includes/header.php'; ?>

<div class="main-content">
    <div class="form-container">
        <h1>Cargar nueva prenda</h1>

```
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

        <!-- ✅ Selector del tipo de prenda - Adaptado a Huhu-Tryon -->
        <div class="form-group">
            <label for="categoria">Tipo de prenda:</label>
            <select id="categoria" name="categoria" required>
                <option value="superior">Prenda Superior (top)</option>
                <option value="inferior">Prenda Inferior (bottom)</option>
                <option value="cuerpo_completo">Cuerpo Completo (fullbody)</option>
            </select>
            <small class="help-text">
                🔹 <strong>Superior:</strong> Camisetas, blusas, tops, etc.<br>
                🔹 <strong>Inferior:</strong> Pantalones, faldas, shorts, etc.<br>
                🔹 <strong>Cuerpo Completo:</strong> Vestidos, enterizos, monos, etc.
            </small>
        </div>

        <button type="submit" class="btn">Cargar prenda</button>
    </form>
</div>
```

</div>

<?php include '../includes/footer.php'; ?>
