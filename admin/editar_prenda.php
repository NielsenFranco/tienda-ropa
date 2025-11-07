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
    $tipo_input = $_POST['tipo'] ?? '';

    if (!$id || !$titulo || !$descripcion || !$tipo_input) {
        header('Location: panel_admin.php?error=Faltan datos');
        exit();
    }

    // 🔁 Mapear categorías amigables a las del modelo Huhu-Tryon
    $categoria_map = [
        'superior' => 'top',
        'inferior' => 'bottom',
        'cuerpo_completo' => 'fullbody'
    ];
    
    // Obtener el valor para la base de datos
    $tipo_db = $categoria_map[$tipo_input] ?? 'top';

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
    $db = $funciones->getDB();
    $stmt = $db->prepare("UPDATE prendas SET titulo = ?, descripcion = ?, categoria = ?, imagen = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $titulo, $descripcion, $tipo_db, $nombre_imagen, $id);
    $stmt->execute();

    header('Location: panel_admin.php?success=Prenda actualizada');
    exit();
}

// Si es GET, mostrar formulario de edición
$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: panel_admin.php?error=ID no especificado');
    exit();
}

$funciones = new Funciones();
$prenda = $funciones->obtenerPrendaPorId($id);
if (!$prenda) {
    header('Location: panel_admin.php?error=Prenda no encontrada');
    exit();
}

// 🔄 Mapeo inverso para mostrar la opción correcta en el formulario
$categoria_map_inverso = [
    'top' => 'superior',
    'bottom' => 'inferior',
    'fullbody' => 'cuerpo_completo'
];

$categoria_actual = $categoria_map_inverso[$prenda['categoria']] ?? 'superior';
?>

<?php include '../includes/header.php'; ?>

<div class="main-content">
    <div class="form-container">
        <h1>Editar Prenda</h1>

```
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($prenda['id']); ?>">

        <div class="form-group">
            <label for="titulo">Título:</label>
            <input type="text" id="titulo" name="titulo" value="<?php echo htmlspecialchars($prenda['titulo']); ?>" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" rows="4" required><?php echo htmlspecialchars($prenda['descripcion']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="imagen">Imagen de la prenda (dejar vacío para mantener la actual):</label>
            <input type="file" id="imagen" name="imagen" accept="image/*">
            <?php if ($prenda['imagen']): ?>
                <div class="current-image">
                    <p>Imagen actual:</p>
                    <img src="<?php echo UPLOAD_DIR_PRENDAS . htmlspecialchars($prenda['imagen']); ?>" alt="Imagen actual" style="max-width: 200px;">
                </div>
            <?php endif; ?>
        </div>

        <!-- ✅ Selector del tipo de prenda - Adaptado a Huhu-Tryon -->
        <div class="form-group">
            <label for="tipo">Tipo de prenda:</label>
            <select id="tipo" name="tipo" required>
                <option value="superior" <?php echo $categoria_actual === 'superior' ? 'selected' : ''; ?>>Prenda Superior (top)</option>
                <option value="inferior" <?php echo $categoria_actual === 'inferior' ? 'selected' : ''; ?>>Prenda Inferior (bottom)</option>
                <option value="cuerpo_completo" <?php echo $categoria_actual === 'cuerpo_completo' ? 'selected' : ''; ?>>Cuerpo Completo (fullbody)</option>
            </select>
            <small class="help-text">
                🔹 <strong>Superior:</strong> Camisetas, blusas, tops, etc.<br>
                🔹 <strong>Inferior:</strong> Pantalones, faldas, shorts, etc.<br>
                🔹 <strong>Cuerpo Completo:</strong> Vestidos, enterizos, monos, etc.
            </small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Actualizar Prenda</button>
            <a href="panel_admin.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
```

</div>

<?php include '../includes/footer.php'; ?>
