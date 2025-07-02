<?php
require_once 'includes/config.php';
require_once 'includes/funciones.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$funciones = new Funciones();
$error = '';
$title = "Registro de Usuario";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $rol = isset($_POST['rol']) ? 'admin' : 'cliente';
    
    $foto_perfil = '';
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $upload_result = $funciones->subirImagen($_FILES['foto_perfil'], UPLOAD_DIR_PERFILES);
        if (isset($upload_result['success'])) {
            $foto_perfil = $upload_result['success'];
        } else {
            $error = $upload_result['error'];
        }
    }
    
    if (empty($error)) {
        if ($funciones->registrarUsuario($username, $email, $password, $foto_perfil, $rol)) {
            header('Location: login.php');
            exit();
        } else {
            $error = 'Error al registrar el usuario.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<h1>Registro de Usuario</h1>

<?php if (!empty($error)): ?>
    <div class="alert error"><?php echo $error; ?></div>
<?php endif; ?>

<form action="registro.php" method="post" enctype="multipart/form-data">
    <div class="form-group">
        <label for="username">Nombre de Usuario:</label>
        <input type="text" id="username" name="username" required>
    </div>
    
    <div class="form-group">
        <label for="email">Correo Electrónico:</label>
        <input type="email" id="email" name="email" required>
    </div>
    
    <div class="form-group">
        <label for="password">Contraseña:</label>
        <input type="password" id="password" name="password" required>
    </div>
    
    <div class="form-group">
        <label for="foto_perfil">Foto de Perfil:</label>
        <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*">
        <small>Se recomienda una foto de cuerpo entero para mejor experiencia con el probador virtual.</small>
    </div>
    
    <div class="form-group checkbox">
        <input type="checkbox" id="rol" name="rol">
        <label for="rol">Registrarme como administrador</label>
    </div>
    
    <button type="submit" class="btn">Registrarse</button>
</form>

<p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>

<?php include 'includes/footer.php'; ?>