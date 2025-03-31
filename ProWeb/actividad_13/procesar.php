<?php
if(isset($_POST['submit'])){
    if (isset($_POST['submit'])) {
        $number1 = $_POST['number1'];
        
        if (!is_numeric($number1)) {
            die("Error: ¡Solo se permiten números!");
        }
        
        // Si pasa la validación, convierte a número
        $number1 = (float)$number1;
    }
    if (isset($_POST['submit'])) {
        $number2 = $_POST['number2'];
        
        if (!is_numeric($number2)) {
            die("Error: ¡Solo se permiten números!");
        }
        
        // Si pasa la validación, convierte a número
        $number2 = (float)$number2;
    }
    $operacion = $_POST['operacion'];

    switch ($operacion) {
        case 'suma':
            $resultado = $number1 + $number2;
        break;
        case 'resta':
            $resultado = $number1 - $number2;
        break;
        case 'multiplicacion':
            $resultado = $number1 * $number2;
        break;
        case 'division':
            if ($number2 != 0) {
                $resultado = $number1 / $number2;
            } else {
                $resultado = "Error: NO puedes dividir entre CERO, piénsale mi chavo";
            }
        break;
        default:
            $resultado = "Opción no válida";
        break;
    }
    echo "<center><h1>Resultado de la $operacion = $resultado</h1></center>";
}
?>

<center>
    <form action="index.html" method="get">
        <button type="submit">Hacer otro cálculo</button>
    </form>
</center>