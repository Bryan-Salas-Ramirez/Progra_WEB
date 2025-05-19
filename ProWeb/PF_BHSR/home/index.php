<?php
// Iniciar sesión y verificar autenticación
session_start();
require_once '../includes/verificar_sesion.php';
require_once '../includes/conexion.php';

$id_usuario = $_SESSION['id_usuario'];

// Obtener resumen de metas
try {
    // Total de metas activas
    $query = "SELECT COUNT(*) as total_activas 
              FROM meta 
              WHERE id_usuario = :id_usuario AND estatus_meta = 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $metas_activas = $stmt->fetch(PDO::FETCH_ASSOC)['total_activas'];

    // Total de metas completadas
    $query = "SELECT COUNT(*) as total_completadas 
              FROM meta 
              WHERE id_usuario = :id_usuario AND estatus_meta = 2";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $metas_completadas = $stmt->fetch(PDO::FETCH_ASSOC)['total_completadas'];

    // Progreso general (promedio de todas las metas activas)
    $query = "SELECT 
                AVG(
                    CASE 
                        WHEN m.total_habitos > 0 
                        THEN (m.habitos_cumplidos * 100.0 / m.total_habitos)
                        ELSE 0 
                    END
                ) as progreso_general
              FROM (
                SELECT m.id_meta,
                       COUNT(rh.id_registro) as total_habitos,
                       SUM(CASE WHEN rh.estatus_habito = 2 THEN 1 ELSE 0 END) as habitos_cumplidos
                FROM meta m
                LEFT JOIN registro_habito rh ON m.id_meta = rh.id_meta
                WHERE m.id_usuario = :id_usuario AND m.estatus_meta = 1
                GROUP BY m.id_meta
              ) m";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $progreso_general = round($stmt->fetch(PDO::FETCH_ASSOC)['progreso_general'] ?? 0);

    // Metas activas con progreso
    $query = "SELECT m.*, 
              COUNT(rh.id_registro) as total_habitos,
              SUM(CASE WHEN rh.estatus_habito = 2 THEN 1 ELSE 0 END) as habitos_cumplidos
              FROM meta m
              LEFT JOIN registro_habito rh ON m.id_meta = rh.id_meta
              WHERE m.id_usuario = :id_usuario AND m.estatus_meta = 1
              GROUP BY m.id_meta
              ORDER BY m.fecha_inicio DESC
              LIMIT 2";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $metas_activas_lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Hábitos activos del día
    $query = "SELECT rh.*, h.nombre, h.descripcion
              FROM registro_habito rh
              JOIN habito h ON rh.id_habito = h.id_habito
              WHERE rh.id_usuario = :id_usuario 
              AND rh.estatus_habito = 1
              ORDER BY h.nombre
              LIMIT 3";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $habitos_activos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Próximos vencimientos de metas
    $query = "SELECT m.*, 
              DATEDIFF(m.fecha_fin, CURDATE()) as dias_restantes
              FROM meta m
              WHERE m.id_usuario = :id_usuario 
              AND m.estatus_meta = 1
              AND m.fecha_fin >= CURDATE()
              ORDER BY m.fecha_fin ASC
              LIMIT 2";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $proximos_vencimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = 'Error al obtener los datos del dashboard';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTrack - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body class="dashboard-page">
    <?php
    // Incluir los componentes necesarios
    include '../includes/header.php';
    include '../includes/menu.php';
    ?>

    <div class="main-content">
        <!-- Sección de bienvenida -->
        <div class="welcome-section">
            <h1>¡Bienvenide!</h1>
            <p class="subtitle">Aquí podrás ver el resumen de tus hábitos y metas</p>
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

        <!-- Grid de tarjetas de resumen -->
        <div class="dashboard-grid">
            <!-- Tarjeta de Resumen General -->
            <div class="dashboard-card summary-card">
                <div class="card-header">
                    <h2>Resumen General</h2>
                </div>
                <div class="card-content">
                    <div class="summary-item">
                        <i class="fas fa-bullseye"></i>
                        <div class="summary-info">
                            <span class="summary-value"><?php echo $metas_activas; ?></span>
                            <span class="summary-label">Metas Activas</span>
                        </div>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-check-circle"></i>
                        <div class="summary-info">
                            <span class="summary-value"><?php echo $metas_completadas; ?></span>
                            <span class="summary-label">Metas Completadas</span>
                        </div>
                    </div>
                    <div class="summary-item">
                        <i class="fas fa-clock"></i>
                        <div class="summary-info">
                            <span class="summary-value"><?php echo $progreso_general; ?>%</span>
                            <span class="summary-label">Progreso General</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta de Metas Activas -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Metas Activas</h2>
                    <a href="metas/administrar_metas.php" class="view-all">Ver todas</a>
                </div>
                <div class="card-content">
                    <?php if (empty($metas_activas_lista)): ?>
                        <p class="has-text-centered">No tienes metas activas</p>
                    <?php else: ?>
                        <?php foreach ($metas_activas_lista as $meta): 
                            $porcentaje = $meta['total_habitos'] > 0 
                                ? round(($meta['habitos_cumplidos'] / $meta['total_habitos']) * 100) 
                                : 0;
                        ?>
                            <div class="goal-item">
                                <div class="goal-info">
                                    <h3><?php echo htmlspecialchars($meta['descripcion']); ?></h3>
                                    <p><?php echo $meta['habitos_cumplidos']; ?>/<?php echo $meta['total_habitos']; ?> hábitos completados</p>
                                </div>
                                <div class="progress-bar">
                                    <progress class="progress is-link" value="<?php echo $porcentaje; ?>" max="100">
                                        <?php echo $porcentaje; ?>%
                                    </progress>
                                </div>
                                <span class="progress-text"><?php echo $porcentaje; ?>%</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tarjeta de Hábitos -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Hábitos del Día</h2>
                    <a href="habitos/administrar_habitos.php" class="view-all">Ver todos</a>
                </div>
                <div class="card-content">
                    <?php if (empty($habitos_activos)): ?>
                        <p class="has-text-centered">No tienes hábitos activos</p>
                    <?php else: ?>
                        <?php foreach ($habitos_activos as $habito): ?>
                            <div class="habit-item">
                                <div class="habit-info">
                                    <h3><?php echo htmlspecialchars($habito['nombre']); ?></h3>
                                    <p><?php echo htmlspecialchars($habito['descripcion']); ?></p>
                                </div>
                                <div class="habit-status <?php echo $habito['estatus_habito'] == 2 ? 'completed' : 'pending'; ?>">
                                    <i class="fas <?php echo $habito['estatus_habito'] == 2 ? 'fa-check' : 'fa-clock'; ?>"></i>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tarjeta de Próximos Vencimientos -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>Próximos Vencimientos</h2>
                </div>
                <div class="card-content">
                    <?php if (empty($proximos_vencimientos)): ?>
                        <p class="has-text-centered">No hay metas próximas a vencer</p>
                    <?php else: ?>
                        <?php foreach ($proximos_vencimientos as $meta): ?>
                            <div class="deadline-item">
                                <div class="deadline-info">
                                    <h3><?php echo htmlspecialchars($meta['descripcion']); ?></h3>
                                    <p>Faltan <?php echo $meta['dias_restantes']; ?> días</p>
                                </div>
                                <div class="deadline-date">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('d/m/Y', strtotime($meta['fecha_fin'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
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
