<?php
require_once 'includes/config.php';
require_once 'includes/funciones.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$funciones = new Funciones();
$prenda = $funciones->obtenerPrendaPorId($_GET['id']);

if (!$prenda) {
    header('Location: index.php');
    exit();
}

$title = htmlspecialchars($prenda['titulo']) . " - Tienda de Ropa";

// Ruta para mostrar imagen
$prenda_img = UPLOADS_URL_PRENDAS . $prenda['imagen'];
?>
<?php include 'includes/header.php'; ?>

<div class="prenda-detalle-container">
    <div class="prenda-imagen">
        <img src="<?php echo $prenda_img; ?>" alt="<?php echo htmlspecialchars($prenda['titulo']); ?>">
    </div>

    <div class="prenda-info">
        <h1><?php echo htmlspecialchars($prenda['titulo']); ?></h1>
        <p><?php echo nl2br(htmlspecialchars($prenda['descripcion'])); ?></p>

        <?php if (isset($_SESSION['user_id'])): ?>
            <button id="probarPrendaBtn"
                    class="btn"
                    data-prenda-id="<?php echo $prenda['id']; ?>"
                    data-usuario-id="<?php echo $_SESSION['user_id']; ?>"
                    data-tipo-prenda="<?php echo htmlspecialchars($prenda['tipo_prenda']); ?>">
                Pruébate la prenda
            </button>
            <div id="resultadoTryOn" class="tryon-result" style="display: none; margin-top: 20px;">
                <img id="imagenResultado" src="" alt="Resultado Try-On" style="max-width: 100%; border: 1px solid #ccc; border-radius: 8px;">
            </div>
        <?php else: ?>
            <p class="info">Inicia sesión para probarte esta prenda virtualmente.</p>
        <?php endif; ?>
    </div>
</div>

<!-- 🔑 Variables necesarias para tryon.js -->
<script>
    const TRYON_PROXY_URL = "<?php echo BASE_URL; ?>/proxy_tryon.php";
    const BASE_URL = "<?php echo BASE_URL; ?>";
</script>
<script type="module" src="<?php echo BASE_URL; ?>/assets/js/tryon.js"></script>

<?php include 'includes/footer.php'; ?>
