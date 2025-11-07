<?php
require_once 'includes/config.php';
require_once 'includes/funciones.php';

$funciones = new Funciones();
$prendas = $funciones->obtenerPrendas();
$title = "Tienda de Ropa";
?>
<?php include 'includes/header.php'; ?>

<div class="main-content">
    <h1>Nuestras Prendas</h1>

    <div class="prendas-grid">
        <?php foreach ($prendas as $prenda): ?>
            <div class="prenda-card">
                <img src="<?php echo UPLOADS_URL_PRENDAS . $prenda['imagen']; ?>" alt="<?php echo htmlspecialchars($prenda['titulo']); ?>">
                <h3><?php echo htmlspecialchars($prenda['titulo']); ?></h3>
                <p><?php echo substr(htmlspecialchars($prenda['descripcion']), 0, 100); ?>...</p>
                <a href="prenda.php?id=<?php echo $prenda['id']; ?>" target="_blank" class="btn">Ver Detalles</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>