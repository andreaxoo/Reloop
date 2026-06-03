<?php
header('Content-Type: application/json');

include("conexion.php");

$nombre = $_POST["nombre"];
$apellidos = $_POST["apellidos"];
$correo = $_POST["correo"];
$telefono = $_POST["telefono"];
$password = $_POST["password"];

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$checkSql = "
SELECT id_cliente
FROM Clientes
WHERE correo = ?
";

$checkParams = array($correo);
$checkStmt = sqlsrv_query($conn, $checkSql, $checkParams);

if ($checkStmt && sqlsrv_has_rows($checkStmt))
{
    echo json_encode([
        "success" => false,
        "message" => "El correo ya está registrado"
    ]);
    exit;
}

$sql = "
INSERT INTO Clientes
(
nombre,
apellidos,
correo,
telefono,
password
)
VALUES
(
?,
?,
?,
?,
?
)
";

$params = array(
    $nombre,
    $apellidos,
    $correo,
    $telefono,
    $passwordHash
);

$stmt = sqlsrv_query($conn, $sql, $params);

if($stmt)
{
    echo json_encode([
        "success" => true,
        "message" => "Usuario registrado"
    ]);
}
else
{
    echo json_encode([
        "success" => false,
        "message" => "Error al registrar"
    ]);
}
?>