/* =========================
FILE: script.js
PATH: /assets/js/script.js
========================= */

document.addEventListener("DOMContentLoaded", () => {
  // =========================
  // Footer year
  // =========================
  const y = document.getElementById("year");
  if (y) y.textContent = new Date().getFullYear();

  // =========================
  // Theme: always LIGHT on entry + toggle
  // =========================
  const root = document.documentElement;
  const themeBtn = document.getElementById("themeBtn");

  function setTheme(theme) {
    root.setAttribute("data-bs-theme", theme);
    localStorage.setItem("theme", theme);

    const icon = themeBtn ? themeBtn.querySelector("i") : null;
    if (icon) {
      icon.classList.remove("bi-moon-stars", "bi-sun");
      icon.classList.add(theme === "dark" ? "bi-sun" : "bi-moon-stars");
    }
  }

  // Force start in light (ignores any previous saved theme)
  setTheme("light");

  if (themeBtn) {
    themeBtn.addEventListener("click", () => {
      const current = root.getAttribute("data-bs-theme") || "light";
      setTheme(current === "dark" ? "light" : "dark");
    });
  }

  // =========================
  // Back to top: FAST + smooth
  // =========================
  const backToTop = document.getElementById("backToTop");
  if (backToTop) {
    backToTop.addEventListener("click", (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, left: 0, behavior: "smooth" });
    });
  }

  // Optional: show/hide backToTop based on scroll
  const toggleBackToTop = () => {
    if (!backToTop) return;
    backToTop.classList.toggle("is-visible", window.scrollY > 400);
  };
  toggleBackToTop();
  window.addEventListener("scroll", toggleBackToTop, { passive: true });

  // =========================
  // Reveal animation
  // =========================
  const reveals = document.querySelectorAll(".reveal");
  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          io.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.12 }
  );
  reveals.forEach((el) => io.observe(el));

  // =========================
  // I18N (full translation)
  // =========================
  const langLabel = document.getElementById("langLabel");
  const langButtons = document.querySelectorAll("[data-lang]");
  const i18nNodes = document.querySelectorAll("[data-i18n]");

  // IMPORTANT:
  // - Keep name "Pablo Troncoso"
  // - Translate only the word Portafolio/Portfolio/Portefølje across ES/EN/DA
  const dict = {
    es: {
      pageTitle: "Portafolio Pablo Troncoso",
      brandName: "Portafolio Pablo Troncoso",

      toggleThemeA11y: "Cambiar tema",

      navHome: "Home",
      navProfile: "Perfil",
      navExperience: "Experiencia",
      navSkills: "Habilidades",
      navLangs: "Idiomas",
      navEducation: "Educación",
      navCerts: "Certificaciones",
      navContact: "Contacto",

      personalSiteLabel: "Sitio personal",
      whatsLabel: "WhatsApp",

      profileTitle: "Máster en Dirección de Marketing & Desarrollador Full Stack",
      profileSummary:
        "Profesional con más de 15 años de experiencia en marketing estratégico y digital, complementado con formación y práctica en desarrollo Full Stack. Integro visión comercial, análisis y tecnología para diseñar e implementar soluciones digitales orientadas a resultados, eficiencia y crecimiento sostenible.",

      expTitle: "Experiencia",
      expIntro:
        "A lo largo de mi trayectoria profesional he desarrollado e implementado estrategias de marketing orientadas al posicionamiento competitivo, crecimiento sostenible y fortalecimiento de la relación entre marcas y audiencias, colaborando con organizaciones de sectores como telecomunicaciones, seguridad y prevención, educación y servicios.",

      expH1: "Integración Estratégica y Desarrollo Digital",
      expH1P1:
        "Uno de los ejes centrales de mi experiencia es la integración entre estrategia comercial y ejecución tecnológica. No solo participo en la planificación de iniciativas digitales, sino también en su implementación técnica cuando el proyecto lo requiere.",
      expH1P2:
        "Esta combinación permite transformar objetivos de negocio en soluciones concretas y funcionales, optimizando procesos, automatizando flujos y asegurando coherencia entre visión estratégica y desarrollo digital. La integración entre marketing y tecnología facilita resultados medibles, eficiencia operativa y escalabilidad en el tiempo.",

      expH2: "Estrategia Comercial y Desarrollo de Negocio",
      expH2P1:
        "He participado en el diseño de estrategias comerciales basadas en análisis de mercado, segmentación y planificación estructurada de acciones orientadas a resultados.",
      expH2P2:
        "Mi enfoque considera de manera integral el ciclo de vida del cliente, abordando adquisición, activación, fidelización y retención mediante planes de comunicación estratégica, gestión de datos y optimización continua del performance digital.",
      expH2P3:
        "En entornos corporativos vinculados a telecomunicaciones y seguridad, he contribuido al desarrollo de estrategias de captación y permanencia, fortalecimiento de comunicación institucional y alineación de marketing con métricas claras de desempeño.",

      expH3: "Marketing Digital y Performance",
      expH3P1:
        "He diseñado e implementado estrategias de SEO y SEM, campañas digitales multicanal y planificación de contenidos orientados a visibilidad, conversión y crecimiento sostenido.",
      expH3P2:
        "La gestión estratégica de bases de datos, segmentación y automatización han sido componentes relevantes para maximizar el valor del cliente y mejorar el retorno de inversión en iniciativas digitales.",

      expH4: "Instituciones Académicas y Entornos Formales",
      expH4P1:
        "He colaborado con instituciones académicas en el fortalecimiento de su presencia digital y comunicación estratégica, participando en proyectos de posicionamiento, captación y vinculación con públicos específicos, adaptando la estrategia digital a contextos institucionales estructurados.",

      skillsTitle: "Habilidades",
      sk1: "Marketing Estratégico",
      sk2: "Marketing Digital",
      sk3: "SEO",
      sk4: "SEM / Google Ads",
      sk5: "Analítica & Reporting",
      sk6: "CRM & Segmentación",
      sk7: "Gestión de Audiencias",
      sk8: "Contenido & Social Media",
      sk9: "Gestión de Proyectos",
      sk10: "Comunicación",
      sk11: "Pensamiento Estratégico",
      sk12: "Orientación a Resultados",
      sk13: "JavaScript",
      sk14: "PHP",
      sk15: "MySQL",
      sk16: "WordPress",
      sk17: "Adobe (PS/AI/LR)",
      sk18: "Google Workspace / Office",

      langsTitle: "Idiomas",
      langEs: "Español — Nativo",
      langEn: "Inglés — Avanzado",

      eduTitle: "Educación",
      edu1Title: "Máster en Dirección de Marketing",
      edu1Meta: "Universidad Adolfo Ibáñez",
      edu2Title: "Publicista Licenciado en Comunicación Social",
      edu2Meta: "Universidad Diego Portales",
      edu3Title: "Programador Full Stack JavaScript",
      edu3Meta: "Formación / Bootcamp",

      certTitle: "Certificaciones",
      cert1Title: "Google Ads",
      cert1Meta: "Certificación / Formación",
      cert2Title: "Marketing Digital",
      cert2Meta: "Cursos y especializaciones",
      cert3Title: "Desarrollo Web / Full Stack",
      cert3Meta: "JavaScript, HTML, CSS, bases de datos",

      contactTitle: "Contacto",
      contactSubtitle: "Disponible para incorporación inmediata en modalidades remota o híbrida.",
      contactBtnText: "Enviar correo",

      footerName: "Pablo Troncoso",
      backToTopA11y: "Volver al inicio"
    },

    en: {
      pageTitle: "Portfolio Pablo Troncoso",
      brandName: "Portfolio Pablo Troncoso",

      toggleThemeA11y: "Toggle theme",

      navHome: "Home",
      navProfile: "Profile",
      navExperience: "Experience",
      navSkills: "Skills",
      navLangs: "Languages",
      navEducation: "Education",
      navCerts: "Certifications",
      navContact: "Contact",

      personalSiteLabel: "Personal website",
      whatsLabel: "WhatsApp",

      profileTitle: "Master’s in Marketing Management & Full-Stack Developer",
      profileSummary:
        "Professional with 15+ years of experience in strategic and digital marketing, complemented by full-stack development training and practice. I combine business vision, analytics, and technology to design and implement digital solutions focused on results, efficiency, and sustainable growth.",

      expTitle: "Experience",
      expIntro:
        "Throughout my career, I have developed and executed marketing strategies focused on competitive positioning, sustainable growth, and stronger relationships between brands and audiences—collaborating with organizations in telecommunications, safety & prevention, education, and services.",

      expH1: "Strategic Integration & Digital Development",
      expH1P1:
        "A core axis of my experience is integrating business strategy with technological execution. I take part not only in planning digital initiatives, but also in technical implementation when needed.",
      expH1P2:
        "This combination turns business goals into concrete, functional solutions—optimizing processes, automating workflows, and ensuring alignment between strategic vision and digital execution. Integrating marketing and technology enables measurable results, operational efficiency, and scalability over time.",

      expH2: "Commercial Strategy & Business Development",
      expH2P1:
        "I have contributed to commercial strategy design based on market analysis, segmentation, and structured planning of results-driven actions.",
      expH2P2:
        "My approach considers the full customer lifecycle—acquisition, activation, loyalty, and retention—through strategic communication plans, data management, and continuous performance optimization.",
      expH2P3:
        "In corporate environments linked to telecommunications and safety, I have supported acquisition and retention strategies, strengthened institutional communication, and aligned marketing with clear performance metrics.",

      expH3: "Digital Marketing & Performance",
      expH3P1:
        "I have designed and executed SEO/SEM strategies, multi-channel digital campaigns, and content plans focused on visibility, conversion, and sustained growth.",
      expH3P2:
        "Strategic database management, segmentation, and automation have been key components to maximize customer value and improve ROI across digital initiatives.",

      expH4: "Academic Institutions & Formal Environments",
      expH4P1:
        "I have collaborated with academic institutions to strengthen their digital presence and strategic communication—supporting positioning, acquisition, and audience engagement projects tailored to structured institutional contexts.",

      skillsTitle: "Skills",
      sk1: "Strategic Marketing",
      sk2: "Digital Marketing",
      sk3: "SEO",
      sk4: "SEM / Google Ads",
      sk5: "Analytics & Reporting",
      sk6: "CRM & Segmentation",
      sk7: "Audience Management",
      sk8: "Content & Social Media",
      sk9: "Project Management",
      sk10: "Communication",
      sk11: "Strategic Thinking",
      sk12: "Results-driven",
      sk13: "JavaScript",
      sk14: "PHP",
      sk15: "MySQL",
      sk16: "WordPress",
      sk17: "Adobe (PS/AI/LR)",
      sk18: "Google Workspace / Office",

      langsTitle: "Languages",
      langEs: "Spanish — Native",
      langEn: "English — Advanced",

      eduTitle: "Education",
      edu1Title: "Master’s in Marketing Management",
      edu1Meta: "Universidad Adolfo Ibáñez",
      edu2Title: "Advertising & Communications (B.A.)",
      edu2Meta: "Universidad Diego Portales",
      edu3Title: "Full Stack JavaScript Developer",
      edu3Meta: "Training / Bootcamp",

      certTitle: "Certifications",
      cert1Title: "Google Ads",
      cert1Meta: "Certification / Training",
      cert2Title: "Digital Marketing",
      cert2Meta: "Courses & specializations",
      cert3Title: "Web Development / Full Stack",
      cert3Meta: "JavaScript, HTML, CSS, databases",

      contactTitle: "Contact",
      contactSubtitle: "Available for immediate start in remote or hybrid roles.",
      contactBtnText: "Send email",

      footerName: "Pablo Troncoso",
      backToTopA11y: "Back to top"
    },

    da: {
      pageTitle: "Portefølje Pablo Troncoso",
      brandName: "Portefølje Pablo Troncoso",

      toggleThemeA11y: "Skift tema",

      navHome: "Home",
      navProfile: "Profil",
      navExperience: "Erfaring",
      navSkills: "Kompetencer",
      navLangs: "Sprog",
      navEducation: "Uddannelse",
      navCerts: "Certificeringer",
      navContact: "Kontakt",

      personalSiteLabel: "Personlig hjemmeside",
      whatsLabel: "WhatsApp",

      profileTitle: "Master i marketingledelse & Full-Stack udvikler",
      profileSummary:
        "Professionel med 15+ års erfaring inden for strategisk og digital marketing, suppleret med træning og praksis i full-stack udvikling. Jeg kombinerer forretningsforståelse, analyse og teknologi for at designe og implementere digitale løsninger med fokus på resultater, effektivitet og bæredygtig vækst.",

      expTitle: "Erfaring",
      expIntro:
        "Gennem min karriere har jeg udviklet og eksekveret marketingstrategier med fokus på konkurrencedygtig positionering, bæredygtig vækst og stærkere relationer mellem brands og målgrupper—i samarbejde med organisationer inden for telekommunikation, sikkerhed & forebyggelse, uddannelse og services.",

      expH1: "Strategisk integration & digital udvikling",
      expH1P1:
        "Et centralt område i min erfaring er at integrere forretningsstrategi med teknisk eksekvering. Jeg deltager ikke kun i planlægningen af digitale initiativer, men også i den tekniske implementering, når det er nødvendigt.",
      expH1P2:
        "Denne kombination omsætter forretningsmål til konkrete, funktionelle løsninger—optimerer processer, automatiserer workflows og sikrer sammenhæng mellem strategi og digital eksekvering. Integration af marketing og teknologi giver målbare resultater, operationel effektivitet og skalerbarhed over tid.",

      expH2: "Kommerciel strategi & forretningsudvikling",
      expH2P1:
        "Jeg har bidraget til udvikling af kommercielle strategier baseret på markedsanalyse, segmentering og struktureret planlægning af resultatorienterede initiativer.",
      expH2P2:
        "Min tilgang dækker hele kunderejsen—anskaffelse, aktivering, loyalitet og fastholdelse—via strategiske kommunikationsplaner, datastyring og løbende performance-optimering.",
      expH2P3:
        "I corporate miljøer relateret til telekommunikation og sikkerhed har jeg understøttet strategier for acquisition og retention, styrket institutionel kommunikation og koblet marketing til klare performance-mål.",

      expH3: "Digital marketing & performance",
      expH3P1:
        "Jeg har designet og eksekveret SEO/SEM-strategier, multikanal kampagner og content-planer med fokus på synlighed, konvertering og vedvarende vækst.",
      expH3P2:
        "Strategisk databasehåndtering, segmentering og automation har været centrale elementer til at maksimere kundeværdi og forbedre ROI i digitale initiativer.",

      expH4: "Akademiske institutioner & formelle miljøer",
      expH4P1:
        "Jeg har samarbejdet med akademiske institutioner om at styrke deres digitale tilstedeværelse og strategiske kommunikation—med projekter inden for positionering, acquisition og målgruppeengagement tilpasset strukturerede institutionelle rammer.",

      skillsTitle: "Kompetencer",
      sk1: "Strategisk marketing",
      sk2: "Digital marketing",
      sk3: "SEO",
      sk4: "SEM / Google Ads",
      sk5: "Analyse & rapportering",
      sk6: "CRM & segmentering",
      sk7: "Målgruppestyring",
      sk8: "Indhold & sociale medier",
      sk9: "Projektledelse",
      sk10: "Kommunikation",
      sk11: "Strategisk tænkning",
      sk12: "Resultatorienteret",
      sk13: "JavaScript",
      sk14: "PHP",
      sk15: "MySQL",
      sk16: "WordPress",
      sk17: "Adobe (PS/AI/LR)",
      sk18: "Google Workspace / Office",

      langsTitle: "Sprog",
      langEs: "Spansk — modersmål",
      langEn: "Engelsk — avanceret",

      eduTitle: "Uddannelse",
      edu1Title: "Master i marketingledelse",
      edu1Meta: "Universidad Adolfo Ibáñez",
      edu2Title: "Reklame & kommunikation (B.A.)",
      edu2Meta: "Universidad Diego Portales",
      edu3Title: "Full Stack JavaScript udvikler",
      edu3Meta: "Træning / bootcamp",

      certTitle: "Certificeringer",
      cert1Title: "Google Ads",
      cert1Meta: "Certificering / træning",
      cert2Title: "Digital marketing",
      cert2Meta: "Kurser & specialiseringer",
      cert3Title: "Webudvikling / Full Stack",
      cert3Meta: "JavaScript, HTML, CSS, databaser",

      contactTitle: "Kontakt",
      contactSubtitle: "Tilgængelig for hurtig opstart i remote eller hybride roller.",
      contactBtnText: "Send e-mail",

      footerName: "Pablo Troncoso",
      backToTopA11y: "Til toppen"
    }
  };

  function applyLang(lang) {
    const t = dict[lang] || dict.es;

    // Translate nodes
    i18nNodes.forEach((node) => {
      const key = node.getAttribute("data-i18n");
      if (!key) return;

      // Only update if key exists in dict.
      // This prevents accidental blanking.
      if (Object.prototype.hasOwnProperty.call(t, key)) {
        node.textContent = t[key];
      }
    });

    // Label (ES/EN/DA)
    if (langLabel) langLabel.textContent = lang.toUpperCase();

    // Save
    localStorage.setItem("lang", lang);

    // Set document language
    document.documentElement.setAttribute(
      "lang",
      lang === "da" ? "da" : lang === "en" ? "en" : "es"
    );

    // Page title
    if (t.pageTitle) document.title = t.pageTitle;

    // A11y labels
    if (themeBtn && t.toggleThemeA11y) {
      themeBtn.setAttribute("aria-label", t.toggleThemeA11y);
      themeBtn.setAttribute("title", t.toggleThemeA11y);
    }
    if (backToTop && t.backToTopA11y) {
      backToTop.setAttribute("aria-label", t.backToTopA11y);
      backToTop.setAttribute("title", t.backToTopA11y);
    }
  }

  const savedLang = localStorage.getItem("lang") || "es";
  applyLang(savedLang);

  // Language switch
  langButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const lang = btn.getAttribute("data-lang");
      if (!lang) return;
      applyLang(lang);
    });
  });
});