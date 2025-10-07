<?php
require 'db.php'; // Incluye los datos de conexión a la base de datos

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Asegurarse de que el id es un número entero

    // Crear conexión a la base de datos
    $conn = new mysqli($host, $username, $password, $dbname);

    // Verificar conexión
    if ($conn->connect_error) {
        die("<div class='alert alert-danger' role='alert'>Error en la conexión: " . $conn->connect_error . "</div>");
    }

    // Establecer el conjunto de caracteres para manejar correctamente los acentos y caracteres especiales
    $conn->set_charset("utf8");

    // Consulta SQL
    $sql = "SELECT A.id as NumDe, A.field_434 as Den, DATE_FORMAT(FROM_UNIXTIME(A.date_added), '%d/%m/%Y') as FecCre, DATE_FORMAT(FROM_UNIXTIME(A.date_updated), '%d/%m/%Y') as FecUpd, DATE_FORMAT(FROM_UNIXTIME(A.field_452), '%d/%m/%Y') as FecCierre, A.field_435, A.field_437 as Vic, A.field_439, A.field_453 as Acu, A.field_454, A.field_445, B.value, B.fields_id, C.id, C.name as Est FROM `app_entity_38` AS A INNER JOIN `app_entity_38_values` AS B ON A.field_445 = B.value INNER JOIN `app_fields_choices` AS C ON B.value = C.id WHERE A.id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Procesar resultados
    echo "<div class='container mt-5'>";
    echo "<div class='card'>";
    echo "<div class='card-header bg-primary text-white'>
            <h4>Detalles de la Denuncia y su estado actual</h4>
          </div>";

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "<div class='card-body'>";
        echo "<p><strong>Denuncia número:</strong> {$row['NumDe']}</p>";
        echo "<p><strong>Identificacion denunciante:</strong> {$row['Den']}</p>";
        echo "<p><strong>Fecha creacion del registro en el sistema:</strong> {$row['FecCre']}</p>";
        echo "<p>Considerar un tiempo maximo de 59 dias como SLA desde esta fecha para una resolucion de la denuncia</p>";
        echo "<p><strong>Fecha actualizacion del registro</strong> {$row['FecUpd']}</p>";
        echo "<p>Dudas escribir al equipo de Genero del CFT de Magallanes</p>";
        echo "<p><strong>Nombre víctima:</strong> {$row['Vic']}</p>";
        echo "<p><strong>Nombre acusado:</strong> {$row['Acu']}</p>";
        echo "<p><strong>Estado de la denuncia :</strong> {$row['Est']}</p>";
        echo "<p><strong>Fecha informada como cierre del proceso</strong> {$row['FecCierre']}</p>";
        echo "<p>Esta fecha es independiente del estado, ya que en algunos casos el estado implica una gestion fuera del equipo de Genero del CFT de Magallanes.</p>";
        echo "</div>";
    } else {
        echo "<div class='card-body'>
                <div class='alert alert-warning' role='alert'>No se encontraron registros asociados para la denuncia ingresada número {$id}.</div>
              </div>";
    }

    echo "</div>"; // Cerrar card
    echo "</div>"; // Cerrar container

    // Cerrar conexión
    $conn->close();
} else {
    echo "<div class='container mt-5'>
            <div class='alert alert-danger' role='alert'>No se proporcionó un número de denuncia.</div>
          </div>";
}
?>

