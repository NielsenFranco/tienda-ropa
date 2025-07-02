<?php
require_once 'includes/config.php';
require_once 'includes/funciones.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$funciones = new Funciones();
$error = '';
$title = "Iniciar Sesión";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $user = $funciones->verificarLogin($email, $password);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['foto_perfil'] = $user['foto_perfil'];
        
        header('Location: ' . ($user['rol'] === 'admin' ? 'admin/panel_admin.php' : 'index.php'));
        exit();
    } else {
        $error = 'Correo electrónico o contraseña incorrectos.';
    }
}
?>
<?php include 'includes/header.php'; ?>

<h1>Iniciar Sesión</h1>

<?php if (!empty($error)): ?>
    <div class="alert error"><?php echo $error; ?></div>
<?php endif; ?>

<form action="login.php" method="post">
    <div class="form-group">
        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" required>
    </div>
    
    <div class="form-group">
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>
    </div>
    
    <button type="submit" class="btn">Iniciar Sesión</button>
</form>

<p>¿No tienes una cuenta? <a href="registro.php">Regístrate aquí</a></p>

<?php include 'includes/footer.php'; ?>