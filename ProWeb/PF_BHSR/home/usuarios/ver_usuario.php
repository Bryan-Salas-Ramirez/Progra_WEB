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

// Verificar si se proporcionó un ID de usuario
if (!isset($_GET['id'])) {
    header('Location: gestionar_usuarios.php');
    exit();
}

$id_usuario = $_GET['id'];

// Obtener información del usuario
$query = "SELECT u.*, r.nombre as rol_nombre,
        CASE WHEN u.id_estatus_us = 1 THEN 'Activo' ELSE 'Inactivo' END as estado
        FROM usuario u
        JOIN rol r ON u.id_rol = r.id_rol
        WHERE u.id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no se encuentra el usuario, redirigir
if (!$usuario) {
    header('Location: gestionar_usuarios.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTrack - Detalles de Usuario</title>
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
            <h1>Detalles del Usuario</h1>
            <p class="subtitle">Información completa del usuario</p>
        </div>

        <!-- Botones de acción -->
        <div class="mb-4">
            <a href="gestionar_usuarios.php" class="button is-light">
                <span class="icon">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <span>Volver</span>
            </a>
            <a href="editar_usuario.php?id=<?php echo $usuario['id_usuario']; ?>" class="button is-warning">
                <span class="icon">
                    <i class="fas fa-edit"></i>
                </span>
                <span>Editar Usuario</span>
            </a>
        </div>

        <!-- Información del usuario -->
        <div class="card">
            <div class="card-content">
                <div class="columns">
                    <div class="column">
                        <h3 class="title is-4">Información Personal</h3>
                        <div class="field">
                            <label class="label">Nombre Completo</label>
                            <p class="subtitle is-6">
                                <?php echo htmlspecialchars($usuario['nombres'] . ' ' . $usuario['a_paterno'] . ' ' . $usuario['a_materno']); ?>
                            </p>
                        </div>
                        <div class="field">
                            <label class="label">Correo Electrónico</label>
                            <p class="subtitle is-6"><?php echo htmlspecialchars($usuario['correo']); ?></p>
                        </div>
                    </div>
                    <div class="column">
                        <h3 class="title is-4">Información de la Cuenta</h3>
                        <div class="field">
                            <label class="label">Rol</label>
                            <p class="subtitle is-6"><?php echo htmlspecialchars($usuario['rol_nombre']); ?></p>
                        </div>
                        <div class="field">
                            <label class="label">Estado</label>
                            <p class="subtitle is-6">
                                <span class="tag <?php echo $usuario['estado'] === 'Activo' ? 'is-success' : 'is-danger'; ?>">
                                    <?php echo $usuario['estado']; ?>
                                </span>
                            </p>
                        </div>
                        <div class="field">
                            <label class="label">Fecha de Registro</label>
                            <p class="subtitle is-6">
                                <?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 