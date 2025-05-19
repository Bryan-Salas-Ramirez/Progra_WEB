<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión a la base de datos
require_once '../../includes/conexion.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 1) {
    header('Location: ../../login.php');
    exit();
}

// Obtener lista de usuarios
$query = "SELECT u.id_usuario, u.nombres, u.a_paterno, u.a_materno, u.correo, 
        r.nombre as rol, DATE_FORMAT(u.fecha_registro, '%d/%m/%Y') as fecha_registro,
        CASE WHEN u.id_estatus_us = 1 THEN 'Activo' ELSE 'Inactivo' END as estado
        FROM usuario u
        JOIN rol r ON u.id_rol = r.id_rol
        ORDER BY u.fecha_registro DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTrack - Gestión de Usuarios</title>
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
            <h1>Gestión de Usuarios</h1>
            <p class="subtitle">Administra los usuarios del sistema</p>
        </div>

        <!-- Botón para crear nuevo usuario -->
        <div class="mb-4">
            <a href="crear_usuario.php" class="button is-primary">
                <span class="icon">
                    <i class="fas fa-plus"></i>
                </span>
                <span>Nuevo Usuario</span>
            </a>
        </div>

        <!-- Lista de usuarios -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-header-title">Lista de Usuarios</h2>
            </div>
            <div class="card-content">
                <div class="table-container">
                    <table class="table is-fullwidth is-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre Completo</th>
                                <th>Correo</th>
                                <th>Rol</th>
                                <th>Fecha de Registro</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?php echo $usuario['id_usuario']; ?></td>
                                    <td><?php echo htmlspecialchars($usuario['nombres'] . ' ' . $usuario['a_paterno'] . ' ' . $usuario['a_materno']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                                    <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                                    <td><?php echo $usuario['fecha_registro']; ?></td>
                                    <td>
                                        <span class="tag <?php echo $usuario['estado'] === 'Activo' ? 'is-success' : 'is-danger'; ?>">
                                            <?php echo $usuario['estado']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="buttons are-small">
                                            <button class="button is-info" onclick="verDetalles(<?php echo $usuario['id_usuario']; ?>)">
                                                <span class="icon">
                                                    <i class="fas fa-eye"></i>
                                                </span>
                                            </button>
                                            <a href="editar_usuario_admin.php?id=<?php echo $usuario['id_usuario']; ?>" 
                                               class="button is-info">
                                                <span class="icon">
                                                    <i class="fas fa-edit"></i>
                                                </span>
                                            </a>
                                            <?php if (!($usuario['id_usuario'] == 1 && $usuario['rol'] == 'Administrador')): ?>
                                                <button class="button is-danger" 
                                                        onclick="cambiarEstado(<?php echo $usuario['id_usuario']; ?>, '<?php echo $usuario['estado']; ?>')">
                                                    <span class="icon">
                                                        <i class="fas fa-<?php echo $usuario['estado'] == 'Activo' ? 'ban' : 'check'; ?>"></i>
                                                    </span>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function verDetalles(idUsuario) {
            window.location.href = `ver_usuario.php?id=${idUsuario}`;
        }

        function editarUsuario(idUsuario) {
            window.location.href = `editar_usuario_admin.php?id=${idUsuario}`;
        }

        function cambiarEstado(idUsuario, estadoActual) {
            if (confirm('¿Estás seguro de que deseas ' + (estadoActual === 'Activo' ? 'desactivar' : 'activar') + ' este usuario?')) {
                fetch('cambiar_estado_usuario.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id_usuario: idUsuario
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error al cambiar el estado del usuario');
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }
    </script>
</body>
</html> 