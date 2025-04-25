<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Máximo - Software de Propiedad Horizontal</title>
    <style>

        .navbar {
            background-color: transparent; /* Inicialmente transparente */
            color: white;
            padding: 1.5rem;
            position: fixed; /* Cambiado a fixed para que siempre esté en la parte superior */
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            transition: background-color 0.3s ease; /* Transición suave para el cambio de color */
        }
        
        /* Clase que se añadirá con JavaScript al hacer scroll */
        .navbar.scrolled {
            background-color: #1a1a1a; /* Color oscuro al hacer scroll */
            padding: 1rem; /* Reducimos un poco el padding al hacer scroll */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .navbar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .menu {
            display: flex;
            list-style: none;
        }
        
        .menu li {
            margin-left: 1.5rem;
        }
        
        .menu a {
            color: white;
            text-decoration: none;
            font-size: 1.3rem;
            transition: color 0.3s;
        }
        
        .menu a:hover {
            color:rgb(0, 217, 255);
        }
        
        .hamburger {
            display: none;
            cursor: pointer;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
        }
        
        /* Contenido de ejemplo para mostrar el scroll */
        .content {
            padding: 100vh 2rem 2rem; /* Espacio para que se vea el efecto de scroll */
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Estilos para dispositivos móviles */
        @media (max-width: 768px) {
            .menu {
                position: fixed;
                top: 70px;
                left: 0;
                right: 0;
                background-color: rgba(26, 26, 26, 0.95); /* Fondo semi-transparente para el menú móvil */
                flex-direction: column;
                align-items: center;
                padding: 0;
                height: 0; /* Inicialmente sin altura */
                overflow: hidden;
                transition: all 0.3s ease;
                opacity: 0; /* Inicialmente invisible */
                visibility: hidden; /* Inicialmente oculto */
            }
            
            .menu.active {
                height: auto; /* Altura automática cuando está activo */
                padding: 1rem 0;
                opacity: 1; /* Visible cuando está activo */
                visibility: visible; /* Visible cuando está activo */
            }
            
            .menu li {
                margin: 1rem 0;
                opacity: 0; /* Inicialmente invisible */
                transform: translateY(-10px); /* Inicialmente desplazado hacia arriba */
                transition: all 0.3s ease;
                transition-delay: 0.05s; /* Pequeño retraso para efecto en cascada */
            }
            
            .menu.active li {
                opacity: 1; /* Visible cuando está activo */
                transform: translateY(0); /* Sin desplazamiento cuando está activo */
            }
            
            /* Retraso escalonado para cada elemento del menú */
            .menu li:nth-child(2) { transition-delay: 0.1s; }
            .menu li:nth-child(3) { transition-delay: 0.15s; }
            .menu li:nth-child(4) { transition-delay: 0.2s; }
            .menu li:nth-child(5) { transition-delay: 0.25s; }
            
            .hamburger {
                display: block;
            }
        }

        /* Variables y estilos base */
        :root {
            --dark-bg: #1a1a1a;
            --darker-bg: #111;
            --light-text: #ffffff;
            --medium-text: #e0e0e0;
            --accent-yellow:rgb(0, 217, 255);
            --module-line: #444;
            --module-number-bg: #333;
            --copyright: #888888;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: var(--dark-bg);
            color: var(--light-text);
            overflow-x: hidden;
        }

        /* Primera sección - Hero */
        .hero-section {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
        }

        .left-side {
            flex: 1;
            min-height: 50vh;
            background-image: url('https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_1.jpg');
            background-size: cover;
            background-position: center;
            filter: grayscale(100%);
            position: relative;
        }

        .logo-container {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 2;
            width: 90%;
            max-width: 300px;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            background-color: white;
            border-radius: 8px;
            margin: 0 auto 10px;
            position: relative;
            overflow: hidden;
        }

        .logo-icon::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 20%;
            background-color: black;
            bottom: 0;
        }

        .logo-icon::after {
            content: "";
            position: absolute;
            width: 20%;
            height: 30%;
            background-color: var(--accent-yellow);
            right: 10%;
            top: 10%;
        }

        .logo-text {
            color: white;
            font-weight: bold;
            font-size: 50px;
            letter-spacing: 1px;
        }

        .right-side {
            margin-top: 4rem;
            flex: 1;
            padding: clamp(20px, 5vw, 80px);
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: var(--dark-bg);
        }

        .right-side h1 {
            font-size: 45px;
            margin-bottom: clamp(15px, 3vw, 30px);
            color: var(--light-text);
            /* font-weight: bold; */
            line-height: 1.3;
        }

        .right-side p {
            font-size: 20px;
            line-height: 1.6;
            margin-bottom: clamp(10px, 3vw, 20px);
            color: var(--medium-text);
        }

        .copyright {
            margin-top: clamp(20px, 5vw, 40px);
            color: var(--copyright);
            font-size: clamp(12px, 3vw, 14px);
        }

        /* Segunda sección - Módulo Premium */
        .module-section {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header-image {
            height: 40vh;
            min-height: 250px;
            background-image: url('https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_2.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
            overflow: hidden;
        }

        .header-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.2) 0%, var(--dark-bg) 100%);
        }

        .module-content {
            flex: 1;
            padding: clamp(30px, 5vw, 60px) clamp(20px, 5vw, 80px);
            position: relative;
        }

        .module-content h1 {
            font-size: 45px;
            text-align: center;
            margin-bottom: clamp(20px, 5vw, 50px);
            color: var(--light-text);
            font-weight: bold;
            line-height: 1.3;
        }

        .modules-container {
            display: flex;
            flex-direction: column;
            gap: clamp(20px, 5vw, 40px);
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }

        .module {
            display: flex;
            gap: clamp(15px, 4vw, 30px);
            position: relative;
        }

        .module-number {
            width: clamp(30px, 8vw, 40px);
            height: clamp(30px, 8vw, 40px);
            background-color: var(--module-number-bg);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
            font-size: clamp(14px, 4vw, 18px);
            flex-shrink: 0;
            position: relative;
            z-index: 2;
            border: 2px solid var(--accent-yellow);
            color: var(--accent-yellow);
        }

        .module-content-text {
            flex: 1;
            padding-top: 8px;
        }

        .module-title {
            font-size: 30px;
            font-weight: bold;
            margin-bottom: clamp(5px, 2vw, 10px);
            color: var(--light-text);
            line-height: 1.3;
        }

        .module-description {
            font-size: 20px;
            line-height: 1.6;
            color: var(--medium-text);
        }

        .vertical-line {
            position: absolute;
            left: calc(clamp(15px, 4vw, 20px) - 1px);
            top: clamp(30px, 8vw, 40px);
            bottom: 35px;
            width: 2px;
            background-color: var(--module-line);
            z-index: -1;
        }

        /* Tercera sección - Gestión Tributaria */
        .tax-section {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: var(--darker-bg);
            position: relative;
        }

        .tax-header-image {
            height: 40vh;
            min-height: 250px;
            background-image: url('https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_3.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
        }

        .tax-header-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.8) 100%);
        }

        .tax-logo-container {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
            z-index: 2;
        }

        .tax-title {
            font-size: clamp(24px, 6vw, 48px);
            color: var(--light-text);
            font-weight: bold;
            position: relative;
            z-index: 2;
            margin-top: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
        }

        .tax-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-template-rows: repeat(2, auto);
            gap: 20px;
            padding: 40px 20px;
            max-width: 800px;
            margin: 0 auto;
            width: 100%;
            flex: 1;
        }


        .tax-card {
            background-color: #333;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            opacity: 0;
            transform: translateY(30px);
        }

        .tax-card.visible {
            opacity: 1;
            transform: translateY(0);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .tax-card h3 {
            font-size: 1.55rem;
            margin-bottom: 15px;
            color: var(--light-text);
            position: relative;
            padding-bottom: 10px;
        }

        .tax-card h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: var(--accent-yellow);
        }

        .tax-card p {
            font-size: 1.2rem;
            line-height: 1.6;
            color: var(--medium-text);
        }

        .tax-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.4);
        }

        /* Efectos de aparición con delay */
        .tax-card:nth-child(1) {
            transition-delay: 0.1s;
        }

        .tax-card:nth-child(2) {
            transition-delay: 0.2s;
        }

        .tax-card:nth-child(3) {
            transition-delay: 0.3s;
        }

        .tax-card:nth-child(4) {
            transition-delay: 0.4s;
        }

        /* Cuarta sección - Contabilidad Completa */
        .accounting-section {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: var(--dark-bg);
        }

        .accounting-content {
            flex: 1;
            padding: clamp(30px, 5vw, 60px) clamp(20px, 5vw, 40px);
            position: relative;
            z-index: 2;
        }

        .accounting-content h1 {
            font-size: clamp(24px, 5vw, 36px);
            margin-bottom: clamp(20px, 5vw, 50px);
            color: var(--light-text);
            font-weight: bold;
            line-height: 1.3;
            max-width: 800px;
        }

        .accounting-features-container {
            display: flex;
            gap: 30px;
            max-width: 800px;
        }

        .accounting-steps {
            display: flex;
            flex-direction: column;
            gap: 0px;
            padding-top: 0px;
        }

        .hexagon-step {
            width: 90px;
            height: 150px;
            background-color: var(--module-number-bg);
            position: relative;
            clip-path: polygon(0% 0%,
                    0% 0%,
                    50% 15%,
                    100% 0%,
                    100% 15%,
                    100% 85%,
                    50% 100%,
                    0% 85%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            color: var(--accent-yellow);
            border: none;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .hexagon-step.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .accounting-features {
            display: flex;
            flex-direction: column;
            gap: clamp(20px, 5vw, 30px);
            flex: 1;
        }

        .accounting-feature {
            display: flex;
            background-color: var(--dark-bg);
            padding: 12px;
            border-radius: 8px;
            align-items: flex-start;
            opacity: 0;
            transform: translateX(-20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .accounting-feature.visible {
            opacity: 1;
            transform: translateX(0);
        }

        .feature-text h3 {
            font-size: clamp(16px, 4vw, 20px);
            font-weight: bold;
            margin-bottom: clamp(5px, 2vw, 10px);
            color: var(--light-text);
            line-height: 1.3;
        }

        .feature-text p {
            font-size: clamp(13px, 3vw, 16px);
            line-height: 1.6;
            color: var(--medium-text);
        }

        .accounting-logo {
            margin-top: 40px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .accounting-image {
            flex: 1;
            min-height: 40vh;
            background-image: url('https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_4.jpg');
            background-size: cover;
            background-position: center;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            padding: 40px 20px;
        }

        .accounting-image::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.7) 100%);
        }

        .accounting-image-logo {
            position: absolute;
            top: 20px;
            gap: 10px;
            z-index: 2;
            text-align: center;
        }

        .accounting-image-text {
            position: relative;
            z-index: 2;
            color: white;
            text-align: center;
            width: 100%;
            max-width: 600px;
            padding: 25px;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            border-radius: 8px 8px 0 0;
            margin-bottom: -1px;
        }

        .accounting-image-text p {
            font-size: clamp(16px, 4vw, 24px);
            line-height: 1.4;
            margin-bottom: 10px;
            font-weight: bold;
        }

        .made-with {
            font-size: 12px;
            opacity: 0.7;
            font-weight: normal;
            letter-spacing: 1px;
        }

        /* Efectos de aparición con delay */
        .hexagon-step:nth-child(1) {
            transition-delay: 0.1s;
        }

        .hexagon-step:nth-child(2) {
            transition-delay: 0.2s;
        }

        .hexagon-step:nth-child(3) {
            transition-delay: 0.3s;
        }

        .accounting-feature:nth-child(1) {
            transition-delay: 0.1s;
        }

        .accounting-feature:nth-child(2) {
            transition-delay: 0.2s;
        }

        .accounting-feature:nth-child(3) {
            transition-delay: 0.3s;
        }

        /* Quinta Sección */
        .porter-control-section {
            background-color: var(--darker-bg);
            color: white;
            padding: 4rem 1.5rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 3rem;
            text-align: center;
            position: relative;
            padding-bottom: 1rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--accent-yellow);
        }

        .porter-grid {
            display: grid;
            gap: 2rem;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            padding: 0 1rem;
        }

        .porter-item {
            background-color: #333;
            padding: 1.5rem;
            border-left: 4px solid var(--accent-yellow);
            border-radius: 0.5rem;
            font-size: 20px;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .porter-item.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Sexta Sección */
        .task-user-section {
            background-image: url('https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_5.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 6rem 1.5rem;
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .task-user-overlay {
            background: rgba(0, 0, 0, 0.7);
            padding: 3rem 2rem;
            border-radius: 1.5rem;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            position: relative;
            padding-bottom: 1rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 4px;
            background: var(--accent-yellow);
        }

        .task-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2.5rem;
            justify-content: center;
        }

        .task-card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            padding: 2rem 1.5rem;
            text-align: center;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.5s ease-in-out;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .task-card:hover {
            background: rgba(255, 255, 255, 0.12);
            transform: translateY(-5px);
        }

        .task-card i {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            display: block;
            color: var(--accent-yellow);
        }

        .task-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .task-card h3 {
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .task-card p {
            font-size: 1.2rem;
            line-height: 1.6;
            opacity: 0.9;
        }

        .task-logo {
            margin-top: 4rem;
            text-align: center;
        }

        .task-card i {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            display: block;
            color: var(--light-text);
            /* Fallback color */
            transition: all 0.3s ease;
        }

        .task-card:hover i {
            transform: scale(1.1);
            color: var(--accent-yellow);
        }


        /*Septima sección*/
        .advanced-features {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: var(--darker-bg);
            position: relative;
            padding-bottom: 40px;
        }

        .features-hero {
            height: 40vh;
            min-height: 300px;
            background-image: url('https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_6.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 20px;
            margin-bottom: 40px;
        }

        .features-hero::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.8) 100%);
            z-index: 1;
        }

        .brand-logo {
            position: absolute;
            top: 30px;
            right: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            z-index: 2;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background-color: var(--accent-yellow);
            border-radius: 50%;
        }

        .logo-text {
            font-size: 50px;
            font-weight: 700;
            color: white;
            text-transform: lowercase;
        }

        .features-title {
            font-size: clamp(2rem, 5vw, 3.5rem);
            color: white;
            font-weight: 700;
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.6);
            margin-top: 20px;
            letter-spacing: -0.5px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(220px, 1fr));
            gap: 30px;
            padding: 0 40px;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .feature-card {
            background-color: #333;
            border-radius: 12px;
            padding: 35px 30px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            height: 100%;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(5px);
            min-height: 280px;
            opacity: 0;
            transform: translateY(30px);
        }

        .feature-card.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .feature-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
            background-color: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.12);
        }

        .feature-card h3 {
            font-size: 1.2rem;
            margin-bottom: 25px;
            color: white;
            position: relative;
            padding-bottom: 15px;
            line-height: 1.3;
            font-weight: 600;
        }

        .feature-card h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background-color: var(--accent-yellow);
            transition: width 0.3s ease;
        }

        .feature-card:hover h3::after {
            width: 70px;
        }

        .feature-card p {
            font-size: 1.1rem;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.8);
            margin-top: auto;
            flex-grow: 1;
        }

        /* Transiciones escalonadas */
        .feature-card:nth-child(1) {
            transition-delay: 0.1s;
        }

        .feature-card:nth-child(2) {
            transition-delay: 0.2s;
        }

        .feature-card:nth-child(3) {
            transition-delay: 0.3s;
        }

        .feature-card:nth-child(4) {
            transition-delay: 0.4s;
        }

        /* Octava sección */
        .key-benefits {
            background-color: #111;
            color: white;
            padding: 80px 40px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .benefits-container {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            gap: 60px;
            max-width: 1400px;
            width: 100%;
            flex-wrap: wrap;
        }

        .benefits-left {
            flex: 1 1 45%;
        }

        .benefits-title {
            font-size: clamp(2rem, 4vw, 3rem);
            margin-bottom: 40px;
            font-weight: bold;
            color: #fff;
        }

        .benefits-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 25px;
        }

        .benefits-list li {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .icon-square {
            width: 16px;
            height: 16px;
            background-color: #ccc;
            flex-shrink: 0;
            margin-top: 6px;
        }

        .benefits-right {
            flex: 1 1 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .benefits-image {
            max-width: 100%;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        /* Footer */
        .footer {
            padding: clamp(15px, 3vw, 20px);
            text-align: center;
            background-color: var(--darker-bg);
            color: var(--copyright);
            font-size: clamp(12px, 3vw, 14px);
        }

        /* -------------------------------------------- */
        /* PANTALLAS GRANDES (992px en adelante) */
        /* -------------------------------------------- */

        @media (min-width: 1200px) {
            .right-side {
                padding: 80px;
            }

            .module-content {
                padding: 60px 80px;
            }
        }

        @media (min-width: 992px) {

            .hero-section {
                flex-direction: row;
                height: 100vh;
            }

            .left-side,
            .right-side {
                height: auto;
            }

            .left-side {
                min-height: 100%;
            }

            .accounting-section {
                flex-direction: row;
                height: 100vh;
            }

            .accounting-content {
                width: 50%;
                padding: clamp(30px, 5vw, 80px);
            }

            .accounting-features-container {
                gap: 30px;
            }

            .accounting-steps {
                gap: 40px;
                padding-top: 10px;
                margin-right: 30px;
            }

            .hexagon-step {
                width: 80px;
                height: 100px;
                font-size: 24px;
            }

            .accounting-features {
                gap: 30px;
            }

            .accounting-feature {
                padding: 10px;
            }

            .feature-text h3 {
                font-size: 25px;
            }

            .feature-text p {
                font-size: 16px;
            }

            .accounting-image {
                width: 50%;
                min-height: 100%;
            }

            .porter-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* -------------------------------------------- */
        /* PANTALLAS MEDIANAS (769px a 991px) */
        /* -------------------------------------------- */
        @media (min-width: 769px) and (max-width: 991px) {
            .accounting-section {
                flex-direction: row;
            }

            .accounting-content {
                width: 55%;
                padding: 40px;
            }

            .accounting-features-container {
                gap: 20px;
            }

            .accounting-steps {
                gap: 30px;
                padding-top: 5px;
                margin-right: 20px;
            }

            .hexagon-step {
                width: 80px;
                height: 120px;
                font-size: 20px;
            }

            .accounting-features {
                gap: 20px;
            }

            .accounting-feature {
                padding: 10px;
            }

            .feature-text h3 {
                font-size: 18px;
            }

            .feature-text p {
                font-size: 15px;
            }

            .accounting-image {
                width: 45%;
            }

            .porter-grid {
                grid-template-columns: 1fr 1fr;
            }

            .features-grid {
                grid-template-columns: repeat(2, minmax(280px, 1fr));
                max-width: 600px;
                gap: 25px;
            }

            .feature-card {
                min-height: 140px;
                padding: 30px 25px;
            }

            .features-hero {
                min-height: 280px;
            }
        }

        /* -------------------------------------------- */
        /* PANTALLAS PEQUEÑAS (hasta 768px) */
        /* -------------------------------------------- */
        @media (max-width: 768px) {

            .tax-grid {
                grid-template-columns: 1fr;
                grid-template-rows: repeat(4, auto);
                max-width: 100%;
                padding: 20px;
            }

            .accounting-section {
                flex-direction: column;
            }

            .accounting-content {
                width: 100%;
                padding: 30px 20px;
            }

            .accounting-features-container {
                flex-direction: row;
                gap: 15px;
            }

            .accounting-steps {
                gap: 20px;
                margin-right: 15px;
            }

            .hexagon-step {
                width: 70px;
                height: 100px;
                font-size: 18px;
            }

            .accounting-feature {
                padding: 8px;
            }

            .feature-text h3 {
                font-size: 16px;
            }

            .feature-text p {
                font-size: 14px;
            }

            .accounting-image {
                width: 100%;
                min-height: 50vh;
            }

            .accounting-logo {
                display: none;
            }

            .porter-control-section {
                padding: 3rem 1rem;
            }

            .section-title {
                font-size: 2rem;
                margin-bottom: 2rem;
            }

            .porter-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .task-user-section {
                padding: 4rem 1rem;
            }

            .task-user-overlay {
                padding: 2rem 1.5rem;
            }

            .section-title {
                font-size: 2rem;
                margin-bottom: 2rem;
            }

            .task-cards {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .task-card {
                padding: 1.5rem 1rem;
            }

            .task-logo {
                margin-top: 3rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 20px;
                padding: 0 25px;
                max-width: 450px;
            }

            .feature-card {
                min-height: auto;
                padding: 30px;
            }

            .features-hero {
                height: 35vh;
                min-height: 250px;
                margin-bottom: 30px;
            }

            .brand-logo {
                top: 20px;
                right: 20px;
            }

            .logo-icon {
                width: 35px;
                height: 35px;
            }

            .logo-text {
                font-size: 45px;
            }

            .feature-card h3 {
                font-size: 1.4rem;
                margin-bottom: 20px;
            }

            .feature-card p {
                font-size: 1.05rem;
            }
        }

        /* Animaciones */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .module {
            opacity: 0;
            animation-fill-mode: both;
        }

        .module.animate {
            animation: fadeInUp 0.6s ease-out forwards;
        }
    </style>

    <!-- Configuración básica y viewport -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="robots" content="index, follow">

    <!-- Descripción y keywords para SEO -->
    <meta name="description" content="MAXIMOPH: Software líder para administración de propiedades horizontales. Soluciones integrales para conjuntos residenciales con módulos administrativos, financieros y contables en tiempo real.">
    <meta name="keywords" content="software propiedad horizontal, administración conjuntos residenciales, contabilidad condominios, control visitas, gestión paquetes, comunicación portería, maximoph, gestion ph, software condominios, ph propiedad horizontal">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://maximoph.co/">
    <meta property="og:title" content="MAXIMOPH | Software para Propiedad Horizontal">
    <meta property="og:description" content="Solución integral para la administración de conjuntos residenciales. Control financiero, contable y operativo en tiempo real con MAXIMOPH.">
    <meta property="og:image" content="https://maximoph.co/img/og-image-maximoph.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="MAXIMOPH">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@MaximoPH">
    <meta name="twitter:creator" content="@MaximoPH">
    <meta name="twitter:title" content="MAXIMOPH | Software para Propiedad Horizontal">
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

    <!-- Canonical URL -->
    <link rel="canonical" href="https://maximoph.co/">

    <!-- Rutas importantes -->
    <link rel="alternate" href="https://maximoph.co/login" title="Acceso a MAXIMOPH">

    <!-- Font Size -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Favicons -->
    <link rel="icon" href="/img/logo_base.png" type="image/png">
    <link rel="alternate icon" href="/img/logo_base.png" type="image/png">
    <link rel="shortcut icon" href="/img/logo_base.png">

    <!-- Metaetiquetas para redes sociales (usando solo las imágenes que ya tienes) -->
    <meta property="og:logo" content="https://maximoph.co/img/logo_base.png">
    <meta name="twitter:image" content="https://maximoph.co/img/logo_contabilidad.png">
    <meta name="twitter:image:alt" content="Logo de MAXIMOPH">

    <!-- Configuración mínima para Apple (usa el mismo logo_base.png) -->
    <link rel="apple-touch-icon" href="/img/logo_base.png">

    <!-- Pre-conexión -->
    <link rel="preconnect" href="https://maximoph.co">
    <link rel="dns-prefetch" href="https://maximoph.co">

    <!-- Schema.org markup -->
    <script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    "name": "MAXIMOPH",
    "alternateName": ["Maximo PH", "Software PH", "Propiedad Horizontal"],
    "description": "Software especializado en la gestión de Propiedades Horizontales y condominios",
    "applicationCategory": "BusinessApplication",
    "operatingSystem": "Web Application",
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
    }
    }
    </script>

</head>

<body>

    <nav class="navbar" id="navbar">
        <div class="navbar-container">
            <div class="logo">Maximo PH</div>
            <button class="hamburger" onclick="toggleMenu()">☰</button>
            <ul class="menu" id="menu">
                <li><a href="/login" class="d-inline-block d-lg-none flex-order-1 f5 no-underline border color-border-default rounded-2 px-2 py-1 color-fg-inherit">Iniciar sesion</a></li>
            </ul>
        </div>
    </nav>

    <!-- Primera sección -->
    <section class="hero-section" id="inicio">
        <div class="left-side">
            <div class="logo-container">
                <img src="/img/logo_base.png" alt="Maximo Logo" />
                <div class="logo-text">maximo</div>
            </div>
        </div>

        <div class="right-side">
            <h1>Software de Propiedad Horizontal</h1>
            <p>
                Bienvenido al revolucionario Software de Propiedad Horizontal, un
                ecosistema integral diseñado para optimizar la gestión administrativa,
                facturación y contable de su comunidad. Nuestro sistema ofrece una
                solución completa que le permite mantenerse informado en tiempo real,
                optimizar procesos y contar con un soporte personalizado. Además,
                facilitamos la migración de su información existente para una
                transición sin complicaciones.
            </p>
            <p>
                Con Máximo puedes administrar Centros comerciales, Oficinas, Bodegas,
                Condominios de casas y Apartamentos tanto residenciales, Comerciales o
                mixtas.
            </p>
        </div>
    </section>

    <!-- Segunda sección -->
    <section class="module-section" id="modulo-premium">
        <div class="header-image"></div>

        <div class="module-content">
            <h1>Módulo Premium: Facturación por Coeficientes y/o valores</h1>

            <div class="modules-container">
                <div class="vertical-line"></div>

                <div class="module">
                    <div class="module-number">1</div>
                    <div class="module-content-text">
                        <div class="module-title">Causación de facturación</div>
                        <div class="module-description">
                            Cálculo de la administración por coeficientes separado por cada
                            inmueble.
                        </div>
                    </div>
                </div>

                <div class="module">
                    <div class="module-number">2</div>
                    <div class="module-content-text">
                        <div class="module-title">Cruce Automático de Anticipos</div>
                        <div class="module-description">
                            Gestión eficiente de pagos anticipados con reconciliación
                            automática.
                        </div>
                    </div>
                </div>

                <div class="module">
                    <div class="module-number">3</div>
                    <div class="module-content-text">
                        <div class="module-title">Causación de Intereses</div>
                        <div class="module-description">
                            Cálculo automático de intereses por saldos morosos, manteniendo
                            un control efectivo de las cuentas por cobrar.
                        </div>
                    </div>
                </div>

                <div class="module">
                    <div class="module-number">4</div>
                    <div class="module-content-text">
                        <div class="module-title">Cuotas Extras y Multas</div>
                        <div class="module-description">
                            Flexibilidad para aplicar cuotas extraordinarias por
                            coeficientes o multas, ya sea de forma individual o masiva, por
                            rangos de fechas específicos.
                        </div>
                    </div>
                </div>

                <div class="module">
                    <div class="module-number">5</div>
                    <div class="module-content-text">
                        <div class="module-title">Conexión en Tiempo Real</div>
                        <div class="module-description">
                            Integración instantánea con la contabilidad para una gestión
                            financiera precisa y actualizada.
                        </div>
                    </div>
                </div>

                <div class="module">
                    <div class="module-number">6</div>
                    <div class="module-content-text">
                        <div class="module-title">Facturación Electrónica</div>
                        <div class="module-description">
                            Gestión de facturas electrónicas para las administraciones
                            comerciales o mixtas.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Tercera sección -->
    <section class="tax-section" id="gestion-tributaria">
        <div class="tax-header-image">
            <h1 class="tax-title">Gestión Tributaria Simplificada</h1>
        </div>

        <div class="tax-grid">
            <div class="tax-card">
                <h3>Medios Magnéticos</h3>
                <p>Generación automática de informes para cumplimiento tributario.</p>
            </div>

            <div class="tax-card">
                <h3>IVA</h3>
                <p>
                    IVA como un mayor valor del gasto en la misma cuenta, cálculo de IVA
                    - AIU
                </p>
            </div>

            <div class="tax-card">
                <h3>ICA</h3>
                <p>Manejo eficiente del Impuesto de Industria y Comercio.</p>
            </div>

            <div class="tax-card">
                <h3>Certificados de Retención</h3>
                <p>Emisión y gestión de certificados de retención fiscal.</p>
            </div>
        </div>
    </section>

    <!-- Cuarta sección -->
    <section class="accounting-section" id="contabilidad-completa">
        <div class="accounting-content">
            <h1>Módulo Premium: Contabilidad Completa</h1>

            <div class="accounting-features-container">
                <div class="accounting-steps">
                    <div class="hexagon-step">1</div>
                    <div class="hexagon-step">2</div>
                    <div class="hexagon-step">3</div>
                </div>

                <div class="accounting-features">
                    <div class="accounting-feature">
                        <div class="feature-text">
                            <h3>Informes Financieros</h3>
                            <p>
                                Generación de informes detallados de cartera, balances y
                                estados de resultados.
                            </p>
                        </div>
                    </div>

                    <div class="accounting-feature">
                        <div class="feature-text">
                            <h3>Ejecución Presupuestaria</h3>
                            <p>
                                Seguimiento en tiempo real de la ejecución del presupuesto con
                                alertas automáticas.
                            </p>
                        </div>
                    </div>

                    <div class="accounting-feature">
                        <div class="feature-text">
                            <h3>Auxiliares e Historiales</h3>
                            <p>
                                Acceso a auxiliares contables e historiales detallados para un
                                análisis profundo.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accounting-image">
            <div class="accounting-image-logo">
                <img src="/img/logo_base.png" alt="Maximo Logo" />
                <div class="logo-text">maximo</div>
            </div>

            <div class="accounting-image-text">
                <p>
                    ADMINISTRACIÓN | FACTURACIÓN<br />Y CONTABILIDAD EN UN SOLO<br />SISTEMA
                </p>
            </div>
        </div>
    </section>

    <!-- Quinta sección  -->
    <section id="porteria" class="porter-control-section">
        <h2 class="section-title">Control de Portería</h2>
        <div class="porter-grid">
            <div class="porter-item">Bitácora documentada</div>
            <div class="porter-item">Restricción de morosos</div>
            <div class="porter-item">Control de entradas y salidas</div>
            <div class="porter-item">Gestión de domicilios y paquetes</div>
            <div class="porter-item">Notificaciones de novedades</div>
            <div class="porter-item">Monitoreo de vehículos y mascotas</div>
            <div class="porter-item">
                Conexión en línea con cámaras inteligentes
            </div>
            <div class="porter-item">Seguimiento de minuta digital</div>
        </div>
    </section>

    <!-- Sexta sección-->
    <section id="gestion-tareas" class="task-user-section">
        <div class="task-user-overlay">
            <h2 class="section-title">Gestión de Tareas y Perfiles de Usuario</h2>
            <div class="task-cards">
                <div class="task-card">
                    <i class="fas fa-tasks fa-3x"></i>
                    <h3>Seguimiento de Tareas</h3>
                    <p>
                        Control eficiente de tareas para rondas, personal de aseo,
                        portería y proyectos específicos.
                    </p>
                </div>
                <div class="task-card">
                    <i class="fas fa-user-cog fa-3x"></i>
                    <h3>Perfiles Parametrizados</h3>
                    <p>
                        Configuración de perfiles para propietarios, inquilinos, personal
                        de la unidad y proveedores.
                    </p>
                </div>
                <div class="task-card">
                    <i class="fas fa-lock fa-3x"></i>
                    <h3>Acceso Seguro</h3>
                    <p>
                        Gestión de accesos y permisos para garantizar la seguridad y
                        privacidad de la información.
                    </p>
                </div>
            </div>
            <div class="task-logo">
                <img src="/img/logo_base.png" alt="Maximo Logo" />
                <div class="logo-text">máximo</div>
            </div>
        </div>
    </section>

    <!-- Septima sección -->
    <section class="advanced-features" id="fun-advanced">
        <div class="features-hero">
            <!-- <div class="brand-logo">
                <img src="/img/logo_base.png" alt="Maximo Logo" />
                <div class="logo-text">máximo</div>
            </div> -->
            <h1 class="features-title">Funcionalidades Avanzadas</h1>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <h3>Tesorería</h3>
                <p>
                    Importador, pasarela de pagos, Conciliación bancaria, Gestión de
                    ingresos y egresos en tiempo real
                </p>
            </div>

            <div class="feature-card">
                <h3 style="font-size: clamp(18px, 19px)">
                    Activos Fijos e Inventarios
                </h3>
                <p>Control de activos e inventarios</p>
            </div>

            <div class="feature-card">
                <h3>Proyectos</h3>
                <p>Seguimientos de los proyectos atreves de tareas asignadas</p>
            </div>

            <div class="feature-card">
                <h3>Censo Familiar</h3>
                <p>
                    Documentación de: Propietario, residente, vehículos y mascotas con
                    foto
                </p>
            </div>
        </div>
    </section>

    <!-- Octava sección -->
    <section class="key-benefits" id="key-benefits">
        <div class="benefits-container">
            <div class="benefits-left">
                <h2 class="benefits-title">Beneficios clave</h2>
                <ul class="benefits-list">
                    <li>
                        <span class="icon-square"></span>
                        <strong>PQRSF</strong> – Gestión ágil y centralizada de
                        peticiones, quejas, reclamos y sugerencias.
                    </li>
                    <li>
                        <span class="icon-square"></span>
                        <strong>Tareas</strong> – Asignación, seguimiento y notificaciones
                        inteligentes.
                    </li>
                    <li>
                        <span class="icon-square"></span>
                        <strong>Turnos</strong> – Control de acceso y horarios
                        automatizados.
                    </li>
                    <li>
                        <span class="icon-square"></span>
                        <strong>Reportes</strong> – Visualización clara y exportación de
                        datos en tiempo real.
                    </li>
                </ul>
            </div>
            <div class="benefits-right">
                <img src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/fondo_pantalla/fondo_8.jpg"
                    alt="Mockup software" class="benefits-image" />
            </div>
        </div>
    </section>

    <footer class="footer">
        © 2025 Máximo - Software de Propiedad Horizontal | Todos los derechos
        reservados
    </footer>

    <script>

        // Función para el menú hamburguesa
        function toggleMenu() {
            const menu = document.getElementById('menu');
            menu.classList.toggle('active');
        }
        
        // Función para cambiar el estilo de la barra al hacer scroll
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            
            // Si el scroll es mayor a 50px, añadimos la clase 'scrolled'
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Animación para los módulos
            const modules = document.querySelectorAll(".module");
            // Animación para las tarjetas tributarias
            const taxCards = document.querySelectorAll(".tax-card");
            // Animación para las características de contabilidad
            const accountingFeatures = document.querySelectorAll(".accounting-feature");
            // Animación para los hexágonos
            const hexagonSteps = document.querySelectorAll(".hexagon-step");
            // Animación para los ítems de control de portería
            const porterItems = document.querySelectorAll(".porter-item");
            // Animación para las tarjetas de tareas
            const taskCards = document.querySelectorAll(".task-card");
            // Animación para las tarjetas de tareas
            const featureCard = document.querySelectorAll(".feature-card");
            // Animación para beneficios

            function checkElements() {
                // Módulos
                modules.forEach((module, index) => {
                    const modulePosition = module.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.3;

                    if (modulePosition < screenPosition) {
                        setTimeout(() => {
                            module.classList.add("animate");
                        }, index * 200);
                    }
                });

                // Tarjetas tributarias
                taxCards.forEach((card, index) => {
                    const cardPosition = card.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.1;

                    if (cardPosition < screenPosition) {
                        setTimeout(() => {
                            card.classList.add("visible");
                        }, index * 200);
                    }
                });

                // Características de contabilidad
                accountingFeatures.forEach((feature, index) => {
                    setTimeout(() => {
                        feature.classList.add("visible");
                    }, index * 200);
                });

                // Hexágonos
                hexagonSteps.forEach((step, index) => {
                    const stepPosition = step.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.1;

                    if (stepPosition < screenPosition) {
                        setTimeout(() => {
                            step.classList.add("visible");
                        }, index * 200);
                    }
                });

                // Porteria
                porterItems.forEach((item, index) => {
                    const itemPosition = item.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.1;

                    if (itemPosition < screenPosition) {
                        setTimeout(() => {
                            item.classList.add("visible");
                        }, index * 200);
                    }
                });

                // Gestion de tareas
                taskCards.forEach((card, index) => {
                    const cardPosition = card.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.3;

                    if (cardPosition < screenPosition) {
                        setTimeout(() => {
                            card.classList.add("visible");
                        }, index * 200);
                    }
                });

                // Funciones avanzadas
                featureCard.forEach((card, index) => {
                    const cardPosition = card.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.1;

                    if (cardPosition < screenPosition) {
                        setTimeout(() => {
                            card.classList.add("visible");
                        }, index * 200);
                    }
                });

                // Beneficios

            }

            window.addEventListener("scroll", checkElements);
            checkElements();
        });
    </script>
</body>

</html>