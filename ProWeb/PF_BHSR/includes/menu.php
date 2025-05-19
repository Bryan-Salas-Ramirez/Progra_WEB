<?php
// Verificar si la sesión está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión a la base de datos
require_once 'conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../index.php');
    exit();
}

// Determinar la ruta base según la ubicación actual
$current_path = $_SERVER['PHP_SELF'];
$is_in_habits = strpos($current_path, '/habitos/') !== false;
$is_in_metas = strpos($current_path, '/metas/') !== false;
$is_in_usuarios = strpos($current_path, '/usuarios/') !== false;
$base_path = ($is_in_habits || $is_in_metas || $is_in_usuarios) ? '../' : '';
?>

<div class="sidebar">
    <!-- Sección del usuario -->
    <div class="user-section">
        <div class="user-info">
            <a href="<?php echo $base_path; ?>usuarios/editar_usuario.php">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
            </a>
        </div>
        <div class="separator"></div>
    </div>

    <!-- Sección de Inicio -->
        <nav class="menu-nav">
            <a href="<?php echo $base_path; ?>index.php" class="menu-item">
                <i class="fas fa-home"></i>
                <span>Inicio</span>
            </a>
        </nav>

    <!-- Sección de Metas -->
    <div class="menu-section">
        <div class="section-title">Metas</div>
        <nav class="menu-nav">
            <a href="<?php echo $base_path; ?>metas/resumen_metas.php" class="menu-item">
                <i class="fas fa-bullseye"></i>
                <span>Resumen de Metas</span>
            </a>
            <a href="<?php echo $base_path; ?>metas/administrar_metas.php" class="menu-item">
                <i class="fas fa-sync-alt"></i>
                <span>Administrar Metas</span>
            </a>
        </nav>
    </div>

    <!-- Sección de Hábitos -->
    <div class="menu-section">
        <div class="section-title">Hábitos</div>
        <nav class="menu-nav">
            <a href="<?php echo $base_path; ?>habitos/habitos.php" class="menu-item">
                <i class="fas fa-bullseye"></i>
                <span>Resumen de Hábitos</span>
            </a>
            <a href="<?php echo $base_path; ?>habitos/administrar_habitos.php" class="menu-item">
                <i class="fas fa-sync-alt"></i>
                <span>Administrar Hábitos</span>
            </a>
            <a href="<?php echo $base_path; ?>habitos/historial_habitos.php" class="menu-item">
                <i class="fas fa-history"></i>
                <span>Historial de Hábitos</span>
            </a>
        </nav>
    </div>

    <!-- Botón de cerrar sesión -->
    <div class="logout-section">
        <a href="/PROWEB_BS/PF_BHSR/includes/cerrar_sesion.php" class="logout-button">
            <i class="fas fa-sign-out-alt"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</div> 