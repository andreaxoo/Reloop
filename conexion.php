<?php

date_default_timezone_set('America/Mexico_City');

$serverName = "LAPTOP-I4QHM1LT\VE_SERVER";

$connectionInfo = array(
    "Database" => "Reloop",
    "UID" => "sa",
    "PWD" => "123456789",
    "CharacterSet" => "UTF-8",
    "ReturnDatesAsStrings" => true
);

$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}
?>