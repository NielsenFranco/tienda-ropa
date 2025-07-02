<?php
require_once '../includes/config.php';
require_once '../includes/funciones.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$funciones = new Funciones();
$prendas = $funciones->obtenerPrendas();

$title = "Panel de Administración";
?>
<?php include '../includes/header.php'; ?>

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
                <button class="btn" onclick="toggleFormulario(<?php echo $prenda['id']; ?>)">Editar</button>
                <a href="eliminar_prenda.php?id=<?php echo $prenda['id']; ?>" class="btn" onclick="return confirm('¿Eliminar esta prenda?')">Eliminar</a>
            </div>

            <!-- Formulario oculto -->
            <form class="form-edicion" id="form-<?php echo $prenda['id']; ?>" action="editar_prenda.php" method="post" enctype="multipart/form-data" style="display: none; padding: 15px;">
                <input type="hidden" name="id" value="<?php echo $prenda['id']; ?>">

                <div class="form-group">
                    <label for="titulo-<?php echo $prenda['id']; ?>">Título</label>
                    <input type="text" name="titulo" id="titulo-<?php echo $prenda['id']; ?>" value="<?php echo htmlspecialchars($prenda['titulo']); ?>">
                </div>

                <div class="form-group">
                    <label for="descripcion-<?php echo $prenda['id']; ?>">Descripción</label>
                    <textarea name="descripcion" id="descripcion-<?php echo $prenda['id']; ?>"><?php echo htmlspecialchars($prenda['descripcion']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="tipo-<?php echo $prenda['id']; ?>">Tipo de prenda</label>
                    <select name="tipo" id="tipo-<?php echo $prenda['id']; ?>">
                        <option value="Top" <?php if ($prenda['tipo_prenda'] === 'Top') echo 'selected'; ?>>Top</option>
                        <option value="Bottom" <?php if ($prenda['tipo_prenda'] === 'Bottom') echo 'selected'; ?>>Bottom</option>
                        <option value="Fullbody" <?php if ($prenda['tipo_prenda'] === 'Fullbody') echo 'selected'; ?>>Fullbody</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="imagen-<?php echo $prenda['id']; ?>">Nueva Imagen (opcional)</label>
                    <input type="file" name="imagen" id="imagen-<?php echo $prenda['id']; ?>">
                </div>

                <button type="submit" class="btn">Guardar cambios</button>
            </form>
        </div>
    <?php endforeach; ?>
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
