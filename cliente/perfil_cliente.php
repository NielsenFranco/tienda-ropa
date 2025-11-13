<?php
require_once '../includes/config.php';
require_once '../includes/funciones.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'cliente') {
    header('Location: ../login.php');
    exit();
}

$funciones = new Funciones();
$mensaje = '';
$tipo_mensaje = '';

// Obtener información actual del usuario
$usuario_id = $_SESSION['user_id'];
$usuario = $funciones->obtenerPerfilUsuario($usuario_id);

// Procesar actualización del perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    
    // Validaciones básicas
    if (empty($username) || empty($email)) {
        $mensaje = 'Todos los campos son obligatorios.';
        $tipo_mensaje = 'error';
    } elseif ($funciones->emailExiste($email, $usuario_id)) {
        $mensaje = 'El correo electrónico ya está en uso por otro usuario.';
        $tipo_mensaje = 'error';
    } elseif ($funciones->usernameExiste($username, $usuario_id)) {
        $mensaje = 'El nombre de usuario ya está en uso.';
        $tipo_mensaje = 'error';
    } else {
        // Procesar imagen de perfil si se subió una nueva
        $foto_perfil = $usuario['foto_perfil'];
        
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === 0) {
            $resultado_imagen = $funciones->subirImagen($_FILES['foto_perfil'], UPLOAD_DIR_PERFILES);
            
            if (isset($resultado_imagen['success'])) {
                // Eliminar la imagen anterior si existe y no es la predeterminada
                if (!empty($usuario['foto_perfil']) && $usuario['foto_perfil'] !== 'default.png') {
                    $ruta_anterior = UPLOAD_DIR_PERFILES . $usuario['foto_perfil'];
                    if (file_exists($ruta_anterior)) {
                        unlink($ruta_anterior);
                    }
                }
                $foto_perfil = $resultado_imagen['success'];
            } else {
                $mensaje = $resultado_imagen['error'];
                $tipo_mensaje = 'error';
            }
        }
        
        // Actualizar en la base de datos si no hay errores
        if (empty($mensaje)) {
            if ($funciones->actualizarPerfil($usuario_id, $username, $email, $foto_perfil)) {
                $mensaje = 'Perfil actualizado correctamente.';
                $tipo_mensaje = 'exito';
                
                // Actualizar datos en sesión
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                
                // Recargar datos del usuario
                $usuario = $funciones->obtenerPerfilUsuario($usuario_id);
            } else {
                $mensaje = 'Error al actualizar el perfil.';
                $tipo_mensaje = 'error';
            }
        }
    }
}

$title = "Mi Perfil";
?>
<?php include '../includes/header.php'; ?>

<div class="main-content">
    <h1>Mi Perfil</h1>

    <div class="cliente-actions">
        <a href="../index.php" class="btn">Volver a la Tienda</a>
    </div>

    <?php if ($mensaje): ?>
        <div class="mensaje <?php echo $tipo_mensaje; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>

    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-layout">
                <!-- Columna izquierda: Foto de perfil -->
                <div class="profile-left">
                    <div class="profile-picture-container">
                        <div class="profile-picture-rectangular">
                            <?php if (!empty($usuario['foto_perfil'])): ?>
                                <img src="<?php echo UPLOADS_URL_PERFILES . htmlspecialchars($usuario['foto_perfil']); ?>" 
                                     alt="Foto de perfil de <?php echo htmlspecialchars($usuario['username']); ?>">
                            <?php else: ?>
                                <div class="default-avatar-rectangular">
                                    <i class="fas fa-user"></i>
                                    <span>Sin foto de perfil</span>
                                </div>
                            <?php endif; ?>
                            <div class="edit-icon-simple" onclick="profileManager.togglePhotoUpload()">
                                <img src="../img/boligrafo.png" alt="Editar foto">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Columna derecha: Información del usuario -->
                <div class="profile-right">
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($usuario['username']); ?></h2>
                        <p class="profile-email"><?php echo htmlspecialchars($usuario['email']); ?></p>
                    </div>

                    <form class="profile-form" method="POST" enctype="multipart/form-data">
                        <div class="form-section">
                            <h3>Información Personal</h3>
                            
                            <div class="form-group">
                                <label for="username">Nombre de usuario</label>
                                <input type="text" name="username" id="username" 
                                       value="<?php echo htmlspecialchars($usuario['username']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Correo electrónico</label>
                                <input type="email" name="email" id="email" 
                                       value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                            </div>
                        </div>

                        <div class="form-section" id="photo-upload-section" style="display: none;">
                            <h3>Foto de Perfil</h3>
                            <div class="form-group">
                                <label for="foto_perfil">Seleccionar nueva foto</label>
                                <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*">
                                <small class="help-text">
                                    Formatos permitidos: JPG, PNG, GIF. Tamaño máximo: 5MB
                                </small>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Actualizar perfil</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir el archivo JavaScript externo -->
<script src="../assets/js/perfil-usuario.js"></script>

<?php include '../includes/footer.php'; ?>