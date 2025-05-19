<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión a la base de datos
require_once '../../includes/conexion.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../index.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Función para actualizar el estado de las metas
function actualizarEstadoMeta($conn, $id_meta) {
    try {
        // Obtener el total de hábitos y los completados
        $query = "SELECT 
                    COUNT(*) as total_habitos,
                    SUM(CASE WHEN estatus_habito = 2 THEN 1 ELSE 0 END) as habitos_cumplidos,
                    SUM(CASE WHEN estatus_habito = 3 THEN 1 ELSE 0 END) as habitos_cancelados
                FROM registro_habito 
                WHERE id_meta = :id_meta";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id_meta', $id_meta);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Determinar el estado de la meta
        $nuevo_estado = 1; // Por defecto, activo
        if ($resultado['habitos_cancelados'] > 0) {
            $nuevo_estado = 3; // Si hay hábitos cancelados, la meta se cancela
        } elseif ($resultado['habitos_cumplidos'] >= $resultado['total_habitos'] && $resultado['total_habitos'] > 0) {
            $nuevo_estado = 2; // Si todos los hábitos están completados, la meta se completa
        }

        // Actualizar la meta con los hábitos cumplidos y el nuevo estado
        $query = "UPDATE meta 
                SET habitos_cumplidos = :habitos_cumplidos,
                    estatus_meta = :nuevo_estado
                WHERE id_meta = :id_meta";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':habitos_cumplidos', $resultado['habitos_cumplidos']);
        $stmt->bindParam(':nuevo_estado', $nuevo_estado);
        $stmt->bindParam(':id_meta', $id_meta);
        $stmt->execute();

        return $resultado;
    } catch (PDOException $e) {
        return false;
    }
}

// Obtener y actualizar todas las metas del usuario
try {
    $query = "SELECT m.id_meta, m.descripcion, m.fecha_inicio, m.fecha_fin, m.estatus_meta,
            GROUP_CONCAT(h.nombre ORDER BY h.nombre SEPARATOR ', ') as habitos_nombres,
            COUNT(rh.id_registro) as total_habitos,
            SUM(CASE WHEN rh.estatus_habito = 2 THEN 1 ELSE 0 END) as habitos_cumplidos
            FROM meta m
            LEFT JOIN registro_habito rh ON m.id_meta = rh.id_meta
            LEFT JOIN habito h ON rh.id_habito = h.id_habito
            WHERE m.id_usuario = :id_usuario 
            AND m.estatus_meta = 1
            GROUP BY m.id_meta, m.descripcion, m.fecha_inicio, m.fecha_fin, m.estatus_meta
            ORDER BY m.fecha_inicio DESC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $metas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Actualizar el estado de cada meta
    foreach ($metas as &$meta) {
        $progreso = actualizarEstadoMeta($conn, $meta['id_meta']);
        if ($progreso) {
            $meta['habitos_cumplidos'] = $progreso['habitos_cumplidos'];
        }
    }

} catch (PDOException $e) {
    $_SESSION['error'] = 'Error al obtener los datos de las metas: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTrack - Resumen de Metas</title>
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
            <h1>Resumen de Metas</h1>
            <p class="subtitle">Visualiza el progreso de tus metas personales</p>
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

        <?php if (empty($metas)): ?>
            <div class="notification is-info is-light">
                <p>No tienes metas creadas aún. ¡Comienza creando tu primera meta!</p>
                <a href="administrar_metas.php" class="button is-info mt-3">
                    <span class="icon">
                        <i class="fas fa-plus"></i>
                    </span>
                    <span>Crear Meta</span>
                </a>
            </div>
        <?php else: ?>
            <div class="columns is-multiline">
                <?php foreach ($metas as $meta): ?>
                    <?php 
                    $porcentaje = $meta['total_habitos'] > 0 
                        ? round(($meta['habitos_cumplidos'] / $meta['total_habitos']) * 100) 
                        : 0;
                    
                    // Determinar el color de la barra de progreso según el estado
                    $color_clase = '';
                    switch ($meta['estatus_meta']) {
                        case 1: // Activo
                            $color_clase = 'is-link';
                            break;
                        case 2: // Completado
                            $color_clase = 'is-success';
                            break;
                        case 3: // Cancelado
                            $color_clase = 'is-danger';
                            break;
                    }
                    ?>
                    <div class="column is-half">
                        <div class="card">
                            <div class="card-content">
                                <div class="content">
                                    <h3 class="title is-4"><?php echo htmlspecialchars($meta['descripcion']); ?></h3>
                                    
                                    <div class="mb-4">
                                        <p class="has-text-grey">
                                            <span class="icon">
                                                <i class="fas fa-calendar"></i>
                                            </span>
                                            Inicio: <?php echo date('d/m/Y', strtotime($meta['fecha_inicio'])); ?>
                                        </p>
                                        <p class="has-text-grey">
                                            <span class="icon">
                                                <i class="fas fa-calendar-check"></i>
                                            </span>
                                            Fin: <?php echo date('d/m/Y', strtotime($meta['fecha_fin'])); ?>
                                        </p>
                                    </div>

                                    <div class="mb-4">
                                        <p class="has-text-weight-semibold">Hábitos:</p>
                                        <p class="is-size-7 has-text-grey">
                                            <?php echo htmlspecialchars($meta['habitos_nombres'] ?? 'Ningún hábito asociado'); ?>
                                        </p>
                                    </div>

                                    <div class="mb-4">
                                        <p class="has-text-weight-semibold">Progreso</p>
                                        <progress class="progress <?php echo $color_clase; ?>" 
                                                value="<?php echo $porcentaje; ?>" 
                                                max="100">
                                            <?php echo $porcentaje; ?>%
                                        </progress>
                                        <p class="is-size-7 has-text-grey">
                                            <?php echo $meta['habitos_cumplidos']; ?>/<?php echo $meta['total_habitos']; ?> hábitos completados
                                            (<?php echo $porcentaje; ?>%)
                                        </p>
                                    </div>

                                    <div class="tags">
                                        <?php
                                        $estado = '';
                                        $clase = '';
                                        switch ($meta['estatus_meta']) {
                                            case 1:
                                                $estado = 'Activo';
                                                $clase = 'is-success';
                                                break;
                                            case 2:
                                                $estado = 'Completado';
                                                $clase = 'is-info';
                                                break;
                                            case 3:
                                                $estado = 'Cancelado';
                                                $clase = 'is-danger';
                                                break;
                                        }
                                        ?>
                                        <span class="tag <?php echo $clase; ?>"><?php echo $estado; ?></span>
                                    </div>
                                </div>
                            </div>
                            <footer class="card-footer">
                                <a href="administrar_metas.php" class="card-footer-item">
                                    <span class="icon">
                                        <i class="fas fa-edit"></i>
                                    </span>
                                    <span>Gestionar Meta</span>
                                </a>
                            </footer>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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