<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión a la base de datos
require_once '../../includes/conexion.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 1) {
    header('Location: ../../index.php');
    exit();
}

// Verificar si se proporcionó un ID de usuario
if (!isset($_GET['id'])) {
    header('Location: gestionar_usuarios.php');
    exit();
}

$id_usuario = $_GET['id'];

// Obtener datos del usuario
try {
    $query = "SELECT u.*, r.nombre as rol_nombre 
              FROM usuario u 
              JOIN rol r ON u.id_rol = r.id_rol 
              WHERE u.id_usuario = :id_usuario";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $_SESSION['error'] = 'Usuario no encontrado';
        header('Location: gestionar_usuarios.php');
        exit();
    }

    // Obtener lista de roles
    $query_roles = "SELECT * FROM rol ORDER BY nombre";
    $stmt_roles = $conn->prepare($query_roles);
    $stmt_roles->execute();
    $roles = $stmt_roles->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = 'Error al obtener los datos del usuario';
    header('Location: gestionar_usuarios.php');
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombres = trim($_POST['nombres']);
        $a_paterno = trim($_POST['a_paterno']);
        $a_materno = trim($_POST['a_materno']);
        $correo = trim($_POST['correo']);
        $id_rol = $_POST['id_rol'];
        $password = trim($_POST['password']);

        // Validar datos
        if (empty($nombres) || empty($a_paterno) || empty($correo)) {
            throw new Exception('Por favor, complete todos los campos requeridos');
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Por favor, ingrese un correo electrónico válido');
        }

        // Verificar si el correo ya existe (excluyendo el usuario actual)
        $query = "SELECT id_usuario FROM usuario WHERE correo = :correo AND id_usuario != :id_usuario";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        if ($stmt->fetch()) {
            throw new Exception('El correo electrónico ya está registrado');
        }

        // Actualizar usuario
        $query = "UPDATE usuario SET 
                 nombres = :nombres,
                 a_paterno = :a_paterno,
                 a_materno = :a_materno,
                 correo = :correo,
                 id_rol = :id_rol";
        
        // Si se proporcionó una nueva contraseña, actualizarla
        if (!empty($password)) {
            if (strlen($password) < 6) {
                throw new Exception('La contraseña debe tener al menos 6 caracteres');
            }
            $query .= ", password = :password";
        }
        
        $query .= " WHERE id_usuario = :id_usuario";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':nombres', $nombres);
        $stmt->bindParam(':a_paterno', $a_paterno);
        $stmt->bindParam(':a_materno', $a_materno);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':id_rol', $id_rol);
        $stmt->bindParam(':id_usuario', $id_usuario);
        
        if (!empty($password)) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $password_hash);
        }
        
        $stmt->execute();
        
        $_SESSION['success'] = 'Usuario actualizado correctamente';
        header('Location: gestionar_usuarios.php');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTrack - Editar Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body class="dashboard-page">
    <?php
    include '../../includes/header.php';
    include '../../includes/admin_menu.php';
    ?>

    <div class="main-content">
        <div class="welcome-section">
            <h1>Editar Usuario</h1>
            <p class="subtitle">Modifica la información del usuario</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="notification is-danger is-light">
                <button class="delete"></button>
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-content">
                <form method="POST" action="">
                    <div class="field">
                        <label class="label">Nombres</label>
                        <div class="control">
                            <input class="input" type="text" name="nombres" value="<?php echo htmlspecialchars($usuario['nombres']); ?>" required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Apellido Paterno</label>
                        <div class="control">
                            <input class="input" type="text" name="a_paterno" value="<?php echo htmlspecialchars($usuario['a_paterno']); ?>" required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Apellido Materno</label>
                        <div class="control">
                            <input class="input" type="text" name="a_materno" value="<?php echo htmlspecialchars($usuario['a_materno']); ?>">
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Correo Electrónico</label>
                        <div class="control">
                            <input class="input" type="email" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Rol</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select name="id_rol" required>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?php echo $rol['id_rol']; ?>" <?php echo $rol['id_rol'] == $usuario['id_rol'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($rol['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Nueva Contraseña (dejar en blanco para mantener la actual)</label>
                        <div class="control">
                            <input class="input" type="password" name="password" minlength="6">
                        </div>
                    </div>

                    <div class="field is-grouped">
                        <div class="control">
                            <button type="submit" class="button is-primary">
                                <span class="icon">
                                    <i class="fas fa-save"></i>
                                </span>
                                <span>Guardar Cambios</span>
                            </button>
                        </div>
                        <div class="control">
                            <a href="gestionar_usuarios.php" class="button is-light">
                                <span class="icon">
                                    <i class="fas fa-times"></i>
                                </span>
                                <span>Cancelar</span>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Cerrar notificaciones al hacer clic en el botón de cerrar
        document.addEventListener('DOMContentLoaded', () => {
            (document.querySelectorAll('.notification .delete') || []).forEach(($delete) => {
                $delete.addEventListener('click', () => {
                    $delete.parentNode.remove();
                });
            });
        });
    </script>
</body>
</html> 