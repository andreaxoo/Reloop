<?php
header('Content-Type: application/json');
session_start();

include("conexion.php");

if(!isset($_SESSION["id_cliente"]))
{
    echo json_encode([
        "success" => false
    ]);
    exit;
}

$id = $_SESSION["id_cliente"];

$sql = "
SELECT id_cliente, nombre, apellidos, correo, telefono, fecha_registro
FROM Clientes
WHERE id_cliente = ?
";

$params = array($id);

$stmt = sqlsrv_query(
    $conn,
    $sql,
    $params
);

$usuario = sqlsrv_fetch_array(
    $stmt,
    SQLSRV_FETCH_ASSOC
);

echo json_encode($usuario);
?>