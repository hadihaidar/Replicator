<?php
$serverName = "replicator253.database.windows.net";
$connectionOptions = array(
    "Database" => "replicator253",
    "Uid" => "mbdeir",
    "PWD" => "aN8*jkiohjlasdf"
);
//Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);
