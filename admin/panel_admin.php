<?php
require_once '../includes/config.php';
require_once '../includes/funciones.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$funciones = new Funciones();
$prendas = $funciones->obtenerPrendas();

// Mapeo para mostrar categorías en español
$categoria_labels = [
    'upper_body' => 'Prenda Superior',
    'lower_body' => 'Prenda Inferior',
    'dresses' => 'Vestido/Cuerpo Completo'
];

$title = "Panel de Administración";
?>
<?php include '../includes/header.php'; ?>
<div class="main-content">
    <h1>Panel de Administración</h1>
    <p>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>

    <div class="admin-actions">
        <a href="cargar_prenda.php" class="btn">Cargar Nueva Prenda</a>
        <a href="../index.php" class="btn">Ver Tienda</a>
    </div>

    <div class="prendas-grid">
        <?php foreach ($prendas as $prenda): ?>
            <div class="prenda-card" id="prenda-<?php echo $prenda['id']; ?>">
                <img src="<?php echo UPLOADS_URL_PRENDAS . $prenda['imagen']; ?>" alt="Imagen de prenda">
                <div style="padding: 15px;">
                    <h3><?php echo htmlspecialchars($prenda['titulo']); ?></h3>
                    <p><strong>Categoría:</strong> <?php echo $categoria_labels[$prenda['categoria']] ?? 'No especificada'; ?></p>
                    <button class="btn" onclick="toggleFormulario(<?php echo $prenda['id']; ?>)">Editar</button>
                    <a href="eliminar_prenda.php?id=<?php echo $prenda['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Eliminar esta prenda?')">Eliminar</a>
                </div>

                <!-- Formulario oculto -->
                <form class="form-edicion" id="form-<?php echo $prenda['id']; ?>" action="editar_prenda.php" method="post" enctype="multipart/form-data" style="display: none; padding: 15px;">
                    <input type="hidden" name="id" value="<?php echo $prenda['id']; ?>">

                    <div class="form-group">
                        <label for="titulo-<?php echo $prenda['id']; ?>">Título</label>
                        <input type="text" name="titulo" id="titulo-<?php echo $prenda['id']; ?>" value="<?php echo htmlspecialchars($prenda['titulo']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="descripcion-<?php echo $prenda['id']; ?>">Descripción</label>
                        <textarea name="descripcion" id="descripcion-<?php echo $prenda['id']; ?>" required><?php echo htmlspecialchars($prenda['descripcion']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="tipo-<?php echo $prenda['id']; ?>">Tipo de prenda</label>
                        <select name="tipo" id="tipo-<?php echo $prenda['id']; ?>" required>
                            <option value="superior" <?php echo $prenda['categoria'] === 'upper_body' ? 'selected' : ''; ?>>Prenda Superior</option>
                            <option value="inferior" <?php echo $prenda['categoria'] === 'lower_body' ? 'selected' : ''; ?>>Prenda Inferior</option>
                            <option value="cuerpo_completo" <?php echo $prenda['categoria'] === 'dresses' ? 'selected' : ''; ?>>Vestido/Cuerpo Completo</option>
                        </select>
                        <small class="help-text">
                            🔍 <strong>Prenda Superior:</strong> Camisetas, blusas, tops, etc.<br>
                            🔍 <strong>Prenda Inferior:</strong> Pantalones, faldas, shorts, etc.<br>
                            🔍 <strong>Vestido/Cuerpo Completo:</strong> Vestidos, enterizos, monos, etc.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="imagen-<?php echo $prenda['id']; ?>">Nueva Imagen (opcional)</label>
                        <input type="file" name="imagen" id="imagen-<?php echo $prenda['id']; ?>" accept="image/*">
                        <?php if ($prenda['imagen']): ?>
                            <div class="current-image">
                                <p>Imagen actual:</p>
                                <img src="<?php echo UPLOADS_URL_PRENDAS . htmlspecialchars($prenda['imagen']); ?>" alt="Imagen actual" style="max-width: 100px; margin-top: 5px;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        <button type="button" class="btn btn-secondary" onclick="toggleFormulario(<?php echo $prenda['id']; ?>)">Cancelar</button>
                    </div>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>    

<script>
function toggleFormulario(id) {
    const form = document.getElementById(`form-${id}`);
    if (form.style.display === "none" || form.style.display === "") {
        form.style.display = "block";
    } else {
        form.style.display = "none";
    }
}
</script>

<?php include '../includes/footer.php'; ?>