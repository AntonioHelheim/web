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


// SELECT A.id, A.field_434, A.field_435, A.field_437, A.field_439, A.field_453, A.field_454, A.field_445, B.value, B.fields_id, C.id, C.name FROM `app_entity_38` AS A INNER JOIN `app_entity_38_values` AS B ON A.field_445 = B.value INNER JOIN `app_fields_choices` AS C ON B.value = C.id WHERE A.id = 3;

// // Cerrar la conexión (aunque PHP la cerrará automáticamente al finalizar el script)
// $pdo = null;

?>
