<?php
session_start();
require_once '../../includes/verificar_sesion.php';
require_once '../../includes/conexion.php';

$mensaje = '';
$tipo_mensaje = '';
$mostrar_modal_exito = false;

// Obtener datos actuales del usuario
$id_usuario = $_SESSION['id_usuario'];
$query = "SELECT nombres, a_paterno, a_materno, correo FROM usuario WHERE id_usuario = :id_usuario";
$stmt = $conn->prepare($query);
$stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombres = trim($_POST['nombres']);
    $a_paterno = trim($_POST['a_paterno']);
    $a_materno = trim($_POST['a_materno']);
    $correo = trim($_POST['correo']);
    $correo_actual = $usuario['correo'];

    // Validar que el correo no esté en uso (si se cambió)
    if ($correo !== $correo_actual) {
        $query = "SELECT COUNT(*) FROM usuario WHERE correo = :correo AND id_usuario != :id_usuario";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->fetchColumn() > 0) {
            $mensaje = 'El correo electrónico ya está registrado en el sistema.';
            $tipo_mensaje = 'error';
        }
    }

    // Si no hay error, actualizar los datos
    if (empty($mensaje)) {
        try {
            $query = "UPDATE usuario 
                    SET nombres = :nombres,
                        a_paterno = :a_paterno,
                        a_materno = :a_materno,
                        correo = :correo
                    WHERE id_usuario = :id_usuario";
            
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':nombres', $nombres, PDO::PARAM_STR);
            $stmt->bindParam(':a_paterno', $a_paterno, PDO::PARAM_STR);
            $stmt->bindParam(':a_materno', $a_materno, PDO::PARAM_STR);
            $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $mostrar_modal_exito = true;
            } else {
                $mensaje = 'Error al actualizar los datos.';
                $tipo_mensaje = 'error';
            }
        } catch (PDOException $e) {
            $mensaje = 'Error al actualizar los datos: ' . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    }
}

// Determinar la ruta base para el menú
$current_path = $_SERVER['PHP_SELF'];
$is_in_usuarios = strpos($current_path, '/usuarios/') !== false;
$base_path = $is_in_usuarios ? '../' : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTrack - Editar Perfil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
</head>
<body class="dashboard-page">
    <?php
    include '../../includes/header.php';
    include '../../includes/menu.php';
    ?>

    <div class="main-content">
        <div class="welcome-section">
            <h1>Editar Perfil</h1>
            <p class="subtitle">Actualiza tu información personal</p>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="notification <?php echo $tipo_mensaje === 'success' ? 'is-success' : 'is-danger'; ?> is-light">
                <button class="delete"></button>
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="profile-edit-container">
            <div class="notification is-warning is-light">
                <strong>Nota importante:</strong> Al actualizar tu perfil, serás redirigido a la página de inicio de sesión para asegurar que todos los cambios se apliquen correctamente.
            </div>

            <form method="POST" class="profile-form" id="profileForm">
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
                        <input class="input" type="text" name="a_materno" value="<?php echo htmlspecialchars($usuario['a_materno']); ?>" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label">Correo Electrónico</label>
                    <div class="control">
                        <input class="input" type="email" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                    </div>
                    <p class="help">El correo electrónico debe ser único en el sistema</p>
                </div>

                <div class="field">
                    <div class="control">
                        <button type="button" class="button is-primary is-fullwidth" onclick="confirmarCambios()">
                            <span class="icon">
                                <i class="fas fa-save"></i>
                            </span>
                            <span>Guardar Cambios</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div class="modal" id="confirmModal">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Confirmar Cambios</p>
                <button class="delete" aria-label="close" onclick="cerrarModal('confirmModal')"></button>
            </header>
            <section class="modal-card-body">
                <p>¿Estás seguro de que deseas actualizar tu perfil? Serás redirigido a la página de inicio de sesión después de guardar los cambios.</p>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success" onclick="enviarFormulario()">Confirmar</button>
                <button class="button" onclick="cerrarModal('confirmModal')">Cancelar</button>
            </footer>
        </div>
    </div>

    <!-- Modal de éxito -->
    <?php if ($mostrar_modal_exito): ?>
    <div class="modal is-active" id="successModal">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">¡Éxito!</p>
                <button class="delete" aria-label="close" onclick="cerrarModal('successModal')"></button>
            </header>
            <section class="modal-card-body">
                <div class="notification is-success is-light">
                    Perfil actualizado correctamente. Serás redirigido a la página de inicio de sesión.
                </div>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success" onclick="redirigirAlLogin()">Aceptar</button>
            </footer>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Cerrar notificaciones
        document.addEventListener('DOMContentLoaded', () => {
            (document.querySelectorAll('.notification .delete') || []).forEach(($delete) => {
                $delete.addEventListener('click', () => {
                    $delete.parentNode.remove();
                });
            });
        });

        // Funciones para el modal de confirmación
        function confirmarCambios() {
            document.getElementById('confirmModal').classList.add('is-active');
        }

        function cerrarModal(modalId) {
            document.getElementById(modalId).classList.remove('is-active');
        }

        function enviarFormulario() {
            document.getElementById('profileForm').submit();
        }

        function redirigirAlLogin() {
            window.location.href = '../../includes/cerrar_sesion.php?redirect=index.php';
        }

        // Cerrar modal al hacer clic en el fondo
        document.querySelectorAll('.modal-background').forEach(($background) => {
            $background.addEventListener('click', () => {
                $background.parentNode.classList.remove('is-active');
            });
        });
    </script>
</body>
</html>
