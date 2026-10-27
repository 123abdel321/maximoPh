/**
 * Máximo PH — Landing Page v2
 * Anime.js v4 (global `anime`)
 */
(function () {
  'use strict';

  const A = window.anime;
  const hasAnime = !!(A && A.animate);
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const FX = hasAnime && !reduce;
  const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

  if (FX) document.documentElement.classList.add('has-anime');

  const animate = FX ? A.animate : null;
  const createTimeline = FX ? A.createTimeline : null;
  const stagger = FX ? A.stagger : null;

  const $ = (s, c = document) => c.querySelector(s);
  const $$ = (s, c = document) => Array.from(c.querySelectorAll(s));
  const clamp = (n, a, b) => Math.min(b, Math.max(a, n));
  const fmtCOP = (n) => '$' + Math.round(n).toLocaleString('es-CO');

  /** Ejecuta cb una sola vez cuando el elemento entra en pantalla */
  function onView(el, cb, threshold) {
    if (!el) return;
    if (!('IntersectionObserver' in window)) { cb(el); return; }
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) { io.disconnect(); cb(e.target); }
      });
    }, { threshold: threshold == null ? 0.2 : threshold });
    io.observe(el);
  }

  /* =========================================
     TEMA CLARO / OSCURO
     ========================================= */
  const themeToggle = $('#themeToggle');
  const html = document.documentElement;

  function applyTheme(theme) {
    html.setAttribute('data-theme', theme);
    try { localStorage.setItem('theme', theme); } catch (e) {}
    const meta = $('meta[name="theme-color"]');
    if (meta) meta.content = theme === 'dark' ? '#0a1410' : '#059669';
  }

  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      applyTheme(html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
      themeToggle.style.transform = 'scale(0.85) rotate(180deg)';
      setTimeout(() => { themeToggle.style.transform = ''; }, 260);
    });
  }

  /* =========================================
     HEADER, PROGRESO, SCROLL TOP, MENÚ MÓVIL
     ========================================= */
  const header = $('#header');
  const scrollTopBtn = $('#scrollTopBtn');
  const progressBar = $('#progressBar');
  const mobileToggle = $('#mobileToggle');
  const nav = $('#nav');
  const navLinks = $$('.nav-link');

  function closeMenu() {
    if (!nav || !mobileToggle) return;
    nav.classList.remove('active');
    mobileToggle.classList.remove('active');
    mobileToggle.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('menu-open');
  }

  if (mobileToggle && nav) {
    mobileToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = nav.classList.toggle('active');
      mobileToggle.classList.toggle('active', isOpen);
      mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      document.body.classList.toggle('menu-open', isOpen);
    });
    document.addEventListener('click', (e) => {
      if (nav.classList.contains('active') && !nav.contains(e.target) && !mobileToggle.contains(e.target)) closeMenu();
    });
    nav.querySelectorAll('a').forEach((a) => a.addEventListener('click', closeMenu));
  }

  if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  }

  function updateActiveNav() {
    if (!navLinks.length) return;
    const sections = navLinks
      .map((l) => l.getAttribute('href'))
      .filter((h) => h && h.startsWith('#') && h.length > 1)
      .map((id) => document.querySelector(id))
      .filter(Boolean);
    if (!sections.length) return;

    const headerOffset = (header ? header.offsetHeight : 0) + 60;
    const viewportCenter = window.innerHeight / 2;
    let activeId = sections[0].id;
    let minDistance = Infinity;

    sections.forEach((sec) => {
      const rect = sec.getBoundingClientRect();
      const center = rect.top + rect.height / 2 + headerOffset * 0.4;
      const dist = Math.abs(center - viewportCenter);
      const isVisible = rect.bottom > headerOffset && rect.top < window.innerHeight;
      if (isVisible && dist < minDistance) { minDistance = dist; activeId = sec.id; }
    });

    if (window.scrollY < 100) activeId = sections[0].id;
    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 80) activeId = sections[sections.length - 1].id;

    navLinks.forEach((link) => link.classList.toggle('active', link.getAttribute('href') === '#' + activeId));
  }

  /* Migración: línea de progreso ligada al scroll */
  const migSteps = $('#migSteps');
  const migFill = $('#migFill');
  const migItems = migSteps ? $$('.mig-step', migSteps) : [];

  function updateMigration() {
    if (!migSteps || !migFill) return;
    const rect = migSteps.getBoundingClientRect();
    const vh = window.innerHeight;
    const progress = clamp((vh * 0.65 - rect.top) / rect.height, 0, 1);
    migFill.style.transform = 'scaleY(' + progress + ')';
    migItems.forEach((item) => {
      const r = item.getBoundingClientRect();
      item.classList.toggle('is-done', r.top + 20 < vh * 0.65);
    });
  }

  function handleScroll() {
    const y = window.scrollY;
    if (header) header.classList.toggle('scrolled', y > 30);
    if (scrollTopBtn) scrollTopBtn.classList.toggle('visible', y > 400);
    if (progressBar) {
      const max = document.documentElement.scrollHeight - window.innerHeight;
      progressBar.style.transform = 'scaleX(' + (max > 0 ? clamp(y / max, 0, 1) : 0) + ')';
    }
    updateActiveNav();
    updateMigration();
  }

  /* Scroll suave con offset del header */
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener('click', function (e) {
      const href = this.getAttribute('href');
      if (href === '#' || href.length < 2) return;
      const target = document.querySelector(href);
      if (!target) return;
      e.preventDefault();
      const offset = (header ? header.offsetHeight : 0) + 16;
      const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
      window.scrollTo({ top, behavior: 'smooth' });
      closeMenu();
    });
  });

  let ticking = false;
  window.addEventListener('scroll', () => {
    if (!ticking) {
      window.requestAnimationFrame(() => { handleScroll(); ticking = false; });
      ticking = true;
    }
  }, { passive: true });

  window.addEventListener('resize', () => {
    if (window.innerWidth > 1023) closeMenu();
    handleScroll();
  });

  /* =========================================
     REVEAL + TEXTO PALABRA POR PALABRA
     ========================================= */
  function initReveal() {
    const els = $$('[data-reveal]');
    if (!FX || !els.length) return;

    const variants = {
      up: { translateY: [44, 0] },
      left: { translateX: [-48, 0] },
      right: { translateX: [48, 0] },
      scale: { scale: [0.9, 1], translateY: [24, 0] },
      fade: {}
    };

    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (!e.isIntersecting) return;
        io.unobserve(e.target);
        const el = e.target;
        const v = variants[el.dataset.reveal] || variants.up;
        animate(el, Object.assign({
          opacity: [0, 1],
          duration: 1000,
          delay: parseInt(el.dataset.delay || '0', 10),
          ease: 'outExpo'
        }, v));
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });

    els.forEach((el) => io.observe(el));
  }

  function splitWords(el) {
    const walk = (node) => {
      Array.from(node.childNodes).forEach((ch) => {
        if (ch.nodeType === 3) {
          const frag = document.createDocumentFragment();
          ch.textContent.split(/(\s+)/).forEach((p) => {
            if (!p) return;
            if (/^\s+$/.test(p)) {
              frag.appendChild(document.createTextNode(' '));
            } else {
              const w = document.createElement('span');
              w.className = 'w';
              const i = document.createElement('span');
              i.className = 'wi';
              i.textContent = p;
              w.appendChild(i);
              frag.appendChild(w);
            }
          });
          ch.replaceWith(frag);
        } else if (ch.nodeType === 1) {
          walk(ch);
        }
      });
    };
    walk(el);
    el.classList.add('is-split');
    return $$('.wi', el);
  }

  function initSplitText() {
    if (!FX) return;
    $$('[data-split]').forEach((el) => {
      const words = splitWords(el);
      onView(el, () => {
        animate(words, {
          translateY: ['110%', '0%'],
          duration: 1000,
          delay: stagger(60),
          ease: 'outExpo'
        });
      }, 0.3);
    });
  }

  /* =========================================
     HERO
     ========================================= */
  function initHeroBlobs() {
    if (!FX) return;
    $$('.blob').forEach((b, i) => {
      animate(b, {
        translateX: [0, 70 * (i % 2 ? -1 : 1), -40 * (i % 2 ? -1 : 1), 0],
        translateY: [0, -50, 40, 0],
        scale: [1, 1.12, 0.94, 1],
        duration: 14000 + i * 3500,
        loop: true,
        ease: 'inOutSine'
      });
    });
    $$('.cta-orb').forEach((b, i) => {
      animate(b, {
        translateX: [0, 60, -30, 0],
        translateY: [0, 40, -40, 0],
        duration: 16000 + i * 3000,
        loop: true,
        ease: 'inOutSine'
      });
    });
  }

  function initHeroDashboard() {
    const dash = $('#dash');
    const wrap = $('#dashWrap');
    const chart = $('#dashChart');
    const feed = $('#feed');
    const kTotal = $('#kpiTotal');
    const kCount = $('#kpiCount');
    const kAsi = $('#kpiAsientos');
    if (!dash || !wrap) return;

    // Barras del gráfico
    const heights = [38, 52, 44, 66, 58, 74, 62, 80, 70, 88, 76, 94, 84, 100];
    if (chart) {
      heights.forEach((h) => {
        const s = document.createElement('span');
        s.style.height = h + '%';
        chart.appendChild(s);
      });
    }
    const bars = chart ? $$('span', chart) : [];

    if (!FX) return;

    // Estado
    const state = { total: 12480000, shown: 0, count: 38, shownCount: 0 };
    const pool = [
      ['502', 'Apto 502 · Torre 2', 'Cuota de administración', 450000],
      ['17', 'Casa 17', 'Cuota de administración', 380000],
      ['1204', 'Apto 1204 · Torre 1', 'Cuota extraordinaria', 520000],
      ['301', 'Apto 301 · Torre 3', 'Cuota de administración', 450000],
      ['L3', 'Local 3', 'Cuota de administración', 910000],
      ['808', 'Apto 808 · Torre 2', 'Pago con anticipo', 470000]
    ];
    let idx = 0;

    function setKpis() {
      if (kTotal) kTotal.textContent = fmtCOP(state.shown);
      if (kCount) kCount.textContent = Math.round(state.shownCount);
      if (kAsi) kAsi.textContent = Math.round(state.shownCount);
    }

    // Intro de la tarjeta: KPIs + barras
    onView(dash, () => {
      state.shown = 0;
      state.shownCount = 0;
      animate(state, {
        shown: state.total,
        shownCount: state.count,
        duration: 2200,
        delay: 500,
        ease: 'outExpo',
        onUpdate: setKpis
      });
      if (bars.length) {
        animate(bars, {
          scaleY: [0, 1],
          duration: 1000,
          delay: stagger(55, { start: 500 }),
          ease: 'outBack'
        });
      }
      // Feed inicial en cascada
      animate($$('.feed-item', feed), {
        opacity: [0, 1],
        translateX: [30, 0],
        duration: 800,
        delay: stagger(140, { start: 900 }),
        ease: 'outExpo'
      });
      // Chips flotantes
      $$('.float-chip').forEach((c, i) => {
        animate(c, { opacity: [0, 1], scale: [0.7, 1], duration: 900, delay: 1400 + i * 250, ease: 'outBack' });
        animate(c, { translateY: [0, -10, 0], duration: 4200, loop: true, delay: 2400 + i * 700, ease: 'inOutSine' });
      });
      // Pagos en vivo
      setTimeout(startFeed, 3200);
    }, 0.25);

    function makeItem(p) {
      const li = document.createElement('li');
      li.className = 'feed-item';
      li.innerHTML =
        '<span class="feed-avatar">' + p[0] + '</span>' +
        '<div class="feed-info"><strong>' + p[1] + '</strong><span>' + p[2] + '</span></div>' +
        '<div class="feed-right"><b>' + fmtCOP(p[3]) + '</b>' +
        '<span class="tag tag-bank">AV Villas</span><span class="tag tag-ok">✓ Contabilizado</span></div>';
      return li;
    }

    function pushPayment() {
      if (document.hidden) return;
      const p = pool[idx++ % pool.length];
      const li = makeItem(p);
      feed.prepend(li);
      animate(li, {
        opacity: [0, 1],
        maxHeight: [0, 76],
        translateX: [-30, 0],
        duration: 800,
        ease: 'outExpo'
      });
      while (feed.children.length > 4) feed.lastElementChild.remove();

      const fromTotal = state.shown;
      const fromCount = state.shownCount;
      state.total += p[3];
      state.count += 1;
      animate(state, {
        shown: state.total,
        shownCount: state.count,
        duration: 900,
        ease: 'outExpo',
        onUpdate: setKpis
      });
      void fromTotal; void fromCount;

      // El gráfico "respira" con cada pago
      if (bars.length) {
        const last = bars[bars.length - 1];
        animate(last, { scaleY: [1, 0.82, 1], duration: 700, ease: 'outElastic(1, .6)' });
        animate(bars, { opacity: [{ to: 1, duration: 150 }, { to: 0.85, duration: 500 }], delay: stagger(25), ease: 'linear' });
      }
      // Pulso en chip del ERP
      const chip = $('.chip-erp');
      if (chip) animate(chip, { scale: [1, 1.08, 1], duration: 600, ease: 'outBack' });
    }

    let feedStarted = false;
    function startFeed() {
      if (feedStarted) return;
      feedStarted = true;
      setInterval(pushPayment, 3400);
    }

    // Tilt 3D
    if (finePointer) {
      wrap.addEventListener('mousemove', (e) => {
        const r = wrap.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width - 0.5;
        const y = (e.clientY - r.top) / r.height - 0.5;
        dash.style.transform = 'rotateY(' + (x * 10) + 'deg) rotateX(' + (-y * 8) + 'deg)';
      });
      wrap.addEventListener('mouseleave', () => { dash.style.transform = ''; });
    }
  }

  /* =========================================
     PAGOS: PIPELINE
     ========================================= */
  let pipeTl = null;
  const pipeMQ = window.matchMedia('(min-width: 900px)');

  function buildPipeline() {
    const pipe = $('#pipe');
    if (!pipe || !FX) return;
    const nodes = $$('.pipe-node', pipe);
    const links = $$('.pipe-link', pipe);
    const horiz = pipeMQ.matches;
    const prop = horiz ? 'translateX' : 'translateY';

    if (pipeTl && typeof pipeTl.revert === 'function') { try { pipeTl.revert(); } catch (e) {} }
    pipeTl = null;

    function setOn(k) {
      nodes.forEach((n, j) => n.classList.toggle('is-on', j <= k));
    }

    pipeTl = createTimeline({ loop: true, loopDelay: 1400, defaults: { ease: 'inOut(2)' } });
    pipeTl.call(() => setOn(0), 0);
    links.forEach((l, i) => {
      const dot = $('.pipe-dot', l);
      const dist = horiz ? l.offsetWidth : l.offsetHeight;
      const params = {
        opacity: [{ to: 1, duration: 120 }, { to: 1, duration: 560 }, { to: 0, duration: 120 }],
        duration: 800
      };
      params[prop] = [0, dist];
      pipeTl.add(dot, params);
      pipeTl.call(() => setOn(i + 1));
    });
  }

  function initPipeline() {
    const pipe = $('#pipe');
    if (!pipe) return;
    if (!FX) { $$('.pipe-node', pipe).forEach((n) => n.classList.add('is-on')); return; }

    onView(pipe, () => {
      const horiz = pipeMQ.matches;
      const fills = $$('.pipe-fill', pipe);
      const fillParams = { duration: 700, delay: stagger(240, { start: 500 }), ease: 'inOut(2)' };
      if (horiz) fillParams.scaleX = [0, 1]; else fillParams.scaleY = [0, 1];
      animate(fills, fillParams);
      setTimeout(buildPipeline, 2200);
    }, 0.3);

    let lastMode = pipeMQ.matches;
    window.addEventListener('resize', () => {
      if (pipeMQ.matches !== lastMode) {
        lastMode = pipeMQ.matches;
        $$('.pipe-fill', pipe).forEach((f) => { f.style.transform = lastMode ? 'scaleX(1)' : 'scaleY(1)'; });
        if (pipeTl) buildPipeline();
      }
    });
  }

  /* =========================================
     LEDGER
     ========================================= */
  function initLedger() {
    const panel = $('#ledgerPanel');
    if (!panel || !FX) return;
    const rows = $$('.ledger-anim-row', panel);
    const total = $('#ledgerTotal');
    onView(panel, () => {
      animate(rows, {
        opacity: [0, 1],
        translateX: [-24, 0],
        duration: 700,
        delay: stagger(260, { start: 500 }),
        ease: 'outExpo'
      });
      if (total) {
        const o = { v: 0 };
        animate(o, {
          v: 450000, duration: 1600, delay: 900, ease: 'outExpo',
          onUpdate: () => { total.textContent = fmtCOP(o.v); }
        });
      }
    }, 0.35);
  }

  /* =========================================
     BENTO: MINI-ANIMACIONES EN LOOP
     ========================================= */
  function initBento() {
    if (!FX) return;

    // Importador
    const imp = $('#impVis');
    if (imp) {
      const bar = $('.imp-bar i', imp);
      const rows = $$('.imp-rows li', imp);
      const cnt = $('#impCount');
      const o = { v: 0 };
      onView(imp, () => {
        const tl = createTimeline({ loop: true, loopDelay: 1800, defaults: { ease: 'inOut(2)' } });
        tl.add(bar, { scaleX: [0, 1], duration: 2000 }, 0)
          .add(rows, { opacity: [0, 1], translateX: [-18, 0], duration: 450, delay: stagger(300) }, 0)
          .add(o, { v: [0, 248], duration: 2000, onUpdate: () => { if (cnt) cnt.textContent = Math.round(o.v); } }, 0);
      }, 0.3);
    }

    // Gastos
    const gx = $('#gxVis');
    if (gx) {
      const rows = $$('.gx li', gx);
      const pills = $$('.pill', gx);
      onView(gx, () => {
        const tl = createTimeline({ loop: true, loopDelay: 1800 });
        tl.add(rows, { opacity: [0, 1], translateY: [16, 0], duration: 550, delay: stagger(380), ease: 'outExpo' }, 0)
          .add(pills, { scale: [0, 1], duration: 500, delay: stagger(380, { start: 650 }), ease: 'outBack' }, 0);
      }, 0.3);
    }

    // Envío por correo y WhatsApp
    const snd = $('#sndVis');
    if (snd) {
      const bars = $$('.snd-bar i', snd);
      const nums = $$('.snd-n', snd);
      const o1 = { v: 0 };
      const o2 = { v: 0 };
      onView(snd, () => {
        const tl = createTimeline({ loop: true, loopDelay: 1800, defaults: { ease: 'inOut(2)' } });
        tl.add(bars[0], { scaleX: [0, 1], duration: 2000 }, 0)
          .add(bars[1], { scaleX: [0, 1], duration: 2000 }, 300)
          .add(o1, { v: [0, 500], duration: 2000, onUpdate: () => { if (nums[0]) nums[0].textContent = Math.round(o1.v); } }, 0)
          .add(o2, { v: [0, 500], duration: 2000, onUpdate: () => { if (nums[1]) nums[1].textContent = Math.round(o2.v); } }, 300);
      }, 0.3);
    }

    // Portería / PQRS / Censo
    const pq = $('#pqVis');
    if (pq) {
      const items = $$('li', pq);
      let cur = 0;
      onView(pq, () => {
        setInterval(() => {
          items[cur].classList.remove('is-hot');
          cur = (cur + 1) % items.length;
          items[cur].classList.add('is-hot');
        }, 1700);
      }, 0.3);
    }
  }

  /* =========================================
     INTEGRACIÓN: RAYO CON PULSOS
     ========================================= */
  function initBeam() {
    const vis = $('#integrationVisual');
    if (!vis || !FX) return;
    const beam = $('.beam', vis);
    const fill = $('.beam-fill', vis);
    const dots = $$('.beam-dot', vis);
    if (!beam || !fill) return;

    onView(vis, () => {
      const horiz = window.matchMedia('(min-width: 1024px)').matches;
      const dist = horiz ? beam.offsetWidth : beam.offsetHeight;
      const prop = horiz ? 'translateX' : 'translateY';
      const fp = { duration: 1300, ease: 'inOut(2)', delay: 300 };
      if (horiz) fp.scaleX = [0, 1]; else fp.scaleY = [0, 1];
      animate(fill, fp);

      const dp = {
        opacity: [{ to: 1, duration: 200 }, { to: 1, duration: 1400 }, { to: 0, duration: 200 }],
        duration: 1800,
        delay: stagger(600, { start: 1500 }),
        loop: true,
        ease: 'inOut(2)'
      };
      dp[prop] = [0, dist];
      animate(dots, dp);
    }, 0.4);
  }

  /* =========================================
     ROLES (tabs)
     ========================================= */
  function initRolesTabs() {
    const tabs = $$('.role-tab');
    const panels = $$('.role-panel');
    if (!tabs.length || !panels.length) return;

    tabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        const role = tab.dataset.role;
        tabs.forEach((t) => { t.classList.remove('active'); t.setAttribute('aria-selected', 'false'); });
        tab.classList.add('active');
        tab.setAttribute('aria-selected', 'true');

        panels.forEach((p) => {
          if (p.dataset.panel === role) {
            p.classList.add('active');
            if (FX) {
              animate(p, { opacity: [0, 1], translateY: [14, 0], duration: 500, ease: 'outExpo' });
              animate($$('.role-features li', p), { opacity: [0, 1], translateX: [-14, 0], duration: 500, delay: stagger(70, { start: 120 }), ease: 'outExpo' });
            }
          } else {
            p.classList.remove('active');
          }
        });
      });
    });
  }

  /* =========================================
     STATS: CONTADORES
     ========================================= */
  function initStatsCounters() {
    const stats = $$('.stat-number[data-count]');
    if (!stats.length) return;
    stats.forEach((el) => {
      const target = parseInt(el.dataset.count, 10) || 0;
      if (!FX) { el.textContent = target.toLocaleString('es-CO'); return; }
      el.textContent = '0';
      onView(el, () => {
        const o = { v: 0 };
        animate(o, {
          v: target, duration: 2000, ease: 'outQuart',
          onUpdate: () => { el.textContent = Math.round(o.v).toLocaleString('es-CO'); }
        });
      }, 0.5);
    });
  }

  /* =========================================
     INTERACCIONES: spotlight + botones magnéticos
     ========================================= */
  function initInteractions() {
    $$('.spot').forEach((c) => {
      c.addEventListener('pointermove', (e) => {
        const r = c.getBoundingClientRect();
        c.style.setProperty('--mx', (e.clientX - r.left) + 'px');
        c.style.setProperty('--my', (e.clientY - r.top) + 'px');
      });
    });

    if (!finePointer || reduce) return;
    $$('.magnetic').forEach((b) => {
      b.addEventListener('pointermove', (e) => {
        const r = b.getBoundingClientRect();
        const x = e.clientX - (r.left + r.width / 2);
        const y = e.clientY - (r.top + r.height / 2);
        b.style.translate = (x * 0.22) + 'px ' + (y * 0.35) + 'px';
      });
      b.addEventListener('pointerleave', () => { b.style.translate = ''; });
    });
  }

  /* =========================================
     INIT
     ========================================= */
  let booted = false;
  function boot() {
    if (booted) return;
    booted = true;
    handleScroll();
    initReveal();
    initSplitText();
    initHeroBlobs();
    initHeroDashboard();
    initPipeline();
    initLedger();
    initBento();
    initBeam();
    initRolesTabs();
    initStatsCounters();
    initInteractions();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();