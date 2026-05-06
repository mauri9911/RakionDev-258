<?php
if(@S_include != "freeclient") exit;

# MYSQL CONFIG
define('MYSQL_HOST', "127.0.0.1");
define('MYSQL_USER', "root");
define('MYSQL_PASS', "1234567");
define('MYSQL_NAME', "rakion");

# AUTH CLIENT
define('CLIENT_AUTH', "freeclient");

# ADD GOLD X DAY
$USER_GOLDL = '25000';
define('USER_GCVAR', false);

# ADD CASH X DAY
$USER_CASHL = '25000';
define('USER_CCVAR', false);

# AUTH DISABLE OR ENABLE
define('SERVER_ON', true);
define('SERVER_MSJ_M', "Server Offline");

// Conexión adaptada a PHP 7 (mysqli)
$conn = mysqli_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_NAME);

if ($conn) {
    // Si necesitas usar esta conexión en otros archivos que incluyan este config,
    // asegúrate de que usen la variable $conn.
} else {
    // Si falla la conexión o la base de datos no existe
    echo "[Error]: Auth OFF";
    exit;
}

// Nota: mysqli_connect ya selecciona la DB en el cuarto parámetro,
// por lo que no hace falta un "select_db" por separado.
?>