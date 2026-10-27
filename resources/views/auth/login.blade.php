@extends('layouts.app_no_nav')

@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Manrope:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

<style>
    :root {
        --bg-primary: #faf8f5;
        --bg-secondary: #f2ede5;
        --bg-tertiary: #ebe3d6;
        --text-primary: #1c1917;
        --text-secondary: #44403c;
        --text-muted: #78716c;
        --border-color: #e7e0d5;
        --border-hover: #d6ccba;
        --card-bg: #ffffff;
        --accent-primary: #059669;
        --accent-primary-hover: #047857;
        --grad: linear-gradient(120deg, #059669 0%, #0d9488 50%, #14b8a6 100%);
        --grid-line: rgba(28, 25, 23, 0.06);
        --shadow-xl: 0 30px 60px rgba(28, 25, 23, 0.18);
        --font-display: "Sora", system-ui, sans-serif;
        --font-body: "Manrope", system-ui, sans-serif;
        --font-mono: "JetBrains Mono", ui-monospace, monospace;
    }

    [data-theme="dark"] {
        --bg-primary: #0a1410;
        --bg-secondary: #0f1e17;
        --bg-tertiary: #142a20;
        --text-primary: #f5f5f4;
        --text-secondary: #d6d3d1;
        --text-muted: #a8a29e;
        --border-color: #1f3a2c;
        --border-hover: #2d5240;
        --card-bg: #0f1e17;
        --accent-primary: #10b981;
        --accent-primary-hover: #34d399;
        --grad: linear-gradient(120deg, #34d399 0%, #2dd4bf 50%, #5eead4 100%);
        --grid-line: rgba(255, 255, 255, 0.05);
        --shadow-xl: 0 30px 60px rgba(0, 0, 0, 0.6);
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    html, body {
        min-height: 100vh;
        font-family: var(--font-body);
        background-color: var(--bg-primary);
        color: var(--text-primary);
        transition: background-color 0.3s ease, color 0.3s ease;
        -webkit-font-smoothing: antialiased;
    }

    /* Fondo con grid + blobs */
    .login-bg {
        position: fixed; inset: 0;
        background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 60%, var(--bg-primary) 100%);
        z-index: 0; overflow: hidden;
    }
    .login-grid-bg {
        position: absolute; inset: 0;
        background-image: linear-gradient(var(--grid-line) 1px, transparent 1px), linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
        background-size: 56px 56px;
        -webkit-mask-image: radial-gradient(ellipse 75% 65% at 50% 45%, #000 30%, transparent 100%);
        mask-image: radial-gradient(ellipse 75% 65% at 50% 45%, #000 30%, transparent 100%);
    }
    .blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.55; will-change: transform; }
    .blob-1 { width: 480px; height: 480px; background: rgba(16, 185, 129, 0.4); top: -160px; right: -80px; }
    .blob-2 { width: 400px; height: 400px; background: rgba(20, 184, 166, 0.35); bottom: -140px; left: -60px; }
    .blob-3 { width: 300px; height: 300px; background: rgba(249, 115, 22, 0.22); top: 45%; left: 55%; }
    [data-theme="dark"] .blob { opacity: 0.3; }

    /* Toggle tema */
    .theme-toggle-fixed {
        position: fixed; top: 1.25rem; right: 1.25rem; z-index: 20;
        width: 2.6rem; height: 2.6rem; border: 1px solid var(--border-color); border-radius: 0.7rem;
        background: var(--card-bg); color: var(--text-primary);
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        transition: all 0.25s ease; box-shadow: 0 1px 2px rgba(0,0,0,0.06);
    }
    .theme-toggle-fixed:hover { border-color: var(--accent-primary); color: var(--accent-primary); }
    .theme-toggle-fixed svg { position: absolute; width: 18px; height: 18px; transition: all 0.35s ease; }
    .theme-toggle-fixed .ic-moon { opacity: 0; transform: rotate(180deg); }
    [data-theme="dark"] .theme-toggle-fixed .ic-sun { opacity: 0; transform: rotate(180deg); }
    [data-theme="dark"] .theme-toggle-fixed .ic-moon { opacity: 1; transform: rotate(0deg); }

    /* Wrapper */
    .login-wrapper { position: relative; z-index: 1; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem 3rem; }
    .login-shell { width: 100%; max-width: 960px; display: grid; grid-template-columns: 1fr; gap: 3rem; align-items: center; }
    @media (min-width: 1024px) { .login-shell { grid-template-columns: 1fr 1fr; gap: 4rem; } }

    /* Panel marketing */
    .login-marketing { display: none; }
    @media (min-width: 1024px) { .login-marketing { display: flex; flex-direction: column; gap: 1.5rem; } }
    .brand-row { display: flex; align-items: center; gap: 0.85rem; text-decoration: none; }
    .brand-row img { width: 48px; height: 48px; object-fit: contain; }
    .brand-row .brand-name { font-family: var(--font-display); font-weight: 800; font-size: 1.15rem; letter-spacing: 0.06em; color: var(--text-primary); }
    .marketing-badge { display: inline-flex; align-items: center; gap: 0.55rem; padding: 0.45rem 0.95rem; background: rgba(5, 150, 105, 0.1); color: var(--accent-primary); border: 1px solid rgba(5, 150, 105, 0.25); border-radius: 999px; font-size: 0.8rem; font-weight: 700; width: fit-content; }
    .live-dot { position: relative; display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: var(--accent-primary); flex-shrink: 0; }
    .live-dot::after { content: ""; position: absolute; inset: 0; border-radius: 50%; background: var(--accent-primary); animation: ring 1.8s ease-out infinite; }
    .marketing-title { font-family: var(--font-display); font-weight: 800; font-size: 2.4rem; line-height: 1.08; letter-spacing: -0.035em; color: var(--text-primary); }
    .marketing-title .highlight { background: var(--grad); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent; }
    .marketing-desc { font-size: 1rem; color: var(--text-secondary); line-height: 1.7; max-width: 460px; }
    .marketing-desc strong { color: var(--text-primary); font-weight: 800; }
    .marketing-points { list-style: none; display: flex; flex-direction: column; gap: 0.75rem; margin-top: 0.25rem; }
    .marketing-points li { display: flex; align-items: center; gap: 0.65rem; font-size: 0.93rem; font-weight: 600; color: var(--text-secondary); }
    .marketing-points .check { width: 1.4rem; height: 1.4rem; border-radius: 50%; background: var(--accent-primary); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 800; flex-shrink: 0; }

    /* Tarjeta */
    .login-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 1.5rem; padding: 2.5rem 2.25rem; box-shadow: var(--shadow-xl); position: relative; overflow: hidden; width: 100%; }
    .login-card::before { content: ""; position: absolute; inset: -1px; border-radius: inherit; z-index: -1; background: var(--grad); opacity: 0.25; filter: blur(30px); }
    .login-card > * { position: relative; z-index: 1; }

    .card-brand { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); margin-bottom: 1.5rem; }
    .card-brand img { width: 48px; height: 48px; object-fit: contain; }
    .card-brand h1 { font-family: var(--font-display); font-size: 1.15rem; font-weight: 800; letter-spacing: 0.08em; color: var(--text-primary); margin: 0; }

    .auth-heading { text-align: center; font-family: var(--font-display); font-weight: 700; font-size: 1.25rem; letter-spacing: -0.01em; color: var(--text-primary); margin-bottom: 1.5rem; }

    /* Inputs */
    .form-label-fl { display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-secondary); margin-bottom: 0.4rem; }
    
    .input-wrap { margin-bottom: 1.1rem; }
    .input-wrap .form-control {
        width: 100%; background: var(--bg-secondary); border: 1.5px solid var(--border-color);
        color: var(--text-primary); font-family: var(--font-body); font-size: 0.95rem; font-weight: 500;
        padding: 0.75rem 1rem; border-radius: 0.75rem; transition: all 0.2s ease; height: auto;
    }
    .input-wrap .form-control:focus { outline: none; background: var(--card-bg); border-color: var(--accent-primary); box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.15); }
    .input-wrap .form-control::placeholder { color: var(--text-muted); }

    /* Arreglo del Input Group para el ojito */
    .input-group.password-group {
        display: flex; flex-wrap: nowrap; border-radius: 0.75rem;
        transition: all 0.2s ease; background: var(--bg-secondary); border: 1.5px solid var(--border-color);
    }
    .input-group.password-group:focus-within {
        background: var(--card-bg); border-color: var(--accent-primary); box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.15);
    }
    .input-group.password-group .form-control {
        border: none !important; box-shadow: none !important; background: transparent !important;
        border-radius: 0.75rem 0 0 0.75rem !important;
    }
    .btn-toggle-pass {
        display: flex; align-items: center; justify-content: center;
        width: 3rem; background: transparent; border: none; border-left: 1px solid var(--border-color);
        color: var(--text-muted); cursor: pointer; transition: all 0.2s ease;
        border-radius: 0 0.75rem 0.75rem 0; font-size: 1.1rem;
    }
    .btn-toggle-pass:hover { color: var(--accent-primary); background: rgba(5, 150, 105, 0.05); }

    /* Botón principal */
    .btn-grad {
        background: var(--grad); color: #fff; border: none; border-radius: 0.75rem;
        font-family: var(--font-body); font-weight: 700; font-size: 1rem;
        padding: 0.85rem 1rem; width: 100%; display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        cursor: pointer; transition: all 0.25s ease; box-shadow: 0 8px 22px rgba(5, 150, 105, 0.3);
        position: relative; overflow: hidden;
    }
    .btn-grad::after { content: ""; position: absolute; inset: 0; background: linear-gradient(100deg, transparent 30%, rgba(255,255,255,0.3) 50%, transparent 70%); transform: translateX(-120%); transition: transform 0.7s ease; }
    .btn-grad:hover { box-shadow: 0 14px 32px rgba(5,150,105,0.45); transform: translateY(-2px); }
    .btn-grad:hover::after { transform: translateX(120%); }
    .btn-grad:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }

    /* Mensajes */
    .msg-error { color: #dc2626; font-size: 0.85rem; font-weight: 600; margin-bottom: 1rem; text-align: center; display: none; }
    .msg-success { color: var(--accent-primary); font-size: 0.85rem; font-weight: 600; margin-bottom: 1rem; text-align: center; display: none; }

    /* Links inferiores */
    .auth-links { display: flex; justify-content: center; align-items: center; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap; }
    .auth-links a { color: var(--accent-primary); font-size: 0.88rem; font-weight: 700; text-decoration: none; cursor: pointer; transition: color 0.2s ease; }
    .auth-links a:hover { color: var(--accent-primary-hover); text-decoration: underline; }

    .version-tag { text-align: center; font-family: var(--font-mono); font-size: 0.75rem; color: var(--text-muted); margin-top: 1.5rem; }
    .powered-by { display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 1rem; font-size: 0.75rem; font-weight: 800; letter-spacing: 0.2em; color: var(--text-muted); text-transform: uppercase; }
    .powered-by img { width: 36px; height: 36px; object-fit: contain; }

    @keyframes ring { 0% { transform: scale(1); opacity: 0.6; } 100% { transform: scale(2.6); opacity: 0; } }
    @media (max-width: 480px) { .login-card { padding: 2rem 1.5rem; } }
</style>

<!-- Fondo -->
<div class="login-bg" aria-hidden="true">
    <div class="login-grid-bg"></div>
    <div class="blob blob-1" id="blob1"></div>
    <div class="blob blob-2" id="blob2"></div>
    <div class="blob blob-3" id="blob3"></div>
</div>

<!-- Toggle tema -->
<button class="theme-toggle-fixed" id="themeToggleLogin" aria-label="Cambiar tema">
    <svg class="ic-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
    <svg class="ic-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
</button>

<div class="login-wrapper">
    <div class="login-shell">

        <!-- Marketing -->
        <div class="login-marketing">
            <a href="/" class="brand-row">
                <img src="/img/logo_base.png" alt="Máximo PH">
                <span class="brand-name">MÁXIMO PH</span>
            </a>
            <div class="marketing-badge"><span class="live-dot"></span> Pagos AV Villas en tiempo real</div>
            <h2 class="marketing-title">Tu conjunto,<br><span class="highlight">en armonía total.</span></h2>
            <p class="marketing-desc">Administración, facturación y contabilidad en una sola plataforma. La contabilidad la hace <strong>Portafolio ERP</strong>, en tiempo real.</p>
            <ul class="marketing-points">
                <li><span class="check">✓</span> Pagos AV Villas en tiempo real</li>
                <li><span class="check">✓</span> Contabilidad automática con Portafolio ERP</li>
                <li><span class="check">✓</span> Facturación masiva por correo y WhatsApp</li>
                <li><span class="check">✓</span> Portería, PQRS y censo familiar</li>
            </ul>
        </div>

        <!-- Login -->
        <div class="login-card">
            <div class="card-brand">
                <img src="/img/logo_base_sin_texto.png" alt="Máximo PH">
                <h1>MÁXIMO PH</h1>
            </div>

            <p id="info-login" style="display:none;">Bienvenido al login, ingrese usuario y contraseña Maximo PH 2026 © All Rights Reserved.</p>
            <p id="error-login" class="msg-error">¡Usuario o contraseña incorrectos!</p>
            <p id="error-recover" class="msg-error">¡El correo electrónico no existe!</p>
            <p id="success-recover" class="msg-success">¡Se ha cambiado la contraseña con éxito!</p>

            <h4 id="texto-login" class="auth-heading">Iniciar sesión</h4>
            <h4 id="texto-recover" class="auth-heading" style="display: none;">Recuperar contraseña</h4>

            <input type="text" id="recaptcha-token" name="recaptcha-token" style="display:none;">

            <div id="input_email_login" class="input-wrap">
                <label for="email_login" class="form-label-fl">Usuario / Correo electrónico</label>
                <input type="email" class="form-control" id="email_login" name="email_login" autocomplete="username">
            </div>

            <div id="input_code_login" class="input-wrap" style="display: none;">
                <label for="code_login" class="form-label-fl">Código de verificación</label>
                <input type="text" maxlength="5" class="form-control" id="code_login" name="code_login" placeholder="5-digit-code">
            </div>

            <!-- Contraseña principal -->
            <div id="input-password" class="input-wrap">
                <label for="password_login" class="form-label-fl">Contraseña</label>
                <div class="input-group password-group" id="show_hide_password">
                    <input class="form-control" type="password" id="password_login" name="password_login" autocomplete="current-password">
                    <button class="btn-toggle-pass" type="button" tabindex="-1">
                        <i class="fa fa-eye-slash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <!-- Nueva contraseña -->
            <div id="input-new-password" class="input-wrap" style="display: none;">
                <label for="new_password_login" class="form-label-fl">Contraseña nueva</label>
                <div class="input-group password-group" id="show_hide_new_password">
                    <input class="form-control" type="password" id="new_password_login" name="new_password_login" autocomplete="new-password">
                    <button class="btn-toggle-pass" type="button" tabindex="-1">
                        <i class="fa fa-eye-slash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <!-- Repetir contraseña -->
            <div id="input-retry-password" class="input-wrap" style="display: none;">
                <label for="new_password_retry_login" class="form-label-fl">Repetir contraseña</label>
                <div class="input-group password-group" id="show_hide_new_password_retry">
                    <input class="form-control" type="password" id="new_password_retry_login" name="new_password_retry_login" autocomplete="new-password">
                    <button class="btn-toggle-pass" type="button" tabindex="-1">
                        <i class="fa fa-eye-slash" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <!-- Botones -->
            <button id="button-login" type="button" class="btn-grad" style="margin-top: 1.5rem;">Iniciar sesión</button>
            <button id="button-login-loading" type="button" class="btn-grad" style="display: none; margin-top: 1.5rem;" disabled>
                Cargando <i class="fas fa-spinner fa-spin" style="margin-left: 0.3rem;"></i>
            </button>

            <button id="button-recover" type="button" class="btn-grad" style="display: none; margin-top: 1.5rem;">Buscar</button>
            <button id="button-recover-loading" type="button" class="btn-grad" style="display: none; margin-top: 1.5rem;" disabled>
                Buscando <i class="fas fa-spinner fa-spin" style="margin-left: 0.3rem;"></i>
            </button>

            <button id="cambiar-password" type="button" class="btn-grad" style="display: none; margin-top: 1.5rem;">Actualizar contraseña</button>
            <button id="button-confir-code" type="button" class="btn-grad" style="display: none; margin-top: 1.5rem;">Validar</button>
            <button id="button-resend" type="button" class="btn-grad" style="display: none; margin-top: 1.5rem;">Volver a enviar email</button>
            <button id="button-resend-disabled" type="button" class="btn-grad" style="display: none; margin-top: 1.5rem;" disabled>Volver a enviar email (60)</button>
            <button id="button-resend-loading" type="button" class="btn-grad" style="display: none; margin-top: 1.5rem;" disabled>
                Enviando correo <i class="fas fa-spinner fa-spin" style="margin-left: 0.3rem;"></i>
            </button>

            <div class="auth-links">
                <a id="link-recover">¿Has olvidado la contraseña?</a>
                <a id="link-login" style="display: none;">Iniciar sesión</a>
            </div>

            <p class="version-tag">VERSIÓN {{ config('app.version') }}</p>

            <div class="powered-by">
                POWERED BY
                <img src="/img/logo_contabilidad.png" alt="Portafolio ERP">
            </div>
        </div>
    </div>
</div>

<!-- Scripts de lógica (intactos) -->
<script type="module">
    async function onGet() {
        var headers = {};
        let response = await fetch("https://api.ipify.org?format=json", {
            method: "GET", mode: 'cors', headers: headers
        })
        let data = await response.json();
        localStorage.setItem("ip_geo", data ? data.ip : null);
    }
    onGet();
</script>

<!-- Lógica de scripts vanilla (sin jQuery para que funcione siempre bien) -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Manejo de mostrar/ocultar contraseña
        function setupPasswordToggle(wrapperId) {
            const wrapper = document.getElementById(wrapperId);
            if (!wrapper) return;
            const btn = wrapper.querySelector('.btn-toggle-pass');
            const input = wrapper.querySelector('input');
            const icon = btn.querySelector('i');

            btn.addEventListener('click', function(e) {
                e.preventDefault();
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            });
        }

        setupPasswordToggle('show_hide_password');
        setupPasswordToggle('show_hide_new_password');
        setupPasswordToggle('show_hide_new_password_retry');

        // Tema claro/oscuro
        var html = document.documentElement;
        function applyTheme(theme) {
            html.setAttribute('data-theme', theme);
            try { localStorage.setItem('theme', theme); } catch (e) {}
        }
        try {
            var saved = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            html.setAttribute('data-theme', saved || (prefersDark ? 'dark' : 'light'));
        } catch (e) { html.setAttribute('data-theme', 'light'); }

        var btnTheme = document.getElementById('themeToggleLogin');
        if (btnTheme) {
            btnTheme.addEventListener('click', function() {
                applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
            });
        }

        // Animación blobs
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!reduce) {
            var blobs = [
                { el: document.getElementById('blob1'), x: 60, y: -50, dur: 16000 },
                { el: document.getElementById('blob2'), x: -50, y: 40, dur: 18000 },
                { el: document.getElementById('blob3'), x: 40, y: 30, dur: 14000 }
            ];
            var start = performance.now();
            function tick(now) {
                var t = (now - start) / 1000;
                blobs.forEach(function(b) {
                    if (!b.el) return;
                    var ph = t * (2 * Math.PI / b.dur * 1000);
                    b.el.style.transform = 'translate(' + (Math.sin(ph) * b.x) + 'px,' + (Math.cos(ph * 0.8) * b.y) + 'px)';
                });
                requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@endsection