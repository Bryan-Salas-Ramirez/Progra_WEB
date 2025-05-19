<?php
session_start();
require_once '../../includes/verificar_sesion.php';
require_once '../../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: administrar_habitos.php');
    exit;
}

if (!isset($_POST['habito']) || !isset($_POST['frecuencia']) || !isset($_POST['lapso'])) {
    $_SESSION['error'] = 'Faltan datos requeridos';
    header('Location: administrar_habitos.php');
    exit;
}

try {
    $conn->beginTransaction();

    // Insertar el registro del hábito
    $query = "INSERT INTO registro_habito (id_usuario, id_habito, frecuencia_objetivo, frecuencia_actual, id_lapso, estatus_habito) 
            VALUES (:id_usuario, :id_habito, :frecuencia, 0, :id_lapso, 1)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $_SESSION['id_usuario'], PDO::PARAM_INT);
    $stmt->bindParam(':id_habito', $_POST['habito'], PDO::PARAM_INT);
    $stmt->bindParam(':frecuencia', $_POST['frecuencia'], PDO::PARAM_INT);
    $stmt->bindParam(':id_lapso', $_POST['lapso'], PDO::PARAM_INT);
    $stmt->execute();

    $conn->commit();
    $_SESSION['success'] = 'Hábito agregado correctamente';
} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['error'] = 'Error al agregar el hábito: ' . $e->getMessage();
}

header('Location: administrar_habitos.php');
exit; 