<?php
header('Content-Type: application/json');
session_start();

include(__DIR__ . "/../conexion.php");

if(!isset($_SESSION["id_cliente"]))
{
    echo json_encode([]);
    exit;
}

$idCliente = $_SESSION["id_cliente"];

$sql = "
SELECT
    id_pedido,
    fecha_pedido,
    total_pedido,
    estado_pago
FROM Pedidos
WHERE id_cliente = ?
ORDER BY fecha_pedido DESC
";

$params = array($idCliente);

$stmt = sqlsrv_query($conn, $sql, $params);

$pedidos = [];

while($fila = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))
{
    $pedidos[] = $fila;
}

echo json_encode($pedidos);
