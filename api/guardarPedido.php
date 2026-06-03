<?php

header('Content-Type: application/json');
session_start();

include(__DIR__ . "/../conexion.php");

if(!isset($_SESSION["id_cliente"]))
{
    echo json_encode([
        "success" => false,
        "message" => "No autenticado"
    ]);
    exit;
}

$datos = json_decode(
    file_get_contents("php://input"),
    true
);

if(
    !$datos ||
    !isset($datos["carrito"]) ||
    !isset($datos["total"]) ||
    !isset($datos["cliente"])
)
{
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos"
    ]);
    exit;
}

$idCliente = $_SESSION["id_cliente"];
$carrito = $datos["carrito"];
$cliente = $datos["cliente"];

$totalPedido = 0;
foreach($carrito as $producto)
{
    $precio = isset($producto["precio"]) ? $producto["precio"] : 0;
    $cantidad = isset($producto["cantidad"]) ? $producto["cantidad"] : 0;
    $totalPedido += $precio * $cantidad;
}

$sqlPedido = "
INSERT INTO Pedidos
(
    id_cliente,
    total_pedido,
    estado_pago,
    metodo_entrega,
    dir_nombre,
    dir_apellido,
    dir_telefono,
    calle_numero,
    colonia,
    municipio,
    estado,
    codigo_postal
)
OUTPUT INSERTED.id_pedido
VALUES
(
    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
)
";

$paramsPedido = [
    $idCliente,
    $totalPedido,
    "Pagado",
    "PayPal",
    $cliente["nombre"],
    $cliente["apellido"],
    $cliente["telefono"],
    $cliente["calle"],
    $cliente["colonia"],
    $cliente["municipio"],
    $cliente["estado"],
    $cliente["cp"]
];

// Begin transaction so we don't leave a Pedidos row without detalle
if(!sqlsrv_begin_transaction($conn)){
    echo json_encode([
        "success" => false,
        "message" => "No se pudo iniciar la transacción",
        "db_errors" => sqlsrv_errors()
    ]);
    exit;
}

$stmtPedido = sqlsrv_query(
    $conn,
    $sqlPedido,
    $paramsPedido
);

if(!$stmtPedido)
{
    sqlsrv_rollback($conn);
    echo json_encode([
        "success" => false,
        "message" => "Error al insertar pedido",
        "db_errors" => sqlsrv_errors()
    ]);
    exit;
}

// Fetch the OUTPUT inserted id_pedido
$filaInsert = sqlsrv_fetch_array($stmtPedido, SQLSRV_FETCH_ASSOC);
$idPedido = $filaInsert["id_pedido"] ?? null;

if(!$idPedido){
    sqlsrv_rollback($conn);
    echo json_encode([
        "success" => false,
        "message" => "No se pudo obtener el id del pedido después de insertar",
        "db_errors" => sqlsrv_errors()
    ]);
    exit;
}

foreach($carrito as $producto)
{
    $idProducto = $producto["id"] ?? null;

    if(!$idProducto && isset($producto["sku"]))
    {
        $sqlBuscarProducto = "
            SELECT id_producto
            FROM Productos
            WHERE sku = ?
        ";

        $stmtBuscarProducto = sqlsrv_query(
            $conn,
            $sqlBuscarProducto,
            [$producto["sku"]]
        );

        if($stmtBuscarProducto)
        {
            $filaProducto = sqlsrv_fetch_array(
                $stmtBuscarProducto,
                SQLSRV_FETCH_ASSOC
            );

            $idProducto = $filaProducto["id_producto"] ?? null;
        }
    }

    if(!$idProducto)
    {
        sqlsrv_rollback($conn);
        echo json_encode([
            "success" => false,
            "message" => "No se encontró el producto en la base de datos.",
            "item" => $producto
        ]);
        exit;
    }

    $sqlDetalle = "
        INSERT INTO Detalle_Pedidos
        (
            id_pedido,
            id_producto,
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
        $idProducto,
        $producto["cantidad"],
        $producto["precio"]
    ];

    $stmtDetalle = sqlsrv_query(
        $conn,
        $sqlDetalle,
        $paramsDetalle
    );

    if(!$stmtDetalle)
    {
            // Ensure logs directory exists
            $logDir = __DIR__ . '/logs';
            if(!is_dir($logDir)) @mkdir($logDir, 0755, true);

            $logFile = $logDir . '/guardarPedido_debug.log';
            $logEntry = "[" . date('Y-m-d H:i:s') . "] ERROR insertar detalle\n";
            $logEntry .= "id_pedido: " . var_export($idPedido, true) . "\n";
            $logEntry .= "sql: " . trim(preg_replace('/\s+/', ' ', $sqlDetalle)) . "\n";
            $logEntry .= "params: " . var_export($paramsDetalle, true) . "\n";
            $logEntry .= "sqlsrv_errors: " . var_export(sqlsrv_errors(), true) . "\n";
            $logEntry .= "producto: " . var_export($producto, true) . "\n\n";
            @file_put_contents($logFile, $logEntry, FILE_APPEND);

            sqlsrv_rollback($conn);
            echo json_encode([
                "success" => false,
                "message" => "Error al insertar detalle del pedido",
                "db_errors" => sqlsrv_errors(),
                "item" => $producto
            ]);
            exit;
    }
}

// Commit transaction
if(!sqlsrv_commit($conn)){
    sqlsrv_rollback($conn);
    echo json_encode([
        "success" => false,
        "message" => "No se pudo confirmar la transacción",
        "db_errors" => sqlsrv_errors()
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "id_pedido" => $idPedido,
    "total" => $totalPedido
]);