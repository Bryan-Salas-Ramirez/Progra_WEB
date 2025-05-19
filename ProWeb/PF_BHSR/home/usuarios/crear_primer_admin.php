<?php
session_start();
require_once '../../includes/conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// Verificar si ya existe un administrador
$query = "SELECT COUNT(*) FROM usuario WHERE id_rol = 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$admin_count = $stmt->fetchColumn();

// Si ya existe un administrador, redirigir al login
if ($admin_count > 0) {
    header('Location: ../../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = trim($_POST['nombres']);
    $a_paterno = trim($_POST['a_paterno']);
    $a_materno = trim($_POST['a_materno']);
    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validaciones
    $errores = [];

    // Validar que todos los campos estén llenos
    if (empty($nombres) || empty($a_paterno) || empty($a_materno) || empty($correo) || empty($password)) {
        $errores[] = "Todos los campos son obligatorios.";
    }

    // Validar formato de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del correo electrónico no es válido.";
    }

    // Validar que el correo no exista
    $query = "SELECT COUNT(*) FROM usuario WHERE correo = :correo";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
    $stmt->execute();
    if ($stmt->fetchColumn() > 0) {
        $errores[] = "El correo electrónico ya está registrado.";
    }

    // Validar contraseña
    if (strlen($password) < 8) {
        $errores[] = "La contraseña debe tener al menos 8 caracteres.";
    }

    if ($password !== $confirm_password) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    // Si no hay errores, crear el administrador
    if (empty($errores)) {
        try {
            // Iniciar transacción
            $conn->beginTransaction();

            // Hash de la contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insertar usuario
            $query = "INSERT INTO usuario (nombres, a_paterno, a_materno, correo, password, id_rol, id_estatus_us) 
                     VALUES (:nombres, :a_paterno, :a_materno, :correo, :password, 1, 1)";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nombres', $nombres, PDO::PARAM_STR);
            $stmt->bindParam(':a_paterno', $a_paterno, PDO::PARAM_STR);
            $stmt->bindParam(':a_materno', $a_materno, PDO::PARAM_STR);
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password_hash, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $conn->commit();
                $mensaje = "¡Primer administrador creado exitosamente! Serás redirigido al inicio de sesión.";
                $tipo_mensaje = "success";
                
                // Redirigir después de 3 segundos
                header("refresh:3;url=../../index.php");
            } else {
                throw new Exception("Error al crear el administrador.");
            }
        } catch (Exception $e) {
            $conn->rollBack();
            $mensaje = "Error: " . $e->getMessage();
            $tipo_mensaje = "error";
        }
    } else {
        $mensaje = implode("<br>", $errores);
        $tipo_mensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTrack - Crear Primer Administrador</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body class="dashboard-page">
    <div class="main-content">
        <div class="welcome-section">
            <h1>Crear Primer Administrador</h1>
            <p class="subtitle">Configura la cuenta de administrador inicial del sistema</p>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="notification <?php echo $tipo_mensaje === 'success' ? 'is-success' : 'is-danger'; ?> is-light">
                <button class="delete"></button>
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="profile-edit-container">
            <div class="notification is-warning is-light">
                <strong>Importante:</strong> Esta página solo funcionará si no existe ningún administrador en el sistema. 
                Una vez creado el primer administrador, esta página no estará disponible.
            </div>

            <form method="POST" class="profile-form" id="adminForm">
                <div class="field">
                    <label class="label">Nombres</label>
                    <div class="control">
                        <input class="input" type="text" name="nombres" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Apellido Paterno</label>
                    <div class="control">
                        <input class="input" type="text" name="a_paterno" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Apellido Materno</label>
                    <div class="control">
                        <input class="input" type="text" name="a_materno" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Correo Electrónico</label>
                    <div class="control">
                        <input class="input" type="email" name="correo" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Contraseña</label>
                    <div class="control password-field">
                        <input class="input" type="password" name="password" required>
                        <span class="password-toggle">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <p class="help">La contraseña debe tener al menos 8 caracteres</p>
                </div>

                <div class="field">
                    <label class="label">Confirmar Contraseña</label>
                    <div class="control password-field">
                        <input class="input" type="password" name="confirm_password" required>
                        <span class="password-toggle">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <div class="field">
                    <div class="control">
                        <button type="submit" class="button is-primary is-fullwidth">
                            <span class="icon">
                                <i class="fas fa-user-shield"></i>
                            </span>
                            <span>Crear Administrador</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Cerrar notificaciones
        document.addEventListener('DOMContentLoaded', () => {
            (document.querySelectorAll('.notification .delete') || []).forEach(($delete) => {
                $delete.addEventListener('click', () => {
                    $delete.parentNode.remove();
                });
            });

            // Mostrar/ocultar contraseña
            document.querySelectorAll('.password-toggle').forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const input = this.previousElementSibling;
                    const icon = this.querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        input.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
        });
    </script>
</body>
</html> 