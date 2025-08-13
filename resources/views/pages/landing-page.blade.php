<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAXIMOPH - Software de Gestión de Propiedad Horizontal</title>
    <meta name="description" content="Ecosistema integral para optimizar la gestión administrativa, facturación y contable de propiedades horizontales">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">


    <!-- Configuración básica y viewport -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="robots" content="index, follow">

    <!-- Descripción y keywords para SEO -->
    <meta name="description" content="MAXIMOPH: Software líder para administración de propiedades horizontales. Accede ahora a módulos administrativos, financieros y contables en tiempo real. Gestión integral para condominios y conjuntos residenciales.">
    <meta name="keywords" content="software propiedad horizontal, administración conjuntos residenciales, contabilidad condominios, control visitas, gestión paquetes, comunicación portería, maximoph, gestion ph, software condominios, ph propiedad horizontal">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://maximoph.co/">
    <meta property="og:title" content="MAXIMOPH - Software de Propiedad Horizontal | Acceso al Sistema">
    <meta property="og:description" content="Solución integral para la administración de conjuntos residenciales. Control financiero, contable y operativo en tiempo real con MAXIMOPH.">
    <meta property="og:image" content="https://maximoph.co/img/og-image-maximoph.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="MAXIMOPH">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@MaximoPH">
    <meta name="twitter:creator" content="@MaximoPH">
    <meta name="twitter:title" content="MAXIMOPH - Software de Propiedad Horizontal | Acceso al Sistema">
    <meta name="twitter:description" content="Transforma la gestión de tu propiedad horizontal con nuestro ecosistema administrativo completo en la nube.">
    <meta name="twitter:image" content="https://maximoph.co/img/twitter-card-maximoph.jpg">

    <!-- Apple / iOS -->
    <meta name="apple-mobile-web-app-title" content="MAXIMOPH">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-itunes-app" content="app-id=64568685, app-argument=https://maximoph.co/">

    <!-- Facebook App -->
    <meta property="fb:app_id" content="1401488693436528">

    <!-- Información del sitio -->
    <meta name="hostname" content="maximoph.co">
    <meta name="expected-hostname" content="maximoph.co">

    <!-- Esquema de color y tema -->
    <meta name="theme-color" content="#1e2327">
    <meta name="color-scheme" content="light dark">

    <style>
        /* Reset and Base Styles */
        * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        }

        html {
        scroll-behavior: smooth;
        }

        body {
        font-family: "Inter", sans-serif;
        background-color: #020617;
        color: #ffffff;
        line-height: 1.6;
        overflow-x: hidden;
        }

        .container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 1rem;
        }

        /* Typography */
        .gradient-text {
        background: linear-gradient(135deg, #22d3ee, #3b82f6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        }

        /* Buttons */
        .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.875rem;
        }

        .btn-primary {
        background-color: #22d3ee;
        color: #020617;
        }

        .btn-primary:hover {
        background-color: #06b6d4;
        }

        .btn-outline {
        background-color: transparent;
        color: #ffffff;
        border: 1px solid #475569;
        }

        .btn-outline:hover {
        background-color: #1e293b;
        }

        .btn-gradient {
        background: linear-gradient(135deg, #22d3ee, #3b82f6);
        color: #020617;
        }

        .btn-gradient:hover {
        background: linear-gradient(135deg, #06b6d4, #2563eb);
        }

        .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
        }

        .btn-full {
        width: 100%;
        }

        .btn-icon {
        margin-left: 0.5rem;
        }

        /* Badge */
        .badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background-color: rgba(34, 211, 238, 0.1);
        color: #22d3ee;
        border: 1px solid rgba(34, 211, 238, 0.2);
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
        margin-bottom: 1rem;
        }

        /* Navigation */
        .navbar {
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        transition: all 0.3s ease;
        }

        .navbar.scrolled {
        background-color: rgba(2, 6, 23, 0.95);
        backdrop-filter: blur(12px);
        border-bottom: 1px solid #334155;
        }

        .nav-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 1rem;
        }

        .nav-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 4rem;
        }

        .nav-brand {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        }

        .nav-logo {
        width: 2rem;
        height: 2rem;
        background-color: #22d3ee;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #020617;
        }

        .nav-title {
        font-size: 1.25rem;
        font-weight: 700;
        }

        .nav-menu {
        display: flex;
        align-items: center;
        gap: 2rem;
        }

        .nav-link {
        color: #ffffff;
        text-decoration: none;
        transition: color 0.3s ease;
        }

        .nav-link:hover {
        color: #22d3ee;
        }

        .nav-toggle {
        display: none;
        flex-direction: column;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.5rem;
        }

        .hamburger {
        width: 1.5rem;
        height: 2px;
        background-color: #ffffff;
        margin: 2px 0;
        transition: 0.3s;
        }

        .mobile-menu {
        display: none;
        background-color: rgba(15, 23, 42, 0.95);
        backdrop-filter: blur(12px);
        border-top: 1px solid #334155;
        }

        .mobile-menu-content {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        }

        .mobile-link {
        color: #ffffff;
        text-decoration: none;
        padding: 0.5rem 0;
        transition: color 0.3s ease;
        }

        .mobile-link:hover {
        color: #22d3ee;
        }

        /* Hero Section */
        .hero {
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        padding: 5rem 0;
        }

        .hero-bg {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, #020617, #0f172a, #020617);
        }

        .hero-image {
        position: absolute;
        inset: 0;
        background-image: url("modern-building.png");
        background-size: cover;
        background-position: center;
        opacity: 0.1;
        }

        .hero-content {
        position: relative;
        display: grid;
        grid-template-columns: 1fr;
        gap: 3rem;
        align-items: center;
        }

        .hero-text {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        }

        .hero-title {
        font-family: "Space Grotesk", sans-serif;
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1.1;
        }

        .hero-description {
        font-size: 1.25rem;
        color: #cbd5e1;
        line-height: 1.6;
        }

        .hero-buttons {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        }

        .hero-stats {
        display: flex;
        gap: 2rem;
        padding-top: 2rem;
        }

        .stat {
        text-align: center;
        }

        .stat-number {
        font-size: 1.5rem;
        font-weight: 700;
        color: #22d3ee;
        }

        .stat-label {
        font-size: 0.875rem;
        color: #94a3b8;
        }

        /* Hero Dashboard */
        .hero-dashboard {
        position: relative;
        }

        .dashboard-glow {
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(34, 211, 238, 0.2), rgba(59, 130, 246, 0.2));
        border-radius: 1.5rem;
        filter: blur(3rem);
        }

        .dashboard-card {
        position: relative;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(8px);
        border: 1px solid #334155;
        border-radius: 1.5rem;
        padding: 2rem;
        overflow: hidden;
        }

        .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        }

        .dashboard-header h3 {
        font-size: 1.125rem;
        font-weight: 600;
        }

        .status-lights {
        display: flex;
        gap: 0.5rem;
        }

        .status-light {
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 50%;
        animation: pulse 2s infinite;
        }

        .status-light.red {
        background-color: #ef4444;
        }

        .status-light.yellow {
        background-color: #eab308;
        animation-delay: 0.1s;
        }

        .status-light.green {
        background-color: #22c55e;
        animation-delay: 0.2s;
        }

        .property-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1.5rem;
        }

        .property-card {
        background: rgba(30, 41, 59, 0.5);
        border-radius: 0.5rem;
        padding: 1rem;
        transition: all 0.3s ease;
        cursor: pointer;
        }

        .property-card:hover {
        background: rgba(30, 41, 59, 0.7);
        transform: scale(1.05);
        }

        .property-title {
        font-size: 1.125rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        }

        .property-subtitle {
        font-size: 0.875rem;
        color: #94a3b8;
        }

        .property-card.cyan .property-title {
        color: #22d3ee;
        }

        .property-card.green .property-title {
        color: #22c55e;
        }

        .property-card.purple .property-title {
        color: #a855f7;
        }

        .property-card.orange .property-title {
        color: #f97316;
        }

        .compatibility-section {
        margin-top: 1.5rem;
        }

        .compatibility-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.875rem;
        margin-bottom: 0.75rem;
        }

        .compatibility-indicator {
        width: 0.5rem;
        height: 0.5rem;
        background-color: #22d3ee;
        border-radius: 50%;
        animation: pulse 2s infinite;
        }

        .compatibility-status {
        color: #22d3ee;
        font-weight: 600;
        }

        .progress-bar {
        width: 100%;
        height: 0.5rem;
        background-color: #1e293b;
        border-radius: 9999px;
        overflow: hidden;
        margin-bottom: 0.75rem;
        }

        .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #22d3ee, #22c55e, #a855f7);
        border-radius: 9999px;
        animation: pulse 2s infinite;
        }

        .property-types {
        font-size: 0.75rem;
        color: #94a3b8;
        text-align: center;
        }

        .floating-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(1rem);
        animation: float 6s ease-in-out infinite;
        }

        .orb-1 {
        top: -1rem;
        right: -1rem;
        width: 5rem;
        height: 5rem;
        background: linear-gradient(135deg, rgba(34, 211, 238, 0.2), rgba(59, 130, 246, 0.2));
        }

        .orb-2 {
        bottom: -1rem;
        left: -1rem;
        width: 4rem;
        height: 4rem;
        background: linear-gradient(135deg, rgba(168, 85, 247, 0.2), rgba(236, 72, 153, 0.2));
        animation-delay: -3s;
        }

        /* Sections */
        .section-header {
        text-align: center;
        margin-bottom: 4rem;
        }

        .section-title {
        font-family: "Space Grotesk", sans-serif;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        }

        .section-description {
        font-size: 1.25rem;
        color: #cbd5e1;
        max-width: 48rem;
        margin: 0 auto;
        }

        /* Features Section */
        .features {
        padding: 5rem 0;
        background-color: rgba(15, 23, 42, 0.5);
        }

        .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        }

        .feature-card {
        background: rgba(30, 41, 59, 0.5);
        border: 1px solid #475569;
        border-radius: 0.75rem;
        padding: 1.5rem;
        transition: all 0.3s ease;
        }

        .feature-card:hover {
        border-color: rgba(34, 211, 238, 0.5);
        }

        .feature-icon {
        width: 3rem;
        height: 3rem;
        background: rgba(34, 211, 238, 0.1);
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        color: #22d3ee;
        transition: background-color 0.3s ease;
        }

        .feature-card:hover .feature-icon {
        background: rgba(34, 211, 238, 0.2);
        }

        .feature-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        }

        .feature-description {
        color: #cbd5e1;
        line-height: 1.6;
        }

        /* Tax Section */
        .tax-section {
        padding: 5rem 0;
        }

        .tax-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 2rem;
        }

        .tax-card {
        background: linear-gradient(135deg, rgba(30, 41, 59, 0.5), rgba(15, 23, 42, 0.5));
        border: 1px solid #475569;
        border-radius: 0.75rem;
        padding: 2rem;
        transition: all 0.3s ease;
        }

        .tax-card:hover {
        border-color: rgba(34, 211, 238, 0.5);
        }

        .tax-content {
        display: flex;
        gap: 1rem;
        }

        .tax-icon {
        width: 2rem;
        height: 2rem;
        background-color: #22d3ee;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #020617;
        flex-shrink: 0;
        }

        .tax-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.75rem;
        }

        .tax-description {
        color: #cbd5e1;
        line-height: 1.6;
        }

        /* Accounting Section */
        .accounting {
        padding: 5rem 0;
        background-color: rgba(15, 23, 42, 0.5);
        }

        .accounting-content {
        display: grid;
        grid-template-columns: 1fr;
        gap: 4rem;
        align-items: center;
        }

        .accounting-steps {
        display: flex;
        flex-direction: column;
        gap: 2rem;
        }

        .step {
        display: flex;
        gap: 1.5rem;
        }

        .step-number {
        width: 3rem;
        height: 3rem;
        background: linear-gradient(135deg, #22d3ee, #3b82f6);
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #020617;
        font-weight: 700;
        font-size: 0.875rem;
        flex-shrink: 0;
        }

        .step-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        }

        .step-description {
        color: #cbd5e1;
        line-height: 1.6;
        }

        .accounting-dashboard {
        position: relative;
        }

        .dashboard-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        }

        .financial-reports {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 1.5rem;
        }

        .report-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: rgba(15, 23, 42, 0.5);
        border-radius: 0.5rem;
        }

        .status-badge {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
        border: 1px solid rgba(34, 197, 94, 0.2);
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        }

        .last-update {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding-top: 1rem;
        border-top: 1px solid #475569;
        font-size: 0.875rem;
        color: #94a3b8;
        }

        /* Benefits Section */
        .benefits {
        padding: 5rem 0;
        }

        .benefits-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin-bottom: 4rem;
        }

        .benefit-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        }

        .benefit-icon {
        width: 1.5rem;
        height: 1.5rem;
        background-color: #22d3ee;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #020617;
        flex-shrink: 0;
        }

        .cta-section {
        text-align: center;
        }

        /* Footer */
        .footer {
        background-color: #020617;
        border-top: 1px solid #334155;
        padding: 3rem 0;
        }

        .footer-content {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        margin-bottom: 3rem;
        }

        .footer-brand {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        }

        .footer-logo {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        }

        .footer-title {
        font-size: 1.25rem;
        font-weight: 700;
        }

        .footer-description {
        color: #94a3b8;
        }

        .footer-links {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 2rem;
        }

        .footer-heading {
        font-weight: 600;
        margin-bottom: 1rem;
        }

        .footer-list {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        }

        .footer-link {
        color: #94a3b8;
        text-decoration: none;
        transition: color 0.3s ease;
        }

        .footer-link:hover {
        color: #22d3ee;
        }

        .footer-bottom {
        border-top: 1px solid #334155;
        padding-top: 2rem;
        text-align: center;
        color: #94a3b8;
        }

        /* Animations */
        @keyframes pulse {
        0%,
        100% {
            opacity: 1;
        }
        50% {
            opacity: 0.5;
        }
        }

        @keyframes float {
        0%,
        100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-20px);
        }
        }

        /* Responsive Design */
        @media (min-width: 640px) {
        .hero-buttons {
            flex-direction: row;
        }

        .benefits-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        }

        @media (min-width: 768px) {
        .nav-toggle {
            display: none;
        }

        .hero-title {
            font-size: 3.75rem;
        }

        .section-title {
            font-size: 3rem;
        }

        .footer-content {
            grid-template-columns: 2fr 3fr;
        }
        }

        @media (min-width: 1024px) {
        .hero-content {
            grid-template-columns: 1fr 1fr;
        }

        .accounting-content {
            grid-template-columns: 1fr 1fr;
        }

        .hero-title {
            font-size: 4rem;
        }

        .section-title {
            font-size: 3rem;
        }
        }

        @media (max-width: 767px) {
        .nav-menu {
            display: none;
        }

        .nav-toggle {
            display: flex;
        }

        .mobile-menu.active {
            display: block;
        }

        .property-grid {
            grid-template-columns: 1fr;
        }

        .tax-grid {
            grid-template-columns: 1fr;
        }

        .hero-stats {
            justify-content: space-around;
        }
        }

    </style>
    
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <div class="nav-content">
                <div class="nav-brand">
                    <div class="nav-logo">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/>
                            <path d="M6 12h4h4"/>
                            <path d="M6 20h4"/>
                            <path d="M6 8h4"/>
                            <path d="M6 16h4"/>
                        </svg>
                    </div>
                    <span class="nav-title">MAXIMOPH</span>
                </div>

                <div class="nav-menu" id="navMenu">
                    <a href="#inicio" class="nav-link">Inicio</a>
                    <a href="#modulos" class="nav-link">Módulos</a>
                    <a href="#contabilidad" class="nav-link">Contabilidad</a>
                    <button onclick="window.location.href='https://maximoph.co/login'" class="btn btn-primary">
                        Iniciar Sesión
                    </button>
                </div>

                <button class="nav-toggle" id="navToggle">
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div class="mobile-menu" id="mobileMenu">
            <div class="mobile-menu-content">
                <a href="#inicio" class="mobile-link">Inicio</a>
                <a href="#modulos" class="mobile-link">Módulos</a>
                <a href="#contabilidad" class="mobile-link">Contabilidad</a>
                <button class="btn btn-primary btn-full">Iniciar Sesión</button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="inicio" class="hero">
        <div class="hero-bg"></div>
        <div class="hero-image"></div>
        
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <div class="badge">Software Líder en Propiedad Horizontal</div>
                    
                    <h1 class="hero-title">
                        Revoluciona la
                        <span class="gradient-text"> Gestión </span>
                        de tu Propiedad
                    </h1>
                    
                    <p class="hero-description">
                        Ecosistema integral diseñado para optimizar la gestión administrativa, facturación y contable de su
                        comunidad. Manténgase informado en tiempo real con nuestro soporte personalizado.
                    </p>
                    
                    <div class="hero-buttons">
                        <button class="btn btn-primary btn-lg">
                            Comenzar Ahora
                            <svg class="btn-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14"/>
                                <path d="m12 5 7 7-7 7"/>
                            </svg>
                        </button>
                        <button class="btn btn-outline btn-lg">Ver Demo</button>
                    </div>
                    
                    <div class="hero-stats">
                        <div class="stat">
                            <div class="stat-number">500+</div>
                            <div class="stat-label">Propiedades</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number">50K+</div>
                            <div class="stat-label">Usuarios</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number">99.9%</div>
                            <div class="stat-label">Uptime</div>
                        </div>
                    </div>
                </div>
                
                <div class="hero-dashboard">
                    <div class="dashboard-glow"></div>
                    <div class="dashboard-card">
                        <div class="dashboard-header">
                            <h3>Tipos de Propiedades</h3>
                            <div class="status-lights">
                                <div class="status-light red"></div>
                                <div class="status-light yellow"></div>
                                <div class="status-light green"></div>
                            </div>
                        </div>
                        
                        <div class="property-grid">
                            <div class="property-card cyan">
                                <div class="property-title">Centros</div>
                                <div class="property-subtitle">Comerciales</div>
                            </div>
                            <div class="property-card green">
                                <div class="property-title">Oficinas</div>
                                <div class="property-subtitle">Corporativas</div>
                            </div>
                            <div class="property-card purple">
                                <div class="property-title">Bodegas</div>
                                <div class="property-subtitle">Industriales</div>
                            </div>
                            <div class="property-card orange">
                                <div class="property-title">Condominios</div>
                                <div class="property-subtitle">Residenciales</div>
                            </div>
                        </div>
                        
                        <div class="compatibility-section">
                            <div class="compatibility-info">
                                <div class="compatibility-indicator"></div>
                                <span>Propiedades Mixtas</span>
                                <span class="compatibility-status">Compatibles</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill"></div>
                            </div>
                            <div class="property-types">Residenciales • Comerciales • Mixtas</div>
                        </div>
                        
                        <div class="floating-orb orb-1"></div>
                        <div class="floating-orb orb-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="modulos" class="features">
        <div class="container">
            <div class="section-header">
                <div class="badge">Módulo Premium</div>
                <h2 class="section-title">
                    Facturación por Coeficientes
                    <span class="gradient-text"> y Valores</span>
                </h2>
                <p class="section-description">
                    Sistema avanzado de facturación que se adapta a las necesidades específicas de cada tipo de propiedad
                    horizontal.
                </p>
            </div>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="16" height="10" x="2" y="3" rx="2" ry="2"/>
                            <path d="m7 11 2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Facturación por Coeficientes</h3>
                    <p class="feature-description">
                        Cálculo automático de administración por coeficientes separado por cada inmueble con precisión matemática.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polygon points="13,2 3,14 12,14 11,22 21,10 12,10 13,2"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Cruce Automático de Anticipos</h3>
                    <p class="feature-description">
                        Gestión eficiente de pagos anticipados con reconciliación automática en tiempo real.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Causación de Intereses</h3>
                    <p class="feature-description">
                        Cálculo automático de intereses por saldos morosos, manteniendo control efectivo de cuentas por cobrar.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                            <polyline points="14,2 14,8 20,8"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Cuotas Extras y Multas</h3>
                    <p class="feature-description">
                        Flexibilidad para aplicar cuotas extraordinarias por coeficientes o multas, individual o masivamente.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="m4.93 4.93 4.24 4.24"/>
                            <path d="m14.83 9.17 4.24-4.24"/>
                            <path d="m14.83 14.83 4.24 4.24"/>
                            <path d="m9.17 14.83-4.24 4.24"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Conexión en Tiempo Real</h3>
                    <p class="feature-description">
                        Integración instantánea con la contabilidad para una gestión financiera precisa y actualizada.
                    </p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Facturación Electrónica</h3>
                    <p class="feature-description">
                        Gestión completa de facturas electrónicas para administraciones comerciales o mixtas.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tax Management Section -->
    <section class="tax-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">
                    Gestión Tributaria
                    <span class="gradient-text"> Simplificada</span>
                </h2>
                <p class="section-description">
                    Cumplimiento tributario automatizado con generación de reportes y certificados en tiempo real.
                </p>
            </div>
            
            <div class="tax-grid">
                <div class="tax-card">
                    <div class="tax-content">
                        <div class="tax-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22,4 12,14.01 9,11.01"/>
                            </svg>
                        </div>
                        <div class="tax-text">
                            <h3 class="tax-title">Medios Magnéticos</h3>
                            <p class="tax-description">
                                Generación automática de informes para cumplimiento tributario completo.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="tax-card">
                    <div class="tax-content">
                        <div class="tax-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22,4 12,14.01 9,11.01"/>
                            </svg>
                        </div>
                        <div class="tax-text">
                            <h3 class="tax-title">Gestión de IVA</h3>
                            <p class="tax-description">
                                IVA como mayor valor del gasto en la misma cuenta, cálculo de IVA - AIU automatizado.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="tax-card">
                    <div class="tax-content">
                        <div class="tax-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22,4 12,14.01 9,11.01"/>
                            </svg>
                        </div>
                        <div class="tax-text">
                            <h3 class="tax-title">Control de ICA</h3>
                            <p class="tax-description">
                                Manejo eficiente del Impuesto de Industria y Comercio con reportes detallados.
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="tax-card">
                    <div class="tax-content">
                        <div class="tax-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22,4 12,14.01 9,11.01"/>
                            </svg>
                        </div>
                        <div class="tax-text">
                            <h3 class="tax-title">Certificados de Retención</h3>
                            <p class="tax-description">
                                Emisión y gestión automatizada de certificados de retención fiscal.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Accounting Section -->
    <section id="contabilidad" class="accounting">
        <div class="container">
            <div class="accounting-content">
                <div class="accounting-text">
                    <div class="badge">Módulo Premium</div>
                    <h2 class="section-title">
                        Contabilidad
                        <span class="gradient-text"> Completa</span>
                    </h2>
                    
                    <div class="accounting-steps">
                        <div class="step">
                            <div class="step-number">01</div>
                            <div class="step-content">
                                <h3 class="step-title">Causación Automática</h3>
                                <p class="step-description">
                                    Sistema inteligente de causación contable con clasificación automática de cuentas.
                                </p>
                            </div>
                        </div>
                        
                        <div class="step">
                            <div class="step-number">02</div>
                            <div class="step-content">
                                <h3 class="step-title">Reportes en Tiempo Real</h3>
                                <p class="step-description">
                                    Estados financieros actualizados instantáneamente con cada transacción registrada.
                                </p>
                            </div>
                        </div>
                        
                        <div class="step">
                            <div class="step-number">03</div>
                            <div class="step-content">
                                <h3 class="step-title">Integración Completa</h3>
                                <p class="step-description">
                                    Conexión directa entre módulos administrativos, financieros y contables.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="accounting-dashboard">
                    <div class="dashboard-glow"></div>
                    <div class="dashboard-card">
                        <h3 class="dashboard-title">Estados Financieros</h3>
                        
                        <div class="financial-reports">
                            <div class="report-item">
                                <span>Balance General</span>
                                <div class="status-badge">Actualizado</div>
                            </div>
                            <div class="report-item">
                                <span>Estado de Resultados</span>
                                <div class="status-badge">Actualizado</div>
                            </div>
                            <div class="report-item">
                                <span>Flujo de Caja</span>
                                <div class="status-badge">Actualizado</div>
                            </div>
                        </div>
                        
                        <div class="last-update">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12,6 12,12 16,14"/>
                            </svg>
                            <span>Última actualización: hace 2 minutos</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section class="benefits">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">
                    ¿Por qué elegir
                    <span class="gradient-text"> MAXIMOPH?</span>
                </h2>
                <p class="section-description">
                    Más de 500 propiedades confían en nuestra plataforma para optimizar su gestión administrativa y
                    financiera.
                </p>
            </div>
            
            <div class="benefits-grid">
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22,4 12,14.01 9,11.01"/>
                        </svg>
                    </div>
                    <span>Reducción del 80% en tiempo de procesamiento administrativo</span>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22,4 12,14.01 9,11.01"/>
                        </svg>
                    </div>
                    <span>Control financiero en tiempo real las 24 horas del día</span>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22,4 12,14.01 9,11.01"/>
                        </svg>
                    </div>
                    <span>Cumplimiento tributario automatizado y sin errores</span>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22,4 12,14.01 9,11.01"/>
                        </svg>
                    </div>
                    <span>Comunicación directa con residentes y propietarios</span>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22,4 12,14.01 9,11.01"/>
                        </svg>
                    </div>
                    <span>Respaldo en la nube con seguridad bancaria</span>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22,4 12,14.01 9,11.01"/>
                        </svg>
                    </div>
                    <span>Soporte técnico especializado incluido</span>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22,4 12,14.01 9,11.01"/>
                        </svg>
                    </div>
                    <span>Migración de datos sin costo adicional</span>
                </div>
                
                <div class="benefit-item">
                    <div class="benefit-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22,4 12,14.01 9,11.01"/>
                        </svg>
                    </div>
                    <span>Actualizaciones automáticas del sistema</span>
                </div>
            </div>
            
            <div class="cta-section">
                <button class="btn btn-gradient btn-lg">
                    Solicitar Demo Gratuita
                    <svg class="btn-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14"/>
                        <path d="m12 5 7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <section id="contacto">
        <footer class="footer">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-brand">
                        <div class="footer-logo">
                            <div class="nav-logo">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/>
                                    <path d="M6 12h4h4"/>
                                    <path d="M6 20h4"/>
                                    <path d="M6 8h4"/>
                                    <path d="M6 16h4"/>
                                </svg>
                            </div>
                            <span class="footer-title">MAXIMOPH</span>
                        </div>
                        <p class="footer-description">
                            Software líder en gestión de propiedad horizontal con más de 10 años de experiencia.
                        </p>
                    </div>

                    
                    
                    <div class="footer-links">
                        
                        <div class="footer-column">
                            <h3 class="footer-heading">Soporte</h3>
                            <ul class="footer-list">
                                <li><a href="#" class="footer-link">Documentación</a></li>
                                <li><a href="#" class="footer-link">Centro de Ayuda</a></li>
                                <li><a href="#" class="footer-link">Contacto</a></li>
                            </ul>
                        </div>
                        
                        <div class="footer-column">
                            <h3 class="footer-heading">Empresa</h3>
                            <ul class="footer-list">
                                <li><a href="#" class="footer-link">Acerca de</a></li>
                                <li><a href="#" class="footer-link">Blog</a></li>
                                <li><a href="#" class="footer-link">Carreras</a></li>
                            </ul>
                        </div>

                        <div class="footer-column">
                            <h3 class="footer-heading">Contacto</h3>
                            <ul class="footer-list">
                                <li class="footer-link">+57 3207141104</li>
                                <li class="footer-link">portafolioerp@gmail.com</li>
                                <li class="footer-link">Bogotá, Colombia</li>
                            </ul>
                        </div>

                    </div>
                </div>
                
                <div class="footer-bottom">
                    <p>&copy; 2025 MAXIMOPH. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>
    </section>

    <script>
        // Navigation functionality
        document.addEventListener("DOMContentLoaded", () => {
        const navbar = document.getElementById("navbar")
        const navToggle = document.getElementById("navToggle")
        const mobileMenu = document.getElementById("mobileMenu")
        const navLinks = document.querySelectorAll(".nav-link, .mobile-link")

        // Handle scroll effect on navbar
        window.addEventListener("scroll", () => {
            if (window.scrollY > 50) {
            navbar.classList.add("scrolled")
            } else {
            navbar.classList.remove("scrolled")
            }
        })

        // Handle mobile menu toggle
        navToggle.addEventListener("click", () => {
            mobileMenu.classList.toggle("active")

            // Animate hamburger menu
            const hamburgers = navToggle.querySelectorAll(".hamburger")
            hamburgers.forEach((hamburger, index) => {
            if (mobileMenu.classList.contains("active")) {
                if (index === 0) {
                hamburger.style.transform = "rotate(45deg) translate(5px, 5px)"
                } else if (index === 1) {
                hamburger.style.opacity = "0"
                } else {
                hamburger.style.transform = "rotate(-45deg) translate(7px, -6px)"
                }
            } else {
                hamburger.style.transform = "none"
                hamburger.style.opacity = "1"
            }
            })
        })

        // Close mobile menu when clicking on links
        navLinks.forEach((link) => {
            link.addEventListener("click", () => {
            mobileMenu.classList.remove("active")

            // Reset hamburger menu
            const hamburgers = navToggle.querySelectorAll(".hamburger")
            hamburgers.forEach((hamburger) => {
                hamburger.style.transform = "none"
                hamburger.style.opacity = "1"
            })
            })
        })

        // Smooth scrolling for anchor links
        navLinks.forEach((link) => {
            link.addEventListener("click", function (e) {
            const href = this.getAttribute("href")
            if (href.startsWith("#")) {
                e.preventDefault()
                const target = document.querySelector(href)
                if (target) {
                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start",
                })
                }
            }
            })
        })

        // Add intersection observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: "0px 0px -50px 0px",
        }

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = "1"
                entry.target.style.transform = "translateY(0)"
            }
            })
        }, observerOptions)

        // Observe elements for animation
        const animatedElements = document.querySelectorAll(".feature-card, .tax-card, .step, .benefit-item")
        animatedElements.forEach((el) => {
            el.style.opacity = "0"
            el.style.transform = "translateY(20px)"
            el.style.transition = "opacity 0.6s ease, transform 0.6s ease"
            observer.observe(el)
        })

        // Add hover effects to property cards
        const propertyCards = document.querySelectorAll(".property-card")
        propertyCards.forEach((card) => {
            card.addEventListener("mouseenter", function () {
            this.style.transform = "scale(1.05)"
            })

            card.addEventListener("mouseleave", function () {
            this.style.transform = "scale(1)"
            })
        })

        // Add click handlers for buttons
        const buttons = document.querySelectorAll(".btn")
        buttons.forEach((button) => {
            button.addEventListener("click", function (e) {
            // Add ripple effect
            const ripple = document.createElement("span")
            const rect = this.getBoundingClientRect()
            const size = Math.max(rect.width, rect.height)
            const x = e.clientX - rect.left - size / 2
            const y = e.clientY - rect.top - size / 2

            ripple.style.width = ripple.style.height = size + "px"
            ripple.style.left = x + "px"
            ripple.style.top = y + "px"
            ripple.classList.add("ripple")

            this.appendChild(ripple)

            setTimeout(() => {
                ripple.remove()
            }, 600)
            })
        })
        })

        // Add CSS for ripple effect
        const style = document.createElement("style")
        style.textContent = `
            .btn {
                position: relative;
                overflow: hidden;
            }
            
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `
        document.head.appendChild(style)

    </script>
</body>
</html>
