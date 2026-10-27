<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Máximo PH | Propiedad Horizontal con Pagos AV Villas y Contabilidad en Tiempo Real</title>
    <meta name="description" content="Administración, facturación y contabilidad en una sola plataforma. Pagos AV Villas en tiempo real, Portafolio ERP, migración de datos en un día y soporte y capacitación permanente.">
    <meta name="author" content="Máximo PH">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <meta name="theme-color" content="#059669">
    <meta name="color-scheme" content="light dark">
    <link rel="canonical" href="https://maximoph.co/">

    <link rel="icon" type="image/png" href="/img/logo_base.png">
    <link rel="apple-touch-icon" href="/img/logo_base.png">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Máximo PH">
    <meta property="og:locale" content="es_CO">
    <meta property="og:title" content="Máximo PH | Pagos AV Villas en tiempo real + Contabilidad automática">
    <meta property="og:description" content="Administración, facturación y contabilidad en una sola plataforma. Migración en un día y soporte permanente.">
    <meta property="og:url" content="https://maximoph.co/">
    <meta property="og:image" content="https://maximoph.co/img/og-image.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@MaximoPH">
    <meta name="twitter:title" content="Máximo PH | Propiedad Horizontal + Contabilidad en Tiempo Real">
    <meta name="twitter:description" content="Pagos AV Villas en tiempo real, facturación, portería, PQRS y contabilidad automática en Portafolio ERP.">
    <meta name="twitter:image" content="https://maximoph.co/img/og-image.jpg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "@id": "https://maximoph.co/#organization",
      "name": "Máximo PH",
      "url": "https://maximoph.co/",
      "logo": "https://maximoph.co/img/logo_base.png",
      "description": "Software de gestión de propiedad horizontal con pagos AV Villas en tiempo real y contabilidad automática mediante Portafolio ERP.",
      "address": { "@type": "PostalAddress", "addressLocality": "Bogotá", "addressCountry": "CO" },
      "contactPoint": [{ "@type": "ContactPoint", "telephone": "+57-320-714-1104", "contactType": "sales", "areaServed": "CO", "availableLanguage": ["es"] }],
      "sameAs": ["https://portafolioerp.com"]
    }
    </script>

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "@id": "https://maximoph.co/#software",
      "name": "Máximo PH",
      "applicationCategory": "BusinessApplication",
      "applicationSubCategory": "Property Management",
      "operatingSystem": "Web",
      "description": "Administración, facturación y contabilidad en una sola plataforma para propiedad horizontal, con pagos AV Villas en tiempo real y Portafolio ERP.",
      "featureList": [
        "Pagos AV Villas en tiempo real",
        "Contabilidad en tiempo real con Portafolio ERP",
        "Facturación por coeficientes",
        "Envío de facturación por correo y WhatsApp",
        "Importador de pagos masivos",
        "Módulo de gastos automático con Portafolio ERP",
        "Portería, PQRS y censo familiar",
        "Migración de datos en un día",
        "Soporte y capacitación permanente"
      ],
      "publisher": { "@id": "https://maximoph.co/#organization" },
      "areaServed": { "@type": "Country", "name": "Colombia" }
    }
    </script>

    <script>
        (function () {
            try {
                var savedTheme = localStorage.getItem('theme');
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.setAttribute('data-theme', savedTheme || (prefersDark ? 'dark' : 'light'));
            } catch (e) {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>

    <script src="https://cdn.jsdelivr.net/npm/animejs@4.0.2/lib/anime.iife.min.js"></script>

    <link id="pagestyle" href="{{ asset('assets/css/landing-page.css') }}" rel="stylesheet" />
</head>

<body>

    <!-- Sprite de iconos -->
    <svg width="0" height="0" style="position:absolute" aria-hidden="true">
        <defs>
            <symbol id="i-check" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></symbol>
            <symbol id="i-user" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></symbol>
            <symbol id="i-users" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></symbol>
            <symbol id="i-bank" viewBox="0 0 24 24"><path d="M3 21h18M5 21V10M9 21V10M15 21V10M19 21V10M2 10l10-7 10 7"/></symbol>
            <symbol id="i-building" viewBox="0 0 24 24"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/><path d="M9 9v.01M9 12v.01M9 15v.01"/></symbol>
            <symbol id="i-layers" viewBox="0 0 24 24"><path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="m2 17 10 5 10-5"/><path d="m2 12 10 5 10-5"/></symbol>
            <symbol id="i-file" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></symbol>
            <symbol id="i-bolt" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></symbol>
            <symbol id="i-chat" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></symbol>
            <symbol id="i-mail" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 6-10 7L2 6"/></symbol>
            <symbol id="i-upload" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></symbol>
            <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></symbol>
            <symbol id="i-clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></symbol>
            <symbol id="i-receipt" viewBox="0 0 24 24"><path d="M4 2v20l3-2 3 2 3-2 3 2 3-2V2l-3 2-3-2-3 2-3-2z"/><path d="M8 8h8M8 12h8"/></symbol>
            <symbol id="i-percent" viewBox="0 0 24 24"><path d="M19 5 5 19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></symbol>
            <symbol id="i-calplus" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M12 14v4M10 16h4"/></symbol>
            <symbol id="i-chart" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 5-6"/></symbol>
            <symbol id="i-package" viewBox="0 0 24 24"><path d="M21 8 12 3 3 8v8l9 5 9-5z"/><path d="m3 8 9 5 9-5M12 13v8"/></symbol>
            <symbol id="i-db" viewBox="0 0 24 24"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5"/><path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3"/></symbol>
            <symbol id="i-headset" viewBox="0 0 24 24"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></symbol>
            <symbol id="i-cap" viewBox="0 0 24 24"><path d="M22 10 12 5 2 10l10 5 10-5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></symbol>
            <symbol id="i-shield" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></symbol>
            <symbol id="i-phone" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></symbol>
            <symbol id="i-pin" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></symbol>
            <symbol id="i-login" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></symbol>
            <symbol id="i-sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></symbol>
            <symbol id="i-moon" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></symbol>
            <symbol id="i-up" viewBox="0 0 24 24"><path d="M12 19V5M5 12l7-7 7 7"/></symbol>
            <symbol id="i-wa" viewBox="0 0 24 24"><path fill="currentColor" stroke="none" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884"/></symbol>
        </defs>
    </svg>

    <div class="progress-bar" id="progressBar" aria-hidden="true"></div>

    <header class="header" id="header">
        <div class="container header-content">
            <a href="/" class="logo" aria-label="Máximo PH - Inicio">
                <img src="https://maximoph.co/img/logo_base.png" alt="Máximo PH" width="44" height="44">
                <span class="logo-text">MÁXIMO PH</span>
            </a>

            <nav class="nav" id="nav" aria-label="Navegación principal">
                <a href="#inicio" class="nav-link active">Inicio</a>
                <a href="#pagos" class="nav-link">Pagos en vivo</a>
                <a href="#plataforma" class="nav-link">Plataforma</a>
                <a href="#modulos" class="nav-link">Módulos</a>
                <a href="#migracion" class="nav-link">Migración</a>
                <a href="#roles" class="nav-link">Roles</a>

                <div class="nav-actions">
                    <button class="theme-toggle" id="themeToggle" aria-label="Cambiar tema">
                        <svg class="ic sun-icon"><use href="#i-sun"/></svg>
                        <svg class="ic moon-icon"><use href="#i-moon"/></svg>
                    </button>
                    <a href="/login" class="btn btn-primary">
                        <svg class="ic" width="16" height="16"><use href="#i-login"/></svg>
                        Ingresar
                    </a>
                </div>
            </nav>

            <button class="mobile-toggle" id="mobileToggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="nav">
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
                <span class="hamburger-line"></span>
            </button>
        </div>
    </header>

    <a href="https://wa.me/573207141104?text=Hola,%20quiero%20una%20demo%20de%20Máximo%20PH" target="_blank" rel="noopener noreferrer" class="whatsapp-float" aria-label="Contáctanos por WhatsApp">
        <svg viewBox="0 0 24 24" width="28" height="28"><use href="#i-wa"/></svg>
    </a>

    <button class="scroll-to-top" id="scrollTopBtn" aria-label="Volver arriba">
        <svg class="ic" width="20" height="20"><use href="#i-up"/></svg>
    </button>

    <main id="main">

        <!-- ==================== HERO ==================== -->
        <section id="inicio" class="hero">
            <div class="hero-bg" aria-hidden="true">
                <div class="hero-grid-bg"></div>
                <div class="blob blob-1"></div>
                <div class="blob blob-2"></div>
                <div class="blob blob-3"></div>
            </div>

            <div class="container">
                <div class="hero-grid">

                    <div class="hero-content">
                        <div class="badge" data-reveal="up">
                            <span class="live-dot"></span>
                            Nuevo · Pagos AV Villas en tiempo real
                        </div>

                        <h1 class="hero-title" data-split>
                            Administración, facturación y contabilidad en
                            <span class="highlight">una sola plataforma.</span>
                        </h1>

                        <p class="hero-description" data-reveal="up" data-delay="250">
                            Máximo PH + <strong>Portafolio ERP</strong>: tus residentes pagan con
                            <strong>AV Villas</strong>, el pago se refleja al instante y la contabilidad
                            se genera sola. Migramos tus datos en <strong>un día</strong> y te acompañamos
                            con soporte y capacitación permanente.
                        </p>

                        <div class="hero-buttons" data-reveal="up" data-delay="400">
                            <a href="https://wa.me/573207141104?text=Hola,%20quiero%20una%20demo%20de%20Máximo%20PH" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-lg magnetic">
                                <svg class="ic" width="20" height="20"><use href="#i-wa"/></svg>
                                Solicitar demo
                            </a>
                            <a href="/login" class="btn btn-outline btn-lg magnetic">
                                Iniciar sesión
                                <svg class="ic" width="18" height="18"><use href="#i-arrow"/></svg>
                            </a>
                        </div>

                        <ul class="hero-points" data-reveal="up" data-delay="550">
                            <li><svg class="ic"><use href="#i-check"/></svg> Pagos AV Villas en tiempo real</li>
                            <li><svg class="ic"><use href="#i-check"/></svg> Contabilidad en tiempo real</li>
                            <li><svg class="ic"><use href="#i-check"/></svg> Migración de datos en 1 día</li>
                            <li><svg class="ic"><use href="#i-check"/></svg> Soporte y capacitación permanente</li>
                        </ul>
                    </div>

                    <div class="hero-visual" id="heroVisual">
                        <div class="dash-wrap" id="dashWrap" data-reveal="scale" data-delay="300">
                            <div class="dash" id="dash">
                                <div class="dash-top">
                                    <div class="dash-dots"><i></i><i></i><i></i></div>
                                    <div class="dash-title">Recaudo en vivo</div>
                                    <div class="live-pill"><span class="live-dot"></span>En vivo</div>
                                </div>

                                <div class="dash-kpis">
                                    <div class="kpi">
                                        <span class="kpi-label">Recaudado hoy</span>
                                        <span class="kpi-value" id="kpiTotal">$12.480.000</span>
                                    </div>
                                    <div class="kpi">
                                        <span class="kpi-label">Pagos aplicados</span>
                                        <span class="kpi-value" id="kpiCount">38</span>
                                    </div>
                                    <div class="kpi">
                                        <span class="kpi-label">Asientos</span>
                                        <span class="kpi-value" id="kpiAsientos">38</span>
                                    </div>
                                </div>

                                <div class="dash-chart" id="dashChart" aria-hidden="true"></div>

                                <ul class="feed" id="feed">
                                    <li class="feed-item">
                                        <span class="feed-avatar">502</span>
                                        <div class="feed-info"><strong>Apto 502 · Torre 2</strong><span>Cuota de administración</span></div>
                                        <div class="feed-right"><b>$450.000</b><span class="tag tag-bank">AV Villas</span><span class="tag tag-ok">✓ Contabilizado</span></div>
                                    </li>
                                    <li class="feed-item">
                                        <span class="feed-avatar">17</span>
                                        <div class="feed-info"><strong>Casa 17</strong><span>Cuota de administración</span></div>
                                        <div class="feed-right"><b>$380.000</b><span class="tag tag-bank">AV Villas</span><span class="tag tag-ok">✓ Contabilizado</span></div>
                                    </li>
                                    <li class="feed-item">
                                        <span class="feed-avatar">1204</span>
                                        <div class="feed-info"><strong>Apto 1204 · Torre 1</strong><span>Cuota extraordinaria</span></div>
                                        <div class="feed-right"><b>$520.000</b><span class="tag tag-bank">AV Villas</span><span class="tag tag-ok">✓ Contabilizado</span></div>
                                    </li>
                                </ul>
                            </div>

                            <div class="float-chip chip-erp">
                                <img src="https://app.portafolioerp.com/img/logo_contabilidad.png" alt="Portafolio ERP" width="34" height="34">
                                <div>
                                    <span class="chip-label">Asiento generado</span>
                                    <span class="chip-name">Portafolio ERP</span>
                                </div>
                            </div>

                            <div class="float-chip chip-bank">
                                <span class="chip-ic"><svg class="ic"><use href="#i-bank"/></svg></span>
                                <div>
                                    <span class="chip-label">AV Villas</span>
                                    <span class="chip-name">Pago aprobado ✓</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ==================== MARQUEE ==================== -->
        <section class="marquee" aria-label="Capacidades principales">
            <div class="marquee-track">
                <div class="marquee-group">
                    <span>Pagos AV Villas en tiempo real</span><i></i>
                    <span>Contabilidad en tiempo real</span><i></i>
                    <span>Migración en 1 día</span><i></i>
                    <span>Soporte y capacitación permanente</span><i></i>
                    <span>Importador de pagos masivos</span><i></i>
                    <span>Gastos automáticos con Portafolio ERP</span><i></i>
                    <span>Portería · PQRS · Censo familiar</span><i></i>
                    <span>Facturación por correo y WhatsApp</span><i></i>
                </div>
                <div class="marquee-group" aria-hidden="true">
                    <span>Pagos AV Villas en tiempo real</span><i></i>
                    <span>Contabilidad en tiempo real</span><i></i>
                    <span>Migración en 1 día</span><i></i>
                    <span>Soporte y capacitación permanente</span><i></i>
                    <span>Importador de pagos masivos</span><i></i>
                    <span>Gastos automáticos con Portafolio ERP</span><i></i>
                    <span>Portería · PQRS · Censo familiar</span><i></i>
                    <span>Facturación por correo y WhatsApp</span><i></i>
                </div>
            </div>
        </section>

        <!-- ==================== PAGOS EN TIEMPO REAL ==================== -->
        <section id="pagos" class="pagos">
            <div class="pagos-glow" aria-hidden="true"></div>
            <div class="container">
                <div class="section-header">
                    <div class="section-badge section-badge-dark">Pasarela AV Villas</div>
                    <h2 class="section-title" data-split>
                        Un pago entra al banco y
                        <span class="highlight">tu contabilidad ya lo sabe</span>
                    </h2>
                    <p class="section-description" data-reveal="up">
                        Pagos con AV Villas en tiempo real, conectados a Máximo PH y a Portafolio ERP.
                        Sin esperar extractos, sin conciliar a mano, sin digitar dos veces.
                    </p>
                </div>

                <div class="pipe" id="pipe">
                    <div class="pipe-node" data-reveal="up" data-delay="0">
                        <div class="pipe-ic"><svg class="ic"><use href="#i-user"/></svg></div>
                        <div class="pipe-txt"><b>El residente paga</b><span>Desde su estado de cuenta</span></div>
                    </div>
                    <div class="pipe-link"><div class="pipe-fill"></div><div class="pipe-dot"></div></div>

                    <div class="pipe-node" data-reveal="up" data-delay="120">
                        <div class="pipe-ic"><svg class="ic"><use href="#i-bank"/></svg></div>
                        <div class="pipe-txt"><b>AV Villas aprueba</b><span>Confirmación inmediata</span></div>
                    </div>
                    <div class="pipe-link"><div class="pipe-fill"></div><div class="pipe-dot"></div></div>

                    <div class="pipe-node" data-reveal="up" data-delay="240">
                        <div class="pipe-ic"><svg class="ic"><use href="#i-building"/></svg></div>
                        <div class="pipe-txt"><b>Máximo PH aplica</b><span>Cruza con la factura</span></div>
                    </div>
                    <div class="pipe-link"><div class="pipe-fill"></div><div class="pipe-dot"></div></div>

                    <div class="pipe-node pipe-node-erp" data-reveal="up" data-delay="360">
                        <div class="pipe-ic"><img src="https://app.portafolioerp.com/img/logo_contabilidad.png" alt="Portafolio ERP" width="40" height="40"></div>
                        <div class="pipe-txt"><b>Portafolio ERP contabiliza</b><span>Asiento automático</span></div>
                    </div>
                    <div class="pipe-link"><div class="pipe-fill"></div><div class="pipe-dot"></div></div>

                    <div class="pipe-node" data-reveal="up" data-delay="480">
                        <div class="pipe-ic"><svg class="ic"><use href="#i-shield"/></svg></div>
                        <div class="pipe-txt"><b>Cuenta al día</b><span>Residente y administrador</span></div>
                    </div>
                </div>

                <div class="pagos-grid">
                    <div class="pagos-benefits">
                        <div class="pb" data-reveal="left">
                            <span class="pb-ic"><svg class="ic"><use href="#i-bolt"/></svg></span>
                            <div><h3>Recaudo que se ve al instante</h3><p>El pago aprobado se refleja en el estado de cuenta y en el dashboard del administrador sin intervención manual.</p></div>
                        </div>
                        <div class="pb" data-reveal="left" data-delay="120">
                            <span class="pb-ic"><svg class="ic"><use href="#i-layers"/></svg></span>
                            <div><h3>Contabilidad que se escribe sola</h3><p>Cada pago genera su asiento en Portafolio ERP en tiempo real. Tu contador solo revisa y cierra.</p></div>
                        </div>
                        <div class="pb" data-reveal="left" data-delay="240">
                            <span class="pb-ic"><svg class="ic"><use href="#i-upload"/></svg></span>
                            <div><h3>Pagos masivos con importador</h3><p>¿Pagos por consignación o por otros canales? Cárgalos en lote y quedan aplicados y contabilizados.</p></div>
                        </div>
                    </div>

                    <div class="ledger-panel" id="ledgerPanel" data-reveal="right">
                        <div class="ledger-header">
                            <div class="ledger-title"><svg class="ic" width="16" height="16"><use href="#i-file"/></svg> Asiento generado</div>
                            <div class="ledger-status"><span class="live-dot"></span> Portafolio ERP</div>
                        </div>
                        <div class="ledger-body">
                            <div class="ledger-row ledger-row-header">
                                <span>Cuenta</span><span>Concepto</span><span>Débito</span><span>Crédito</span>
                            </div>
                            <div class="ledger-row ledger-anim-row">
                                <span class="ledger-account">1110</span>
                                <span class="ledger-concept">Bancos · AV Villas</span>
                                <span class="ledger-debit">$450.000</span>
                                <span class="ledger-credit">—</span>
                            </div>
                            <div class="ledger-row ledger-anim-row">
                                <span class="ledger-account">1305</span>
                                <span class="ledger-concept">Cuentas por cobrar · Apto 502</span>
                                <span class="ledger-debit">—</span>
                                <span class="ledger-credit">$450.000</span>
                            </div>
                        </div>
                        <div class="ledger-footer">
                            <span class="ledger-total-label">Partida doble cuadrada</span>
                            <span class="ledger-total-value" id="ledgerTotal">$450.000</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== PLATAFORMA ÚNICA ==================== -->
        <section id="plataforma" class="platform">
            <div class="container">
                <div class="section-header">
                    <div class="section-badge">Todo en uno</div>
                    <h2 class="section-title" data-split>
                        Contabilidad, facturación y administración,
                        <span class="highlight">sin saltar entre sistemas</span>
                    </h2>
                    <p class="section-description" data-reveal="up">
                        Una sola plataforma, una sola fuente de verdad. Lo que se registra en un lugar
                        aparece en los demás, sin exportar ni digitar dos veces.
                    </p>
                </div>

                <div class="pillars">
                    <article class="pillar spot" data-reveal="up" data-delay="0">
                        <div class="pillar-ic pillar-ic-green"><svg class="ic"><use href="#i-building"/></svg></div>
                        <h3>Administración</h3>
                        <p class="pillar-sub">Máximo PH</p>
                        <ul>
                            <li><svg class="ic"><use href="#i-check"/></svg>Portería y visitas</li>
                            <li><svg class="ic"><use href="#i-check"/></svg>PQRS con seguimiento</li>
                            <li><svg class="ic"><use href="#i-check"/></svg>Censo familiar</li>
                            <li><svg class="ic"><use href="#i-check"/></svg>Paz y salvo en PDF</li>
                            <li><svg class="ic"><use href="#i-check"/></svg>Comunicados masivos</li>
                        </ul>
                    </article>

                    <div class="pillar-plus" aria-hidden="true">+</div>

                    <article class="pillar spot" data-reveal="up" data-delay="140">
                        <div class="pillar-ic pillar-ic-orange"><svg class="ic"><use href="#i-receipt"/></svg></div>
                        <h3>Facturación</h3>
                        <p class="pillar-sub">Máximo PH</p>
                        <ul>
                            <li><svg class="ic"><use href="#i-check"/></svg>Facturación por coeficientes</li>
                            <li><svg class="ic"><use href="#i-check"/></svg>Cruce de anticipos e intereses</li>
                            <li><svg class="ic"><use href="#i-check"/></svg>Cuotas extras y multas</li>
                            <li><svg class="ic"><use href="#i-check"/></svg>Envío por correo y WhatsApp</li>
                            <li><svg class="ic"><use href="#i-check"/></svg>Pagos AV Villas en línea</li>
                        </ul>
                    </article>

                    <div class="pillar-plus" aria-hidden="true">+</div>

                    <article class="pillar pillar-erp spot" data-reveal="up" data-delay="280">
                        <div class="pillar-ic pillar-ic-logo"><img src="https://app.portafolioerp.com/img/logo_contabilidad.png" alt="Portafolio ERP" width="36" height="36"></div>
                        <h3>Contabilidad</h3>
                        <p class="pillar-sub">Portafolio ERP</p>
                        <ul>
                            <li><svg class="ic"><use href="#i-check"/></svg>Asientos en tiempo real</li>
                            <li><svg class="ic"><use href="#i-check"/></svg>Módulo de gastos automático</li>
                            <li><svg class="ic"><use href="#i-check"/></svg>Estados financieros al día</li>
                            <li><svg class="ic"><use href="#i-check"/></svg>PUC colombiano</li>
                            <li><svg class="ic"><use href="#i-check"/></svg>Cierre mensual simplificado</li>
                        </ul>
                    </article>
                </div>

                <div class="unify" data-reveal="up">
                    <span class="unify-bar"></span>
                    <div class="unify-pill"><svg class="ic"><use href="#i-layers"/></svg> = Una sola plataforma, cero doble digitación</div>
                    <span class="unify-bar"></span>
                </div>
            </div>
        </section>

        <!-- ==================== MÓDULOS (BENTO) ==================== -->
        <section id="modulos" class="modules">
            <div class="container">
                <div class="section-header">
                    <div class="section-badge">Módulos incluidos</div>
                    <h2 class="section-title" data-split>
                        Todo lo que tu conjunto necesita,
                        <span class="highlight">trabajando en conjunto</span>
                    </h2>
                    <p class="section-description" data-reveal="up">
                        Desde el importador de pagos hasta la portería. Sin add-ons, sin costos extra.
                    </p>
                </div>

                <div class="bento">
                    <!-- Importador -->
                    <article class="bcard span-2 spot" data-reveal="up">
                        <div class="bcard-head">
                            <span class="bcard-ic bcard-ic-green"><svg class="ic"><use href="#i-upload"/></svg></span>
                            <div><h3>Importador de pagos masivos</h3><p>Sube un archivo y aplica cientos de pagos de una vez, con su asiento contable.</p></div>
                        </div>
                        <div class="vis imp" id="impVis">
                            <div class="imp-file"><svg class="ic" width="18" height="18"><use href="#i-file"/></svg><span>pagos_octubre.xlsx</span><em>248 filas</em></div>
                            <div class="imp-bar"><i></i></div>
                            <ul class="imp-rows">
                                <li><span>Apto 101</span><b>$450.000</b><u>✓ Aplicado</u></li>
                                <li><span>Apto 102</span><b>$450.000</b><u>✓ Aplicado</u></li>
                                <li><span>Apto 203</span><b>$520.000</b><u>✓ Aplicado</u></li>
                                <li><span>Casa 17</span><b>$380.000</b><u>✓ Aplicado</u></li>
                            </ul>
                            <div class="imp-count"><b id="impCount">248</b> pagos aplicados y contabilizados</div>
                        </div>
                    </article>

                    <!-- Gastos -->
                    <article class="bcard span-2 spot" data-reveal="up" data-delay="120">
                        <div class="bcard-head">
                            <span class="bcard-ic bcard-ic-erp"><img src="https://app.portafolioerp.com/img/logo_contabilidad.png" alt="" width="26" height="26"></span>
                            <div><h3>Módulo de gastos automático</h3><p>Registra la factura del proveedor y Portafolio ERP la causa y contabiliza por ti.</p></div>
                        </div>
                        <div class="vis" id="gxVis">
                            <ul class="gx">
                                <li><div><b>Aseo y mantenimiento</b><span>Factura proveedor · $1.200.000</span></div><i class="pill">Causado ✓</i></li>
                                <li><div><b>Vigilancia</b><span>Factura proveedor · $6.800.000</span></div><i class="pill">Causado ✓</i></li>
                                <li><div><b>Servicios públicos</b><span>Factura proveedor · $2.150.000</span></div><i class="pill">Causado ✓</i></li>
                            </ul>
                        </div>
                    </article>

                    <!-- Envío correo / WhatsApp -->
                    <article class="bcard span-2 spot" data-reveal="up">
                        <div class="bcard-head">
                            <span class="bcard-ic bcard-ic-wa"><svg class="ic"><use href="#i-wa"/></svg></span>
                            <div><h3>Facturación por correo y WhatsApp</h3><p>Envía todas las facturas del mes a cada residente por los dos canales, en un clic.</p></div>
                        </div>
                        <div class="vis snd" id="sndVis">
                            <div class="snd-row">
                                <span class="snd-ic"><svg class="ic"><use href="#i-mail"/></svg></span>
                                <div class="snd-main"><div class="snd-label"><b>Correo</b><em><span class="snd-n">0</span> / 500</em></div><div class="snd-bar"><i></i></div></div>
                            </div>
                            <div class="snd-row">
                                <span class="snd-ic snd-ic-wa"><svg class="ic"><use href="#i-wa"/></svg></span>
                                <div class="snd-main"><div class="snd-label"><b>WhatsApp</b><em><span class="snd-n">0</span> / 500</em></div><div class="snd-bar snd-bar-wa"><i></i></div></div>
                            </div>
                        </div>
                    </article>

                    <!-- Portería PQRS Censo -->
                    <article class="bcard span-2 spot" data-reveal="up" data-delay="120">
                        <div class="bcard-head">
                            <span class="bcard-ic bcard-ic-purple"><svg class="ic"><use href="#i-package"/></svg></span>
                            <div><h3>Portería, PQRS y censo familiar</h3><p>La operación diaria del conjunto, trazada y notificada en tiempo real.</p></div>
                        </div>
                        <div class="vis">
                            <ul class="pq" id="pqVis">
                                <li class="is-hot"><span class="pq-ic"><svg class="ic"><use href="#i-package"/></svg></span><div><b>Portería</b><span>Visitas, domicilios y paquetes con aviso al residente</span></div><i></i></li>
                                <li><span class="pq-ic"><svg class="ic"><use href="#i-chat"/></svg></span><div><b>PQRS</b><span>Solicitudes con seguimiento y respuesta formal</span></div><i></i></li>
                                <li><span class="pq-ic"><svg class="ic"><use href="#i-users"/></svg></span><div><b>Censo familiar</b><span>Propietarios, arrendatarios, mascotas y vehículos</span></div><i></i></li>
                            </ul>
                        </div>
                    </article>

                    <!-- Pequeñas -->
                    <article class="bcard bcard-sm spot" data-reveal="up">
                        <span class="bcard-ic bcard-ic-orange"><svg class="ic"><use href="#i-percent"/></svg></span>
                        <h3>Facturación por coeficientes</h3>
                        <p>Cálculo automático por inmueble, con precisión matemática.</p>
                    </article>
                    <article class="bcard bcard-sm spot" data-reveal="up" data-delay="100">
                        <span class="bcard-ic bcard-ic-green"><svg class="ic"><use href="#i-bolt"/></svg></span>
                        <h3>Anticipos e intereses</h3>
                        <p>Cruce automático de anticipos y causación de intereses por mora.</p>
                    </article>
                    <article class="bcard bcard-sm spot" data-reveal="up" data-delay="200">
                        <span class="bcard-ic bcard-ic-purple"><svg class="ic"><use href="#i-calplus"/></svg></span>
                        <h3>Cuotas extras y multas</h3>
                        <p>Individuales o masivas, con trazabilidad completa.</p>
                    </article>
                    <article class="bcard bcard-sm spot" data-reveal="up" data-delay="300">
                        <span class="bcard-ic bcard-ic-green"><svg class="ic"><use href="#i-shield"/></svg></span>
                        <h3>Paz y salvo</h3>
                        <p>Certificado en PDF con verificación automática del estado de cuenta.</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- ==================== MIGRACIÓN + SOPORTE ==================== -->
        <section id="migracion" class="migration">
            <div class="container">
                <div class="mig-grid">
                    <div class="mig-left">
                        <div class="section-badge" data-reveal="up">Sin dolores de cabeza</div>
                        <h2 class="mig-title" data-split>
                            Migramos tus datos en
                            <span class="highlight">un solo día</span>
                        </h2>
                        <p class="section-description" data-reveal="up">
                            Traemos tu información desde Excel u otro software, la validamos contigo y
                            tu conjunto queda operando desde el día uno.
                        </p>

                        <ol class="mig-steps" id="migSteps">
                            <span class="mig-line"><span class="mig-fill" id="migFill"></span></span>
                            <li class="mig-step">
                                <span class="mig-dot"><svg class="ic"><use href="#i-file"/></svg></span>
                                <div><b>Recibimos tu información</b><p>Residentes, coeficientes, saldos y cartera desde Excel u otro sistema.</p></div>
                            </li>
                            <li class="mig-step">
                                <span class="mig-dot"><svg class="ic"><use href="#i-db"/></svg></span>
                                <div><b>Importamos y validamos</b><p>Cargamos todo a Máximo PH y Portafolio ERP y cuadramos saldos contigo.</p></div>
                            </li>
                            <li class="mig-step">
                                <span class="mig-dot"><svg class="ic"><use href="#i-cap"/></svg></span>
                                <div><b>Capacitamos a tu equipo</b><p>Administración, portería y contador aprenden a usarlo de inmediato.</p></div>
                            </li>
                            <li class="mig-step">
                                <span class="mig-dot"><svg class="ic"><use href="#i-check"/></svg></span>
                                <div><b>¡Listo para operar!</b><p>Tu conjunto factura, recauda y contabiliza desde el primer día.</p></div>
                            </li>
                        </ol>
                    </div>

                    <div class="mig-right">
                        <div class="support-card" data-reveal="right">
                            <div class="support-big">
                                <span class="support-num" id="bigDay" data-count="1">1</span>
                                <span class="support-unit">día</span>
                            </div>
                            <p class="support-cap">para migrar tus datos y empezar a operar</p>

                            <div class="support-divider"></div>

                            <h3>Soporte y capacitación <span class="highlight">permanente</span></h3>
                            <ul class="support-list">
                                <li><span><svg class="ic"><use href="#i-cap"/></svg></span><div><b>Capacitación continua</b><p>Para tu equipo actual y para quien llegue después.</p></div></li>
                                <li><span><svg class="ic"><use href="#i-headset"/></svg></span><div><b>Soporte cercano</b><p>Un equipo real que conoce tu conjunto y responde por WhatsApp y teléfono.</p></div></li>
                                <li><span><svg class="ic"><use href="#i-chart"/></svg></span><div><b>Acompañamiento en cierres</b><p>Te ayudamos con el cierre contable y los reportes del mes.</p></div></li>
                            </ul>
                            <a href="https://wa.me/573207141104?text=Hola,%20quiero%20migrar%20mi%20conjunto%20a%20Máximo%20PH" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-block magnetic">
                                <svg class="ic" width="18" height="18"><use href="#i-wa"/></svg>
                                Hablar con un asesor
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== ROLES ==================== -->
        <section id="roles" class="roles">
            <div class="container">
                <div class="section-header">
                    <div class="section-badge">Para cada rol</div>
                    <h2 class="section-title" data-split>
                        Cada persona ve
                        <span class="highlight">lo que le corresponde</span>
                    </h2>
                    <p class="section-description" data-reveal="up">
                        Un mismo sistema, cinco vistas distintas. Cada rol tiene su panel
                        con la información que necesita para hacer su trabajo.
                    </p>
                </div>

                <div class="roles-container" data-reveal="up">
                    <div class="roles-tabs" role="tablist">
                        <button class="role-tab active" data-role="admin" role="tab" aria-selected="true"><svg class="ic" width="16" height="16"><use href="#i-users"/></svg> Administrador</button>
                        <button class="role-tab" data-role="contador" role="tab" aria-selected="false"><svg class="ic" width="16" height="16"><use href="#i-chart"/></svg> Contador</button>
                        <button class="role-tab" data-role="revisor" role="tab" aria-selected="false"><svg class="ic" width="16" height="16"><use href="#i-file"/></svg> Revisor Fiscal</button>
                        <button class="role-tab" data-role="copropietario" role="tab" aria-selected="false"><svg class="ic" width="16" height="16"><use href="#i-building"/></svg> Copropietario</button>
                        <button class="role-tab" data-role="porteria" role="tab" aria-selected="false"><svg class="ic" width="16" height="16"><use href="#i-clock"/></svg> Portería</button>
                    </div>

                    <div class="roles-content" id="rolesContent">
                        <div class="role-panel active" data-panel="admin">
                            <h3>Administrador del conjunto</h3>
                            <p>Tiene la vista completa: recaudo, cartera, gastos, comunicaciones y reportes. Puede aprobar pagos, generar cuotas y publicar comunicados.</p>
                            <ul class="role-features">
                                <li>Dashboard financiero en tiempo real</li>
                                <li>Aprobación de comprobantes y pagos</li>
                                <li>Comunicados y notificaciones masivas</li>
                                <li>Reportes de cartera y recaudo</li>
                            </ul>
                        </div>
                        <div class="role-panel" data-panel="contador">
                            <h3>Contador</h3>
                            <p>Recibe los movimientos ya registrados por Máximo PH y los encuentra causados automáticamente en Portafolio ERP. Solo revisa, ajusta y cierra.</p>
                            <ul class="role-features">
                                <li>Asientos contables automáticos</li>
                                <li>Cierre mensual simplificado</li>
                                <li>Estados financieros en tiempo real</li>
                                <li>Integración con PUC colombiano</li>
                            </ul>
                        </div>
                        <div class="role-panel" data-panel="revisor">
                            <h3>Revisor Fiscal</h3>
                            <p>Acceso de solo lectura a todos los movimientos, asientos y reportes. Puede descargar soportes y generar certificaciones.</p>
                            <ul class="role-features">
                                <li>Acceso de lectura completo</li>
                                <li>Descarga de soportes y anexos</li>
                                <li>Certificaciones automáticas</li>
                                <li>Auditoría con trazabilidad</li>
                            </ul>
                        </div>
                        <div class="role-panel" data-panel="copropietario">
                            <h3>Copropietario / Residente</h3>
                            <p>Consulta sus facturas, paga en línea, adjunta comprobantes, crea PQRS, autoriza visitas y descarga su paz y salvo.</p>
                            <ul class="role-features">
                                <li>Estado de cuenta personal</li>
                                <li>Pago en línea con AV Villas</li>
                                <li>PQRS con seguimiento</li>
                                <li>Paz y salvo en PDF</li>
                            </ul>
                        </div>
                        <div class="role-panel" data-panel="porteria">
                            <h3>Portería</h3>
                            <p>Registra visitas, domicilios y paquetes. Notifica al residente en tiempo real. Todo queda trazado en el sistema.</p>
                            <ul class="role-features">
                                <li>Registro rápido de visitas</li>
                                <li>Notificación al residente</li>
                                <li>Historial completo por unidad</li>
                                <li>Control de paquetes y domicilios</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== INTEGRACIÓN ==================== -->
        <section id="integracion" class="integration">
            <div class="container">
                <div class="section-header">
                    <div class="section-badge">Integración total</div>
                    <h2 class="section-title" data-split>
                        Máximo PH y Portafolio ERP,
                        <span class="highlight">hablando en tiempo real</span>
                    </h2>
                    <p class="section-description" data-reveal="up">
                        Máximo PH administra y factura tu propiedad horizontal. Portafolio ERP hace la contabilidad.
                        Tú solo ves resultados actualizados.
                    </p>
                </div>

                <div class="integration-visual" id="integrationVisual">
                    <div class="integration-node" data-reveal="scale">
                        <div class="integration-node-icon"><img src="https://maximoph.co/img/logo_base.png" alt="Máximo PH" width="64" height="64"></div>
                        <div class="integration-node-label">Máximo PH</div>
                        <div class="integration-node-sub">Administración y facturación</div>
                    </div>

                    <div class="beam" aria-hidden="true">
                        <div class="beam-fill"></div>
                        <i class="beam-dot"></i><i class="beam-dot"></i><i class="beam-dot"></i>
                    </div>

                    <div class="integration-node integration-node-erp" data-reveal="scale" data-delay="200">
                        <div class="integration-node-icon"><img src="https://app.portafolioerp.com/img/logo_contabilidad.png" alt="Portafolio ERP" width="64" height="64"></div>
                        <div class="integration-node-label">Portafolio ERP</div>
                        <div class="integration-node-sub">Contabilidad</div>
                    </div>
                </div>

                <div class="integration-benefits">
                    <div class="integration-benefit spot" data-reveal="up">
                        <div class="integration-benefit-icon"><svg class="ic"><use href="#i-check"/></svg></div>
                        <div><h4>Sin doble digitación</h4><p>Lo que registras en Máximo PH se contabiliza automáticamente en Portafolio ERP.</p></div>
                    </div>
                    <div class="integration-benefit spot" data-reveal="up" data-delay="100">
                        <div class="integration-benefit-icon"><svg class="ic"><use href="#i-check"/></svg></div>
                        <div><h4>Contabilidad siempre al día</h4><p>Cada pago genera su asiento en tiempo real. Nada se queda sin registrar.</p></div>
                    </div>
                    <div class="integration-benefit spot" data-reveal="up" data-delay="200">
                        <div class="integration-benefit-icon"><svg class="ic"><use href="#i-check"/></svg></div>
                        <div><h4>Notificaciones automáticas</h4><p>Correos y WhatsApp salen automáticamente hacia los residentes.</p></div>
                    </div>
                    <div class="integration-benefit spot" data-reveal="up" data-delay="300">
                        <div class="integration-benefit-icon"><svg class="ic"><use href="#i-check"/></svg></div>
                        <div><h4>Cierre contable sin fricción</h4><p>Tu contador recibe todo causado y clasificado. Solo revisa y cierra.</p></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== STATS ==================== -->
        <section class="stats">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-block">
                        <div class="stat-number-wrap"><span class="stat-number" data-count="500">500</span><span class="stat-suffix">+</span></div>
                        <p class="stat-label">Conjuntos confían en Máximo PH</p>
                    </div>
                    <div class="stat-block">
                        <div class="stat-number-wrap"><span class="stat-number" data-count="50">50</span><span class="stat-suffix">K+</span></div>
                        <p class="stat-label">Unidades administradas</p>
                    </div>
                    <div class="stat-block">
                        <div class="stat-number-wrap"><span class="stat-number" data-count="1">1</span><span class="stat-suffix"> día</span></div>
                        <p class="stat-label">Para migrar tus datos</p>
                    </div>
                    <div class="stat-block">
                        <div class="stat-number-wrap"><span class="stat-number" data-count="99">99</span><span class="stat-suffix">.9%</span></div>
                        <p class="stat-label">Uptime garantizado</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== CTA FINAL ==================== -->
        <section class="cta-final">
            <div class="cta-orb cta-orb-1" aria-hidden="true"></div>
            <div class="cta-orb cta-orb-2" aria-hidden="true"></div>
            <div class="container">
                <div class="cta-final-content">
                    <h2 class="cta-final-title" data-split>
                        ¿Listo para que tu conjunto
                        <span class="highlight">trabaje en armonía?</span>
                    </h2>
                    <p class="cta-final-desc" data-reveal="up">
                        Agenda una demo de 20 minutos. Te mostramos Máximo PH + Portafolio ERP trabajando juntos,
                        con pagos AV Villas en tiempo real y tus datos reales.
                    </p>
                    <div class="cta-final-buttons" data-reveal="up" data-delay="150">
                        <a href="https://wa.me/573207141104?text=Hola,%20quiero%20una%20demo%20de%20Máximo%20PH" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-lg magnetic">
                            <svg class="ic" width="20" height="20"><use href="#i-wa"/></svg>
                            Agendar demo
                        </a>
                        <a href="/login" class="btn btn-outline btn-lg magnetic">Iniciar sesión</a>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="/" class="logo">
                        <img src="https://maximoph.co/img/logo_base.png" alt="Máximo PH" width="44" height="44">
                        <span class="logo-text">MÁXIMO PH</span>
                    </a>
                    <p>Administración, facturación y contabilidad en una sola plataforma. La contabilidad la hace <strong>Portafolio ERP</strong>, en tiempo real.</p>
                </div>

                <div class="footer-column">
                    <h4>Producto</h4>
                    <ul>
                        <li><a href="#pagos">Pagos AV Villas</a></li>
                        <li><a href="#plataforma">Plataforma</a></li>
                        <li><a href="#modulos">Módulos</a></li>
                        <li><a href="#migracion">Migración y soporte</a></li>
                        <li><a href="#integracion">Integración ERP</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Soporte</h4>
                    <ul>
                        <li><a href="#">Documentación</a></li>
                        <li><a href="#">Centro de ayuda</a></li>
                        <li><a href="https://wa.me/573207141104" target="_blank" rel="noopener noreferrer">Contacto</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Contacto</h4>
                    <ul>
                        <li><a href="tel:+573207141104"><svg class="ic" width="16" height="16"><use href="#i-phone"/></svg> +57 320 714 1104</a></li>
                        <li><a href="mailto:portafolioerp@gmail.com"><svg class="ic" width="16" height="16"><use href="#i-mail"/></svg> portafolioerp@gmail.com</a></li>
                        <li><a href="#"><svg class="ic" width="16" height="16"><use href="#i-pin"/></svg> Bogotá, Colombia</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2026 Máximo PH. Todos los derechos reservados.</p>
                <p>Contabilidad por <a href="https://portafolioerp.com" target="_blank" rel="noopener noreferrer">Portafolio ERP</a> · Certificado DIAN</p>
            </div>
        </div>
    </footer>

    <script src="{{ asset('assets/js/landing-page.js') }}"></script>
</body>
</html>