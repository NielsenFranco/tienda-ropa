<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title : 'Tienda de Ropa'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/styles.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">NeuraWear</div>
            <nav>
                <ul>
                    <li><a href="<?php echo BASE_URL; ?>/index.php">Inicio</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['rol'] === 'admin'): ?>
                            <li><a href="<?php echo BASE_URL; ?>/admin/panel_admin.php">Panel Admin</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo BASE_URL; ?>/logout.php">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo BASE_URL; ?>/login.php">Iniciar Sesión</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/registro.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main class="container">