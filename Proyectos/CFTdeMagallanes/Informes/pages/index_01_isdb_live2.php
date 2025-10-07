<?php
// Asumiendo que el contenido del archivo original comienza aquí
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Presupuestario</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        @media print {
            .pagebreak { page-break-before: always; }
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="mb-4">Informe Presupuestario</h2>

    <table class="table table-bordered">
        <thead class="thead-dark">
        <tr>
            <th>Descripción</th>
            <th>Monto</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td>Aprobado</td>
            <td>$12.000.000</td>
        </tr>
        <tr>
            <td>Gastado</td>
            <td>$8.500.000</td>
        </tr>
        <tr>
            <td>Comprometido</td>
            <td>$2.000.000</td>
        </tr>
        </tbody>
    </table>

    <!-- Agregado: Tabla con grafico de barras -->
    <div class="mt-4">
        <h5>Visualización de Presupuesto</h5>
        <canvas id="budgetChart" height="100"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('budgetChart').getContext('2d');
        const budgetChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Aprobado', 'Gastado', 'Comprometido'],
                datasets: [{
                    label: 'Monto en CLP',
                    data: [12000000, 8500000, 2000000],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(255, 206, 86, 0.7)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString('es-CL');
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>

    <div class="pagebreak"></div>

    <!-- CONTINUA CONTENIDO ORIGINAL DESDE AQUÍ -->
    <div class="mt-4">
        <h4>Detalle Adicional</h4>
        <p>Aquí comienza el contenido de la siguiente página...</p>
    </div>

</div>
</body>
</html>
