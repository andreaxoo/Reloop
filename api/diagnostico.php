<?php

header('Content-Type: application/json');

include(__DIR__ . "/../conexion.php");

// Verificar conexión
if (!$conn) {
    echo json_encode(["error" => "No connection to database"]);
    exit;
}

echo json_encode([
    "connection" => "OK",
    "database" => "Reloop"
], JSON_PRETTY_PRINT);

// Obtener estructura de tabla Productos
$sql = "
    SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'Productos'
    ORDER BY ORDINAL_POSITION
";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt) {
    $columns = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $columns[] = $row;
    }
    
    echo "\n\n=== ESTRUCTURA TABLA PRODUCTOS ===\n";
    echo json_encode($columns, JSON_PRETTY_PRINT);
}

// Contar productos
$sql2 = "SELECT COUNT(*) as total FROM Productos";
$stmt2 = sqlsrv_query($conn, $sql2);

if ($stmt2) {
    $row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
    echo "\n\n=== TOTAL PRODUCTOS ===\n";
    echo "Total: " . $row['total'];
}

// Obtener primeros 3 productos
$sql3 = "SELECT TOP 3 * FROM Productos";
$stmt3 = sqlsrv_query($conn, $sql3);

if ($stmt3) {
    $productos = [];
    while ($row = sqlsrv_fetch_array($stmt3, SQLSRV_FETCH_ASSOC)) {
        $productos[] = $row;
    }
    
    echo "\n\n=== PRIMEROS 3 PRODUCTOS ===\n";
    echo json_encode($productos, JSON_PRETTY_PRINT);
}

?>
