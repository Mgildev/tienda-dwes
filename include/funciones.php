<?php
include("./basededatos/config.php");

function connect_db(){
    $connect = new PDO("mysql:host=" . DB_HOST .";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASSWORD);
    return $connect;
}
?>
