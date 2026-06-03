<?php
header('Content-Type: application/json');
session_start();

include(__DIR__ . "/../conexion.php");

$correo = $_POST["correo"];
$password = $_POST["password"];

$sql = "
SELECT *
FROM Clientes
WHERE correo = ?
";

$params = array($correo);

$stmt = sqlsrv_query($conn,$sql,$params);

$usuario = sqlsrv_fetch_array(
    $stmt,
    SQLSRV_FETCH_ASSOC
);

if(
    $usuario &&
    password_verify(
        $password,
        $usuario["password"]
    )
)
{
    $_SESSION["id_cliente"] =
        $usuario["id_cliente"];

    $_SESSION["nombre"] =
        $usuario["nombre"];

    echo json_encode([
        "success" => true
    ]);
}
else
{
    echo json_encode([
        "success" => false
    ]);
}
?>