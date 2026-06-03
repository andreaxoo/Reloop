<?php

header('Content-Type: application/json');

include("../conexion.php");

$datos = json_decode(
    file_get_contents("php://input"),
    true
);

$cliente = $datos["cliente"];

/*
    1. Buscar cliente por correo
*/

$sqlBuscar = "
    SELECT id_cliente
    FROM Clientes
    WHERE correo = ?
";

$paramsBuscar = [
    $cliente["correo"]
];

$stmtBuscar = sqlsrv_query(
    $conn,
    $sqlBuscar,
    $paramsBuscar
);

if(!$stmtBuscar){
    die(json_encode(sqlsrv_errors()));
}

$fila = sqlsrv_fetch_array(
    $stmtBuscar,
    SQLSRV_FETCH_ASSOC
);

/*
    2. Si existe
*/

if($fila){

    $idCliente = $fila["id_cliente"];

} else {

    /*
        3. Insertar cliente
    */

    $sqlInsertar = "
        INSERT INTO Clientes
        (
            nombre,
            apellidos,
            correo,
            telefono
        )
        VALUES
        (
            ?, ?, ?, ?
        )
    ";

    $paramsInsertar = [

        $cliente["nombre"],
        $cliente["apellido"],
        $cliente["correo"],
        $cliente["telefono"]

    ];

    $stmtInsertar = sqlsrv_query(
        $conn,
        $sqlInsertar,
        $paramsInsertar
    );

    if(!$stmtInsertar){
        die(json_encode(sqlsrv_errors()));
    }

    /*
        4. Obtener id generado
    */

    $sqlUltimo = "
        SELECT TOP 1 id_cliente
        FROM Clientes
        WHERE correo = ?
    ";

    $stmtUltimo = sqlsrv_query(
        $conn,
        $sqlUltimo,
        [$cliente["correo"]]
    );

    $nuevoCliente =
        sqlsrv_fetch_array(
            $stmtUltimo,
            SQLSRV_FETCH_ASSOC
        );

    $idCliente =
        $nuevoCliente["id_cliente"];
}

/*
    Calcular total del pedido
*/

$totalPedido = 0;

foreach($datos["carrito"] as $producto){

    $totalPedido +=
        $producto["precio"] *
        $producto["cantidad"];
}

/*
    Crear pedido
*/

$sqlPedido = "
    INSERT INTO Pedidos
    (
        id_cliente,
        metodo_entrega,
        estado_pago,
        total_pedido,
        dir_nombre,
        dir_apellido,
        dir_telefono,
        calle_numero,
        colonia,
        municipio,
        estado,
        codigo_postal
    )
    VALUES
    (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )
";

$paramsPedido = [

    $idCliente,

    "Envio a domicilio",

    "Pagado",

    $totalPedido,

    $cliente["nombre"],
    $cliente["apellido"],
    $cliente["telefono"],

    $cliente["calle"],
    $cliente["colonia"],
    $cliente["municipio"],
    $cliente["estado"],
    $cliente["cp"]

];

$stmtPedido = sqlsrv_query(
    $conn,
    $sqlPedido,
    $paramsPedido
);
/*
    Obtener ID del pedido recién creado
*/

$sqlUltimoPedido = "
    SELECT TOP 1 id_pedido
    FROM Pedidos
    ORDER BY id_pedido DESC
";

$stmtUltimoPedido = sqlsrv_query(
    $conn,
    $sqlUltimoPedido
);

$filaPedido = sqlsrv_fetch_array(
    $stmtUltimoPedido,
    SQLSRV_FETCH_ASSOC
);

$idPedido = $filaPedido["id_pedido"];

foreach($datos["carrito"] as $producto){

    $sqlDetalle = "
        INSERT INTO Detalle_Pedidos
        (
            id_pedido,
            sku_producto,
            cantidad,
            precio_unitario
        )
        VALUES
        (
            ?, ?, ?, ?
        )
    ";

    $paramsDetalle = [

        $idPedido,
        $producto["sku"],
        $producto["cantidad"],
        $producto["precio"]

    ];

    $stmtDetalle = sqlsrv_query(
        $conn,
        $sqlDetalle,
        $paramsDetalle
    );

    if(!$stmtDetalle){

        die(json_encode(sqlsrv_errors()));

    }
}

if(!$stmtPedido){

    die(json_encode(sqlsrv_errors()));

}

echo json_encode([
    "success" => true,
    "id_cliente" => $idCliente,
    "total" => $totalPedido
]);