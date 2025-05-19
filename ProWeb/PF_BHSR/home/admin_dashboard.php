<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión a la base de datos
require_once '../includes/conexion.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 1) {
    header('Location: ../login.php');
    exit();
}

// Obtener estadísticas del sistema
$query = "SELECT 
    COUNT(*) as total_usuarios,
    SUM(CASE WHEN id_estatus_us = 1 THEN 1 ELSE 0 END) as usuarios_activos,
    SUM(CASE WHEN id_estatus_us != 1 THEN 1 ELSE 0 END) as usuarios_inactivos,
    SUM(CASE WHEN id_rol = 1 THEN 1 ELSE 0 END) as total_admins,
    SUM(CASE WHEN id_rol = 2 THEN 1 ELSE 0 END) as total_usuarios_normales
    FROM usuario";
$stmt = $conn->prepare($query);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTrack - Panel de Administración</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="dashboard-page">
    <?php
    include '../includes/header.php';
    include '../includes/admin_menu.php';
    ?>

    <div class="main-content">
        <div class="welcome-section">
            <h1>Panel de Administración</h1>
            <p class="subtitle">Resumen del sistema</p>
        </div>

        <!-- Grid de tarjetas de resumen -->
        <div class="dashboard-grid">
            <!-- Total de Usuarios -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Total de Usuarios</h2>
                </div>
                <div class="card-content">
                    <div class="summary-item">
                        <i class="fas fa-users"></i>
                        <div class="summary-info">
                            <span class="summary-value"><?php echo $stats['total_usuarios']; ?></span>
                            <span class="summary-label">Usuarios Registrados</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usuarios Activos -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Usuarios Activos</h2>
                </div>
                <div class="card-content">
                    <div class="summary-item">
                        <i class="fas fa-user-check"></i>
                        <div class="summary-info">
                            <span class="summary-value"><?php echo $stats['usuarios_activos']; ?></span>
                            <span class="summary-label">Usuarios Activos</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usuarios Inactivos -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Usuarios Inactivos</h2>
                </div>
                <div class="card-content">
                    <div class="summary-item">
                        <i class="fas fa-user-times"></i>
                        <div class="summary-info">
                            <span class="summary-value"><?php echo $stats['usuarios_inactivos']; ?></span>
                            <span class="summary-label">Usuarios Inactivos</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Administradores -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Administradores</h2>
                </div>
                <div class="card-content">
                    <div class="summary-item">
                        <i class="fas fa-user-shield"></i>
                        <div class="summary-info">
                            <span class="summary-value"><?php echo $stats['total_admins']; ?></span>
                            <span class="summary-label">Administradores</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usuarios Normales -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Usuarios Normales</h2>
                </div>
                <div class="card-content">
                    <div class="summary-item">
                        <i class="fas fa-user"></i>
                        <div class="summary-info">
                            <span class="summary-value"><?php echo $stats['total_usuarios_normales']; ?></span>
                            <span class="summary-label">Usuarios Normales</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
</html> 