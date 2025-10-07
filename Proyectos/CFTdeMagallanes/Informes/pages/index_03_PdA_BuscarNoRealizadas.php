<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: text/html; charset=utf-8");

require 'db.php';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
  die("<div class='alert alert-danger'>Error en la conexión: " . $conn->connect_error . "</div>");
}
$conn->set_charset("utf8");

$sql = "SELECT 
    A.id, 
    A.field_496 AS actividad,
    DATE_FORMAT(FROM_UNIXTIME(A.field_499), '%d/%m/%Y') as FecPlan,
    A.field_497 AS Objetivo, 
    A.field_505 AS Monto, 
    A.field_506 AS FuenteFinanciamiento, 
    A.field_507 AS Indicador, 
    A.field_508 AS Meta,
    A.field_526 AS EntornoEsperado,
    A.field_529 AS EntornoReal,
    B.name AS Unidad,
    C.name AS Eje,
    D.name AS Mecanismo,
    E.name AS EstadoActividad
FROM app_entity_41 AS A
INNER JOIN app_fields_choices AS B ON A.field_493 = B.id
INNER JOIN app_fields_choices AS C ON A.field_494 = C.id
INNER JOIN app_fields_choices AS D ON A.field_495 = D.id
INNER JOIN app_fields_choices AS E ON A.field_498 = E.id
WHERE E.id != 187
ORDER BY A.field_499";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
  <style>
    .collapse-row {
        background-color: #f8f9fa;
        color: #212529;
    }
    .table-dark tbody tr:hover {
        background-color: #2b2b2b;
    }
  </style>
</head>
<body class="bg-dark text-white">

<div class="container my-5">
  <h3 class="mb-4 text-light"></h3>

  <div class="table-responsive">
    <table id="tablaActividades" class="table table-dark table-bordered table-hover">
      <thead class="table-primary text-dark">
        <tr>
          <th>Actividad</th>
          <th>Fecha Planificada</th>
          <th>Estado</th>
          <th>Entorno Esperado</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): $i = 0; ?>
        <?php while($row = $result->fetch_assoc()): $i++; ?>
        <tr data-bs-toggle="collapse" data-bs-target="#detalle<?= $i ?>" class="accordion-toggle">
          <td><strong><?= htmlspecialchars($row['actividad']) ?></strong></td>
          <td><?= $row['FecPlan'] ?></td>
          <td><?= $row['EstadoActividad'] ?></td>
          <td><?= $row['EntornoEsperado'] ?></td>
        </tr>
        <tr class="collapse collapse-row" id="detalle<?= $i ?>">
          <td colspan="4">
            <strong>Objetivo:</strong> <?= $row['Objetivo'] ?><br>
            <strong>Monto:</strong> <?= $row['Monto'] ?><br>
            <strong>Fuente:</strong> <?= $row['FuenteFinanciamiento'] ?><br>
            <strong>Indicador:</strong> <?= $row['Indicador'] ?><br>
            <strong>Meta:</strong> <?= $row['Meta'] ?><br>
            <strong>Unidad:</strong> <?= $row['Unidad'] ?><br>
            <strong>Eje:</strong> <?= $row['Eje'] ?><br>
            <strong>Mecanismo:</strong> <?= $row['Mecanismo'] ?><br>
            <strong>Entorno Real:</strong> <?= $row['EntornoReal'] ?>
          </td>
        </tr>
        <?php endwhile; ?>
        <?php else: ?>
        <tr><td colspan="4" class="text-center">No se encontraron actividades.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script>
$(document).ready(function () {
    $('#tablaActividades').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excelHtml5',
                className: 'btn btn-success',
                text: 'Exportar a Excel'
            },
            {
                extend: 'pdfHtml5',
                className: 'btn btn-danger',
                text: 'Exportar a PDF',
                orientation: 'landscape',
                pageSize: 'A4'
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        }
    });
});
</script>
</body>
</html>
