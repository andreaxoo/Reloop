<?php
header('Content-Type: application/json');
session_start();

include(__DIR__ . "/../conexion.php");

session_destroy();

echo json_encode([
    "success" => true
]);
?>