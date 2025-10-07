// Función para alternar la visibilidad de las secciones
function toggleSectionByClass(sectionClass, buttonClass) {
    const content = document.querySelector(sectionClass + ' .project-content');
    const button = document.querySelector(buttonClass);

    // Comprobamos el estado actual y cambiamos
    if (content.style.display === "none" || content.style.display === "") {
        content.style.display = "block"; // Desplegar
        button.innerHTML = "&#9650;"; // Cambiar el ícono a flecha arriba
    } else {
        content.style.display = "none"; // Contraer
        button.innerHTML = "&#9660;"; // Cambiar el ícono a flecha abajo
    }
}


// Event listener para cuando el DOM esté completamente cargado
document.addEventListener("DOMContentLoaded", function () {
    // Inicializar los gráficos una sola vez
    createChart();

    // Asociar las funciones de alternancia con las secciones correspondientes
    document.querySelector('.ProyectosFDNR .toggle-button').addEventListener('click', function () {
        toggleSectionByClass('.ProyectosFDNR', '.ProyectosFDNR .toggle-button');
    });

    document.querySelector('.ProyectosGenero .toggle-button').addEventListener('click', function () {
        toggleSectionByClass('.ProyectosGenero', '.ProyectosGenero .toggle-button');
    });

    document.querySelector('.ProyectosUTA .toggle-button').addEventListener('click', function () {
        toggleSectionByClass('.ProyectosUTA', '.ProyectosUTA .toggle-button');
    });
});


// Función para crear el gráfico de barras
function createChart() {
    const ctx = document.getElementById('presupuestoChart').getContext('2d');
    const presupuestoData = {
        labels: ['Junio', 'Agosto', 'Septiembre'], // Etiquetas de las barras
        datasets: [{
            label: 'Gastado',
            data: [65507354, 75673129, 87022099], // Datos de 'Gastado'
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1
        }, {
            label: 'Comprometido',
            data: [42124074, 66828767, 78644580], // Datos de 'Comprometido'
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }, {
            label: 'Planificado',
            data: [209106720, 75158560, 21096569], // Datos de 'Planificado'
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1
        }]
    };

    const options = {
        responsive: true, // Hace que el gráfico sea responsivo
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function (value) {
                        return value.toLocaleString(); // Separadores de miles
                    }
                }
            }
        },
        plugins: {
            datalabels: {
                anchor: 'end',
                align: 'top',
                font: {
                    weight: 'bold',
                    size: 15
                },
                color: '#333',
                formatter: function (value) {
                    return value.toLocaleString(); // Formato con separadores de miles
                },
                offset: 5
            }
        }
    };

    new Chart(ctx, {
        type: 'bar',
        data: presupuestoData,
        options: options
    });
}
