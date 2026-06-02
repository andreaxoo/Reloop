<?php

include("conexion.php");

echo "<h2>Conexión exitosa a SQL Server 🚀</h2>";

$sql = "SELECT COUNT(*) AS total FROM Productos";

$stmt = sqlsrv_query($conn, $sql);

if (!$stmt) {
    die(print_r(sqlsrv_errors(), true));
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

echo "<p>Productos registrados: " . $row['total'] . "</p>";
?>