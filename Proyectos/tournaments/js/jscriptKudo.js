function updateCountdown() {
    const countdownDate = new Date("May 11, 2024 00:00:00").getTime();
    const now = new Date().getTime();
    const distance = countdownDate - now;

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    document.getElementById("timer").innerHTML = days + " días " + hours + " horas "
    + minutes + " minutos " + seconds + " segundos ";

    // Si la cuenta regresiva termina, escribe algún texto
    if (distance < 0) {
        clearInterval(x);
        document.getElementById("timer").innerHTML = "¡El evento ha comenzado!";
    }
}

// Actualiza el contador cada segundo
let x = setInterval(updateCountdown, 1000);