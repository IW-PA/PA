<?php
require_once __DIR__ . '/../src/config/config.php';

$script = $_SERVER['SCRIPT_NAME'] ?? '';
$publicPos = strpos($script, '/public');
$basePath = $publicPos !== false ? substr($script, 0, $publicPos) : '';
$baseHref = $basePath . '/public/';
?>
<!DOCTYPE html>
<html lang="fr" data-theme="">
<script>
(function(){
    var t = localStorage.getItem('budgie-theme');
    if (t === 'dark') document.documentElement.setAttribute('data-theme','dark');
})();
</script>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budgie — Ton partenaire financier personnel</title>
    <base href="<?php echo htmlspecialchars($baseHref, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --wine:   #8d2b5c;
    --pink:   #f252a1;
    --sky:    #BACCD8;
    --cream:  #FAF8F5;
    --dark:   #1a0d14;
    --muted:  #6b5060;
    --border: #e8dde3;
    --white:  #ffffff;
    --green:  #10b981;
    --font:   'Outfit', system-ui, sans-serif;
    --radius: 16px;
    --shadow: 0 4px 24px rgba(141,43,92,.10);
    --shadow-lg: 0 16px 48px rgba(141,43,92,.16);
}

html { scroll-behavior: smooth; }
body { font-family: var(--font); background: var(--cream); color: var(--dark); line-height: 1.6; overflow-x: hidden; }
a { text-decoration: none; color: inherit; }

.container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }

/* SVG icon wrapper */
.icon {
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.icon svg { display: block; }

.btn {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .75rem 1.75rem; border-radius: 50px; font-weight: 700;
    font-size: .95rem; font-family: var(--font); cursor: pointer;
    border: 2px solid transparent; transition: all .22s ease;
}
.btn-primary {
    background: linear-gradient(135deg, var(--wine), var(--pink));
    color: #fff; border-color: transparent;
    box-shadow: 0 4px 18px rgba(242,82,161,.35);
}
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(242,82,161,.5); }
.btn-outline {
    background: transparent; color: var(--wine);
    border-color: var(--wine);
}
.btn-outline:hover { background: var(--wine); color: #fff; }
.btn-lg { padding: .9rem 2.2rem; font-size: 1rem; }

.badge-pill {
    display: inline-flex; align-items: center; gap: .5rem;
    background: rgba(141,43,92,.07); color: var(--wine);
    border: 1px solid rgba(141,43,92,.18);
    padding: .35rem 1rem; border-radius: 50px;
    font-size: .82rem; font-weight: 700; letter-spacing: .02em;
}

/* ── Navbar ── */
.lp-nav {
    position: sticky; top: 0; z-index: 100;
    background: rgba(250,248,245,.9); backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--border);
}
.lp-nav-inner {
    display: flex; align-items: center; justify-content: space-between;
    padding: 1rem 2rem; max-width: 1200px; margin: 0 auto;
}
.lp-logo {
    display: flex; align-items: center; gap: .55rem;
    font-size: 1.4rem; font-weight: 900; color: var(--wine);
    letter-spacing: -.02em;
}
.lp-logo .logo-bird { font-size: 1.5rem; line-height: 1; }
.lp-links { display: flex; gap: 2rem; }
.lp-links a { font-weight: 600; font-size: .9rem; color: var(--muted); transition: color .2s; }
.lp-links a:hover { color: var(--wine); }
.lp-ctas { display: flex; gap: .75rem; align-items: center; }
.link-btn { font-weight: 600; color: var(--muted); font-size: .9rem; padding: .5rem .75rem; transition: color .2s; }
.link-btn:hover { color: var(--wine); }

/* ── Hero ── */
.hero {
    padding: 5.5rem 2rem 5rem;
    background: linear-gradient(140deg, #FAF8F5 0%, #f4ecf5 50%, #e8d8ee 100%);
    position: relative; overflow: hidden;
}
.hero::before {
    content: ''; position: absolute; top: -180px; right: -180px;
    width: 580px; height: 580px; border-radius: 50%;
    background: radial-gradient(circle, rgba(242,82,161,.12) 0%, transparent 70%);
    pointer-events: none;
}
.hero-grid {
    max-width: 1200px; margin: 0 auto;
    display: grid; grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center;
}
.hero-eyebrow { margin-bottom: 1.5rem; }
.hero-h1 {
    font-size: clamp(2.4rem, 4vw, 3.5rem);
    font-weight: 900; line-height: 1.1;
    letter-spacing: -.03em; color: var(--dark);
    margin-bottom: 1rem;
}
.hero-h1 .accent { color: var(--wine); }
.hero-desc { font-size: 1.1rem; color: var(--muted); line-height: 1.7; max-width: 500px; margin-bottom: 2rem; }
.hero-btns { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem; }
.hero-proof { display: flex; gap: 1.5rem; }
.hero-proof-item { display: flex; align-items: center; gap: .4rem; font-size: .85rem; font-weight: 600; color: var(--muted); }
.proof-check { color: var(--green); }

/* Mockup */
.mockup-wrap { position: relative; }
.mockup-card {
    background: var(--white); border-radius: 20px;
    box-shadow: var(--shadow-lg); border: 1px solid var(--border);
    overflow: hidden; transition: transform .35s ease;
}
.mockup-card:hover { transform: translateY(-6px) rotate(.4deg); }
.mockup-bar {
    background: var(--cream); border-bottom: 1px solid var(--border);
    padding: .85rem 1.25rem; display: flex; align-items: center; gap: .75rem;
}
.m-dots { display: flex; gap: 5px; }
.m-dot { width: 10px; height: 10px; border-radius: 50%; }
.m-dot.r { background: #ef4444; } .m-dot.y { background: #f59e0b; } .m-dot.g { background: #10b981; }
.m-title { font-size: .875rem; font-weight: 700; color: var(--dark); }
.m-tag { margin-left: auto; background: rgba(16,185,129,.12); color: #059669; font-size: .72rem; font-weight: 700; padding: .2rem .65rem; border-radius: 50px; }
.mockup-body { padding: 1.5rem; }
.m-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem; }
.m-stat { background: var(--cream); border: 1px solid var(--border); border-radius: 12px; padding: 1rem; text-align: center; }
.m-stat-label { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); margin-bottom: .3rem; display: block; }
.m-stat-val { font-size: 1.45rem; font-weight: 800; color: var(--wine); display: block; }
.m-stat-val.green { color: var(--green); }
.m-accounts { display: flex; flex-direction: column; gap: .6rem; }
.m-account { display: flex; justify-content: space-between; align-items: center; padding: .75rem 1rem; background: var(--cream); border: 1px solid var(--border); border-radius: 12px; }
.m-account-name { font-size: .85rem; font-weight: 700; color: var(--dark); }
.m-account-sub { font-size: .72rem; color: var(--muted); margin-top: 1px; }
.m-account-val { font-size: .9rem; font-weight: 800; color: var(--dark); }
.m-account-val.green { color: var(--green); }
.m-account-val.pink { color: var(--wine); }

/* Floating badge */
.mockup-float {
    position: absolute; bottom: -18px; left: -22px;
    background: var(--white); border: 1px solid var(--border);
    border-radius: 14px; padding: .85rem 1.1rem;
    box-shadow: var(--shadow); display: flex; align-items: center; gap: .65rem;
    animation: floatUp 3s ease-in-out infinite;
}
@keyframes floatUp { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
.float-icon-wrap {
    width: 36px; height: 36px; border-radius: 10px;
    background: rgba(141,43,92,.1); display: flex; align-items: center; justify-content: center;
}
.float-label { font-size: .75rem; font-weight: 700; color: var(--dark); }
.float-sub { font-size: .68rem; color: var(--muted); }

/* ── Section ── */
.section { padding: 5.5rem 2rem; }
.section-alt { background: var(--white); }
.section-head { text-align: center; max-width: 640px; margin: 0 auto 3.5rem; }
.section-head h2 { font-size: clamp(1.8rem, 3vw, 2.6rem); font-weight: 800; letter-spacing: -.025em; color: var(--dark); margin-bottom: .75rem; }
.section-head p { font-size: 1.05rem; color: var(--muted); line-height: 1.65; }

/* ── Features ── */
.features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; max-width: 1200px; margin: 0 auto; }
.feat-card {
    background: var(--cream); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 2rem;
    transition: transform .22s, box-shadow .22s, border-color .22s;
}
.feat-card:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); border-color: rgba(242,82,161,.3); }
.feat-icon-wrap {
    width: 48px; height: 48px; border-radius: 12px;
    background: rgba(141,43,92,.08);
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 1.1rem;
}
.feat-card h3 { font-size: 1.1rem; font-weight: 700; color: var(--dark); margin-bottom: .6rem; }
.feat-card p { font-size: .92rem; color: var(--muted); line-height: 1.6; }

/* ── Security ── */
.security-banner {
    max-width: 1200px; margin: 0 auto;
    background: linear-gradient(135deg, var(--dark) 0%, var(--wine) 100%);
    border-radius: 24px; padding: 4rem 3.5rem;
    display: grid; grid-template-columns: 1fr auto; gap: 3rem; align-items: center;
    box-shadow: var(--shadow-lg);
}
.security-banner h2 { font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: .75rem; }
.security-banner > div > p { color: rgba(255,255,255,.72); line-height: 1.65; margin-bottom: 1.75rem; }
.security-list { display: grid; grid-template-columns: 1fr 1fr; gap: .7rem 2rem; list-style: none; }
.security-list li { font-size: .9rem; font-weight: 600; color: rgba(255,255,255,.85); display: flex; align-items: center; gap: .5rem; }
.sec-check { color: rgba(255,255,255,.5); }
.security-badge {
    background: rgba(255,255,255,.1); backdrop-filter: blur(8px);
    border: 1px solid rgba(255,255,255,.18); border-radius: 20px;
    padding: 2.5rem 2rem; text-align: center; min-width: 200px;
}
.security-badge .s-icon { display: flex; justify-content: center; margin-bottom: .85rem; }
.security-badge strong { display: block; color: #fff; font-size: 1.15rem; font-weight: 800; margin-bottom: .25rem; }
.security-badge small { color: rgba(255,255,255,.55); font-size: .82rem; }

/* ── Pricing ── */
.pricing-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; max-width: 860px; margin: 0 auto; }
.price-card {
    background: var(--white); border: 1px solid var(--border);
    border-radius: 24px; padding: 2.75rem 2.25rem;
    display: flex; flex-direction: column; position: relative;
    transition: transform .22s, box-shadow .22s; box-shadow: var(--shadow);
}
.price-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); }
.price-card.featured { border: 2px solid var(--pink); box-shadow: 0 16px 40px rgba(242,82,161,.18); }
.price-ribbon {
    position: absolute; top: -13px; left: 50%; transform: translateX(-50%);
    background: linear-gradient(135deg, var(--wine), var(--pink));
    color: #fff; font-size: .72rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .06em; padding: .3rem 1.1rem; border-radius: 50px;
    box-shadow: 0 4px 12px rgba(242,82,161,.4);
}
.price-card h3 { font-size: 1.4rem; font-weight: 800; color: var(--dark); margin-bottom: .5rem; }
.price-amount { font-size: 3rem; font-weight: 900; color: var(--wine); line-height: 1; margin: .75rem 0; }
.price-amount span { font-size: 1rem; font-weight: 500; color: var(--muted); }
.price-card > p { color: var(--muted); font-size: .9rem; margin-bottom: 1.75rem; }
.price-features { list-style: none; flex: 1; margin-bottom: 2rem; }
.price-features li { padding: .7rem 0; border-bottom: 1px solid var(--border); font-size: .92rem; color: var(--muted); display: flex; align-items: center; gap: .5rem; }
.price-check { color: var(--green); flex-shrink: 0; }
.btn-block { width: 100%; justify-content: center; }

/* ── Footer ── */
.lp-footer { background: var(--dark); color: rgba(255,255,255,.6); padding: 4.5rem 2rem 2rem; }
.footer-inner { max-width: 1200px; margin: 0 auto; }
.footer-grid { display: grid; grid-template-columns: 1.2fr 1fr 1fr 1fr; gap: 3rem; margin-bottom: 3rem; }
.footer-brand .brand-logo-wrap { display: flex; align-items: center; gap: .55rem; margin-bottom: 1rem; }
.footer-brand .brand-logo-wrap .logo-bird { font-size: 1.4rem; }
.footer-brand .brand-logo-wrap strong { font-size: 1.3rem; font-weight: 900; color: #fff; letter-spacing: -.02em; }
.footer-brand p { font-size: .9rem; line-height: 1.65; }
.footer-col h4 { font-size: .78rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.35); margin-bottom: 1.1rem; }
.footer-col a { display: block; font-size: .9rem; color: rgba(255,255,255,.6); margin-bottom: .6rem; transition: color .2s; }
.footer-col a:hover { color: #fff; }
.footer-bottom { border-top: 1px solid rgba(255,255,255,.08); padding-top: 1.75rem; font-size: .85rem; text-align: center; }

/* ── Responsive ── */
@media (max-width: 1024px) {
    .features-grid { grid-template-columns: repeat(2, 1fr); }
    .security-banner { grid-template-columns: 1fr; }
    .footer-grid { grid-template-columns: 1fr 1fr; gap: 2rem; }
}
@media (max-width: 768px) {
    .hero-grid { grid-template-columns: 1fr; }
    .hero-grid .mockup-wrap { display: none; }
    .features-grid { grid-template-columns: 1fr; }
    .pricing-grid { grid-template-columns: 1fr; }
    .lp-links { display: none; }
    .security-banner { padding: 2.5rem 1.75rem; }
    .footer-grid { grid-template-columns: 1fr; }
}

/* ── Dark Mode ── */
[data-theme="dark"] {
    --wine:   #f252a1;
    --pink:   #ff79c6;
    --cream:  #1a1a2e;
    --dark:   #e2e8ff;
    --muted:  #9aa5ce;
    --border: #30344e;
    --white:  #16213e;
    --green:  #50fa7b;
}
[data-theme="dark"] body { background: #1a1a2e; color: #e2e8ff; }
[data-theme="dark"] .lp-nav { background: rgba(22,33,62,.96); border-bottom-color: #30344e; }
[data-theme="dark"] .lp-links a { color: #9aa5ce; }
[data-theme="dark"] .lp-links a:hover { color: #f252a1; }
[data-theme="dark"] .link-btn { color: #9aa5ce; }
[data-theme="dark"] .link-btn:hover { color: #f252a1; }
[data-theme="dark"] .hero { background: linear-gradient(135deg, #1a1a2e 0%, #1e213d 50%, #2a1040 100%); border-bottom-color: #30344e; }
[data-theme="dark"] .badge-pill { background: rgba(242,82,161,.12); color: #f252a1; border-color: rgba(242,82,161,.25); }
[data-theme="dark"] .hero-desc { color: #9aa5ce; }
[data-theme="dark"] .hero-proof-item { color: #9aa5ce; }
[data-theme="dark"] .mockup-card { background: #1e2445; border-color: #30344e; }
[data-theme="dark"] .mockup-bar { background: #252845; border-bottom-color: #30344e; }
[data-theme="dark"] .m-title { color: #e2e8ff; }
[data-theme="dark"] .m-stat { background: #252845; border-color: #30344e; }
[data-theme="dark"] .m-account { background: #252845; border-color: #30344e; }
[data-theme="dark"] .m-account-name { color: #e2e8ff; }
[data-theme="dark"] .m-account-val { color: #e2e8ff; }
[data-theme="dark"] .mockup-float { background: #1e2445; border-color: #30344e; }
[data-theme="dark"] .float-label { color: #e2e8ff; }
[data-theme="dark"] .float-sub { color: #9aa5ce; }
[data-theme="dark"] .section-alt { background: #16213e; }
[data-theme="dark"] .section-head h2 { color: #e2e8ff; }
[data-theme="dark"] .section-head p { color: #9aa5ce; }
[data-theme="dark"] .feat-card { background: #252845; border-color: #30344e; }
[data-theme="dark"] .feat-card h3 { color: #e2e8ff; }
[data-theme="dark"] .feat-card p { color: #9aa5ce; }
[data-theme="dark"] .pricing-section { background: #1a1a2e; border-top-color: #30344e; }
[data-theme="dark"] .price-card { background: #16213e; border-color: #30344e; }
[data-theme="dark"] .price-card-header h3 { color: #e2e8ff; }
[data-theme="dark"] .price-card-header p { color: #9aa5ce; }
[data-theme="dark"] .price-features li { border-bottom-color: #30344e; color: #9aa5ce; }
[data-theme="dark"] .public-footer, [data-theme="dark"] footer { background: #0d1020; }
[data-theme="dark"] .footer-brand p { color: #7b8ab4; }
[data-theme="dark"] .footer-col h4 { color: #e2e8ff; }
[data-theme="dark"] .footer-col a { color: #7b8ab4; }
[data-theme="dark"] .footer-col a:hover { color: #fff; }
[data-theme="dark"] .footer-bottom { color: #7b8ab4; }

/* Dark mode toggle button (landing) */
.lp-dark-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    padding: 0.45rem 0.85rem;
    border-radius: 50px;
    border: 1px solid rgba(141,43,92,.25);
    background: var(--white);
    color: var(--dark);
    font-size: 0.85rem;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s ease;
}
.lp-dark-toggle:hover {
    background: rgba(141,43,92,.08);
    color: var(--wine);
    border-color: var(--wine);
}
[data-theme="dark"] .lp-dark-toggle {
    background: #252845;
    border-color: #30344e;
    color: #e2e8ff;
}
[data-theme="dark"] .lp-dark-toggle:hover {
    background: #30344e;
    color: #f252a1;
}
</style>
</head>
<body>

<!-- ═══ NAVBAR ═══ -->
<nav class="lp-nav">
    <div class="lp-nav-inner">
        <a href="index.php" class="lp-logo">
            <span class="logo-bird">🐦</span>
            Budgie
        </a>
        <div class="lp-links">
            <a href="#features">Fonctionnalités</a>
            <a href="#previsions">Prévisions</a>
            <a href="#security">Sécurité</a>
            <a href="#pricing">Tarifs</a>
        </div>
        <div class="lp-ctas">
            <button id="lpDarkToggle" class="lp-dark-toggle" aria-label="Basculer mode sombre" title="Mode sombre">
                <svg id="lpDmIcon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                <span id="lpDmText">Sombre</span>
            </button>
            <?php if (isLoggedIn()): ?>
                <a href="index.php" class="btn btn-primary">Mon Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="link-btn">Se connecter</a>
                <a href="signup.php" class="btn btn-primary">Créer un compte</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- ═══ HERO ═══ -->
<section class="hero">
    <div class="hero-grid">
        <div class="hero-text">
            <div class="hero-eyebrow">
                <span class="badge-pill">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Sans connexion bancaire
                </span>
            </div>
            <h1 class="hero-h1">
                Pilotez vos finances.<br>
                <span class="accent">Protégez votre vie privée.</span>
            </h1>
            <p class="hero-desc">
                Budgie vous accompagne au quotidien dans le suivi et la simulation de vos finances —
                sans jamais exiger un accès à votre banque. Vos données restent les vôtres.
            </p>
            <div class="hero-btns">
                <a href="signup.php" class="btn btn-primary btn-lg">
                    Commencer gratuitement
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="login.php" class="btn btn-outline btn-lg">Se connecter</a>
            </div>
            <div class="hero-proof">
                <span class="hero-proof-item">
                    <svg class="proof-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Inscription sans carte bleue
                </span>
                <span class="hero-proof-item">
                    <svg class="proof-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Données 100% privées
                </span>
                <span class="hero-proof-item">
                    <svg class="proof-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Prévisions jusqu'en 2035
                </span>
            </div>
        </div>

        <div class="mockup-wrap">
            <div class="mockup-card">
                <div class="mockup-bar">
                    <div class="m-dots">
                        <span class="m-dot r"></span>
                        <span class="m-dot y"></span>
                        <span class="m-dot g"></span>
                    </div>
                    <span class="m-title">Aperçu du Patrimoine</span>
                    <span class="m-tag">Prévision Active</span>
                </div>
                <div class="mockup-body">
                    <div class="m-stats">
                        <div class="m-stat">
                            <span class="m-stat-label">Solde Total</span>
                            <span class="m-stat-val">12 450 €</span>
                        </div>
                        <div class="m-stat">
                            <span class="m-stat-label">Flux Mensuel</span>
                            <span class="m-stat-val green">+920 €</span>
                        </div>
                    </div>
                    <div class="m-accounts">
                        <div class="m-account">
                            <div>
                                <div class="m-account-name">Société Générale</div>
                                <div class="m-account-sub">Courant · 0% intérêt</div>
                            </div>
                            <span class="m-account-val">3 500 €</span>
                        </div>
                        <div class="m-account">
                            <div>
                                <div class="m-account-name">Livret A</div>
                                <div class="m-account-sub">Épargne · 1,70%/an</div>
                            </div>
                            <span class="m-account-val green">8 500 €</span>
                        </div>
                        <div class="m-account">
                            <div>
                                <div class="m-account-name">CTO Investissement</div>
                                <div class="m-account-sub">7% rendement · 30% impôt</div>
                            </div>
                            <span class="m-account-val pink">450 €</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mockup-float">
                <div class="float-icon-wrap">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8d2b5c" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                </div>
                <div>
                    <div class="float-label">Prévision 2035</div>
                    <div class="float-sub">Patrimoine simulé · 48 390 €</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══ FEATURES ═══ -->
<section id="features" class="section section-alt">
    <div class="container">
        <div class="section-head">
            <h2>Tout pour maîtriser votre budget</h2>
            <p>Des outils pensés pour simplifier votre gestion financière quotidienne, sans complexité inutile.</p>
        </div>
        <div class="features-grid">

            <div class="feat-card">
                <div class="feat-icon-wrap">
                    <!-- Briefcase / accounts -->
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8d2b5c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="12.01"/></svg>
                </div>
                <h3>Comptes Rémunérés & Fiscalité</h3>
                <p>Gérez vos comptes courants, Livret A et CTO. Calcul automatique des intérêts nets et de la déduction fiscale appliquée.</p>
            </div>

            <div class="feat-card">
                <div class="feat-icon-wrap">
                    <!-- Arrows in/out -->
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8d2b5c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 16V4m0 0L3 8m4-4 4 4"/><path d="M17 8v12m0 0 4-4m-4 4-4-4"/></svg>
                </div>
                <h3>Dépenses & Revenus</h3>
                <p>Suivez vos flux ponctuels et récurrents — mensuel, bimensuel, trimestriel, annuel. Filtrez en un clic.</p>
            </div>

            <div class="feat-card" id="previsions">
                <div class="feat-icon-wrap">
                    <!-- Chart line -->
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8d2b5c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                </div>
                <h3>Prévisions Financières</h3>
                <p>Simulez votre patrimoine à une date cible (ex. fin 2035) en intégrant intérêts composés et tous vos mouvements.</p>
            </div>

            <div class="feat-card">
                <div class="feat-icon-wrap">
                    <!-- Sliders -->
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8d2b5c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
                </div>
                <h3>Gestion des Exceptions</h3>
                <p>Ajustez ponctuellement un montant — vacances, prime — sans altérer vos règles récurrentes de base.</p>
            </div>

            <div class="feat-card">
                <div class="feat-icon-wrap">
                    <!-- Share/link -->
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8d2b5c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                </div>
                <h3>Partage Sécurisé</h3>
                <p>Partagez un compte en lecture seule avec votre partenaire via un lien d'invitation révocable à tout moment.</p>
            </div>

            <div class="feat-card">
                <div class="feat-icon-wrap">
                    <!-- Shield -->
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#8d2b5c" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3>Zéro Connexion Bancaire</h3>
                <p>Aucun accès à votre banque requis. Budgie fonctionne en totale autonomie sans intermédiaire financier.</p>
            </div>

        </div>
    </div>
</section>

<!-- ═══ SECURITY ═══ -->
<section id="security" class="section">
    <div class="container">
        <div class="security-banner">
            <div>
                <h2>Sécurité & Confidentialité intégrale</h2>
                <p>
                    Vos données financières vous appartiennent. Aucune clé API bancaire n'est demandée,
                    aucune donnée n'est revendue. Budgie est souverain par conception.
                </p>
                <ul class="security-list">
                    <li>
                        <svg class="sec-check" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Chiffrement SSL/TLS 256-bit
                    </li>
                    <li>
                        <svg class="sec-check" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Hachage bcrypt des mots de passe
                    </li>
                    <li>
                        <svg class="sec-check" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Déploiement Docker conteneurisé
                    </li>
                    <li>
                        <svg class="sec-check" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        Tokens d'invitation révocables
                    </li>
                </ul>
            </div>
            <div class="security-badge">
                <span class="s-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.9)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                </span>
                <strong>100% Souverain</strong>
                <small>Zéro tracking financier</small>
            </div>
        </div>
    </div>
</section>

<!-- ═══ PRICING ═══ -->
<section id="pricing" class="section section-alt">
    <div class="container">
        <div class="section-head">
            <h2>Des tarifs simples et honnêtes</h2>
            <p>Sans engagement de durée. Changez ou annulez à tout moment.</p>
        </div>
        <div class="pricing-grid">
            <div class="price-card">
                <h3>Gratuit</h3>
                <div class="price-amount">0 €<span> / mois</span></div>
                <p>Parfait pour démarrer et gérer l'essentiel de votre budget.</p>
                <ul class="price-features">
                    <li><svg class="price-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><strong>2</strong>&nbsp;comptes maximum</li>
                    <li><svg class="price-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><strong>7</strong>&nbsp;dépenses par compte</li>
                    <li><svg class="price-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><strong>2</strong>&nbsp;revenus par compte</li>
                    <li><svg class="price-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>Calcul des intérêts &amp; prévisions</li>
                    <li><svg class="price-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>Recherche instantanée</li>
                </ul>
                <a href="signup.php" class="btn btn-outline btn-block">Créer un compte gratuit</a>
            </div>
            <div class="price-card featured">
                <div class="price-ribbon">Recommandé</div>
                <h3>Premium</h3>
                <div class="price-amount">9,99 €<span> / mois</span></div>
                <p>Liberté totale et simulations illimitées pour gérer tout votre patrimoine.</p>
                <ul class="price-features">
                    <li><svg class="price-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><strong>Comptes illimités</strong></li>
                    <li><svg class="price-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><strong>Dépenses illimitées</strong></li>
                    <li><svg class="price-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><strong>Revenus illimités</strong></li>
                    <li><svg class="price-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>Prévisions &amp; simulations sans limite</li>
                    <li><svg class="price-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>Exceptions &amp; Partage de comptes</li>
                    <li><svg class="price-check" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>Paiement sécurisé via Stripe</li>
                </ul>
                <a href="signup.php?plan=premium" class="btn btn-primary btn-block">
                    Passer au Premium
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ═══ FOOTER ═══ -->
<footer class="lp-footer">
    <div class="footer-inner">
        <div class="footer-grid">
            <div class="footer-brand">
                <div class="brand-logo-wrap">
                    <span class="logo-bird">🐦</span>
                    <strong>Budgie</strong>
                </div>
                <p>Ton partenaire financier personnel. Sécurisé, confidentiel et totalement indépendant des banques.</p>
            </div>
            <div class="footer-col">
                <h4>Produit</h4>
                <a href="#features">Fonctionnalités</a>
                <a href="#previsions">Prévisions</a>
                <a href="#security">Sécurité</a>
                <a href="#pricing">Tarifs</a>
            </div>
            <div class="footer-col">
                <h4>Compte</h4>
                <a href="login.php">Se connecter</a>
                <a href="signup.php">Créer un compte</a>
            </div>
            <div class="footer-col">
                <h4>Légal</h4>
                <a href="terms.php">CGU</a>
                <a href="privacy.php">Confidentialité</a>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?php echo date('Y'); ?> Budgie — Tous droits réservés.
        </div>
    </div>
</footer>

<script>
(function() {
    var html = document.documentElement;
    var toggle = document.getElementById('lpDarkToggle');
    var icon   = document.getElementById('lpDmIcon');
    var text   = document.getElementById('lpDmText');
    var STORAGE = 'budgie-theme';

    function applyTheme(isDark) {
        html.setAttribute('data-theme', isDark ? 'dark' : '');
        if (isDark) {
            if (icon) {
                icon.innerHTML = '<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>';
                icon.setAttribute('viewBox', '0 0 24 24');
                icon.removeAttribute('fill');
                icon.setAttribute('stroke', 'currentColor');
            }
            if (text) text.textContent = 'Clair';
        } else {
            if (icon) {
                icon.innerHTML = '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>';
                icon.setAttribute('viewBox', '0 0 24 24');
                icon.setAttribute('fill', 'currentColor');
                icon.removeAttribute('stroke');
            }
            if (text) text.textContent = 'Sombre';
        }
    }

    applyTheme(localStorage.getItem(STORAGE) === 'dark');

    if (toggle) {
        toggle.addEventListener('click', function() {
            var isDark = html.getAttribute('data-theme') !== 'dark';
            localStorage.setItem(STORAGE, isDark ? 'dark' : 'light');
            applyTheme(isDark);
        });
    }
})();
</script>
</body>
</html>
