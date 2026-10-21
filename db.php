<?php
$host     = getenv('DB_HOST')     ?: 'gateway01.ap-northeast-1.prod.aws.tidbcloud.com';
$user     = getenv('DB_USER')     ?: '3HAWRh3isi9aYxU.root';
$password = getenv('DB_PASSWORD') ?: 'H7ckagCvEWJc5ztu';
$database = getenv('DB_NAME')     ?: 'test'; // <-- Make sure this is 'test'
$port     = getenv('DB_PORT')     ?: 4000;

$conn = mysqli_init();
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

if (!@$conn->real_connect($host, $user, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Connect Error: " . mysqli_connect_error());
}
?>
