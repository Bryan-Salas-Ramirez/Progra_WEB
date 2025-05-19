<?php
// Iniciar sesión y verificar autenticación
session_start();
require_once '../../includes/verificar_sesion.php';
require_once '../../includes/conexion.php';

// Obtener el filtro de lapso si existe
$filtro_lapso = isset($_GET['lapso']) ? $_GET['lapso'] : '';

// Construir la consulta base
$query = "SELECT rh.*, h.nombre as nombre_habito, h.descripcion, 
        c.nombre as categoria, l.nombre as lapso_nombre,
        ea.nombre as estado_nombre
        FROM registro_habito rh
        JOIN habito h ON rh.id_habito = h.id_habito
        JOIN categoria c ON h.id_categoria = c.id_categoria
        JOIN lapso l ON rh.id_lapso = l.id_lapso
        JOIN estatus_actividad ea ON rh.estatus_habito = ea.id_estatus_ac
        WHERE rh.id_usuario = :id_usuario 
        AND rh.estatus_habito IN (2, 3)"; // 2 = Completado, 3 = Cancelado

// Agregar filtro de lapso si se especificó
if (!empty($filtro_lapso)) {
    $query .= " AND l.id_lapso = :id_lapso";
}

$query .= " ORDER BY rh.fecha_registro DESC";

$stmt = $conn->prepare($query);
$stmt->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT);

if (!empty($filtro_lapso)) {
    $stmt->bindParam(':id_lapso', $filtro_lapso, PDO::PARAM_INT);
}

$stmt->execute();
$habitos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener lapsos para el filtro
$query_lapsos = "SELECT * FROM lapso ORDER BY nombre";
$stmt_lapsos = $conn->prepare($query_lapsos);
$stmt_lapsos->execute();
$lapsos = $stmt_lapsos->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTrack - Historial de Hábitos</title>
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
            <h1>Historial de Hábitos</h1>
            <p class="subtitle">Visualiza tus hábitos completados y cancelados</p>
        </div>

        <!-- Filtros -->
        <div class="filters-section">
            <div class="field">
                <label class="label">Filtrar por frecuencia</label>
                <div class="control">
                    <div class="select">
                        <select onchange="window.location.href='historial_habitos.php?lapso=' + this.value">
                            <option value="">Todos los lapsos</option>
                            <?php foreach ($lapsos as $lapso): ?>
                                <option value="<?php echo $lapso['id_lapso']; ?>" 
                                        <?php echo $filtro_lapso == $lapso['id_lapso'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($lapso['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de hábitos -->
        <div class="habits-list">
            <?php if (empty($habitos)): ?>
                <div class="no-habits-message">
                    <h2>No hay hábitos en el historial</h2>
                    <p>No se encontraron hábitos completados o cancelados</p>
                </div>
            <?php else: ?>
                <?php foreach ($habitos as $habito): ?>
                    <div class="habit-card">
                        <div class="habit-content">
                            <div class="habit-info">
                                <h3 class="habit-name"><?php echo htmlspecialchars($habito['nombre_habito']); ?></h3>
                                <p class="habit-description"><?php echo htmlspecialchars($habito['descripcion']); ?></p>
                                <div class="habit-details">
                                    <span class="tag is-info">
                                        <?php echo htmlspecialchars($habito['lapso_nombre']); ?>
                                    </span>
                                    <span class="tag is-warning">
                                        Objetivo: <?php echo $habito['frecuencia_objetivo']; ?> veces
                                    </span>
                                    <span class="tag is-success">
                                        Alcanzado: <?php echo $habito['frecuencia_actual']; ?> veces
                                    </span>
                                    <span class="tag <?php echo $habito['estado_nombre'] === 'Completado' ? 'is-success' : 'is-danger'; ?>">
                                        <?php echo htmlspecialchars($habito['estado_nombre']); ?>
                                    </span>
                                    <span class="tag is-light">
                                        Fecha: <?php echo date('d/m/Y', strtotime($habito['fecha_registro'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 