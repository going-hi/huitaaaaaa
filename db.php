<?php
$host = getenv("MYSQL_HOST") ?: "127.0.0.1";
$port = (int) (getenv("MYSQL_PORT") ?: 3306);
$user = getenv("MYSQL_USER") ?: "root";
$pass = getenv("MYSQL_PASSWORD") !== false ? getenv("MYSQL_PASSWORD") : "root";
$db = getenv("MYSQL_DATABASE") ?: "mindbase";

$link = mysqli_connect($host, $user, $pass, $db, $port);
if (!$link) {
    die("Ошибка подключения MySQL: " . mysqli_connect_error());
}
$link->query("set names utf8");
