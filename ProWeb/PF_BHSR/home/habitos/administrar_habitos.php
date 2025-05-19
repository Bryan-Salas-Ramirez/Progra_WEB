<?php
// Iniciar sesión y verificar autenticación
session_start();
require_once '../../includes/verificar_sesion.php';
require_once '../../includes/conexion.php';

// Obtener categorías
$query_categorias = "SELECT * FROM categoria ORDER BY nombre";
$stmt_categorias = $conn->prepare($query_categorias);
$stmt_categorias->execute();
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

// Obtener estados de actividad
$query_estados = "SELECT * FROM estatus_actividad ORDER BY nombre";
$stmt_estados = $conn->prepare($query_estados);
$stmt_estados->execute();
$estados = $stmt_estados->fetchAll(PDO::FETCH_ASSOC);

// Obtener lapsos
$query_lapsos = "SELECT * FROM lapso ORDER BY nombre";
$stmt_lapsos = $conn->prepare($query_lapsos);
$stmt_lapsos->execute();
$lapsos = $stmt_lapsos->fetchAll(PDO::FETCH_ASSOC);

// Obtener hábitos del usuario agrupados por categoría
$id_usuario = $_SESSION['id_usuario'];
$query_habitos = "SELECT rh.*, h.nombre as nombre_habito, h.descripcion, 
                c.nombre as categoria, l.nombre as lapso_nombre,
                ea.nombre as estado_nombre
                FROM registro_habito rh
                JOIN habito h ON rh.id_habito = h.id_habito
                JOIN categoria c ON h.id_categoria = c.id_categoria
                JOIN lapso l ON rh.id_lapso = l.id_lapso
                JOIN estatus_actividad ea ON rh.estatus_habito = ea.id_estatus_ac
                WHERE rh.id_usuario = :id_usuario AND estatus_habito = 1
                ORDER BY c.nombre, h.nombre";
$stmt_habitos = $conn->prepare($query_habitos);
$stmt_habitos->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
$stmt_habitos->execute();
$habitos_usuario = $stmt_habitos->fetchAll(PDO::FETCH_ASSOC);

// Agrupar hábitos por categoría
$habitos_por_categoria = [];
foreach ($habitos_usuario as $habito) {
    $categoria = $habito['categoria'];
    if (!isset($habitos_por_categoria[$categoria])) {
        $habitos_por_categoria[$categoria] = [];
    }
    $habitos_por_categoria[$categoria][] = $habito;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTrack - Administrar Hábitos</title>
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
            <h1>Administrar Hábitos</h1>
            <p class="subtitle">Gestiona tus hábitos y establece tus metas</p>
        </div>

        <!-- Formulario para agregar nuevo hábito -->
        <div class="card">
            <div class="card-content">
                <h2 class="title is-4">Agregar Nuevo Hábito</h2>
                <form action="agregar_habito.php" method="POST" class="form">
                    <div class="field">
                        <label class="label">Categoría</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="categoria" name="categoria" required>
                                    <option value="">Selecciona una categoría</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo $categoria['id_categoria']; ?>">
                                            <?php echo htmlspecialchars($categoria['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Hábito</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="habito" name="habito" required disabled>
                                    <option value="">Primero selecciona una categoría</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Frecuencia</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="lapso" name="lapso" required>
                                    <option value="">Selecciona la frecuencia</option>
                                    <?php foreach ($lapsos as $lapso): ?>
                                        <option value="<?php echo $lapso['id_lapso']; ?>">
                                            <?php echo htmlspecialchars($lapso['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Cantidad de veces</label>
                        <div class="control">
                            <input class="input" type="number" name="frecuencia" min="1" required>
                        </div>
                    </div>

                    <div class="field">
                        <div class="control">
                            <button type="submit" class="button is-primary is-fullwidth">
                                <span class="icon">
                                    <i class="fas fa-plus"></i>
                                </span>
                                <span>Agregar Hábito</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de hábitos por categoría -->
        <div class="habits-list mt-6">
            <?php foreach ($habitos_por_categoria as $categoria => $habitos): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-header-title category-title <?php echo strtolower($categoria); ?>">
                            <?php echo htmlspecialchars($categoria); ?>
                        </h3>
                    </div>
                    <div class="card-content">
                        <?php foreach ($habitos as $habito): ?>
                            <div class="habit-item">
                                <div class="habit-content">
                                    <div class="habit-info">
                                        <h4 class="habit-name"><?php echo htmlspecialchars($habito['nombre_habito']); ?></h4>
                                        <p class="habit-description"><?php echo htmlspecialchars($habito['descripcion']); ?></p>
                                        <div class="habit-details">
                                            <span class="tag is-info">
                                                <?php echo htmlspecialchars($habito['lapso_nombre']); ?>
                                            </span>
                                            <span class="tag is-warning">
                                                Objetivo: <?php echo $habito['frecuencia_objetivo']; ?> veces
                                            </span>
                                            <span class="tag is-success">
                                                Actual: <?php echo $habito['frecuencia_actual']; ?> veces
                                            </span>
                                            <span class="tag <?php echo $habito['estado_nombre'] === 'Activo' ? 'is-success' : 'is-danger'; ?>">
                                                <?php echo htmlspecialchars($habito['estado_nombre']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="habit-actions">
                                        <button class="button is-small is-info" onclick="editarHabito(<?php echo htmlspecialchars(json_encode($habito)); ?>)">
                                            <span class="icon">
                                                <i class="fas fa-edit"></i>
                                            </span>
                                            <span>Editar</span>
                                        </button>
                                        <button class="button is-small is-danger" onclick="eliminarHabito(<?php echo $habito['id_habito']; ?>)">
                                            <span class="icon">
                                                <i class="fas fa-trash"></i>
                                            </span>
                                            <span>Eliminar</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal de edición de hábito -->
    <div class="modal" id="editarHabitoModal">
        <div class="modal-background"></div>
        <div class="modal-card">
            <header class="modal-card-head">
                <p class="modal-card-title">Editar Hábito</p>
                <button class="delete" aria-label="close" onclick="cerrarModal()"></button>
            </header>
            <section class="modal-card-body">
                <form id="editarHabitoForm">
                    <input type="hidden" id="edit_id_registro" name="id_registro">
                    <input type="hidden" id="edit_id_habito" name="id_habito">
                    
                    <div class="field">
                        <label class="label">Hábito</label>
                        <div class="control">
                            <input class="input" type="text" id="edit_nombre_habito" readonly>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Frecuencia</label>
                        <div class="control">
                            <div class="select is-fullwidth">
                                <select id="edit_lapso" name="lapso" required>
                                    <?php foreach ($lapsos as $lapso): ?>
                                        <option value="<?php echo $lapso['id_lapso']; ?>">
                                            <?php echo htmlspecialchars($lapso['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Cantidad de veces</label>
                        <div class="control">
                            <input class="input" type="number" id="edit_frecuencia" name="frecuencia" min="1" required>
                        </div>
                    </div>
                </form>
            </section>
            <footer class="modal-card-foot">
                <button class="button is-success" onclick="guardarCambios()">Guardar cambios</button>
                <button class="button" onclick="cerrarModal()">Cancelar</button>
            </footer>
        </div>
    </div>

    <script>
        // Cargar hábitos por categoría
        document.getElementById('categoria').addEventListener('change', function() {
            const categoriaId = this.value;
            const habitoSelect = document.getElementById('habito');
            
            if (categoriaId) {
                fetch(`get_habitos.php?categoria=${categoriaId}`)
                    .then(response => response.json())
                    .then(data => {
                        habitoSelect.innerHTML = '<option value="">Selecciona un hábito</option>';
                        data.forEach(habito => {
                            habitoSelect.innerHTML += `
                                <option value="${habito.id_habito}">
                                    ${habito.nombre}
                                </option>
                            `;
                        });
                        habitoSelect.disabled = false;
                    });
            } else {
                habitoSelect.innerHTML = '<option value="">Primero selecciona una categoría</option>';
                habitoSelect.disabled = true;
            }
        });

        function editarHabito(habito) {
            document.getElementById('edit_id_registro').value = habito.id_registro;
            document.getElementById('edit_id_habito').value = habito.id_habito;
            document.getElementById('edit_nombre_habito').value = habito.nombre_habito;
            document.getElementById('edit_lapso').value = habito.id_lapso;
            document.getElementById('edit_frecuencia').value = habito.frecuencia_objetivo;
            
            document.getElementById('editarHabitoModal').classList.add('is-active');
        }

        function cerrarModal() {
            document.getElementById('editarHabitoModal').classList.remove('is-active');
        }

        function guardarCambios() {
            if (confirm('¿Estás seguro de que deseas modificar este hábito? Todo el progreso actual se perderá.')) {
                const formData = new FormData(document.getElementById('editarHabitoForm'));
                
                fetch('actualizar_habito.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Hábito actualizado correctamente');
                        window.location.reload();
                    } else {
                        alert('Error al actualizar el hábito: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al actualizar el hábito');
                });
            }
        }

        function eliminarHabito(habitoId) {
            if (confirm('¿Estás seguro de que deseas eliminar este hábito? Esta acción no se puede deshacer.')) {
                fetch('actualizar_estado_habito.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        id_habito: habitoId,
                        nuevo_estado: 4 // Estado cancelado
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Hábito eliminado correctamente');
                        window.location.reload();
                    } else {
                        alert('Error al eliminar el hábito: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el hábito');
                });
            }
        }
    </script>
</body>
</html> 