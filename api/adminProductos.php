<?php
header('Content-Type: application/json');
include(__DIR__ . '/../conexion.php');

$requestMethod = $_SERVER['REQUEST_METHOD'];

function respond($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($requestMethod === 'GET') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    if ($id) {
        $sql = "SELECT id_producto, sku, nombre_articulo, descripcion, categoria, material, precio, precio_anterior, stock, imagen, activo FROM Productos WHERE id_producto = ?";
        $stmt = sqlsrv_query($conn, $sql, [$id]);
        if (!$stmt) {
            respond(['success' => false, 'message' => 'Error al leer producto.']);
        }
        $item = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if (!$item) {
            respond(['success' => false, 'message' => 'Producto no encontrado.']);
        }
        respond([
            'Id' => (int)$item['id_producto'],
            'SKU' => $item['sku'],
            'Nombre' => $item['nombre_articulo'],
            'Descripcion' => $item['descripcion'],
            'Categoria' => $item['categoria'],
            'Material' => $item['material'],
            'Precio' => (float)$item['precio'],
            'Precio_Anterior' => $item['precio_anterior'] !== null ? (float)$item['precio_anterior'] : null,
            'stock' => (int)$item['stock'],
            'Imagen' => $item['imagen'],
            'activo' => (int)$item['activo']
        ]);
    }

    $sql = "SELECT id_producto, sku, nombre_articulo, descripcion, categoria, material, precio, precio_anterior, stock, imagen, activo FROM Productos ORDER BY id_producto ASC";
    $stmt = sqlsrv_query($conn, $sql);
    if (!$stmt) {
        respond(['success' => false, 'message' => 'Error al cargar productos.']);
    }

    $productos = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $productos[] = [
            'Id' => (int)$row['id_producto'],
            'SKU' => $row['sku'],
            'Nombre' => $row['nombre_articulo'],
            'Descripcion' => $row['descripcion'],
            'Categoria' => $row['categoria'],
            'Material' => $row['material'],
            'Precio' => (float)$row['precio'],
            'Precio_Anterior' => $row['precio_anterior'] !== null ? (float)$row['precio_anterior'] : null,
            'stock' => (int)$row['stock'],
            'Imagen' => $row['imagen'],
            'activo' => (int)$row['activo']
        ];
    }

    respond($productos);
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!$payload) {
    respond(['success' => false, 'message' => 'Solicitud inválida']);
}

$action = $payload['action'] ?? '';

switch ($action) {
    case 'create':
        $sku = trim($payload['sku'] ?? '');
        $nombre = trim($payload['nombre_articulo'] ?? '');
        $categoria = trim($payload['categoria'] ?? '');
        $material = trim($payload['material'] ?? '');
        $descripcion = trim($payload['descripcion'] ?? '');
        $precio = isset($payload['precio']) ? floatval($payload['precio']) : 0;
        $precioAnterior = isset($payload['precio_anterior']) && $payload['precio_anterior'] !== '' ? floatval($payload['precio_anterior']) : null;
        $stock = isset($payload['stock']) ? intval($payload['stock']) : 0;
        $imagen = trim($payload['imagen'] ?? '');
        $activo = isset($payload['activo']) ? intval($payload['activo']) : 1;

        if (!$sku || !$nombre || !$categoria) {
            respond(['success' => false, 'message' => 'SKU, nombre y categoría son obligatorios.']);
        }

        $sql = "INSERT INTO Productos (sku, nombre_articulo, descripcion, categoria, material, precio, precio_anterior, stock, imagen, activo) OUTPUT INSERTED.id_producto VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [$sku, $nombre, $descripcion, $categoria, $material, $precio, $precioAnterior, $stock, $imagen, $activo];
        $stmt = sqlsrv_query($conn, $sql, $params);
        if (!$stmt) {
            respond(['success' => false, 'message' => 'Error al crear producto.', 'errors' => sqlsrv_errors()]);
        }
        $inserted = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        respond(['success' => true, 'message' => 'Producto creado correctamente.', 'id_producto' => $inserted['id_producto']]);
        break;

    case 'update':
        $id = isset($payload['id_producto']) ? intval($payload['id_producto']) : 0;
        if (!$id) {
            respond(['success' => false, 'message' => 'ID de producto inválido.']);
        }
        $sku = trim($payload['sku'] ?? '');
        $nombre = trim($payload['nombre_articulo'] ?? '');
        $categoria = trim($payload['categoria'] ?? '');
        $material = trim($payload['material'] ?? '');
        $descripcion = trim($payload['descripcion'] ?? '');
        $precio = isset($payload['precio']) ? floatval($payload['precio']) : 0;
        $precioAnterior = isset($payload['precio_anterior']) && $payload['precio_anterior'] !== '' ? floatval($payload['precio_anterior']) : null;
        $stock = isset($payload['stock']) ? intval($payload['stock']) : 0;
        $imagen = trim($payload['imagen'] ?? '');
        $activo = isset($payload['activo']) ? intval($payload['activo']) : 1;

        if (!$sku || !$nombre || !$categoria) {
            respond(['success' => false, 'message' => 'SKU, nombre y categoría son obligatorios.']);
        }

        $sql = "UPDATE Productos SET sku = ?, nombre_articulo = ?, descripcion = ?, categoria = ?, material = ?, precio = ?, precio_anterior = ?, stock = ?, imagen = ?, activo = ? WHERE id_producto = ?";
        $params = [$sku, $nombre, $descripcion, $categoria, $material, $precio, $precioAnterior, $stock, $imagen, $activo, $id];
        $stmt = sqlsrv_query($conn, $sql, $params);
        if (!$stmt) {
            respond(['success' => false, 'message' => 'Error al actualizar producto.', 'errors' => sqlsrv_errors()]);
        }
        respond(['success' => true, 'message' => 'Producto actualizado correctamente.']);
        break;

    case 'delete':
        $id = isset($payload['id_producto']) ? intval($payload['id_producto']) : 0;
        if (!$id) {
            respond(['success' => false, 'message' => 'ID de producto inválido.']);
        }
        $sql = "DELETE FROM Productos WHERE id_producto = ?";
        $stmt = sqlsrv_query($conn, $sql, [$id]);
        if (!$stmt) {
            respond(['success' => false, 'message' => 'Error al eliminar producto.', 'errors' => sqlsrv_errors()]);
        }
        respond(['success' => true, 'message' => 'Producto eliminado correctamente.']);
        break;

    default:
        respond(['success' => false, 'message' => 'Acción no válida.']);
}
