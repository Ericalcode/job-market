<?php
$host     = getenv('DB_HOST')     ?: 'localhost';
$user     = getenv('DB_USER')     ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME')     ?: 'job_portal';
$port     = getenv('DB_PORT')     ?: '3306';

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Database Connection Error: " . $conn->connect_error);
}
?>
