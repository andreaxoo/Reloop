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
        $sql = "SELECT id_cliente, nombre, apellidos, correo, telefono, fecha_registro FROM Clientes WHERE id_cliente = ?";
        $stmt = sqlsrv_query($conn, $sql, [$id]);
        if (!$stmt) {
            respond(['success' => false, 'message' => 'Error al leer cliente.']);
        }
        $item = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if (!$item) {
            respond(['success' => false, 'message' => 'Cliente no encontrado.']);
        }
        respond([
            'Id' => (int)$item['id_cliente'],
            'Nombre' => $item['nombre'],
            'Apellidos' => $item['apellidos'],
            'Correo' => $item['correo'],
            'Telefono' => $item['telefono'],
            'Fecha_Registro' => $item['fecha_registro']
        ]);
    }

    $sql = "SELECT id_cliente, nombre, apellidos, correo, telefono, fecha_registro FROM Clientes ORDER BY id_cliente ASC";
    $stmt = sqlsrv_query($conn, $sql);
    if (!$stmt) {
        respond(['success' => false, 'message' => 'Error al cargar clientes.']);
    }

    $clientes = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $clientes[] = [
            'Id' => (int)$row['id_cliente'],
            'Nombre' => $row['nombre'],
            'Apellidos' => $row['apellidos'],
            'Correo' => $row['correo'],
            'Telefono' => $row['telefono'],
            'Fecha_Registro' => $row['fecha_registro']
        ];
    }

    respond($clientes);
}

$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput, true);
if (!$payload || !is_array($payload)) {
    $payload = $_POST ?: [];
    if (!$payload && $rawInput) {
        parse_str($rawInput, $parsed);
        $payload = $parsed ?: [];
    }
}

if (!$payload || !is_array($payload)) {
    respond(['success' => false, 'message' => 'Solicitud inválida']);
}

$action = $payload['action'] ?? '';

switch ($action) {
    case 'create':
        $nombre = trim($payload['nombre'] ?? '');
        $apellidos = trim($payload['apellidos'] ?? '');
        $correo = trim($payload['correo'] ?? '');
        $telefono = trim($payload['telefono'] ?? '');
        $password = trim($payload['password'] ?? '');

        if (!$nombre || !$apellidos || !$correo || !$password) {
            respond(['success' => false, 'message' => 'Nombre, apellidos, correo y contraseña son obligatorios.']);
        }

        $checkSql = "SELECT id_cliente FROM Clientes WHERE correo = ?";
        $checkStmt = sqlsrv_query($conn, $checkSql, [$correo]);
        if ($checkStmt && sqlsrv_has_rows($checkStmt)) {
            respond(['success' => false, 'message' => 'El correo ya está registrado.']);
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO Clientes (nombre, apellidos, correo, telefono, password) VALUES (?, ?, ?, ?, ?)";
        $params = [$nombre, $apellidos, $correo, $telefono, $passwordHash];
        $stmt = sqlsrv_query($conn, $sql, $params);
        if (!$stmt) {
            respond(['success' => false, 'message' => 'Error al crear cliente.', 'errors' => sqlsrv_errors()]);
        }
        respond(['success' => true, 'message' => 'Cliente creado correctamente.']);
        break;

    case 'update':
        $id = isset($payload['id_cliente']) ? intval($payload['id_cliente']) : 0;
        if (!$id) {
            respond(['success' => false, 'message' => 'ID de cliente inválido.']);
        }
        $nombre = trim($payload['nombre'] ?? '');
        $apellidos = trim($payload['apellidos'] ?? '');
        $correo = trim($payload['correo'] ?? '');
        $telefono = trim($payload['telefono'] ?? '');
        $password = trim($payload['password'] ?? '');

        if (!$nombre || !$apellidos || !$correo) {
            respond(['success' => false, 'message' => 'Nombre, apellidos y correo son obligatorios.']);
        }

        $checkSql = "SELECT id_cliente FROM Clientes WHERE correo = ? AND id_cliente <> ?";
        $checkStmt = sqlsrv_query($conn, $checkSql, [$correo, $id]);
        if ($checkStmt && sqlsrv_has_rows($checkStmt)) {
            respond(['success' => false, 'message' => 'El correo ya está en uso por otro cliente.']);
        }

        if ($password !== '') {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE Clientes SET nombre = ?, apellidos = ?, correo = ?, telefono = ?, password = ? WHERE id_cliente = ?";
            $params = [$nombre, $apellidos, $correo, $telefono, $passwordHash, $id];
        } else {
            $sql = "UPDATE Clientes SET nombre = ?, apellidos = ?, correo = ?, telefono = ? WHERE id_cliente = ?";
            $params = [$nombre, $apellidos, $correo, $telefono, $id];
        }

        $stmt = sqlsrv_query($conn, $sql, $params);
        if (!$stmt) {
            respond(['success' => false, 'message' => 'Error al actualizar cliente.', 'errors' => sqlsrv_errors()]);
        }
        respond(['success' => true, 'message' => 'Cliente actualizado correctamente.']);
        break;

    case 'delete':
        $id = isset($payload['id_cliente']) ? intval($payload['id_cliente']) : 0;
        if (!$id) {
            respond(['success' => false, 'message' => 'ID de cliente inválido.']);
        }
        $sql = "DELETE FROM Clientes WHERE id_cliente = ?";
        $stmt = sqlsrv_query($conn, $sql, [$id]);
        if (!$stmt) {
            $errors = sqlsrv_errors();
            $message = 'Error al eliminar cliente.';
            if ($errors && is_array($errors)) {
                foreach ($errors as $error) {
                    if (strpos($error['message'], 'REFERENCE') !== false || strpos($error['message'], 'FOREIGN KEY') !== false) {
                        $message = 'No se puede eliminar el cliente porque tiene datos relacionados en la base de datos.';
                        break;
                    }
                }
            }
            respond(['success' => false, 'message' => $message, 'errors' => $errors]);
        }
        respond(['success' => true, 'message' => 'Cliente eliminado correctamente.']);
        break;

    default:
        respond(['success' => false, 'message' => 'Acción no válida.']);
}
