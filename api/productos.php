<?php

header('Content-Type: application/json');

include('../conexion.php');

$categoria = $_GET['categoria'] ?? '';

$sql = "SELECT * FROM Productos";

$params = [];

if($categoria != ''){
    $sql .= " WHERE categoria = ?";
    $params[] = $categoria;
}

$stmt = sqlsrv_query($conn, $sql, $params);

if(!$stmt){
    die(json_encode(sqlsrv_errors()));
}

$productos = [];

while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){

    $productos[] = [
        "Id" => $row["id_producto"],
        "Nombre" => $row["nombre_articulo"],
        "Categoria" => $row["categoria"],
        "Material" => $row["material"],
        "Descripcion" => $row["descripcion"],
        "Precio" => (float)$row["precio"],
        "Precio_Anterior" => $row["precio_anterior"] ? (float)$row["precio_anterior"] : null,
        "Imagen" => $row["imagen"]
    ];
}

echo json_encode($productos, JSON_UNESCAPED_UNICODE);