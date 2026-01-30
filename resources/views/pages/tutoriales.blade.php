<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAXIMO PH - Software de Gestión de Propiedad Horizontal</title>
    <link id="pagestyle" href="{{ asset('assets/css/landing-page.css') }}" rel="stylesheet" />
    <!-- Todo el head se mantiene igual hasta el final -->
    <!-- ... metadatos, estilos, scripts existentes ... -->
</head>
<body>
    <!-- Navigation - Agregar enlace a tutoriales -->
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
                    <span class="nav-title">MAXIMO PH</span>
                </div>

                <div class="nav-menu" id="navMenu">
                    <a href="#inicio" class="nav-link">Inicio</a>
                    <a href="#modulos" class="nav-link">Módulos</a>
                    <a href="#contabilidad" class="nav-link">Contabilidad</a>
                    <!-- AGREGAR ENLACE A TUTORIALES -->
                    <a href="#tutoriales" class="nav-link">Tutoriales</a>
                    <a href="#contacto" class="nav-link">Contacto</a>
                    <button class="theme-toggle" id="themeToggle" aria-label="Cambiar tema">
                        <svg class="sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="5"/>
                            <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                        </svg>
                        <svg class="moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                        </svg>
                    </button>
                    <button
                        class="btn btn-primary"
                        onclick="window.location.href='/login'">
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

        <!-- Mobile Menu - Agregar enlace a tutoriales -->
        <div class="mobile-menu" id="mobileMenu">
            <div class="mobile-menu-content">
                <a href="#inicio" class="mobile-link">Inicio</a>
                <a href="#modulos" class="mobile-link">Módulos</a>
                <a href="#contabilidad" class="mobile-link">Contabilidad</a>
                <!-- AGREGAR ENLACE A TUTORIALES -->
                <a href="#tutoriales" class="mobile-link">Tutoriales</a>
                <a href="#contacto" class="mobile-link">Contacto</a>
                <button class="theme-toggle mobile-theme-toggle" id="mobileThemeToggle" aria-label="Cambiar tema" style="width: 40px;">
                    <svg class="sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5"/>
                        <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                    </svg>
                    <svg class="moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                    </svg>
                </button>
                <button
                    class="btn btn-primary btn-full"
                    onclick="window.location.href='/login'">
                    Iniciar Sesión
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section (se mantiene igual) -->
    <section id="inicio" class="hero">
        <!-- ... contenido hero existente ... -->
    </section>

    <!-- Features Section (se mantiene igual) -->
    <section id="modulos" class="features">
        <!-- ... contenido features existente ... -->
    </section>

    <!-- Tax Management Section (se mantiene igual) -->
    <section class="tax-section">
        <!-- ... contenido tax existente ... -->
    </section>

    <!-- Accounting Section (se mantiene igual) -->
    <section id="contabilidad" class="accounting">
        <!-- ... contenido accounting existente ... -->
    </section>

    <!-- NUEVA SECCIÓN DE TUTORIALES - SIMPLE Y DIRECTA -->
    <section id="tutoriales" class="tutoriales">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">
                    Video Tutorial:
                    <span class="gradient-text"> Cómo crear un PQRS</span>
                </h2>
                <p class="section-description">
                    Aprende a gestionar Peticiones, Quejas, Reclamos y Sugerencias en tu sistema MAXIMO PH
                </p>
            </div>
            
            <!-- CONTENEDOR DEL VIDEO -->
            <div class="video-tutorial-container">
                <div class="video-wrapper">
                    <video 
                        controls 
                        preload="metadata"
                        style="width: 100%; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                        <source src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/tutoriales/ense%C3%B1ando%20a%20poner%20un%20pqrs.mp4" type="video/mp4">
                        Tu navegador no soporta videos HTML5.
                    </video>
                </div>
                
                <!-- ESPACIO PARA EXPLICACIÓN (usando estilos existentes) -->
                <div class="feature-card" style="margin-top: 30px; text-align: left;">
                    <div class="feature-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                    </div>
                    <h3 class="feature-title">Explicación del Tutorial</h3>
                    <p class="feature-description" id="explicacion-tutorial">
                        <!-- ESPACIO RESERVADO PARA LA EXPLICACIÓN -->
                        <em>Explicación detallada del video tutorial estará disponible aquí próximamente.</em>
                    </p>
                    
                    <!-- Puedes agregar más contenido explicativo aquí -->
                    <div style="margin-top: 20px; padding: 15px; background: rgba(102, 126, 234, 0.1); border-radius: 8px;">
                        <p><strong>Puntos clave del video:</strong></p>
                        <ul style="margin: 10px 0; padding-left: 20px;">
                            <li>Acceso al módulo de PQRS</li>
                            <li>Creación de nueva solicitud</li>
                            <li>Clasificación por tipo (Petición, Queja, Reclamo, Sugerencia)</li>
                            <li>Asignación y seguimiento</li>
                            <li>Respuesta y cierre del caso</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section (se mantiene igual) -->
    <section class="benefits">
        <!-- ... contenido benefits existente ... -->
    </section>

    <!-- Footer (se mantiene igual) -->
    <section id="contacto">
        <footer class="footer">
            <!-- ... contenido footer existente ... -->
        </footer>
    </section>

    <!-- ESTILOS ADICIONALES MÍNIMOS (agregar al final del CSS existente) -->
    <style>
        .tutoriales {
            padding: 80px 0;
            background: var(--section-bg, #f8fafc);
        }
        
        .video-tutorial-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .video-wrapper {
            margin-bottom: 30px;
        }
        
        /* Estilos responsivos para el video */
        @media (max-width: 768px) {
            .video-wrapper {
                margin-left: -20px;
                margin-right: -20px;
            }
            
            .video-wrapper video {
                border-radius: 0 !important;
            }
        }
    </style>

    <script src="{{ asset('assets/js/landing-page.js') }}"></script>
</body>
</html>