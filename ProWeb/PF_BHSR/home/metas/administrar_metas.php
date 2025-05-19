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

// Obtener hábitos activos del usuario que no están asociados a una meta
try {
    $id_meta = isset($_GET['id']) ? $_GET['id'] : null;
    
    $query = "SELECT rh.id_registro, h.nombre, h.descripcion, c.nombre as categoria
              FROM registro_habito rh
              JOIN habito h ON rh.id_habito = h.id_habito
              JOIN categoria c ON h.id_categoria = c.id_categoria
              WHERE rh.id_usuario = :id_usuario 
              AND rh.estatus_habito = 1 
              AND (rh.id_meta IS NULL OR rh.id_meta = :id_meta)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->bindParam(':id_meta', $id_meta);
    $stmt->execute();
    $habitos_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener metas actuales del usuario
    $query = "SELECT m.*, 
              (SELECT COUNT(*) FROM registro_habito WHERE id_meta = m.id_meta) as total_habitos,
              (SELECT COUNT(*) FROM registro_habito WHERE id_meta = m.id_meta AND frecuencia_actual >= frecuencia_objetivo) as habitos_cumplidos
              FROM meta m
              WHERE m.id_usuario = :id_usuario
              ORDER BY m.fecha_inicio DESC";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario);
    $stmt->execute();
    $metas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error'] = 'Error al obtener los datos';
}

// Procesar el formulario de creación de meta
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    if (empty($_POST['habitos']) || empty($_POST['fecha_fin'])) {
                        throw new Exception('Por favor, seleccione al menos un hábito y una fecha de finalización');
                    }

                    // Crear la meta
                    $query = "INSERT INTO meta (id_usuario, descripcion, habitos_cumplidos, estatus_meta, fecha_inicio, fecha_fin) 
                             VALUES (:id_usuario, :descripcion, 0, 1, NOW(), :fecha_fin)";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':id_usuario', $id_usuario);
                    $stmt->bindParam(':descripcion', $_POST['descripcion']);
                    $stmt->bindParam(':fecha_fin', $_POST['fecha_fin']);
                    $stmt->execute();
                    $id_meta = $conn->lastInsertId();

                    // Actualizar los hábitos seleccionados con el id_meta
                    $query = "UPDATE registro_habito SET id_meta = :id_meta 
                             WHERE id_registro IN (" . implode(',', $_POST['habitos']) . ")";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':id_meta', $id_meta);
                    $stmt->execute();

                    $_SESSION['success'] = 'Meta creada correctamente';
                    break;

                case 'update':
                    if (empty($_POST['id_meta']) || empty($_POST['fecha_fin'])) {
                        throw new Exception('Datos incompletos para actualizar la meta');
                    }

                    $query = "UPDATE meta SET 
                             descripcion = :descripcion,
                             fecha_fin = :fecha_fin
                             WHERE id_meta = :id_meta AND id_usuario = :id_usuario";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':descripcion', $_POST['descripcion']);
                    $stmt->bindParam(':fecha_fin', $_POST['fecha_fin']);
                    $stmt->bindParam(':id_meta', $_POST['id_meta']);
                    $stmt->bindParam(':id_usuario', $id_usuario);
                    $stmt->execute();

                    $_SESSION['success'] = 'Meta actualizada correctamente';
                    break;

                case 'delete':
                    if (empty($_POST['id_meta'])) {
                        throw new Exception('ID de meta no proporcionado');
                    }

                    // Primero actualizar los hábitos para quitar la asociación con la meta
                    $query = "UPDATE registro_habito SET id_meta = NULL 
                             WHERE id_meta = :id_meta";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':id_meta', $_POST['id_meta']);
                    $stmt->execute();

                    // Luego eliminar la meta
                    $query = "DELETE FROM meta 
                             WHERE id_meta = :id_meta AND id_usuario = :id_usuario";
                    $stmt = $conn->prepare($query);
                    $stmt->bindParam(':id_meta', $_POST['id_meta']);
                    $stmt->bindParam(':id_usuario', $id_usuario);
                    $stmt->execute();

                    $_SESSION['success'] = 'Meta eliminada correctamente';
                    break;
            }
        }
        header('Location: administrar_metas.php');
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTrack - Administrar Metas</title>
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
            <h1>Administrar Metas</h1>
            <p class="subtitle">Crea y gestiona tus metas personales</p>
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

        <?php if (isset($_SESSION['success'])): ?>
            <div class="notification is-success is-light">
                <button class="delete"></button>
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para crear nueva meta -->
        <div class="card mb-4">
            <div class="card-header">
                <h2 class="card-header-title">Crear Nueva Meta</h2>
            </div>
            <div class="card-content">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="field">
                        <label class="label">Descripción de la Meta</label>
                        <div class="control">
                            <textarea class="textarea" name="descripcion" required></textarea>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Hábitos a Incluir</label>
                        <div class="control">
                            <div class="select is-multiple">
                                <select multiple size="5" name="habitos[]" required>
                                    <?php foreach ($habitos_disponibles as $habito): ?>
                                        <option value="<?php echo $habito['id_registro']; ?>">
                                            <?php echo htmlspecialchars($habito['nombre'] . ' (' . $habito['categoria'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <p class="help">Mantén presionado Ctrl (Cmd en Mac) para seleccionar múltiples hábitos</p>
                    </div>

                    <div class="field">
                        <label class="label">Fecha de Finalización</label>
                        <div class="control">
                            <input class="input" type="date" name="fecha_fin" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="field">
                        <div class="control">
                            <button type="submit" class="button is-primary">
                                <span class="icon">
                                    <i class="fas fa-plus"></i>
                                </span>
                                <span>Crear Meta</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de metas actuales -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-header-title">Mis Metas</h2>
            </div>
            <div class="card-content">
                <?php if (empty($metas)): ?>
                    <p class="has-text-centered">No tienes metas creadas aún.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table is-fullwidth is-striped">
                            <thead>
                                <tr>
                                    <th>Descripción</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Progreso</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($metas as $meta): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($meta['descripcion']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($meta['fecha_inicio'])); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($meta['fecha_fin'])); ?></td>
                                        <td>
                                            <?php 
                                            $porcentaje = $meta['total_habitos'] > 0 
                                                ? round(($meta['habitos_cumplidos'] / $meta['total_habitos']) * 100) 
                                                : 0;
                                            ?>
                                            <progress class="progress is-primary" value="<?php echo $porcentaje; ?>" max="100">
                                                <?php echo $porcentaje; ?>%
                                            </progress>
                                            <span class="is-size-7">
                                                <?php echo $meta['habitos_cumplidos']; ?>/<?php echo $meta['total_habitos']; ?> hábitos
                                            </span>
                                        </td>
                                        <td>
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
                                                    $estado = 'Suspendido';
                                                    $clase = 'is-warning';
                                                    break;
                                                case 4:
                                                    $estado = 'Cancelado';
                                                    $clase = 'is-danger';
                                                    break;
                                            }
                                            ?>
                                            <span class="tag <?php echo $clase; ?>"><?php echo $estado; ?></span>
                                        </td>
                                        <td>
                                            <div class="buttons are-small">
                                                <button class="button is-info" onclick="editarMeta(<?php echo htmlspecialchars(json_encode($meta)); ?>)">
                                                    <span class="icon">
                                                        <i class="fas fa-edit"></i>
                                                    </span>
                                                </button>
                                                <button class="button is-danger" onclick="eliminarMeta(<?php echo $meta['id_meta']; ?>)">
                                                    <span class="icon">
                                                        <i class="fas fa-trash"></i>
                                                    </span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal para editar meta -->
    <div class="modal" id="modal-editar">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Editar Meta</p>
                <button class="delete" aria-label="close"></button>
            </header>
            <section class="modal-card-body">
                <form id="form-editar" method="POST" action="">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id_meta" id="edit-id-meta">
                    
                    <div class="field">
                        <label class="label">Descripción</label>
                        <div class="control">
                            <textarea class="textarea" name="descripcion" id="edit-descripcion" required></textarea>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Fecha de Finalización</label>
                        <div class="control">
                            <input class="input" type="date" name="fecha_fin" id="edit-fecha-fin" required>
                        </div>
                    </div>
                </form>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success" form="form-editar">Guardar Cambios</button>
                <button class="button" onclick="cerrarModal()">Cancelar</button>
            </footer>
        </div>
    </div>

    <script>
        // Función para editar meta
        function editarMeta(meta) {
            document.getElementById('edit-id-meta').value = meta.id_meta;
            document.getElementById('edit-descripcion').value = meta.descripcion;
            document.getElementById('edit-fecha-fin').value = meta.fecha_fin.split(' ')[0];
            document.getElementById('modal-editar').classList.add('is-active');
        }

        // Función para eliminar meta
        function eliminarMeta(idMeta) {
            if (confirm('¿Estás seguro de que deseas eliminar esta meta?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id_meta';
                idInput.value = idMeta;

                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Función para cerrar el modal
        function cerrarModal() {
            document.getElementById('modal-editar').classList.remove('is-active');
        }

        // Cerrar notificaciones al hacer clic en el botón de cerrar
        document.addEventListener('DOMContentLoaded', () => {
            (document.querySelectorAll('.notification .delete') || []).forEach(($delete) => {
                $delete.addEventListener('click', () => {
                    $delete.parentNode.remove();
                });
            });

            // Cerrar modal al hacer clic en el fondo o en el botón de cerrar
            document.querySelector('.modal-background').addEventListener('click', cerrarModal);
            document.querySelector('.modal .delete').addEventListener('click', cerrarModal);
        });
    </script>
</body>
</html> 