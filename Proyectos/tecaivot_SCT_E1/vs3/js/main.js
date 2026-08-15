/* ==========================================================
   SAFETY CONTROL TOWER
   MAIN.JS
   ========================================================== */


/* ==========================================================
   NAVBAR SCROLL
   ========================================================== */

const navbar = document.getElementById("mainNavbar");

window.addEventListener("scroll", function () {

    if (window.scrollY > 30) {

        navbar.classList.add("scrolled");

    } else {

        navbar.classList.remove("scrolled");

    }

});


/* ==========================================================
   CLOSE MOBILE NAVBAR AFTER CLICK
   ========================================================== */

const navLinks = document.querySelectorAll(
    ".navbar-nav .nav-link"
);

const navbarCollapse = document.getElementById(
    "navbarContent"
);

navLinks.forEach(function (link) {

    link.addEventListener("click", function () {

        if (
            window.innerWidth < 992 &&
            navbarCollapse.classList.contains("show")
        ) {

            const bsCollapse =
                bootstrap.Collapse.getInstance(
                    navbarCollapse
                );

            if (bsCollapse) {

                bsCollapse.hide();

            }

        }

    });

});


/* ==========================================================
   CURRENT YEAR
   ========================================================== */

const currentYear =
    document.getElementById("currentYear");

if (currentYear) {

    currentYear.textContent =
        new Date().getFullYear();

}


/* ==========================================================
   PASSWORD VISIBILITY
   ========================================================== */

const togglePassword =
    document.getElementById("togglePassword");

const loginPassword =
    document.getElementById("loginPassword");

if (togglePassword && loginPassword) {

    togglePassword.addEventListener(
        "click",
        function () {

            const isPassword =
                loginPassword.type === "password";

            loginPassword.type =
                isPassword ? "text" : "password";

            const icon =
                togglePassword.querySelector("i");

            if (isPassword) {

                icon.classList.remove("bi-eye");

                icon.classList.add("bi-eye-slash");

            } else {

                icon.classList.remove("bi-eye-slash");

                icon.classList.add("bi-eye");

            }

        }
    );

}


/* ==========================================================
   CONTACT FORM
   ========================================================== */

const contactForm =
    document.getElementById("contactForm");

if (contactForm) {

    contactForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();

            const button =
                contactForm.querySelector(
                    'button[type="submit"]'
                );

            const originalText =
                button.innerHTML;

            button.disabled = true;

            button.innerHTML = `
                Enviando...
                <i class="bi bi-hourglass-split"></i>
            `;


            setTimeout(function () {

                button.innerHTML = `
                    Solicitud enviada
                    <i class="bi bi-check-circle"></i>
                `;


                contactForm.reset();


                setTimeout(function () {

                    button.disabled = false;

                    button.innerHTML =
                        originalText;

                }, 2500);

            }, 1000);

        }
    );

}


/* ==========================================================
   LOGIN FORM
   ========================================================== */

const loginForm =
    document.getElementById("loginForm");

if (loginForm) {

    loginForm.addEventListener(
        "submit",
        function (event) {

            event.preventDefault();

            const button =
                loginForm.querySelector(
                    'button[type="submit"]'
                );

            const originalText =
                button.innerHTML;

            button.disabled = true;

            button.innerHTML = `
                Verificando...
                <i class="bi bi-arrow-repeat"></i>
            `;


            /*
             * IMPORTANTE:
             *
             * Esta parte es solamente UX.
             *
             * Cuando exista el backend,
             * reemplazar este comportamiento
             * por la autenticación real.
             */


            setTimeout(function () {

                button.disabled = false;

                button.innerHTML =
                    originalText;

                alert(
                    "Aquí se conectará el sistema de inicio de sesión de Safety Control Tower."
                );

            }, 1000);

        }
    );

}


/* ==========================================================
   SCROLL REVEAL
   ========================================================== */

const revealElements =
    document.querySelectorAll(
        ".problem-card, .feature-card, .benefit-item, .step-item"
    );

// Observar elementos con animaciones
document.querySelectorAll(
    ".problem-card, .feature-card, .case-card, .pricing-card, .benefit-item"
).forEach((element) => {
    element.classList.add("reveal");
    animationObserver.observe(element);
});



const observer =
    new IntersectionObserver(
        function (entries) {

            entries.forEach(function (entry) {

                if (entry.isIntersecting) {

                    entry.target.classList.add(
                        "visible"
                    );

                    observer.unobserve(
                        entry.target
                    );

                }

            });

        },
        {
            threshold: 0.15
        }
    );


revealElements.forEach(function (element) {

    element.classList.add("reveal");

    observer.observe(element);

});


/* ==========================================================
   SMOOTH SCROLL
   ========================================================== */

document.querySelectorAll(
    'a[href^="#"]'
).forEach(function (anchor) {

    anchor.addEventListener(
        "click",
        function (event) {

            const targetId =
                this.getAttribute("href");

            if (
                targetId === "#" ||
                !document.querySelector(targetId)
            ) {

                return;

            }

            event.preventDefault();

            const target =
                document.querySelector(targetId);

            const navbarHeight =
                navbar.offsetHeight;

            const targetPosition =
                target.getBoundingClientRect().top +
                window.scrollY -
                navbarHeight;

            window.scrollTo({

                top: targetPosition,

                behavior: "smooth"

            });

        }
    );

});