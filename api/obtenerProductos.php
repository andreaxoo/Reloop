<?php

header('Content-Type: application/json');

include(__DIR__ . "/../conexion.php");

$tipo = isset($_GET["tipo"]) ? strtolower($_GET["tipo"]) : null;

if (!$tipo || !in_array($tipo, ["ropa", "accesorios", "promociones"])) {
    echo json_encode([
        "success" => false,
        "message" => "Tipo de producto no válido"
    ]);
    exit;
}

// Mapeo de tipos a categoría
$categorias = [
    "ropa" => "Ropa",
    "accesorios" => "Accesorios",
    "promociones" => "Ropa" // Las promociones pueden estar en cualquier categoría
];

$categoria = $categorias[$tipo];

// Query: obtener productos activos
$sql = "
    SELECT 
        id_producto,
        nombre_articulo,
        descripcion,
        precio,
        precio_anterior,
        stock,
        sku,
        imagen,
        material,
        categoria,
        activo
    FROM Productos
    WHERE activo = 1
    ORDER BY id_producto ASC
";

$stmt = sqlsrv_query($conn, $sql);

if (!$stmt) {
    $errors = sqlsrv_errors();
    
    // Log para debugging
    $logDir = __DIR__ . '/logs';
    if(!is_dir($logDir)) @mkdir($logDir, 0755, true);
    $logFile = $logDir . '/obtenerProductos_debug.log';
    $logEntry = "[" . date('Y-m-d H:i:s') . "] Error en obtenerProductos\n";
    $logEntry .= "Tipo: " . $tipo . "\n";
    $logEntry .= "SQL: " . trim(preg_replace('/\s+/', ' ', $sql)) . "\n";
    $logEntry .= "Errors: " . var_export($errors, true) . "\n\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    echo json_encode([
        "success" => false,
        "message" => "Error al consultar tabla Productos",
        "db_errors" => $errors
    ]);
    exit;
}

$productos = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Filtrar por categoría si no es promociones
    if ($tipo !== 'promociones' && $row['categoria'] !== $categoria) {
        continue;
    }
    
    // Para promociones: solo mostrar si tiene precio_anterior (descuento)
    if ($tipo === 'promociones' && !$row['precio_anterior']) {
        continue;
    }
    
    // Calcular porcentaje de descuento si hay precio_anterior
    $porcentaje = 0;
    if ($row['precio_anterior']) {
        $porcentaje = round((($row['precio_anterior'] - $row['precio']) / $row['precio_anterior']) * 100, 2);
    }
    
    // Normalizar nombres de campos para el frontend
    $producto = [
        'Id' => (int)$row['id_producto'],
        'Nombre' => $row['nombre_articulo'],
        'Descripcion' => $row['descripcion'],
        'Precio' => (float)$row['precio'],
        'stock' => (int)$row['stock'],
        'SKU' => $row['sku'],
        'Imagen' => $row['imagen'],
        'Material' => $row['material']
    ];
    
    // Campos adicionales para promociones
    if ($row['precio_anterior']) {
        $producto['Precio_Anterior'] = (float)$row['precio_anterior'];
        $producto['Porcentaje'] = $porcentaje;
    }
    
    $productos[] = $producto;
}

echo json_encode($productos);
?>


