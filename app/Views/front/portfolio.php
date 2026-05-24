<?php
// ─── Temel değişkenler ──────────────────────────────────────────
$siteBase    = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'https://fezadan.org';
$isSubdomain = $isSubdomain ?? false;   // furkan.fezadan.org mu?

$lang  = 'TR';
$isEn  = false;

$t_all      = $isEn ? 'All Work'    : 'Tümü';
$t_photos   = $isEn ? 'Photography' : 'Fotoğraf';
$t_drawings = $isEn ? 'Illustration': 'Çizim / İllüstrasyon';
$t_noItems  = $isEn ? 'No work added yet. Check back soon.' : 'Henüz çalışma eklenmedi.';
$t_zoom     = $isEn ? 'Open full size' : 'Tam boyut aç';

$page_title       = 'Furkan Şen — Portfolio';
$page_description = $isEn
    ? 'Photography and illustration works by Furkan Şen.'
    : 'Furkan Şen\'in fotoğraf ve çizim çalışmaları.';
$page_canonical   = 'https://furkan.fezadan.org';
$og_url           = $page_canonical;
$og_type          = 'website';

// Subdomain'de ana site header'ını include etme — kendi header'ımızı kullan.
if (!$isSubdomain) {
    require_once ROOT . '/app/Views/inc/header.php';
}
?>
<?php if ($isSubdomain): ?>
<!DOCTYPE html>
<html lang="<?= $isEn ? 'en' : 'tr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page_description) ?>">
    <link rel="canonical" href="<?= htmlspecialchars($page_canonical) ?>">
    <meta property="og:title"       content="<?= htmlspecialchars($page_title) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page_description) ?>">
    <meta property="og:url"         content="<?= htmlspecialchars($og_url) ?>">
    <meta property="og:type"        content="website">
    <meta name="theme-color" content="#0a0a0a">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Space+Grotesk:wght@300;400;500;700&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:        #0a0a0a;
            --bg-card:   #111111;
            --bg-card-h: #161616;
            --border:    #222222;
            --text:      #f0ece4;
            --muted:     #666666;
            --accent:    #ff4d4d;
            --accent2:   #e8d5b0;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Space Grotesk', sans-serif;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            overflow-x: hidden;
        }
        h1, h2, h3 { font-family: 'Syne', sans-serif; }
        a { color: inherit; text-decoration: none; }
        img { display: block; max-width: 100%; }
    </style>
</head>
<body>
<?php endif; ?>

<style>
/* ================================================================
   FURKAN.FEZADAN.ORG — BEHANCE-STYLE PORTFOLIO
   ================================================================ */

/* ── Standalone header (only when subdomain) ── */
.fk-header {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 100;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2.5rem;
    background: rgba(10,10,10,0.88);
    backdrop-filter: blur(20px) saturate(1.4);
    border-bottom: 1px solid var(--border, #222);
    transition: border-color .3s;
}

.fk-header-brand {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: <?= $isSubdomain ? '#f0ece4' : 'var(--text-main)' ?>;
}
.fk-header-brand span {
    color: <?= $isSubdomain ? '#ff4d4d' : 'var(--text-accent)' ?>;
}

.fk-header-nav {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.fk-header-nav a {
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: <?= $isSubdomain ? 'rgba(240,236,228,0.5)' : 'var(--text-main)' ?>;
    transition: color .2s;
}
.fk-header-nav a:hover {
    color: <?= $isSubdomain ? '#ff4d4d' : 'var(--text-accent)' ?>;
}

/* ── Hero / Profile Block ── */
.fk-profile-container {
    width: 100%;
    position: relative;
    z-index: 2;
}

.fk-cover-wrapper {
    width: 100%;
    height: 320px;
    position: relative;
    overflow: hidden;
}

.fk-cover {
    width: 100%;
    height: 100%;
    position: relative;
    background: linear-gradient(135deg, var(--text-accent, #A31D1D) 0%, var(--bg-secondary, #E5D0AC) 100%);
    transition: background 0.3s ease;
}

[data-theme="dark"] .fk-cover {
    background: linear-gradient(135deg, #3D1F1F 0%, #120A0A 100%);
}

.fk-cover-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.45;
    filter: grayscale(40%) contrast(105%);
    transition: opacity 0.5s ease;
}

.fk-cover::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0) 20%, rgba(10,10,10,0.95) 100%);
}

[data-theme="light"] .fk-cover::after {
    background: linear-gradient(to bottom, rgba(254,249,225,0) 20%, var(--bg-paper) 100%);
}

/* ── Split Column Layout (Behance style) ── */
.fk-portfolio-layout {
    max-width: 1600px;
    margin: 0 auto;
    padding: 0 1.5rem 6rem;
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
    position: relative;
    z-index: 5;
}

@media (min-width: 1024px) {
    .fk-portfolio-layout {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 3.5rem;
        padding: 0 2.5rem 6rem;
    }
}

.fk-sidebar {
    width: 100%;
    margin-top: -120px;
    position: relative;
    z-index: 10;
}

@media (min-width: 1024px) {
    .fk-sidebar {
        margin-top: -140px;
        position: sticky;
        top: 110px;
        height: fit-content;
    }
}

.fk-main {
    width: 100%;
    min-width: 0; /* Prevents grid layout blowout */
    margin-top: 1rem;
}

@media (min-width: 1024px) {
    .fk-main {
        margin-top: 40px;
    }
}

/* ── Profile Card ── */
.fk-profile-card {
    width: 100%;
    background: <?= $isSubdomain ? 'rgba(17, 17, 17, 0.72)' : 'rgba(254, 249, 225, 0.82)' ?>;
    backdrop-filter: blur(30px);
    -webkit-backdrop-filter: blur(30px);
    border: 1px solid <?= $isSubdomain ? 'rgba(255, 255, 255, 0.08)' : 'var(--line-color)' ?>;
    border-radius: 20px;
    box-shadow: 0 30px 70px rgba(0, 0, 0, <?= $isSubdomain ? '0.45' : '0.08' ?>);
    padding: 2.5rem 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

@media (min-width: 1024px) {
    .fk-profile-card {
        align-items: flex-start;
        text-align: left;
        padding: 2.5rem 2.25rem;
    }
}

.fk-avatar-wrapper {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    border: 3px solid <?= $isSubdomain ? '#ff4d4d' : 'var(--text-accent)' ?>;
    padding: 3px;
    background: <?= $isSubdomain ? '#111' : 'var(--bg-paper)' ?>;
    margin-top: -5.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    position: relative;
    flex-shrink: 0;
}

.fk-avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    filter: grayscale(10%) contrast(105%);
}

.fk-avatar-fallback {
    position: absolute;
    inset: 3px;
    border-radius: 50%;
    background: linear-gradient(135deg, #A31D1D 0%, #E5D0AC 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 2.2rem;
    font-weight: 700;
    letter-spacing: -0.05em;
    text-transform: uppercase;
}

[data-theme="dark"] .fk-avatar-fallback {
    background: linear-gradient(135deg, #ff4d4d 0%, #120A0A 100%);
}

.fk-name-wrap {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
    justify-content: center;
}

@media (min-width: 1024px) {
    .fk-name-wrap {
        justify-content: flex-start;
    }
}

.fk-profile-name {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.8rem;
    font-weight: 700;
    letter-spacing: -0.03em;
    color: <?= $isSubdomain ? '#f0ece4' : 'var(--text-main)' ?>;
    line-height: 1.15;
}

.fk-badge-pro {
    background: <?= $isSubdomain ? '#ff4d4d' : 'var(--text-accent)' ?>;
    color: <?= $isSubdomain ? '#0a0a0a' : 'var(--bg-paper)' ?>;
    font-size: 0.58rem;
    font-weight: 800;
    letter-spacing: 0.08em;
    padding: 0.15rem 0.45rem;
    border-radius: 4px;
    display: inline-block;
    line-height: 1.2;
}

.fk-profile-role {
    font-size: 0.72rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    font-weight: 700;
    color: <?= $isSubdomain ? '#ff4d4d' : 'var(--text-accent)' ?>;
    margin-bottom: 0.5rem;
}

.fk-profile-location {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: <?= $isSubdomain ? 'rgba(240,236,228,0.5)' : 'var(--text-main)' ?>;
    opacity: 0.75;
    margin-bottom: 1.25rem;
    justify-content: center;
}

@media (min-width: 1024px) {
    .fk-profile-location {
        justify-content: flex-start;
    }
}

.fk-profile-location svg {
    flex-shrink: 0;
}

.fk-profile-bio {
    font-size: 0.88rem;
    line-height: 1.65;
    color: <?= $isSubdomain ? 'rgba(240,236,228,0.65)' : 'var(--text-main)' ?>;
    margin-bottom: 1.5rem;
    opacity: 0.85;
}

/* ── Actions / Buttons ── */
.fk-actions {
    display: flex;
    gap: 0.75rem;
    width: 100%;
    margin-bottom: 1.5rem;
}

.fk-btn-primary, .fk-btn-secondary {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    transition: all 0.22s cubic-bezier(.16,1,.3,1);
    cursor: pointer;
    text-decoration: none;
}

.fk-btn-primary {
    background: <?= $isSubdomain ? '#ff4d4d' : 'var(--text-accent)' ?>;
    color: <?= $isSubdomain ? '#0a0a0a' : 'var(--bg-paper)' ?>;
    border: 1px solid transparent;
}

.fk-btn-primary:hover {
    background: <?= $isSubdomain ? '#ff6666' : 'var(--text-main)' ?>;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.fk-btn-secondary {
    background: transparent;
    color: <?= $isSubdomain ? '#f0ece4' : 'var(--text-main)' ?>;
    border: 1px solid <?= $isSubdomain ? 'rgba(255,255,255,0.15)' : 'var(--line-color)' ?>;
}

.fk-btn-secondary:hover {
    border-color: <?= $isSubdomain ? '#ff4d4d' : 'var(--text-accent)' ?>;
    color: <?= $isSubdomain ? '#ff4d4d' : 'var(--text-accent)' ?>;
    background: <?= $isSubdomain ? 'rgba(255,77,77,0.05)' : 'rgba(163,29,29,0.03)' ?>;
    transform: translateY(-2px);
}

/* ── Social Links & Stats ── */
.fk-profile-socials {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    width: 100%;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid <?= $isSubdomain ? 'rgba(255,255,255,0.08)' : 'var(--line-color)' ?>;
    padding-bottom: 1.5rem;
}

.fk-social-icon {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: <?= $isSubdomain ? 'rgba(240,236,228,0.6)' : 'var(--text-main)' ?>;
    opacity: 0.8;
    transition: all 0.2s;
    text-decoration: none;
    align-self: center;
}

@media (min-width: 1024px) {
    .fk-social-icon {
        align-self: flex-start;
    }
}

.fk-social-icon:hover {
    color: <?= $isSubdomain ? '#ff4d4d' : 'var(--text-accent)' ?>;
    opacity: 1;
    transform: translateX(3px);
}

.fk-stats {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    border-bottom: 1px solid <?= $isSubdomain ? 'rgba(255,255,255,0.08)' : 'var(--line-color)' ?>;
    padding-bottom: 1.5rem;
    margin-bottom: 1.5rem;
}

@media (min-width: 1024px) {
    .fk-stats {
        justify-content: flex-start;
        gap: 1.5rem;
    }
}

.fk-stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
}

@media (min-width: 1024px) {
    .fk-stat-item {
        align-items: flex-start;
    }
}

.fk-stat-val {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.4rem;
    font-weight: 700;
    color: <?= $isSubdomain ? '#f0ece4' : 'var(--text-main)' ?>;
    line-height: 1.1;
    margin-bottom: 0.2rem;
}

.fk-stat-label {
    font-size: 0.6rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    font-weight: 600;
    color: <?= $isSubdomain ? 'rgba(240,236,228,0.45)' : 'var(--text-main)' ?>;
    opacity: 0.7;
}

.fk-stat-divider {
    width: 1px;
    height: 24px;
    background: <?= $isSubdomain ? 'rgba(255,255,255,0.08)' : 'var(--line-color)' ?>;
    opacity: 0.6;
}

/* ── Focus Tags ── */
.fk-profile-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    width: 100%;
    justify-content: center;
}

@media (min-width: 1024px) {
    .fk-profile-tags {
        justify-content: flex-start;
    }
}

.fk-tag {
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    background: <?= $isSubdomain ? 'rgba(255,255,255,0.05)' : 'var(--bg-secondary)' ?>;
    color: <?= $isSubdomain ? 'rgba(240,236,228,0.7)' : 'var(--text-main)' ?>;
    padding: 0.25rem 0.55rem;
    border-radius: 4px;
    transition: all 0.2s;
}

.fk-tag:hover {
    background: <?= $isSubdomain ? 'rgba(255,77,77,0.1)' : 'var(--text-accent)' ?>;
    color: <?= $isSubdomain ? '#ff4d4d' : 'var(--bg-paper)' ?>;
}

/* ── Big Type Divider (bottom) ── */
.fk-big-title-wrap {
    padding: 3rem 0 0;
    width: 100%;
    overflow: hidden;
    position: relative;
    z-index: 1;
}

.fk-big-title {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: clamp(3rem, 9vw, 9rem);
    line-height: 0.88;
    letter-spacing: -0.02em;
    text-transform: uppercase;
    white-space: nowrap;
    display: block;
    text-align: center;
    padding-left: 0;
    background: <?= $isSubdomain
        ? 'linear-gradient(120deg, rgba(255,77,77,0.55) 0%, rgba(232,213,176,0.35) 60%, rgba(255,77,77,0.18) 100%)'
        : 'linear-gradient(120deg, rgba(109,35,35,0.55) 0%, rgba(163,29,29,0.75) 40%, rgba(229,208,172,0.30) 100%)' ?>;
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    user-select: none;
    pointer-events: none;
    opacity: 0;
    transform: translateY(40px);
    animation: bigTitleIn 1s cubic-bezier(.16,1,.3,1) .15s forwards;
}

@keyframes bigTitleIn {
    to { opacity: 1; transform: translateY(0); }
}

/* ── Gallery Section ── */
.fk-gallery-section {
    max-width: 1600px;
    margin: 0 auto;
    padding: 0 1.5rem 6rem;
    position: relative;
    z-index: 2;
}

/* Filter tabs — Behance style */
.fk-filters {
    display: flex;
    align-items: center;
    gap: 0;
    border-bottom: 1px solid <?= $isSubdomain ? '#222' : 'var(--line-color)' ?>;
    margin-bottom: 2.5rem;
    overflow-x: auto;
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.fk-filters::-webkit-scrollbar { display: none; }

.fk-filter-btn {
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    padding: 0.85rem 1.5rem;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: <?= $isSubdomain ? 'rgba(240,236,228,0.35)' : 'var(--text-main)' ?>;
    opacity: <?= $isSubdomain ? '1' : '0.45' ?>;
    cursor: pointer;
    white-space: nowrap;
    transition: all .2s;
}

.fk-filter-btn:hover {
    color: <?= $isSubdomain ? '#f0ece4' : 'var(--text-main)' ?>;
    opacity: 1;
}

.fk-filter-btn.active {
    color: <?= $isSubdomain ? '#f0ece4' : 'var(--text-main)' ?>;
    border-bottom-color: <?= $isSubdomain ? '#ff4d4d' : 'var(--text-accent)' ?>;
    opacity: 1;
}

.fk-filter-badge {
    display: inline-block;
    background: <?= $isSubdomain ? '#1f1f1f' : 'var(--bg-secondary)' ?>;
    border-radius: 99px;
    padding: 0 0.4rem;
    font-size: 0.6rem;
    margin-left: 0.3rem;
    vertical-align: middle;
    opacity: 0.6;
}

/* Masonry grid */
.fk-grid {
    columns: 1;
    column-gap: 1rem;
}
@media (min-width: 600px)  { .fk-grid { columns: 2; } }
@media (min-width: 900px)  { .fk-grid { columns: 3; } }
@media (min-width: 1300px) { .fk-grid { columns: 4; } }

/* Project Card — Behance style */
.fk-card {
    break-inside: avoid;
    margin-bottom: 1rem;
    position: relative;
    cursor: pointer;
    border-radius: 6px;
    overflow: hidden;
    background: <?= $isSubdomain ? 'var(--bg-card)' : 'var(--bg-secondary)' ?>;
    border: 1px solid <?= $isSubdomain ? '#1c1c1c' : 'var(--line-color)' ?>;
    transition: border-color .25s, box-shadow .25s;

    /* entrance */
    opacity: 0;
    transform: translateY(20px);
}

.fk-card.in-view {
    animation: cardSlideUp .55s cubic-bezier(.16,1,.3,1) forwards;
}

@keyframes cardSlideUp {
    to { opacity: 1; transform: translateY(0); }
}

.fk-card:hover {
    border-color: <?= $isSubdomain ? '#333' : 'var(--line-color)' ?>;
    box-shadow: 0 20px 60px rgba(0,0,0,<?= $isSubdomain ? '.6' : '.15' ?>);
}

.fk-card:focus-visible {
    outline: 2px solid <?= $isSubdomain ? '#ff4d4d' : 'var(--text-accent)' ?>;
    outline-offset: 2px;
}

/* Image */
.fk-card-img {
    position: relative;
    overflow: hidden;
    aspect-ratio: unset;      /* masonry — natural height */
    background: <?= $isSubdomain ? '#161616' : 'var(--bg-secondary)' ?>;
}

.fk-card-img img {
    width: 100%;
    height: auto;
    display: block;
    transition: transform .6s cubic-bezier(.16,1,.3,1), filter .4s ease;
}

.fk-card:hover .fk-card-img img {
    transform: scale(1.05);
    filter: brightness(.88);
}

/* Hover overlay — Behance dark overlay with info */
.fk-card-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to top,
        rgba(0,0,0,.85) 0%,
        rgba(0,0,0,.2)  50%,
        transparent     100%
    );
    opacity: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 1.25rem;
    transition: opacity .3s ease;
    gap: 0.35rem;
}

.fk-card:hover .fk-card-overlay {
    opacity: 1;
}

.fk-card-overlay-tag {
    font-size: 0.58rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: <?= $isSubdomain ? '#ff4d4d' : 'rgba(254,249,225,0.7)' ?>;
    transform: translateY(4px);
    transition: transform .3s ease;
}

.fk-card:hover .fk-card-overlay-tag {
    transform: translateY(0);
}

.fk-card-overlay-title {
    font-family: 'Syne', sans-serif;
    font-size: 1.05rem;
    font-weight: 700;
    color: #fff;
    line-height: 1.25;
    transform: translateY(6px);
    transition: transform .35s cubic-bezier(.16,1,.3,1) .04s;
}

.fk-card:hover .fk-card-overlay-title {
    transform: translateY(0);
}

/* View icon */
.fk-card-overlay-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,.25);
    background: rgba(255,255,255,.08);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    opacity: 0;
    transform: scale(.8);
    transition: opacity .25s ease, transform .25s cubic-bezier(.16,1,.3,1);
}

.fk-card:hover .fk-card-overlay-icon {
    opacity: 1;
    transform: scale(1);
}

/* Caption below image */
.fk-card-caption {
    padding: 0.85rem 1rem;
    border-top: 1px solid <?= $isSubdomain ? '#1c1c1c' : 'var(--line-color)' ?>;
}

.fk-card-caption-title {
    font-family: 'Syne', sans-serif;
    font-size: 0.88rem;
    font-weight: 700;
    color: <?= $isSubdomain ? '#f0ece4' : 'var(--text-main)' ?>;
    margin-bottom: 0.2rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.fk-card-caption-meta {
    font-size: 0.6rem;
    font-weight: 600;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    color: <?= $isSubdomain ? '#ff4d4d' : 'var(--text-accent)' ?>;
}

.fk-card.filtered-out { display: none; }

/* Empty state */
.fk-empty {
    text-align: center;
    padding: 8rem 2rem;
    font-size: 0.75rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: <?= $isSubdomain ? '#333' : 'var(--text-main)' ?>;
    opacity: 0.4;
}

/* ── Standalone Footer ── */
.fk-footer {
    border-top: 1px solid <?= $isSubdomain ? '#1c1c1c' : 'var(--line-color)' ?>;
    padding: 2rem 2.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    position: relative;
    z-index: 2;
}

.fk-footer-left {
    font-size: 0.65rem;
    letter-spacing: 0.1em;
    color: <?= $isSubdomain ? '#444' : 'var(--text-main)' ?>;
    opacity: 0.5;
}

.fk-footer-right a {
    font-size: 0.65rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: <?= $isSubdomain ? '#555' : 'var(--text-main)' ?>;
    transition: color .2s;
}

.fk-footer-right a:hover {
    color: <?= $isSubdomain ? '#ff4d4d' : 'var(--text-accent)' ?>;
}

/* ================================================================
   LIGHTBOX — Full Behance-like overlay
   ================================================================ */
.fk-lb {
    position: fixed;
    inset: 0;
    z-index: 9999;
    display: flex;
    visibility: hidden;
    pointer-events: none;
}

.fk-lb.open {
    visibility: visible;
    pointer-events: all;
}

.fk-lb-bg {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,.97);
    backdrop-filter: blur(20px) saturate(.6);
    opacity: 0;
    transition: opacity .35s ease;
}

.fk-lb.open .fk-lb-bg { opacity: 1; }

/* Top bar */
.fk-lb-topbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    height: 56px;
    z-index: 10001;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
    border-bottom: 1px solid rgba(255,255,255,.06);
    background: rgba(0,0,0,.6);
    backdrop-filter: blur(12px);
    opacity: 0;
    transform: translateY(-10px);
    transition: opacity .3s ease .1s, transform .3s ease .1s;
}

.fk-lb.open .fk-lb-topbar {
    opacity: 1;
    transform: translateY(0);
}

.fk-lb-topbar-title {
    font-family: 'Syne', sans-serif;
    font-size: 0.88rem;
    font-weight: 700;
    color: rgba(255,255,255,.8);
}

.fk-lb-topbar-right {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.fk-lb-btn {
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 6px;
    color: rgba(255,255,255,.65);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    transition: all .2s;
}

.fk-lb-btn:hover {
    background: rgba(255,255,255,.14);
    color: #fff;
}

.fk-lb-close-btn {
    width: 36px; height: 36px;
    border-radius: 50%;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.1);
    color: rgba(255,255,255,.65);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all .2s;
}

.fk-lb-close-btn:hover {
    background: rgba(255,77,77,.3);
    border-color: rgba(255,77,77,.5);
    color: #fff;
    transform: rotate(90deg);
}

/* Image area */
.fk-lb-stage {
    position: fixed;
    inset: 56px 0 80px 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 5rem;
}

.fk-lb-img-wrap {
    position: relative;
    max-width: 100%;
    max-height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.fk-lb-img-wrap img {
    max-width: 100%;
    max-height: calc(100vh - 200px);
    object-fit: contain;
    border-radius: 4px;
    box-shadow: 0 40px 100px rgba(0,0,0,.8);
    opacity: 0;
    transform: scale(.97);
    transition: opacity .3s ease, transform .3s cubic-bezier(.16,1,.3,1);
}

.fk-lb.open .fk-lb-img-wrap img.loaded {
    opacity: 1;
    transform: scale(1);
}

/* Spinner */
.fk-lb-spinner {
    position: absolute;
    width: 32px; height: 32px;
    border: 2px solid rgba(255,255,255,.08);
    border-top-color: rgba(255,255,255,.5);
    border-radius: 50%;
    animation: spin .7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

/* Nav arrows */
.fk-lb-nav-btn {
    position: fixed;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10001;
    width: 48px; height: 72px;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 6px;
    color: rgba(255,255,255,.55);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all .2s;
}

.fk-lb-nav-btn:hover {
    background: rgba(255,255,255,.12);
    color: #fff;
}

.fk-lb-nav-prev { left: 1.25rem; }
.fk-lb-nav-next { right: 1.25rem; }

/* Bottom info bar */
.fk-lb-infobar {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    height: 80px;
    z-index: 10001;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 2rem;
    border-top: 1px solid rgba(255,255,255,.06);
    background: rgba(0,0,0,.6);
    backdrop-filter: blur(12px);
    opacity: 0;
    transform: translateY(10px);
    transition: opacity .3s ease .1s, transform .3s ease .1s;
}

.fk-lb.open .fk-lb-infobar {
    opacity: 1;
    transform: translateY(0);
}

.fk-lb-info-title {
    font-family: 'Syne', sans-serif;
    font-size: 1rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 0.15rem;
}

.fk-lb-info-desc {
    font-size: 0.75rem;
    color: rgba(255,255,255,.4);
    line-height: 1.5;
    max-width: 500px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.fk-lb-info-right {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-shrink: 0;
}

.fk-lb-counter {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.7rem;
    color: rgba(255,255,255,.25);
    letter-spacing: 0.1em;
}

.fk-lb-open-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: rgba(255,255,255,.4);
    border: 1px solid rgba(255,255,255,.12);
    border-radius: 4px;
    padding: 0.4rem 0.85rem;
    text-decoration: none;
    transition: all .2s;
}

.fk-lb-open-btn:hover {
    color: #fff;
    border-color: rgba(255,255,255,.3);
    background: rgba(255,255,255,.07);
}

/* Swipe hint dots */
.fk-lb-dots {
    position: fixed;
    bottom: 90px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10001;
    display: flex;
    gap: 5px;
}

.fk-lb-dot {
    width: 5px; height: 5px;
    border-radius: 50%;
    background: rgba(255,255,255,.18);
    transition: background .2s, transform .2s;
}

.fk-lb-dot.active {
    background: rgba(255,255,255,.7);
    transform: scaleX(2.5);
    border-radius: 3px;
}

/* Progress bar */
.fk-lb-bar {
    position: fixed;
    top: 0; left: 0;
    height: 2px;
    background: <?= $isSubdomain ? '#ff4d4d' : '#A31D1D' ?>;
    z-index: 10002;
    transition: width .25s ease;
}

/* Responsive lightbox */
@media (max-width: 700px) {
    .fk-lb-stage { padding: 1rem 1rem; inset: 56px 0 120px 0; }
    .fk-lb-nav-btn { display: none; }
    .fk-lb-infobar { flex-direction: column; align-items: flex-start; justify-content: center; gap: .2rem; height: 120px; }
    .fk-lb-info-desc { display: none; }
}

/* ── Misc Animations ── */
@keyframes fadeDown { from { opacity:0; transform:translateY(-10px); } to { opacity:1; transform:none; } }

.fk-profile-inner { animation: fadeDown .7s cubic-bezier(.16,1,.3,1) .05s both; }
.fk-filters       { animation: fadeDown .5s ease .25s both; }

/* ── Animated Background ── */
.portfolio-bg-animation {
    position: fixed;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    overflow: hidden;
}
#canvas-bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    display: block;
}
.ambient-glow {
    position: absolute;
    border-radius: 50%;
    filter: blur(140px);
    opacity: 0.15;
    mix-blend-mode: plus-lighter;
}
[data-theme="dark"] .ambient-glow,
body.dark .ambient-glow,
.portfolio-bg-animation.is-subdomain .ambient-glow {
    opacity: 0.08;
}
.orb-1 {
    top: 5%;
    left: 10%;
    width: 450px;
    height: 450px;
    background: radial-gradient(circle, rgba(163,29,29,0.3) 0%, transparent 80%);
    animation: floatOrb1 28s infinite alternate ease-in-out;
}
.orb-2 {
    bottom: 10%;
    right: 5%;
    width: 550px;
    height: 550px;
    background: radial-gradient(circle, rgba(229,208,172,0.2) 0%, transparent 80%);
    animation: floatOrb2 35s infinite alternate ease-in-out;
}
.orb-3 {
    top: 45%;
    left: 45%;
    width: 380px;
    height: 380px;
    background: radial-gradient(circle, rgba(163,29,29,0.25) 0%, transparent 70%);
    animation: floatOrb3 22s infinite alternate ease-in-out;
}
@keyframes floatOrb1 {
    0% { transform: translate(0, 0) scale(1); }
    100% { transform: translate(140px, 90px) scale(1.15); }
}
@keyframes floatOrb2 {
    0% { transform: translate(0, 0) scale(1.1); }
    100% { transform: translate(-120px, -140px) scale(0.9); }
}
@keyframes floatOrb3 {
    0% { transform: translate(-30px, 40px) scale(0.9); }
    100% { transform: translate(70px, -50px) scale(1.12); }
}
</style>

<!-- ═══════════════════════════════════════════════════════════
     STANDALONE HEADER (only for subdomain)
     ═══════════════════════════════════════════════════════════ -->
<?php if ($isSubdomain): ?>
<header class="fk-header">
    <div class="fk-header-brand">FURKAN<span>.</span>ŞEN</div>
    <nav class="fk-header-nav">
        <a href="https://fezadan.org" target="_blank" rel="noopener">Fezadan.org</a>
        <a href="https://github.com/shenfurkan" target="_blank" rel="noopener">GitHub</a>
        <a href="mailto:furkan@fezadan.org">İletişim</a>
    </nav>
</header>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     ANIMATED BACKGROUND
     ═══════════════════════════════════════════════════════════ -->
<div class="portfolio-bg-animation <?= $isSubdomain ? 'is-subdomain' : '' ?>">
    <div class="ambient-glow orb-1"></div>
    <div class="ambient-glow orb-2"></div>
    <div class="ambient-glow orb-3"></div>
    <canvas id="canvas-bg"></canvas>
</div>

<!-- ═══════════════════════════════════════════════════════════
     COVER BANNER BLOCK
     ═══════════════════════════════════════════════════════════ -->
<section class="fk-profile-container">
    <div class="fk-cover-wrapper">
        <div class="fk-cover" id="fk-cover-banner">
            <img class="fk-cover-img" id="fk-cover-banner-img" src="https://images.unsplash.com/photo-1483737186981-a657508cf3a8?auto=format&fit=crop&w=1920&q=80" alt="Cover">
        </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     PORTFOLIO LAYOUT (Sidebar + Main Gallery)
     ═══════════════════════════════════════════════════════════ -->
<div class="fk-portfolio-layout">
    
    <!-- Left Column: Sidebar Profile -->
    <aside class="fk-sidebar">
        <div class="fk-profile-card">
            <div class="fk-avatar-wrapper" id="fk-avatar-container">
                <img class="fk-avatar-img" id="fk-avatar-profile-img" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&h=150&q=80" alt="Furkan Şen">
            </div>
            
            <div class="fk-name-wrap">
                <h1 class="fk-profile-name">Furkan Şen</h1>
                <span class="fk-badge-pro">PRO</span>
            </div>
            
            <p class="fk-profile-role">Photographer &amp; Illustrator</p>
            
            <div class="fk-profile-location">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span>Istanbul, Turkey</span>
            </div>
            
            <p class="fk-profile-bio">
                Collecting moments, drawing quiet thoughts. A visual archive of aesthetics between science and art.
            </p>
            
            <div class="fk-actions">
                <a href="mailto:furkan@fezadan.org" class="fk-btn-primary">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m2 7 10 7 10-7"/></svg>
                    Contact
                </a>
                <button class="fk-btn-secondary" id="fk-follow-btn">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    Follow
                </button>
            </div>

            <div class="fk-profile-socials">
                <a href="https://fezadan.org" class="fk-social-icon" target="_blank" rel="noopener">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20M12 2a15 15 0 0 0 0 20"/></svg>
                    <span>fezadan.org</span>
                </a>
                <a href="https://github.com/shenfurkan" class="fk-social-icon" target="_blank" rel="noopener">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.37 0 0 5.37 0 12c0 5.3 3.44 9.8 8.2 11.39.6.11.82-.26.82-.58v-2.03c-3.34.73-4.04-1.61-4.04-1.61-.55-1.39-1.34-1.76-1.34-1.76-1.09-.74.08-.73.08-.73 1.2.09 1.84 1.24 1.84 1.24 1.07 1.83 2.81 1.3 3.5 1 .1-.78.42-1.31.76-1.61-2.67-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.12-.3-.54-1.52.12-3.17 0 0 1.01-.32 3.3 1.23a11.5 11.5 0 0 1 3-.4c1.02 0 2.04.13 3 .4 2.28-1.55 3.29-1.23 3.29-1.23.66 1.65.24 2.87.12 3.17.77.84 1.24 1.91 1.24 3.22 0 4.61-2.81 5.63-5.48 5.92.43.37.81 1.1.81 2.22v3.29c0 .32.22.7.82.58C20.56 21.8 24 17.3 24 12c0-6.63-5.37-12-12-12z"/></svg>
                    <span>github</span>
                </a>
            </div>
            
            <?php if (!empty($items)): ?>
            <div class="fk-stats">
                <div class="fk-stat-item">
                    <span class="fk-stat-val"><?= count($items) ?></span>
                    <span class="fk-stat-label">Works</span>
                </div>
                <div class="fk-stat-divider"></div>
                <div class="fk-stat-item">
                    <span class="fk-stat-val"><?= count(array_filter($items, fn($i) => $i['type'] === 'photo')) ?></span>
                    <span class="fk-stat-label">Photos</span>
                </div>
                <div class="fk-stat-divider"></div>
                <div class="fk-stat-item">
                    <span class="fk-stat-val"><?= count(array_filter($items, fn($i) => $i['type'] === 'drawing')) ?></span>
                    <span class="fk-stat-label">Illustrations</span>
                </div>
            </div>
            <?php endif; ?>

            <div class="fk-profile-tags">
                <span class="fk-tag">Photography</span>
                <span class="fk-tag">Illustration</span>
                <span class="fk-tag">Brutalist Art</span>
                <span class="fk-tag">Vector Art</span>
            </div>
        </div>
    </aside>

    <!-- Right Column: Gallery Content -->
    <main class="fk-main" id="main-content">
        <?php if (!empty($items)):
            $photoCount   = count(array_filter($items, fn($i) => $i['type'] === 'photo'));
            $drawingCount = count(array_filter($items, fn($i) => $i['type'] === 'drawing'));
        ?>

        <!-- Filter tabs -->
        <nav class="fk-filters" aria-label="Kategori filtresi" role="tablist">
            <button class="fk-filter-btn active" data-filter="all" role="tab" aria-selected="true">
                <?= $t_all ?><span class="fk-filter-badge"><?= count($items) ?></span>
            </button>
            <button class="fk-filter-btn" data-filter="photo" role="tab" aria-selected="false">
                <?= $t_photos ?><span class="fk-filter-badge"><?= $photoCount ?></span>
            </button>
            <button class="fk-filter-btn" data-filter="drawing" role="tab" aria-selected="false">
                <?= $t_drawings ?><span class="fk-filter-badge"><?= $drawingCount ?></span>
            </button>
        </nav>

        <!-- Masonry grid -->
        <div class="fk-grid" id="fk-grid" role="list">
        <?php foreach ($items as $idx => $item):
            $title   = htmlspecialchars($isEn ? ($item['title_en'] ?? $item['title_tr']) : $item['title_tr'], ENT_QUOTES, 'UTF-8');
            $desc    = htmlspecialchars($isEn ? ($item['description_en'] ?? $item['description_tr'] ?? '') : ($item['description_tr'] ?? ''), ENT_QUOTES, 'UTF-8');
            $imgUrl  = htmlspecialchars(Upload::assetUrl($item['image_url']), ENT_QUOTES, 'UTF-8');
            $typeLabel = $item['type'] === 'drawing' ? $t_drawings : $t_photos;
        ?>
        <article
            class="fk-card"
            data-type="<?= $item['type'] ?>"
            data-index="<?= $idx ?>"
            data-image="<?= $imgUrl ?>"
            data-title="<?= $title ?>"
            data-description="<?= $desc ?>"
            tabindex="0"
            role="listitem button"
            aria-label="<?= $title ?>"
            style="animation-delay: <?= min($idx * 55, 550) ?>ms"
        >
            <div class="fk-card-img">
                <img
                    src="<?= $imgUrl ?>"
                    alt="<?= $title ?>"
                    loading="<?= $idx < 8 ? 'eager' : 'lazy' ?>"
                    decoding="async"
                >
                <div class="fk-card-overlay">
                    <span class="fk-card-overlay-tag"><?= $typeLabel ?></span>
                    <span class="fk-card-overlay-title"><?= $title ?></span>
                </div>
                <div class="fk-card-overlay-icon">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <path d="M1 12S5 4 12 4s11 8 11 8-4 8-11 8S1 12 1 12z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </div>
            </div>
            <div class="fk-card-caption">
                <div class="fk-card-caption-title"><?= $title ?></div>
                <div class="fk-card-caption-meta"><?= $typeLabel ?></div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <?php else: ?>
    <div class="fk-empty"><?= $t_noItems ?></div>
    <?php endif; ?>

    </main>
</div>

<!-- ═══════════════════════════════════════════════════════════
     BIG TYPOGRAPHIC TITLE (en alta)
     ═══════════════════════════════════════════════════════════ -->
<div class="fk-big-title-wrap" aria-hidden="true">
    <div class="fk-big-title"><?= $isEn ? 'PORTFOLIO' : 'PORTFOLYO' ?></div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     STANDALONE FOOTER
     ═══════════════════════════════════════════════════════════ -->
<?php if ($isSubdomain): ?>
<footer class="fk-footer">
    <div class="fk-footer-left">© <?= date('Y') ?> Furkan Şen. <?= $isEn ? 'All rights reserved.' : 'Tüm hakları saklıdır.' ?></div>
    <div class="fk-footer-right">
        <a href="https://fezadan.org" target="_blank" rel="noopener">fezadan.org ↗</a>
    </div>
</footer>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════════════
     LIGHTBOX
     ═══════════════════════════════════════════════════════════ -->
<div id="fk-lightbox" class="fk-lb" aria-modal="true" role="dialog" aria-label="Görsel gösterici">
    <div class="fk-lb-bg" id="fk-lb-bg"></div>
    <div class="fk-lb-bar" id="fk-lb-bar" style="width:0%"></div>

    <!-- Top bar -->
    <div class="fk-lb-topbar">
        <div class="fk-lb-topbar-title" id="fk-lb-top-title"></div>
        <div class="fk-lb-topbar-right">
            <a href="#" id="fk-lb-open" class="fk-lb-open-btn" target="_blank" rel="noopener" title="<?= $t_zoom ?>">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                <?= $isEn ? 'Full size' : 'Tam boyut' ?>
            </a>
            <button class="fk-lb-close-btn" id="fk-lb-close" aria-label="Kapat">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                    <path d="M18 6L6 18M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Stage -->
    <div class="fk-lb-stage">
        <div class="fk-lb-img-wrap">
            <img id="fk-lb-img" src="" alt="">
            <div class="fk-lb-spinner" id="fk-lb-spinner"></div>
        </div>
    </div>

    <!-- Nav -->
    <button class="fk-lb-nav-btn fk-lb-nav-prev" id="fk-lb-prev" aria-label="Önceki">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 18l-6-6 6-6"/></svg>
    </button>
    <button class="fk-lb-nav-btn fk-lb-nav-next" id="fk-lb-next" aria-label="Sonraki">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M9 18l6-6-6-6"/></svg>
    </button>

    <!-- Dot strip -->
    <div class="fk-lb-dots" id="fk-lb-dots" aria-hidden="true"></div>

    <!-- Bottom info bar -->
    <div class="fk-lb-infobar">
        <div>
            <div class="fk-lb-info-title" id="fk-lb-title"></div>
            <div class="fk-lb-info-desc" id="fk-lb-desc"></div>
        </div>
        <div class="fk-lb-info-right">
            <div class="fk-lb-counter" id="fk-lb-counter"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {

    /* ── Image Fallbacks (Unsplash block protection) ── */
    const avatarImg = document.getElementById('fk-avatar-profile-img');
    const avatarContainer = document.getElementById('fk-avatar-container');
    if (avatarImg && avatarContainer) {
        avatarImg.addEventListener('error', () => {
            avatarImg.style.display = 'none';
            avatarContainer.classList.add('has-fallback');
            if (!avatarContainer.querySelector('.fk-avatar-fallback')) {
                const fallback = document.createElement('div');
                fallback.className = 'fk-avatar-fallback';
                fallback.textContent = 'FŞ';
                avatarContainer.appendChild(fallback);
            }
        });
        // Trigger error handler if already loaded as broken before script ran
        if (avatarImg.complete && avatarImg.naturalWidth === 0) {
            avatarImg.dispatchEvent(new Event('error'));
        }
    }

    const coverImg = document.getElementById('fk-cover-banner-img');
    if (coverImg) {
        coverImg.addEventListener('error', () => {
            coverImg.style.display = 'none';
        });
        if (coverImg.complete && coverImg.naturalWidth === 0) {
            coverImg.dispatchEvent(new Event('error'));
        }
    }

    /* ── Follow button interaction ── */
    const followBtn = document.getElementById('fk-follow-btn');
    if (followBtn) {
        let isFollowing = false;
        followBtn.addEventListener('click', () => {
            isFollowing = !isFollowing;
            if (isFollowing) {
                followBtn.innerHTML = 'Following';
                followBtn.style.borderColor = 'transparent';
                followBtn.style.background = 'rgba(163,29,29,0.1)';
                followBtn.style.color = 'var(--text-accent)';
            } else {
                followBtn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg> Follow';
                followBtn.style.borderColor = '';
                followBtn.style.background = '';
                followBtn.style.color = '';
            }
        });
    }

    /* ── IntersectionObserver: staggered card entrance ── */
    const allCards = Array.from(document.querySelectorAll('.fk-card'));
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.classList.add('in-view');
                io.unobserve(e.target);
            }
        });
    }, { threshold: 0.06, rootMargin: '0px 0px -30px 0px' });
    allCards.forEach(c => io.observe(c));

    /* ── Filters ── */
    const filterBtns = document.querySelectorAll('.fk-filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => { b.classList.remove('active'); b.setAttribute('aria-selected','false'); });
            btn.classList.add('active');
            btn.setAttribute('aria-selected','true');
            const f = btn.dataset.filter;
            allCards.forEach(c => c.classList.toggle('filtered-out', f !== 'all' && c.dataset.type !== f));
        });
    });

    /* ── Lightbox ── */
    const lb        = document.getElementById('fk-lightbox');
    const lbImg     = document.getElementById('fk-lb-img');
    const lbTitle   = document.getElementById('fk-lb-title');
    const lbDesc    = document.getElementById('fk-lb-desc');
    const lbTopTitle= document.getElementById('fk-lb-top-title');
    const lbCounter = document.getElementById('fk-lb-counter');
    const lbBar     = document.getElementById('fk-lb-bar');
    const lbDots    = document.getElementById('fk-lb-dots');
    const lbSpinner = document.getElementById('fk-lb-spinner');
    const lbOpen    = document.getElementById('fk-lb-open');
    const lbClose   = document.getElementById('fk-lb-close');
    const lbBg      = document.getElementById('fk-lb-bg');
    const lbPrev    = document.getElementById('fk-lb-prev');
    const lbNext    = document.getElementById('fk-lb-next');

    let activeIdx  = -1;
    let visible    = [];
    let touchX     = 0;

    function getVisible() {
        return allCards.filter(c => !c.classList.contains('filtered-out'));
    }

    function buildDots(total, current) {
        const max = 12;
        lbDots.innerHTML = '';
        if (total <= 1) return;
        const show = Math.min(total, max);
        for (let i = 0; i < show; i++) {
            const d = document.createElement('div');
            d.className = 'fk-lb-dot' + (i === current ? ' active' : '');
            lbDots.appendChild(d);
        }
    }

    function showItem(idx) {
        if (idx < 0 || idx >= visible.length) return;
        activeIdx = idx;

        const card   = visible[idx];
        const imgUrl = card.dataset.image;
        const title  = card.dataset.title;
        const desc   = card.dataset.description;

        lbImg.classList.remove('loaded');
        lbSpinner.style.display = 'flex';

        lbTitle.textContent    = title;
        lbDesc.textContent     = desc;
        lbTopTitle.textContent = title;
        lbOpen.href            = imgUrl;
        lbCounter.textContent  = (idx + 1) + ' / ' + visible.length;

        const pct = visible.length > 1 ? ((idx + 1) / visible.length) * 100 : 100;
        lbBar.style.width = pct + '%';

        buildDots(visible.length, idx);

        const img = new Image();
        img.onload = img.onerror = () => {
            lbImg.src = imgUrl;
            lbImg.alt = title;
            lbSpinner.style.display = 'none';
            requestAnimationFrame(() => lbImg.classList.add('loaded'));
        };
        img.src = imgUrl;

        lbPrev.style.display = visible.length > 1 ? '' : 'none';
        lbNext.style.display = visible.length > 1 ? '' : 'none';
    }

    function open(card) {
        visible   = getVisible();
        activeIdx = visible.indexOf(card);
        lb.classList.add('open');
        document.body.style.overflow = 'hidden';
        showItem(activeIdx);
        lbClose.focus();
    }

    function close() {
        lb.classList.remove('open');
        document.body.style.overflow = '';
        setTimeout(() => { lbImg.src = ''; lbImg.classList.remove('loaded'); lbBar.style.width = '0%'; }, 350);
        activeIdx = -1;
    }

    function prev() { showItem((activeIdx - 1 + visible.length) % visible.length); }
    function next() { showItem((activeIdx + 1) % visible.length); }

    /* Open triggers */
    allCards.forEach(card => {
        card.addEventListener('click', () => open(card));
        card.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(card); } });
    });

    /* Controls */
    lbClose.addEventListener('click', close);
    lbBg.addEventListener('click', close);
    lbPrev.addEventListener('click', prev);
    lbNext.addEventListener('click', next);

    document.addEventListener('keydown', e => {
        if (!lb.classList.contains('open')) return;
        if (e.key === 'Escape')     close();
        if (e.key === 'ArrowLeft')  prev();
        if (e.key === 'ArrowRight') next();
    });

    /* Touch swipe */
    lb.addEventListener('touchstart', e => { touchX = e.changedTouches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend',   e => {
        const dx = e.changedTouches[0].clientX - touchX;
        if (Math.abs(dx) > 50) dx < 0 ? next() : prev();
    }, { passive: true });

    // ─── Interactive Constellation & Fluid Ambient Glow Background ───
    class SpaceParticles {
        constructor(canvasId) {
            this.canvas = document.getElementById(canvasId);
            if (!this.canvas) return;
            this.ctx = this.canvas.getContext('2d');
            this.particles = [];
            this.mouse = { x: null, y: null, radius: 160 };
            this.particleCount = 50;
            this.maxDistance = 110;
            
            this.init();
            this.animate();
            
            window.addEventListener('resize', () => this.init());
            
            document.addEventListener('mousemove', (e) => {
                this.mouse.x = e.clientX;
                this.mouse.y = e.clientY;
            });
            document.addEventListener('mouseleave', () => {
                this.mouse.x = null;
                this.mouse.y = null;
            });
        }
        
        init() {
            this.canvas.width = window.innerWidth;
            this.canvas.height = window.innerHeight;
            this.particles = [];
            
            const area = this.canvas.width * this.canvas.height;
            this.particleCount = Math.min(Math.floor(area / 16000), 100);
            
            for (let i = 0; i < this.particleCount; i++) {
                this.particles.push(new Particle(this.canvas.width, this.canvas.height));
            }
        }
        
        animate() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark' || 
                           document.body.classList.contains('dark') || 
                           window.getComputedStyle(document.body).backgroundColor === 'rgb(10, 10, 10)' || 
                           document.querySelector('.portfolio-bg-animation').classList.contains('is-subdomain');
                           
            const r = isDark ? 255 : 109;
            const g = isDark ? 77 : 35;
            const b = isDark ? 77 : 35;
            
            const mr = isDark ? 232 : 163;
            const mg = isDark ? 213 : 29;
            const mb = isDark ? 176 : 29;

            const colorParticle = `rgba(${r}, ${g}, ${b}, ${isDark ? 0.35 : 0.22})`;

            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
            
            for (let i = 0; i < this.particles.length; i++) {
                this.particles[i].update(this.canvas.width, this.canvas.height);
                this.particles[i].draw(this.ctx, colorParticle);
                
                if (this.mouse.x !== null) {
                    const dx = this.mouse.x - this.particles[i].x;
                    const dy = this.mouse.y - this.particles[i].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist < this.mouse.radius) {
                        const alpha = (1 - dist / this.mouse.radius) * 0.16;
                        this.ctx.strokeStyle = `rgba(${mr}, ${mg}, ${mb}, ${alpha})`;
                        this.ctx.lineWidth = 0.9;
                        this.ctx.beginPath();
                        this.ctx.moveTo(this.mouse.x, this.mouse.y);
                        this.ctx.lineTo(this.particles[i].x, this.particles[i].y);
                        this.ctx.stroke();
                        
                        this.particles[i].x += dx * 0.005;
                        this.particles[i].y += dy * 0.005;
                    }
                }
                
                for (let j = i + 1; j < this.particles.length; j++) {
                    const dx = this.particles[i].x - this.particles[j].x;
                    const dy = this.particles[i].y - this.particles[j].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    
                    if (dist < this.maxDistance) {
                        const alpha = (1 - dist / this.maxDistance) * 0.07;
                        this.ctx.strokeStyle = `rgba(${r}, ${g}, ${b}, ${alpha})`;
                        this.ctx.lineWidth = 0.5;
                        this.ctx.beginPath();
                        this.ctx.moveTo(this.particles[i].x, this.particles[i].y);
                        this.ctx.lineTo(this.particles[j].x, this.particles[j].y);
                        this.ctx.stroke();
                    }
                }
            }
            requestAnimationFrame(() => this.animate());
        }
    }

    class Particle {
        constructor(w, h) {
            this.x = Math.random() * w;
            this.y = Math.random() * h;
            this.vx = (Math.random() - 0.5) * 0.4;
            this.vy = (Math.random() - 0.5) * 0.4;
            this.radius = Math.random() * 2 + 0.8;
        }
        
        update(w, h) {
            this.x += this.vx;
            this.y += this.vy;
            
            if (this.x < 0 || this.x > w) this.vx = -this.vx;
            if (this.y < 0 || this.y > h) this.vy = -this.vy;
        }
        
        draw(ctx, color) {
            ctx.fillStyle = color;
            ctx.beginPath();
            ctx.arc(this.x, this.y, this.radius, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    new SpaceParticles('canvas-bg');

});
</script>

<?php
// Standalone veya ana site layout — footer
if ($isSubdomain): ?>
</body>
</html>
<?php else:
    require_once ROOT . '/app/Views/inc/footer.php';
endif; ?>
