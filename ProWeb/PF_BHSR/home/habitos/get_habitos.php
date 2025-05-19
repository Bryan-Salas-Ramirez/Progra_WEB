<?php
session_start();
require_once '../../includes/verificar_sesion.php';
require_once '../../includes/conexion.php';

header('Content-Type: application/json');

if (!isset($_GET['categoria'])) {
    echo json_encode(['error' => 'Categoría no especificada']);
    exit;
}

$categoria_id = $_GET['categoria'];

try {
    $query = "SELECT id_habito, nombre, descripcion 
            FROM habito 
            WHERE id_categoria = :categoria_id 
            ORDER BY nombre";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':categoria_id', $categoria_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $habitos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($habitos);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al obtener los hábitos']);
}