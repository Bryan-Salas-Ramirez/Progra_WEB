<?php
    // Iniciar sesión y verificar autenticación
    session_start();
    require_once '../../includes/verificar_sesion.php';
    require_once '../../includes/conexion.php';

    // Obtener hábitos activos del usuario
    $id_usuario = $_SESSION['id_usuario'];
    $query = "SELECT h.id_habito, h.nombre, h.descripcion, c.nombre as categoria, 
            rh.frecuencia_objetivo, rh.frecuencia_actual, l.nombre as lapso
            FROM registro_habito rh
            JOIN habito h ON rh.id_habito = h.id_habito
            JOIN categoria c ON h.id_categoria = c.id_categoria
            JOIN lapso l ON rh.id_lapso = l.id_lapso
            WHERE rh.id_usuario = :id_usuario AND rh.estatus_habito = 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $habitos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTrack - Mis Hábitos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="dashboard-page">
    <?php
    include '../../includes/header.php';
    include '../../includes/menu.php';
    ?>

    <div class="main-content">
        <div class="welcome-section">
            <h1>Mis Hábitos</h1>
            <p class="subtitle">Visualiza y gestiona tus hábitos activos</p>
        </div>

        <?php if (empty($habitos)): ?>
            <div class="no-habits-message">
                <h2>¡No tienes hábitos por realizar hoy!</h2>
                <p>Disfruta tu día libre</p>
            </div>
        <?php else: ?>
            <!-- Gráfica de hábitos -->
            <div class="habits-chart-container">
                <canvas id="habitsChart"></canvas>
            </div>

            <!-- Listado de hábitos -->
            <div class="habits-list">
                <?php foreach ($habitos as $habito): ?>
                    <div class="habit-card">
                        <div class="habit-header">
                            <h3 class="habit-name"><?php echo htmlspecialchars($habito['nombre']); ?></h3>
                            <span class="category-tag <?php echo strtolower($habito['categoria']); ?>"><?php echo htmlspecialchars($habito['categoria']); ?></span>
                        </div>
                        <p class="habit-description"><?php echo htmlspecialchars($habito['descripcion']); ?></p>
                        <div class="habit-stats">
                            <div class="stat-item">
                                <span class="stat-label">Frecuencia Objetivo:</span>
                                <span class="stat-value"><?php echo $habito['frecuencia_objetivo'] . ' veces por ' . $habito['lapso']; ?></span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-label">Frecuencia Actual:</span>
                                <span class="stat-value" id="freq-<?php echo $habito['id_habito']; ?>"><?php echo $habito['frecuencia_actual'] . ' veces cumplidas '; ?></span>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress" id="progress-<?php echo $habito['id_habito']; ?>" style="width: <?php echo min(($habito['frecuencia_actual'] / $habito['frecuencia_objetivo']) * 100, 100); ?>%"></div>
                        </div>
                        <div class="habit-actions">
                            <button class="button is-primary increment-button" 
                                    onclick="incrementHabit(<?php echo $habito['id_habito']; ?>, <?php echo $habito['frecuencia_objetivo']; ?>)"
                                    <?php echo ($habito['frecuencia_actual'] >= $habito['frecuencia_objetivo']) ? 'disabled' : ''; ?>>
                                <i class="fas fa-plus"></i>
                                <span><?php echo ($habito['frecuencia_actual'] >= $habito['frecuencia_objetivo']) ? 'Objetivo Alcanzado' : 'Incrementar'; ?></span>
                            </button>
                            <button class="button is-success complete-button" 
                                    onclick="completarHabito(<?php echo $habito['id_habito']; ?>)"
                                    <?php echo ($habito['frecuencia_actual'] < $habito['frecuencia_objetivo']) ? 'disabled' : ''; ?>>
                                <i class="fas fa-check"></i>
                                <span>Completar Hábito</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <script>
                // Configuración de la gráfica
                const ctx = document.getElementById('habitsChart').getContext('2d');
                const habitsData = <?php echo json_encode($habitos); ?>;
                let habitsChart;
                
                function updateChart() {
                    if (habitsChart) {
                        habitsChart.destroy();
                    }
                    
                    habitsChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: habitsData.map(h => h.nombre),
                            datasets: [{
                                label: 'Frecuencia Actual',
                                data: habitsData.map(h => h.frecuencia_actual),
                                backgroundColor: '#4A90E2',
                                borderColor: '#2C3E50',
                                borderWidth: 1
                            }, {
                                label: 'Frecuencia Objetivo',
                                data: habitsData.map(h => h.frecuencia_objetivo),
                                backgroundColor: '#2C3E50',
                                borderColor: '#4A90E2',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Frecuencia'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Hábitos'
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top'
                                },
                                title: {
                                    display: true,
                                    text: 'Progreso de Hábitos',
                                    font: {
                                        size: 16
                                    }
                                }
                            }
                        }
                    });
                }

                function incrementHabit(habitoId, frecuenciaObjetivo) {
                    fetch('incrementar_habito.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id_habito: habitoId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Actualizar la frecuencia actual en el DOM
                            const freqElement = document.getElementById(`freq-${habitoId}`);
                            const progressElement = document.getElementById(`progress-${habitoId}`);
                            const incrementButton = document.querySelector(`button[onclick="incrementHabit(${habitoId}, ${frecuenciaObjetivo})"]`);
                            const completeButton = document.querySelector(`button[onclick="completarHabito(${habitoId})"]`);
                            
                            // Actualizar el hábito en el array de datos
                            const habito = habitsData.find(h => h.id_habito === habitoId);
                            if (habito) {
                                habito.frecuencia_actual = data.frecuencia_actual;
                                freqElement.textContent = `${data.frecuencia_actual} veces cumplidas`;
                                
                                // Actualizar la barra de progreso
                                const progress = Math.min((data.frecuencia_actual / frecuenciaObjetivo) * 100, 100);
                                progressElement.style.width = `${progress}%`;
                                
                                // Deshabilitar el botón si se alcanzó el objetivo
                                if (data.frecuencia_actual >= frecuenciaObjetivo) {
                                    incrementButton.disabled = true;
                                    incrementButton.querySelector('span').textContent = 'Objetivo Alcanzado';
                                    // Habilitar el botón de completar
                                    completeButton.disabled = false;
                                }
                                
                                // Actualizar la gráfica
                                updateChart();
                            }
                        }
                    })
                    .catch(error => console.error('Error:', error));
                }

                function completarHabito(habitoId) {
                    fetch('actualizar_estado_habito.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id_habito: habitoId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Deshabilitar ambos botones
                            const incrementButton = document.querySelector(`button[onclick="incrementHabit(${habitoId}, ${habitsData.find(h => h.id_habito === habitoId).frecuencia_objetivo})"]`);
                            const completeButton = document.querySelector(`button[onclick="completarHabito(${habitoId})"]`);
                            
                            if (incrementButton && completeButton) {
                                incrementButton.disabled = true;
                                completeButton.disabled = true;
                                completeButton.querySelector('span').textContent = 'Hábito Completado';
                                
                                // Mostrar mensaje de éxito
                                alert('¡Hábito completado con éxito!');
                            }
                        } else {
                            alert('Error al completar el hábito: ' + (data.message || 'Error desconocido'));
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error al completar el hábito: ' + error.message);
                    });
                }

                // Inicializar la gráfica
                updateChart();
            </script>

            <style>
                .habit-actions {
                    display: flex;
                    gap: 1rem;
                    margin-top: 1rem;
                }

                .complete-button {
                    background-color: #2ecc71 !important;
                    color: white !important;
                }

                .complete-button:disabled {
                    background-color: #95a5a6 !important;
                    cursor: not-allowed;
                }

                .complete-button:hover:not(:disabled) {
                    background-color: #27ae60 !important;
                }
            </style>
        <?php endif; ?>
    </div>
</body>
</html> 