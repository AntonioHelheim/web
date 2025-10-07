<?php
define( 'WP_CACHE', true );
//Usuario Administrador
// $host = '190.107.177.247';
// $dbname = 'unnffbpk_proyectos';
// $username = 'unnffbpk';
// $password = 'wizMat-zactyk-7casvi';

//Usuario Especifico BD unnffbpk_proyectos
$host = '190.107.177.247';
$dbname = 'unnffbpk_proyectos';
$user = 'unnffbpk_cft23';
$username = 'unnffbpk_cft23';
$password = '4S1)Xp60]4';

// define database connection
define('DB_SERVER', '190.107.177.247'); // eg, localhost - should not be empty for productive servers
define('DB_SERVER_USERNAME', 'unnffbpk_cft23');
define('DB_SERVER_PASSWORD', '4S1)Xp60]4');
define('DB_SERVER_PORT', '');		
define('DB_DATABASE', 'unnffbpk_proyectos');  

// Intentar establecer la conexión
// try {
//     $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_DATABASE . ";port=" . DB_SERVER_PORT, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//     // Si se llega a este punto, la conexión fue exitosa
//     echo "Conexión a la base de datos establecida correctamente.";

// } catch (PDOException $e) {
//     // Si ocurre un error, se captura y se muestra un mensaje
//     echo "Error de conexión: " . $e->getMessage();
// }

// Intentar establecer la conexión (Op2)
// $conn = new mysqli($host, $user, $password, $dbname);

// if ($conn->connect_error) {
//     echo '❌ Conexión fallida: ' . $conn->connect_error;
// } else {
//     echo '✅ Conexión exitosa a la base de datos.';
// }

// $conn->close();


?>
