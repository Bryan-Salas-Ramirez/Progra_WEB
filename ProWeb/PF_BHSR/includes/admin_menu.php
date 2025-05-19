<?php
// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 1) {
    header('Location: ../login.php');
    exit();
}

$current_path = $_SERVER['PHP_SELF'];
?>
<aside class="menu">
    <p class="menu-label has-text-white">
        Panel de Administración
    </p>
    <ul class="menu-list">
        <li>
            <a href="../admin_dashboard.php" class="<?php echo strpos($current_path, 'admin_dashboard.php') !== false ? 'is-active' : ''; ?> has-text-white">
                <span class="icon">
                    <i class="fas fa-tachometer-alt"></i>
                </span>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="../home/usuarios/gestionar_usuarios.php" class="<?php echo strpos($current_path, 'gestionar_usuarios.php') !== false ? 'is-active' : ''; ?> has-text-white">
                <span class="icon">
                    <i class="fas fa-users-cog"></i>
                </span>
                <span>Gestionar Usuarios</span>
            </a>
        </li>
        <li>
            <a href="../../includes/cerrar_sesion.php" class="has-text-white">
                <span class="icon">
                    <i class="fas fa-sign-out-alt"></i>
                </span>
                <span>Cerrar Sesión</span>
            </a>
        </li>
    </ul>
</aside> 