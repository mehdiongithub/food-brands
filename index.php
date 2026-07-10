<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FoodScope — Global Menu & Price Comparison</title>
<meta name="description" content="Compare food menus, prices, and deals from world-famous brands across countries. Discover what's available near you.">
<meta name="keywords" content="food menu, price comparison, KFC, McDonald's, Burger King, global food prices">
<meta property="og:title" content="FoodScope — Global Menu & Price Comparison">
<meta property="og:description" content="Compare food menus, prices, and deals from world-famous brands across countries.">
<meta property="og:type" content="website">
<meta property="og:url" content="https://foodscope.com">
<meta name="twitter:card" content="summary_large_image">
<link rel="canonical" href="https://foodscope.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800;900&family=DM+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
<script type="application/ld+json">
{"@context":"https://schema.org","@type":"Organization","name":"FoodScope","url":"https://foodscope.com","logo":"https://foodscope.com/logo.png","description":"Global food menu and price comparison platform","sameAs":["https://facebook.com/foodscope","https://twitter.com/foodscope"]}
</script>
<style>
/* ========== CSS Custom Properties ========== */
:root {
  --primary: #E85D04;
  --primary-dark: #C44900;
  --primary-light: #FFBA08;
  --secondary: #1B1B2F;
  --accent: #06D6A0;
  --accent-dark: #05B384;
  --danger: #EF4444;
  --success: #22C55E;
  --warning: #F59E0B;
  --bg: #FFFAF5;
  --bg-alt: #FFF0E5;
  --surface: #FFFFFF;
  --surface-alt: #F8F4F0;
  --text: #1A1A2E;
  --text-secondary: #5A5A6E;
  --muted: #9A9AAE;
  --border: #E8E0D8;
  --border-light: #F0EBE5;
  --shadow-sm: 0 1px 3px rgba(26,26,46,0.06);
  --shadow-md: 0 4px 16px rgba(26,26,46,0.08);
  --shadow-lg: 0 8px 32px rgba(26,26,46,0.12);
  --shadow-xl: 0 16px 48px rgba(26,26,46,0.16);
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 24px;
  --radius-full: 9999px;
  --font-display: 'Playfair Display', Georgia, serif;
  --font-body: 'DM Sans', -apple-system, sans-serif;
  --header-h: 72px;
  --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
[data-theme="dark"] {
  --bg: #0A0A10;
  --bg-alt: #12121C;
  --surface: #161622;
  --surface-alt: #1C1C2E;
  --text: #F0F0F5;
  --text-secondary: #B0B0C0;
  --muted: #6B6B80;
  --border: #2A2A3E;
  --border-light: #222236;
  --shadow-sm: 0 1px 3px rgba(0,0,0,0.2);
  --shadow-md: 0 4px 16px rgba(0,0,0,0.3);
  --shadow-lg: 0 8px 32px rgba(0,0,0,0.4);
  --shadow-xl: 0 16px 48px rgba(0,0,0,0.5);
}

/* ========== Base Reset ========== */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; scroll-padding-top: var(--header-h); }
body {
  font-family: var(--font-body);
  background: var(--bg);
  color: var(--text);
  line-height: 1.6;
  overflow-x: hidden;
  transition: background var(--transition), color var(--transition);
  -webkit-font-smoothing: antialiased;
}
a { color: var(--primary); text-decoration: none; transition: color var(--transition); }
a:hover { color: var(--primary-dark); }
img { max-width: 100%; height: auto; display: block; }
h1, h2, h3, h4, h5, h6 { font-family: var(--font-display); font-weight: 700; line-height: 1.2; color: var(--text); }
::selection { background: var(--primary); color: #fff; }
::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-track { background: var(--bg-alt); }
::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 4px; }
:focus-visible { outline: 2px solid var(--primary); outline-offset: 2px; }

/* ========== Preloader ========== */
#preloader {
  position: fixed; inset: 0; z-index: 99999;
  background: var(--secondary);
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  transition: opacity 0.5s, visibility 0.5s;
}
#preloader.hidden { opacity: 0; visibility: hidden; pointer-events: none; }
.preloader-logo {
  font-family: var(--font-display);
  font-size: 2.5rem; font-weight: 900; color: #fff;
  margin-bottom: 2rem; letter-spacing: -0.02em;
}
.preloader-logo span { color: var(--primary); }
.preloader-bar {
  width: 200px; height: 3px;
  background: rgba(255,255,255,0.1);
  border-radius: 3px; overflow: hidden;
}
.preloader-bar-inner {
  width: 0%; height: 100%;
  background: linear-gradient(90deg, var(--primary), var(--primary-light));
  border-radius: 3px;
  animation: preloaderFill 1.8s ease-in-out forwards;
}
@keyframes preloaderFill { to { width: 100%; } }
.preloader-dots { display: flex; gap: 8px; margin-top: 1.5rem; }
.preloader-dots span {
  width: 8px; height: 8px; border-radius: 50%;
  background: var(--primary);
  animation: dotBounce 1.4s ease-in-out infinite;
}
.preloader-dots span:nth-child(2) { animation-delay: 0.2s; }
.preloader-dots span:nth-child(3) { animation-delay: 0.4s; }
@keyframes dotBounce {
  0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
  40% { transform: scale(1); opacity: 1; }
}

/* ========== Country Change Overlay ========== */
#country-overlay {
  position: fixed; inset: 0; z-index: 99998;
  background: rgba(27,27,47,0.92);
  backdrop-filter: blur(20px);
  display: none; align-items: center; justify-content: center;
  flex-direction: column; gap: 1.5rem;
}
#country-overlay.active { display: flex; }
#country-overlay .flag-icon { font-size: 4rem; animation: flagPop 0.5s ease; }
#country-overlay .country-name { font-family: var(--font-display); font-size: 1.5rem; color: #fff; }
#country-overlay .loader-spinner {
  width: 40px; height: 40px; border: 3px solid rgba(255,255,255,0.15);
  border-top-color: var(--primary); border-radius: 50%;
  animation: spin 0.8s linear infinite;
}
@keyframes flagPop { 0% { transform: scale(0); } 60% { transform: scale(1.2); } 100% { transform: scale(1); } }
@keyframes spin { to { transform: rotate(360deg); } }

/* ========== Skeleton Loader ========== */
.skeleton { background: linear-gradient(90deg, var(--surface-alt) 25%, var(--border-light) 50%, var(--surface-alt) 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; border-radius: var(--radius-sm); }
@keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
.skeleton-card { height: 360px; border-radius: var(--radius-md); }
.skeleton-text { height: 16px; margin-bottom: 8px; }
.skeleton-text.short { width: 60%; }
.skeleton-title { height: 28px; width: 70%; margin-bottom: 12px; }
.skeleton-img { height: 200px; border-radius: var(--radius-md) var(--radius-md) 0 0; }

/* ========== Header ========== */
#main-header {
  position: fixed; top: 0; left: 0; right: 0;
  height: var(--header-h); z-index: 1000;
  background: rgba(255,250,245,0.8);
  backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border-light);
  transition: all var(--transition);
}
[data-theme="dark"] #main-header { background: rgba(10,10,16,0.85); }
#main-header.scrolled { box-shadow: var(--shadow-md); }
.header-inner {
  max-width: 1400px; margin: 0 auto;
  height: 100%; padding: 0 1.5rem;
  display: flex; align-items: center; gap: 1.5rem;
}
.header-logo {
  font-family: var(--font-display);
  font-size: 1.6rem; font-weight: 900;
  color: var(--text); white-space: nowrap;
  cursor: pointer; flex-shrink: 0;
}
.header-logo span { color: var(--primary); }
.header-nav { display: flex; align-items: center; gap: 0.25rem; flex: 1; justify-content: center; }
.header-nav a {
  padding: 0.5rem 0.85rem; font-size: 0.9rem; font-weight: 500;
  color: var(--text-secondary); border-radius: var(--radius-sm);
  transition: all var(--transition); white-space: nowrap;
}
.header-nav a:hover, .header-nav a.active { color: var(--primary); background: rgba(232,93,4,0.08); }
.header-actions { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }

/* Country Selector */
.country-selector {
  position: relative; display: flex; align-items: center; gap: 0.4rem;
  padding: 0.4rem 0.75rem; border-radius: var(--radius-full);
  background: var(--surface); border: 1px solid var(--border);
  cursor: pointer; font-size: 0.85rem; font-weight: 500;
  color: var(--text); transition: all var(--transition);
  white-space: nowrap;
}
.country-selector:hover { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(232,93,4,0.1); }
.country-selector .flag { font-size: 1.1rem; line-height: 1; }
.country-selector .chevron { font-size: 0.65rem; color: var(--muted); transition: transform var(--transition); }
.country-dropdown {
  position: absolute; top: calc(100% + 8px); right: 0;
  min-width: 220px; background: var(--surface);
  border: 1px solid var(--border); border-radius: var(--radius-md);
  box-shadow: var(--shadow-xl); padding: 0.5rem;
  display: none; z-index: 100;
}
.country-dropdown.show { display: block; animation: dropIn 0.2s ease; }
@keyframes dropIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
.country-dropdown-item {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 0.6rem 0.75rem; border-radius: var(--radius-sm);
  cursor: pointer; transition: background var(--transition);
  font-size: 0.9rem; color: var(--text);
}
.country-dropdown-item:hover { background: var(--bg-alt); }
.country-dropdown-item.active { background: rgba(232,93,4,0.1); color: var(--primary); font-weight: 600; }
.country-dropdown-item .flag { font-size: 1.3rem; }
.country-dropdown-item .name { flex: 1; }
.country-dropdown-item .curr { font-size: 0.8rem; color: var(--muted); }

/* Search */
.header-search-btn {
  width: 38px; height: 38px; border-radius: var(--radius-full);
  border: 1px solid var(--border); background: var(--surface);
  color: var(--text-secondary); display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all var(--transition); font-size: 0.9rem;
}
.header-search-btn:hover { border-color: var(--primary); color: var(--primary); }
#search-overlay {
  position: fixed; inset: 0; z-index: 9999;
  background: rgba(27,27,47,0.6);
  backdrop-filter: blur(10px);
  display: none; align-items: flex-start; justify-content: center;
  padding-top: 120px;
}
#search-overlay.active { display: flex; animation: fadeIn 0.2s ease; }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
.search-container {
  width: 90%; max-width: 680px; background: var(--surface);
  border-radius: var(--radius-lg); box-shadow: var(--shadow-xl);
  overflow: hidden; animation: slideDown 0.3s ease;
}
@keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
.search-input-wrap {
  display: flex; align-items: center; padding: 1rem 1.25rem;
  border-bottom: 1px solid var(--border);
}
.search-input-wrap i { color: var(--muted); font-size: 1.1rem; margin-right: 0.75rem; }
.search-input-wrap input {
  flex: 1; border: none; outline: none; background: none;
  font-size: 1.1rem; font-family: var(--font-body); color: var(--text);
}
.search-input-wrap input::placeholder { color: var(--muted); }
.search-close-btn {
  width: 32px; height: 32px; border-radius: var(--radius-full);
  border: none; background: var(--bg-alt); color: var(--muted);
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: all var(--transition);
}
.search-close-btn:hover { background: var(--danger); color: #fff; }
.search-suggestions { max-height: 400px; overflow-y: auto; padding: 0.5rem; }
.search-suggestion-group { padding: 0.5rem 0.75rem; }
.search-suggestion-group-title {
  font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.08em; color: var(--muted); margin-bottom: 0.4rem;
}
.search-suggestion-item {
  display: flex; align-items: center; gap: 0.75rem;
  padding: 0.55rem 0.5rem; border-radius: var(--radius-sm);
  cursor: pointer; transition: background var(--transition);
}
.search-suggestion-item:hover { background: var(--bg-alt); }
.search-suggestion-item img { width: 36px; height: 36px; border-radius: var(--radius-sm); object-fit: cover; }
.search-suggestion-item .info { flex: 1; }
.search-suggestion-item .info .name { font-size: 0.9rem; font-weight: 500; color: var(--text); }
.search-suggestion-item .info .sub { font-size: 0.78rem; color: var(--muted); }
.search-empty { padding: 2rem; text-align: center; color: var(--muted); font-size: 0.9rem; }

/* Theme Toggle */
.theme-toggle {
  width: 38px; height: 38px; border-radius: var(--radius-full);
  border: 1px solid var(--border); background: var(--surface);
  color: var(--text-secondary); display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all var(--transition); font-size: 0.9rem;
}
.theme-toggle:hover { border-color: var(--primary); color: var(--primary); }

/* Mobile Menu Button */
.mobile-menu-btn {
  display: none; width: 38px; height: 38px; border-radius: var(--radius-full);
  border: 1px solid var(--border); background: var(--surface);
  color: var(--text-secondary); align-items: center; justify-content: center;
  cursor: pointer; transition: all var(--transition); font-size: 1rem;
}

/* ========== Hero Section ========== */
.hero-section {
  min-height: 100vh; position: relative; overflow: hidden;
  display: flex; align-items: center; justify-content: center;
  padding: calc(var(--header-h) + 2rem) 1.5rem 3rem;
}
.hero-bg {
  position: absolute; inset: 0; z-index: 0;
  background: url('https://picsum.photos/seed/foodhero9/1920/1080.jpg') center/cover no-repeat;
}
.hero-bg::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(27,27,47,0.88) 0%, rgba(27,27,47,0.7) 50%, rgba(232,93,4,0.3) 100%);
}
.hero-particles {
  position: absolute; inset: 0; z-index: 1; pointer-events: none; overflow: hidden;
}
.hero-particle {
  position: absolute; border-radius: 50%; opacity: 0.15;
  animation: floatParticle linear infinite;
}
@keyframes floatParticle {
  0% { transform: translateY(100vh) rotate(0deg); }
  100% { transform: translateY(-100px) rotate(360deg); }
}
.hero-content { position: relative; z-index: 2; text-align: center; max-width: 800px; width: 100%; }
.hero-badge {
  display: inline-flex; align-items: center; gap: 0.5rem;
  padding: 0.4rem 1rem; border-radius: var(--radius-full);
  background: rgba(232,93,4,0.2); border: 1px solid rgba(232,93,4,0.3);
  color: var(--primary-light); font-size: 0.8rem; font-weight: 600;
  margin-bottom: 1.5rem; backdrop-filter: blur(10px);
}
.hero-badge i { font-size: 0.7rem; }
.hero-title {
  font-size: clamp(2.5rem, 6vw, 4.5rem); font-weight: 900;
  color: #fff; margin-bottom: 1rem; letter-spacing: -0.03em;
  line-height: 1.05;
}
.hero-title .highlight {
  background: linear-gradient(135deg, var(--primary), var(--primary-light));
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
}
.hero-subtitle {
  font-size: clamp(1rem, 2vw, 1.2rem); color: rgba(255,255,255,0.7);
  margin-bottom: 2.5rem; max-width: 600px; margin-left: auto; margin-right: auto;
  font-weight: 300; line-height: 1.7;
}
.hero-search-box {
  background: rgba(255,255,255,0.12); backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,0.15); border-radius: var(--radius-lg);
  padding: 1.25rem; margin-bottom: 1.5rem;
}
.hero-search-row { display: flex; gap: 0.75rem; margin-bottom: 0.75rem; }
.hero-search-input {
  flex: 1; padding: 0.85rem 1.2rem; border-radius: var(--radius-md);
  border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.1);
  color: #fff; font-size: 1rem; font-family: var(--font-body); outline: none;
  transition: all var(--transition);
}
.hero-search-input::placeholder { color: rgba(255,255,255,0.5); }
.hero-search-input:focus { border-color: var(--primary); background: rgba(255,255,255,0.15); }
.hero-search-btn-main {
  padding: 0.85rem 2rem; border-radius: var(--radius-md); border: none;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: #fff; font-weight: 600; font-size: 0.95rem; cursor: pointer;
  font-family: var(--font-body); transition: all var(--transition);
  display: flex; align-items: center; gap: 0.5rem; white-space: nowrap;
}
.hero-search-btn-main:hover { transform: translateY(-1px); box-shadow: 0 4px 20px rgba(232,93,4,0.4); }
.hero-filters { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.hero-filter-select {
  padding: 0.55rem 1rem; border-radius: var(--radius-full);
  border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.08);
  color: rgba(255,255,255,0.8); font-size: 0.85rem; font-family: var(--font-body);
  cursor: pointer; outline: none; transition: all var(--transition);
  appearance: none; -webkit-appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='rgba(255,255,255,0.5)'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 0.75rem center;
  padding-right: 2rem;
}
.hero-filter-select option { background: var(--secondary); color: #fff; }
.hero-filter-select:focus { border-color: var(--primary); }
.hero-popular { display: flex; align-items: center; justify-content: center; gap: 0.5rem; flex-wrap: wrap; }
.hero-popular span { color: rgba(255,255,255,0.5); font-size: 0.82rem; }
.hero-popular-tag {
  padding: 0.3rem 0.75rem; border-radius: var(--radius-full);
  background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1);
  color: rgba(255,255,255,0.7); font-size: 0.78rem; cursor: pointer;
  transition: all var(--transition);
}
.hero-popular-tag:hover { background: rgba(232,93,4,0.2); border-color: rgba(232,93,4,0.3); color: var(--primary-light); }
.hero-scroll-indicator {
  position: absolute; bottom: 2rem; left: 50%; transform: translateX(-50%);
  z-index: 2; color: rgba(255,255,255,0.5); text-align: center;
  animation: bounceDown 2s ease infinite;
}
.hero-scroll-indicator i { font-size: 1.2rem; display: block; margin-top: 0.3rem; }
@keyframes bounceDown { 0%, 100% { transform: translateX(-50%) translateY(0); } 50% { transform: translateX(-50%) translateY(8px); } }

/* ========== Section Styles ========== */
.section-padding { padding: 5rem 0; }
.section-header { text-align: center; margin-bottom: 3rem; }
.section-header .section-label {
  display: inline-flex; align-items: center; gap: 0.5rem;
  font-size: 0.78rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.1em; color: var(--primary); margin-bottom: 0.75rem;
}
.section-header .section-label::before, .section-header .section-label::after {
  content: ''; width: 24px; height: 2px; background: var(--primary); opacity: 0.4;
}
.section-title {
  font-size: clamp(1.8rem, 4vw, 2.8rem); font-weight: 800;
  margin-bottom: 0.75rem; letter-spacing: -0.02em;
}
.section-desc { font-size: 1.05rem; color: var(--text-secondary); max-width: 600px; margin: 0 auto; }

/* ========== Featured Brand Section ========== */
.featured-brand-section {
  padding: 4rem 0; position: relative; overflow: hidden;
}
.featured-brand-card {
  background: var(--surface); border-radius: var(--radius-lg);
  overflow: hidden; box-shadow: var(--shadow-md);
  transition: all 0.4s ease; border: 1px solid var(--border-light);
}
.featured-brand-card:hover { box-shadow: var(--shadow-xl); transform: translateY(-4px); }
.fb-cover {
  height: 180px; position: relative; overflow: hidden;
}
.fb-cover img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
.featured-brand-card:hover .fb-cover img { transform: scale(1.05); }
.fb-cover::after {
  content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 60%;
  background: linear-gradient(transparent, rgba(0,0,0,0.5));
}
.fb-logo {
  position: absolute; bottom: -25px; left: 1.25rem; z-index: 2;
  width: 64px; height: 64px; border-radius: var(--radius-md);
  background: var(--surface); padding: 8px; box-shadow: var(--shadow-md);
  display: flex; align-items: center; justify-content: center;
}
.fb-logo img { width: 100%; height: 100%; object-fit: contain; }
.fb-body { padding: 2rem 1.25rem 1.25rem; }
.fb-name { font-family: var(--font-display); font-size: 1.35rem; font-weight: 700; margin-bottom: 0.4rem; }
.fb-desc { font-size: 0.88rem; color: var(--text-secondary); margin-bottom: 0.75rem; line-height: 1.5; }
.fb-countries { display: flex; gap: 0.25rem; margin-bottom: 1rem; flex-wrap: wrap; }
.fb-country-flag { font-size: 1.1rem; }
.fb-categories .swiper { padding-bottom: 1.5rem; }
.fb-cat-pill {
  padding: 0.35rem 0.85rem; border-radius: var(--radius-full);
  background: var(--bg-alt); border: 1px solid var(--border);
  font-size: 0.78rem; font-weight: 500; color: var(--text-secondary);
  white-space: nowrap; cursor: pointer; transition: all var(--transition);
}
.fb-cat-pill:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
.fb-view-all {
  display: flex; align-items: center; gap: 0.5rem;
  font-size: 0.88rem; font-weight: 600; color: var(--primary);
  margin-top: 0.75rem; cursor: pointer; transition: gap var(--transition);
}
.fb-view-all:hover { gap: 0.75rem; }

/* ========== Product Cards ========== */
.product-card {
  background: var(--surface); border-radius: var(--radius-md);
  overflow: hidden; box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-light);
  transition: all 0.4s ease; position: relative;
}
.product-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-6px); }
.pc-image { position: relative; height: 200px; overflow: hidden; background: var(--bg-alt); }
.pc-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
.product-card:hover .pc-image img { transform: scale(1.08); }
.pc-discount-badge {
  position: absolute; top: 0.75rem; left: 0.75rem;
  padding: 0.25rem 0.6rem; border-radius: var(--radius-full);
  background: var(--danger); color: #fff; font-size: 0.72rem; font-weight: 700;
  z-index: 2;
}
.pc-actions {
  position: absolute; top: 0.75rem; right: 0.75rem; z-index: 2;
  display: flex; flex-direction: column; gap: 0.4rem;
  opacity: 0; transform: translateX(10px);
  transition: all 0.3s ease;
}
.product-card:hover .pc-actions { opacity: 1; transform: translateX(0); }
.pc-action-btn {
  width: 32px; height: 32px; border-radius: var(--radius-full);
  background: var(--surface); border: 1px solid var(--border);
  color: var(--text-secondary); display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 0.75rem; transition: all var(--transition);
  box-shadow: var(--shadow-sm);
}
.pc-action-btn:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
.pc-action-btn.favorited { color: var(--danger); }
.pc-body { padding: 1rem; }
.pc-brand {
  display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;
}
.pc-brand img { width: 20px; height: 20px; object-fit: contain; }
.pc-brand span { font-size: 0.75rem; color: var(--muted); font-weight: 500; }
.pc-name { font-family: var(--font-display); font-size: 1.05rem; font-weight: 700; margin-bottom: 0.25rem; line-height: 1.3; }
.pc-category { font-size: 0.78rem; color: var(--muted); margin-bottom: 0.75rem; }
.pc-meta { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.75rem; }
.pc-meta span { font-size: 0.78rem; color: var(--text-secondary); display: flex; align-items: center; gap: 0.3rem; }
.pc-meta i { color: var(--primary); font-size: 0.7rem; }
.pc-footer { display: flex; align-items: center; justify-content: space-between; padding-top: 0.75rem; border-top: 1px solid var(--border-light); }
.pc-prices { display: flex; align-items: baseline; gap: 0.5rem; }
.pc-original-price { font-size: 0.82rem; color: var(--muted); text-decoration: line-through; }
.pc-current-price { font-size: 1.2rem; font-weight: 700; color: var(--primary); }
.pc-view-btn {
  padding: 0.4rem 0.85rem; border-radius: var(--radius-full);
  background: var(--primary); color: #fff; border: none;
  font-size: 0.78rem; font-weight: 600; cursor: pointer;
  font-family: var(--font-body); transition: all var(--transition);
}
.pc-view-btn:hover { background: var(--primary-dark); transform: scale(1.05); }

/* ========== Category Cards ========== */
.category-card {
  position: relative; border-radius: var(--radius-lg);
  overflow: hidden; height: 220px; cursor: pointer;
  box-shadow: var(--shadow-md); transition: all 0.4s ease;
}
.category-card:hover { box-shadow: var(--shadow-xl); transform: translateY(-6px); }
.category-card img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
.category-card:hover img { transform: scale(1.1); }
.category-card::after {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(transparent 30%, rgba(27,27,47,0.85));
  transition: background var(--transition);
}
.category-card:hover::after { background: linear-gradient(transparent 20%, rgba(232,93,4,0.7)); }
.cc-content {
  position: absolute; bottom: 0; left: 0; right: 0; z-index: 2;
  padding: 1.25rem;
}
.cc-name { font-family: var(--font-display); font-size: 1.25rem; font-weight: 700; color: #fff; margin-bottom: 0.25rem; }
.cc-info { font-size: 0.82rem; color: rgba(255,255,255,0.8); }
.cc-discount {
  position: absolute; top: 0.75rem; right: 0.75rem; z-index: 2;
  padding: 0.3rem 0.7rem; border-radius: var(--radius-full);
  background: var(--danger); color: #fff; font-size: 0.72rem; font-weight: 700;
}

/* ========== Brand Cards (All Brands) ========== */
.brand-card {
  background: var(--surface); border-radius: var(--radius-md);
  overflow: hidden; border: 1px solid var(--border-light);
  box-shadow: var(--shadow-sm); transition: all 0.4s ease; cursor: pointer;
}
.brand-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); }
.bc-cover { height: 140px; overflow: hidden; position: relative; }
.bc-cover img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
.brand-card:hover .bc-cover img { transform: scale(1.05); }
.bc-logo {
  position: absolute; bottom: -22px; left: 1rem; z-index: 2;
  width: 50px; height: 50px; border-radius: var(--radius-sm);
  background: var(--surface); padding: 6px; box-shadow: var(--shadow-md);
}
.bc-logo img { width: 100%; height: 100%; object-fit: contain; }
.bc-body { padding: 1.75rem 1rem 1rem; }
.bc-name { font-family: var(--font-display); font-size: 1.15rem; font-weight: 700; margin-bottom: 0.3rem; }
.bc-desc { font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 0.75rem; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.bc-stats { display: flex; gap: 1rem; margin-bottom: 0.75rem; }
.bc-stat { font-size: 0.78rem; color: var(--muted); }
.bc-stat strong { color: var(--text); font-weight: 600; }
.bc-footer { display: flex; align-items: center; justify-content: space-between; padding-top: 0.75rem; border-top: 1px solid var(--border-light); }
.bc-price { font-size: 0.85rem; color: var(--text-secondary); }
.bc-price strong { color: var(--primary); font-weight: 700; }
.bc-btn {
  padding: 0.4rem 1rem; border-radius: var(--radius-full);
  background: var(--primary); color: #fff; border: none;
  font-size: 0.8rem; font-weight: 600; cursor: pointer;
  font-family: var(--font-body); transition: all var(--transition);
}
.bc-btn:hover { background: var(--primary-dark); }

/* ========== Swiper Overrides ========== */
.swiper-button-next, .swiper-button-prev {
  width: 40px; height: 40px; border-radius: var(--radius-full);
  background: var(--surface); border: 1px solid var(--border);
  box-shadow: var(--shadow-md); transition: all var(--transition);
}
.swiper-button-next::after, .swiper-button-prev::after { font-size: 0.8rem; font-weight: 700; color: var(--text); }
.swiper-button-next:hover, .swiper-button-prev:hover { background: var(--primary); border-color: var(--primary); }
.swiper-button-next:hover::after, .swiper-button-prev:hover::after { color: #fff; }
.swiper-pagination-bullet { background: var(--muted); opacity: 0.4; }
.swiper-pagination-bullet-active { background: var(--primary); opacity: 1; width: 24px; border-radius: 4px; }

/* ========== Offer Cards ========== */
.offer-card {
  background: var(--surface); border-radius: var(--radius-md);
  overflow: hidden; border: 1px solid var(--border-light);
  box-shadow: var(--shadow-sm); transition: all 0.4s ease;
  position: relative;
}
.offer-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); }
.offer-card::before {
  content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px;
  background: linear-gradient(90deg, var(--primary), var(--primary-light));
}
.oc-body { padding: 1.25rem; }
.oc-brand { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; }
.oc-brand img { width: 28px; height: 28px; object-fit: contain; }
.oc-brand span { font-size: 0.82rem; font-weight: 600; color: var(--text); }
.oc-title { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1.3; }
.oc-desc { font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem; line-height: 1.5; }
.oc-footer { display: flex; align-items: center; justify-content: space-between; }
.oc-discount { font-size: 1.3rem; font-weight: 800; color: var(--danger); }
.oc-code {
  padding: 0.3rem 0.75rem; border-radius: var(--radius-sm);
  background: var(--bg-alt); border: 1px dashed var(--border);
  font-size: 0.82rem; font-weight: 700; color: var(--primary);
  font-family: monospace;
}

/* ========== Testimonials ========== */
.testimonial-card {
  background: var(--surface); border-radius: var(--radius-md);
  padding: 1.75rem; border: 1px solid var(--border-light);
  box-shadow: var(--shadow-sm); transition: all 0.4s ease; height: 100%;
}
.testimonial-card:hover { box-shadow: var(--shadow-md); }
.tc-stars { color: var(--primary-light); font-size: 0.85rem; margin-bottom: 1rem; }
.tc-text { font-size: 0.95rem; color: var(--text-secondary); line-height: 1.7; margin-bottom: 1.25rem; font-style: italic; }
.tc-author { display: flex; align-items: center; gap: 0.75rem; }
.tc-avatar { width: 44px; height: 44px; border-radius: var(--radius-full); object-fit: cover; }
.tc-name { font-weight: 600; font-size: 0.9rem; }
.tc-role { font-size: 0.78rem; color: var(--muted); }

/* ========== FAQ ========== */
.faq-item { border: 1px solid var(--border); border-radius: var(--radius-md); margin-bottom: 0.75rem; overflow: hidden; background: var(--surface); transition: all var(--transition); }
.faq-item.active { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(232,93,4,0.1); }
.faq-question {
  padding: 1rem 1.25rem; cursor: pointer; display: flex;
  align-items: center; justify-content: space-between; gap: 1rem;
  font-weight: 600; font-size: 0.95rem; color: var(--text);
  transition: color var(--transition); background: none; border: none; width: 100%;
  font-family: var(--font-body); text-align: left;
}
.faq-question:hover { color: var(--primary); }
.faq-question i { transition: transform var(--transition); font-size: 0.8rem; color: var(--muted); flex-shrink: 0; }
.faq-item.active .faq-question i { transform: rotate(180deg); color: var(--primary); }
.faq-answer { max-height: 0; overflow: hidden; transition: max-height 0.4s ease; }
.faq-answer-inner { padding: 0 1.25rem 1.25rem; font-size: 0.9rem; color: var(--text-secondary); line-height: 1.7; }

/* ========== Newsletter ========== */
.newsletter-section {
  background: linear-gradient(135deg, var(--secondary) 0%, #2D1B4E 100%);
  padding: 4rem 0; position: relative; overflow: hidden;
}
.newsletter-section::before {
  content: ''; position: absolute; top: -50%; right: -20%; width: 500px; height: 500px;
  border-radius: 50%; background: rgba(232,93,4,0.15); filter: blur(80px);
}
.newsletter-content { position: relative; z-index: 2; text-align: center; max-width: 560px; margin: 0 auto; }
.newsletter-title { font-size: 2rem; font-weight: 800; color: #fff; margin-bottom: 0.75rem; }
.newsletter-desc { color: rgba(255,255,255,0.65); margin-bottom: 2rem; font-size: 1rem; }
.newsletter-form { display: flex; gap: 0.75rem; max-width: 460px; margin: 0 auto; }
.newsletter-input {
  flex: 1; padding: 0.85rem 1.25rem; border-radius: var(--radius-full);
  border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.1);
  color: #fff; font-size: 0.95rem; font-family: var(--font-body); outline: none;
  transition: all var(--transition);
}
.newsletter-input::placeholder { color: rgba(255,255,255,0.4); }
.newsletter-input:focus { border-color: var(--primary); background: rgba(255,255,255,0.15); }
.newsletter-btn {
  padding: 0.85rem 1.75rem; border-radius: var(--radius-full); border: none;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: #fff; font-weight: 600; font-size: 0.9rem; cursor: pointer;
  font-family: var(--font-body); transition: all var(--transition); white-space: nowrap;
}
.newsletter-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(232,93,4,0.4); }

/* ========== Why Choose Us ========== */
.wcu-card {
  text-align: center; padding: 2rem 1.5rem; border-radius: var(--radius-md);
  background: var(--surface); border: 1px solid var(--border-light);
  box-shadow: var(--shadow-sm); transition: all 0.4s ease; height: 100%;
}
.wcu-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); border-color: var(--primary); }
.wcu-icon {
  width: 64px; height: 64px; border-radius: var(--radius-md);
  background: linear-gradient(135deg, rgba(232,93,4,0.1), rgba(232,93,4,0.05));
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 1.25rem; font-size: 1.5rem; color: var(--primary);
}
.wcu-title { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem; }
.wcu-desc { font-size: 0.88rem; color: var(--text-secondary); line-height: 1.6; }

/* ========== Page Banner ========== */
.page-banner {
  padding: calc(var(--header-h) + 3rem) 0 3rem;
  background: linear-gradient(135deg, var(--secondary) 0%, #2D1B4E 100%);
  position: relative; overflow: hidden;
}
.page-banner::before {
  content: ''; position: absolute; bottom: -50%; left: -10%; width: 400px; height: 400px;
  border-radius: 50%; background: rgba(232,93,4,0.1); filter: blur(60px);
}
.page-banner-content { position: relative; z-index: 2; }
.page-banner .breadcrumb { margin-bottom: 0.75rem; }
.page-banner .breadcrumb-item a { color: rgba(255,255,255,0.6); font-size: 0.85rem; }
.page-banner .breadcrumb-item.active { color: var(--primary-light); font-size: 0.85rem; }
.page-banner .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,0.3); }
.page-banner h1 { color: #fff; font-size: clamp(1.8rem, 4vw, 2.5rem); margin-bottom: 0.5rem; }
.page-banner p { color: rgba(255,255,255,0.6); font-size: 1rem; margin: 0; }

/* ========== Filter Sidebar ========== */
.filter-panel {
  background: var(--surface); border-radius: var(--radius-md);
  border: 1px solid var(--border-light); padding: 1.5rem;
  position: sticky; top: calc(var(--header-h) + 1rem);
}
.filter-title { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; margin-bottom: 1.25rem; display: flex; align-items: center; justify-content: space-between; }
.filter-group { margin-bottom: 1.25rem; }
.filter-group-title { font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--muted); margin-bottom: 0.75rem; }
.filter-option {
  display: flex; align-items: center; gap: 0.6rem; padding: 0.4rem 0;
  cursor: pointer; font-size: 0.9rem; color: var(--text-secondary);
  transition: color var(--transition);
}
.filter-option:hover { color: var(--primary); }
.filter-option input[type="checkbox"] {
  width: 16px; height: 16px; accent-color: var(--primary);
  cursor: pointer; flex-shrink: 0;
}
.filter-option .count { margin-left: auto; font-size: 0.78rem; color: var(--muted); }
.price-range-wrap { padding: 0.5rem 0; }
.price-range-wrap input[type="range"] { width: 100%; accent-color: var(--primary); }
.price-range-labels { display: flex; justify-content: space-between; font-size: 0.8rem; color: var(--muted); margin-top: 0.5rem; }
.filter-apply-btn {
  width: 100%; padding: 0.7rem; border-radius: var(--radius-md);
  background: var(--primary); color: #fff; border: none;
  font-weight: 600; font-size: 0.9rem; cursor: pointer;
  font-family: var(--font-body); transition: all var(--transition);
}
.filter-apply-btn:hover { background: var(--primary-dark); }
.filter-reset-btn {
  width: 100%; padding: 0.6rem; border-radius: var(--radius-md);
  background: none; border: 1px solid var(--border);
  color: var(--text-secondary); font-weight: 500; font-size: 0.85rem;
  cursor: pointer; font-family: var(--font-body); transition: all var(--transition);
  margin-top: 0.5rem;
}
.filter-reset-btn:hover { border-color: var(--danger); color: var(--danger); }

/* ========== Toolbar ========== */
.toolbar {
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;
  padding: 1rem 1.25rem; background: var(--surface);
  border-radius: var(--radius-md); border: 1px solid var(--border-light);
}
.toolbar-left { display: flex; align-items: center; gap: 0.75rem; }
.toolbar-count { font-size: 0.9rem; color: var(--text-secondary); }
.toolbar-count strong { color: var(--text); }
.toolbar-right { display: flex; align-items: center; gap: 0.75rem; }
.toolbar-sort select {
  padding: 0.45rem 2rem 0.45rem 0.75rem; border-radius: var(--radius-sm);
  border: 1px solid var(--border); background: var(--surface);
  font-size: 0.85rem; font-family: var(--font-body); color: var(--text);
  outline: none; cursor: pointer; appearance: none; -webkit-appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6'%3E%3Cpath d='M0 0l5 6 5-6z' fill='%239A9AAE'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right 0.6rem center;
}
.toolbar-view-btn {
  width: 34px; height: 34px; border-radius: var(--radius-sm);
  border: 1px solid var(--border); background: var(--surface);
  color: var(--muted); display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all var(--transition); font-size: 0.85rem;
}
.toolbar-view-btn.active, .toolbar-view-btn:hover { background: var(--primary); color: #fff; border-color: var(--primary); }

/* ========== Product List View ========== */
.products-list .product-card {
  display: flex; flex-direction: row;
}
.products-list .pc-image { width: 240px; min-width: 240px; height: auto; min-height: 180px; }
.products-list .pc-body { flex: 1; display: flex; flex-direction: column; justify-content: center; }

/* ========== Product Detail ========== */
.pd-gallery { position: relative; border-radius: var(--radius-lg); overflow: hidden; background: var(--bg-alt); }
.pd-main-image { height: 420px; overflow: hidden; cursor: zoom-in; }
.pd-main-image img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
.pd-main-image:hover img { transform: scale(1.5); }
.pd-thumbs { display: flex; gap: 0.5rem; padding: 0.75rem; }
.pd-thumb {
  width: 72px; height: 72px; border-radius: var(--radius-sm);
  overflow: hidden; cursor: pointer; border: 2px solid transparent;
  transition: border-color var(--transition); opacity: 0.6;
}
.pd-thumb.active, .pd-thumb:hover { border-color: var(--primary); opacity: 1; }
.pd-thumb img { width: 100%; height: 100%; object-fit: cover; }
.pd-info { padding: 1.5rem 0; }
.pd-brand-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
.pd-brand-row img { width: 36px; height: 36px; object-fit: contain; }
.pd-brand-row span { font-size: 0.9rem; color: var(--primary); font-weight: 600; }
.pd-name { font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; letter-spacing: -0.02em; }
.pd-category-tag { display: inline-block; padding: 0.3rem 0.75rem; border-radius: var(--radius-full); background: var(--bg-alt); font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1rem; }
.pd-desc { font-size: 0.95rem; color: var(--text-secondary); line-height: 1.7; margin-bottom: 1.5rem; }
.pd-pricing {
  background: var(--bg-alt); border-radius: var(--radius-md);
  padding: 1.25rem; margin-bottom: 1.5rem;
}
.pd-pricing-row { display: flex; align-items: baseline; gap: 0.75rem; margin-bottom: 0.5rem; }
.pd-original { font-size: 1.2rem; color: var(--muted); text-decoration: line-through; }
.pd-current { font-size: 2rem; font-weight: 800; color: var(--primary); }
.pd-save { font-size: 0.85rem; color: var(--success); font-weight: 600; }
.pd-details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-bottom: 1.5rem; }
.pd-detail-item {
  display: flex; align-items: center; gap: 0.6rem;
  padding: 0.6rem 0.75rem; border-radius: var(--radius-sm);
  background: var(--surface); border: 1px solid var(--border-light);
  font-size: 0.85rem;
}
.pd-detail-item i { color: var(--primary); width: 16px; text-align: center; }
.pd-detail-item .label { color: var(--muted); }
.pd-detail-item .value { font-weight: 600; margin-left: auto; }
.pd-share { display: flex; gap: 0.5rem; margin-top: 1rem; }
.pd-share-btn {
  width: 40px; height: 40px; border-radius: var(--radius-full);
  border: 1px solid var(--border); background: var(--surface);
  color: var(--text-secondary); display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: all var(--transition); font-size: 0.9rem;
}
.pd-share-btn:hover { background: var(--primary); color: #fff; border-color: var(--primary); }

/* ========== Nutrition Table ========== */
.nutrition-table { width: 100%; border-collapse: collapse; }
.nutrition-table th, .nutrition-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid var(--border-light); font-size: 0.9rem; }
.nutrition-table th { font-weight: 600; color: var(--muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; }
.nutrition-table td:last-child { text-align: right; font-weight: 600; }

/* ========== Reviews ========== */
.review-item { padding: 1.25rem 0; border-bottom: 1px solid var(--border-light); }
.review-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; }
.review-avatar { width: 40px; height: 40px; border-radius: var(--radius-full); object-fit: cover; }
.review-name { font-weight: 600; font-size: 0.9rem; }
.review-date { font-size: 0.78rem; color: var(--muted); }
.review-stars { color: var(--primary-light); font-size: 0.8rem; margin-bottom: 0.5rem; }
.review-text { font-size: 0.9rem; color: var(--text-secondary); line-height: 1.6; }

/* ========== Pagination ========== */
.pagination-wrap { display: flex; align-items: center; justify-content: center; gap: 0.5rem; margin-top: 2.5rem; }
.page-btn {
  width: 40px; height: 40px; border-radius: var(--radius-sm);
  border: 1px solid var(--border); background: var(--surface);
  color: var(--text-secondary); display: flex; align-items: center; justify-content: center;
  cursor: pointer; font-size: 0.9rem; font-weight: 500;
  font-family: var(--font-body); transition: all var(--transition);
}
.page-btn:hover, .page-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.page-btn:disabled { opacity: 0.3; pointer-events: none; }

/* ========== Footer ========== */
#main-footer {
  background: var(--secondary); color: rgba(255,255,255,0.7);
  padding: 4rem 0 0;
}
.footer-grid { display: grid; grid-template-columns: 1.5fr 1fr 1fr 1fr 1.2fr; gap: 2rem; margin-bottom: 3rem; }
.footer-logo { font-family: var(--font-display); font-size: 1.5rem; font-weight: 900; color: #fff; margin-bottom: 1rem; }
.footer-logo span { color: var(--primary); }
.footer-about { font-size: 0.88rem; line-height: 1.7; margin-bottom: 1.25rem; }
.footer-social { display: flex; gap: 0.5rem; }
.footer-social a {
  width: 36px; height: 36px; border-radius: var(--radius-full);
  background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.6);
  display: flex; align-items: center; justify-content: center;
  transition: all var(--transition); font-size: 0.85rem;
}
.footer-social a:hover { background: var(--primary); color: #fff; }
.footer-title { font-family: var(--font-display); font-size: 1rem; font-weight: 700; color: #fff; margin-bottom: 1.25rem; }
.footer-links { list-style: none; }
.footer-links li { margin-bottom: 0.5rem; }
.footer-links a { color: rgba(255,255,255,0.6); font-size: 0.88rem; transition: all var(--transition); }
.footer-links a:hover { color: var(--primary); padding-left: 4px; }
.footer-bottom {
  border-top: 1px solid rgba(255,255,255,0.08);
  padding: 1.25rem 0; display: flex; align-items: center;
  justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;
  font-size: 0.82rem; color: rgba(255,255,255,0.4);
}
.footer-bottom-links { display: flex; gap: 1.5rem; }
.footer-bottom-links a { color: rgba(255,255,255,0.4); font-size: 0.82rem; }
.footer-bottom-links a:hover { color: var(--primary); }

/* ========== Back to Top ========== */
#back-to-top {
  position: fixed; bottom: 2rem; right: 2rem; z-index: 999;
  width: 46px; height: 46px; border-radius: var(--radius-full);
  background: var(--primary); color: #fff; border: none;
  display: none; align-items: center; justify-content: center;
  cursor: pointer; font-size: 1rem; box-shadow: 0 4px 20px rgba(232,93,4,0.4);
  transition: all var(--transition);
}
#back-to-top.show { display: flex; animation: fadeIn 0.3s ease; }
#back-to-top:hover { transform: translateY(-3px); box-shadow: 0 6px 28px rgba(232,93,4,0.5); }

/* ========== Quick View Modal ========== */
.qv-modal-overlay {
  position: fixed; inset: 0; z-index: 9999;
  background: rgba(27,27,47,0.6); backdrop-filter: blur(8px);
  display: none; align-items: center; justify-content: center; padding: 1.5rem;
}
.qv-modal-overlay.active { display: flex; animation: fadeIn 0.2s ease; }
.qv-modal {
  background: var(--surface); border-radius: var(--radius-lg);
  max-width: 800px; width: 100%; max-height: 90vh; overflow-y: auto;
  box-shadow: var(--shadow-xl); animation: slideDown 0.3s ease;
}
.qv-close {
  position: absolute; top: 1rem; right: 1rem;
  width: 36px; height: 36px; border-radius: var(--radius-full);
  background: var(--bg-alt); border: none; color: var(--text-secondary);
  cursor: pointer; display: flex; align-items: center; justify-content: center;
  transition: all var(--transition); z-index: 2;
}
.qv-close:hover { background: var(--danger); color: #fff; }

/* ========== Toast ========== */
.toast-container { position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%); z-index: 99999; display: flex; flex-direction: column; gap: 0.5rem; align-items: center; }
.toast-msg {
  padding: 0.75rem 1.5rem; border-radius: var(--radius-full);
  background: var(--secondary); color: #fff; font-size: 0.88rem;
  box-shadow: var(--shadow-xl); animation: toastIn 0.3s ease;
  display: flex; align-items: center; gap: 0.5rem;
}
@keyframes toastIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

/* ========== 404 Page ========== */
.error-page { min-height: 80vh; display: flex; align-items: center; justify-content: center; text-align: center; padding: 2rem; }
.error-code { font-family: var(--font-display); font-size: clamp(6rem, 15vw, 12rem); font-weight: 900; color: var(--primary); line-height: 1; opacity: 0.2; }
.error-title { font-size: 1.8rem; font-weight: 700; margin: -1rem 0 0.75rem; }
.error-desc { color: var(--text-secondary); max-width: 480px; margin: 0 auto 2rem; }

/* ========== Static Pages ========== */
.static-content { max-width: 800px; margin: 0 auto; }
.static-content h2 { font-size: 1.5rem; margin: 2rem 0 0.75rem; }
.static-content p { color: var(--text-secondary); line-height: 1.8; margin-bottom: 1rem; }
.static-content ul { padding-left: 1.5rem; margin-bottom: 1rem; }
.static-content li { color: var(--text-secondary); line-height: 1.8; margin-bottom: 0.25rem; }

/* ========== Contact Form ========== */
.contact-form .form-control, .contact-form .form-select {
  border: 1px solid var(--border); border-radius: var(--radius-md);
  padding: 0.8rem 1rem; font-size: 0.95rem; background: var(--surface);
  color: var(--text); transition: border-color var(--transition);
}
.contact-form .form-control:focus, .contact-form .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(232,93,4,0.1); }
.contact-form .form-label { font-weight: 600; font-size: 0.88rem; color: var(--text); margin-bottom: 0.4rem; }
.btn-primary-custom {
  padding: 0.8rem 2rem; border-radius: var(--radius-md); border: none;
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: #fff; font-weight: 600; font-size: 0.95rem; cursor: pointer;
  font-family: var(--font-body); transition: all var(--transition);
}
.btn-primary-custom:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(232,93,4,0.4); color: #fff; }

/* ========== Countries Grid ========== */
.country-card {
  background: var(--surface); border-radius: var(--radius-md);
  padding: 1.5rem; text-align: center; border: 1px solid var(--border-light);
  box-shadow: var(--shadow-sm); transition: all 0.4s ease; cursor: pointer;
}
.country-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); border-color: var(--primary); }
.country-card .flag { font-size: 3rem; margin-bottom: 0.75rem; display: block; }
.country-card .name { font-weight: 700; font-size: 1rem; margin-bottom: 0.25rem; }
.country-card .info { font-size: 0.82rem; color: var(--muted); }

/* ========== App Banner ========== */
.app-banner {
  background: linear-gradient(135deg, var(--bg-alt) 0%, var(--surface) 100%);
  border: 1px solid var(--border-light); border-radius: var(--radius-lg);
  padding: 3rem; display: flex; align-items: center; gap: 3rem; overflow: hidden; position: relative;
}
.app-banner::before {
  content: ''; position: absolute; top: -50%; right: -20%; width: 400px; height: 400px;
  border-radius: 50%; background: rgba(232,93,4,0.08); filter: blur(60px);
}
.app-banner-content { flex: 1; position: relative; z-index: 2; }
.app-banner-content h3 { font-size: 1.6rem; margin-bottom: 0.75rem; }
.app-banner-content p { color: var(--text-secondary); margin-bottom: 1.5rem; }
.app-buttons { display: flex; gap: 0.75rem; }
.app-btn {
  display: inline-flex; align-items: center; gap: 0.6rem;
  padding: 0.7rem 1.25rem; border-radius: var(--radius-md);
  background: var(--secondary); color: #fff; font-size: 0.85rem;
  font-weight: 500; transition: all var(--transition);
}
.app-btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); color: #fff; }
.app-btn i { font-size: 1.3rem; }
.app-btn .app-btn-text small { display: block; font-size: 0.65rem; font-weight: 400; opacity: 0.7; }
.app-banner-visual { flex-shrink: 0; position: relative; z-index: 2; }
.app-phone {
  width: 200px; height: 400px; border-radius: 30px;
  background: var(--secondary); border: 3px solid var(--border);
  display: flex; align-items: center; justify-content: center;
  box-shadow: var(--shadow-xl);
}
.app-phone i { font-size: 4rem; color: var(--primary); }

/* ========== Empty State ========== */
.empty-state { text-align: center; padding: 4rem 2rem; }
.empty-state i { font-size: 3rem; color: var(--muted); margin-bottom: 1rem; opacity: 0.5; }
.empty-state h3 { font-size: 1.2rem; margin-bottom: 0.5rem; }
.empty-state p { color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem; }

/* ========== Responsive ========== */
@media (max-width: 1200px) {
  .footer-grid { grid-template-columns: 1fr 1fr 1fr; }
}
@media (max-width: 991px) {
  .header-nav { display: none; }
  .mobile-menu-btn { display: flex; }
  .footer-grid { grid-template-columns: 1fr 1fr; }
  .app-banner { flex-direction: column; text-align: center; }
  .app-buttons { justify-content: center; }
  .app-banner-visual { display: none; }
  .hero-search-row { flex-direction: column; }
  .pd-gallery { margin-bottom: 1.5rem; }
  .pd-main-image { height: 300px; }
  .products-list .product-card { flex-direction: column; }
  .products-list .pc-image { width: 100%; min-width: 100%; height: 200px; }
}
@media (max-width: 767px) {
  .section-padding { padding: 3rem 0; }
  .footer-grid { grid-template-columns: 1fr; }
  .footer-bottom { flex-direction: column; text-align: center; }
  .pd-details-grid { grid-template-columns: 1fr; }
  .newsletter-form { flex-direction: column; }
  .toolbar { flex-direction: column; align-items: stretch; }
  .toolbar-left, .toolbar-right { justify-content: space-between; }
  .hero-filters { justify-content: center; }
  .country-selector .cs-text { display: none; }
}

/* ========== Accessibility ========== */
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
  html { scroll-behavior: auto; }
}
.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); border: 0; }

/* ========== Blog Preview ========== */
.blog-card {
  background: var(--surface); border-radius: var(--radius-md);
  overflow: hidden; border: 1px solid var(--border-light);
  box-shadow: var(--shadow-sm); transition: all 0.4s ease;
}
.blog-card:hover { box-shadow: var(--shadow-lg); transform: translateY(-4px); }
.blog-card img { height: 180px; object-fit: cover; width: 100%; transition: transform 0.5s ease; }
.blog-card:hover img { transform: scale(1.05); }
.blog-card-body { padding: 1.25rem; }
.blog-card-meta { font-size: 0.78rem; color: var(--muted); margin-bottom: 0.5rem; display: flex; gap: 1rem; }
.blog-card-title { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem; line-height: 1.3; }
.blog-card-excerpt { font-size: 0.85rem; color: var(--text-secondary); line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

/* ========== Stat Counter ========== */
.stat-counter { text-align: center; }
.stat-number { font-family: var(--font-display); font-size: 2.5rem; font-weight: 900; color: var(--primary); line-height: 1; margin-bottom: 0.25rem; }
.stat-label { font-size: 0.88rem; color: var(--text-secondary); }
</style>
</head>
<body>

<!-- Preloader -->
<div id="preloader" role="status" aria-label="Loading">
  <div class="preloader-logo">Food<span>Scope</span></div>
  <div class="preloader-bar"><div class="preloader-bar-inner"></div></div>
  <div class="preloader-dots"><span></span><span></span><span></span></div>
</div>

<!-- Country Change Overlay -->
<div id="country-overlay" aria-hidden="true">
  <div class="flag-icon" id="co-flag"></div>
  <div class="country-name" id="co-name"></div>
  <div class="loader-spinner"></div>
</div>

<!-- Search Overlay -->
<div id="search-overlay" role="dialog" aria-label="Search">
  <div class="search-container">
    <div class="search-input-wrap">
      <i class="fas fa-search"></i>
      <input type="text" id="search-input" placeholder="Search brands, categories, products..." autocomplete="off" aria-label="Search">
      <button class="search-close-btn" onclick="closeSearch()" aria-label="Close search"><i class="fas fa-times"></i></button>
    </div>
    <div class="search-suggestions" id="search-suggestions">
      <div class="search-empty">Start typing to search...</div>
    </div>
  </div>
</div>

<!-- Quick View Modal -->
<div class="qv-modal-overlay" id="qv-modal" role="dialog" aria-label="Quick view">
  <div class="qv-modal" style="position:relative;" id="qv-content"></div>
</div>

<!-- Header -->
<header id="main-header" role="banner">
  <div class="header-inner">
    <div class="header-logo" onclick="navigate('#home')" tabindex="0" role="button" aria-label="FoodScope Home">Food<span>Scope</span></div>
    <nav class="header-nav" role="navigation" aria-label="Main navigation" id="main-nav">
      <a href="#home" class="active" data-page="home">Home</a>
      <a href="#brands" data-page="brands">Brands</a>
      <a href="#offers" data-page="offers">Offers</a>
      <a href="#categories" data-page="categories">Categories</a>
      <a href="#about" data-page="about">About</a>
      <a href="#contact" data-page="contact">Contact</a>
    </nav>
    <div class="header-actions">
      <div class="country-selector" id="country-selector" tabindex="0" role="button" aria-label="Select country" aria-expanded="false">
        <span class="flag" id="cs-flag"></span>
        <span class="cs-text" id="cs-text"></span>
        <i class="fas fa-chevron-down chevron"></i>
        <div class="country-dropdown" id="country-dropdown" role="listbox"></div>
      </div>
      <button class="header-search-btn" onclick="openSearch()" aria-label="Open search"><i class="fas fa-search"></i></button>
      <button class="theme-toggle" id="theme-toggle" aria-label="Toggle dark mode"><i class="fas fa-moon"></i></button>
      <button class="mobile-menu-btn" onclick="toggleMobileMenu()" aria-label="Toggle menu"><i class="fas fa-bars"></i></button>
    </div>
  </div>
</header>

<!-- Mobile Off-Canvas Menu -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
  <div class="offcanvas-header" style="border-bottom:1px solid var(--border);">
    <h5 class="offcanvas-title" id="mobileMenuLabel" style="font-family:var(--font-display);font-weight:800;">Food<span style="color:var(--primary)">Scope</span></h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body" style="padding:1.5rem 0;">
    <nav style="display:flex;flex-direction:column;gap:0.25rem;">
      <a href="#home" class="d-block px-4 py-2.5 text-decoration-none" style="color:var(--text);font-weight:500;border-radius:var(--radius-sm);transition:all 0.2s;" onclick="closeMobileMenu()" onmouseover="this.style.background='var(--bg-alt)'" onmouseout="this.style.background='transparent'"><i class="fas fa-home me-2" style="color:var(--primary);width:20px;"></i>Home</a>
      <a href="#brands" class="d-block px-4 py-2.5 text-decoration-none" style="color:var(--text);font-weight:500;border-radius:var(--radius-sm);transition:all 0.2s;" onclick="closeMobileMenu()" onmouseover="this.style.background='var(--bg-alt)'" onmouseout="this.style.background='transparent'"><i class="fas fa-store me-2" style="color:var(--primary);width:20px;"></i>Brands</a>
      <a href="#offers" class="d-block px-4 py-2.5 text-decoration-none" style="color:var(--text);font-weight:500;border-radius:var(--radius-sm);transition:all 0.2s;" onclick="closeMobileMenu()" onmouseover="this.style.background='var(--bg-alt)'" onmouseout="this.style.background='transparent'"><i class="fas fa-tags me-2" style="color:var(--primary);width:20px;"></i>Offers</a>
      <a href="#categories" class="d-block px-4 py-2.5 text-decoration-none" style="color:var(--text);font-weight:500;border-radius:var(--radius-sm);transition:all 0.2s;" onclick="closeMobileMenu()" onmouseover="this.style.background='var(--bg-alt)'" onmouseout="this.style.background='transparent'"><i class="fas fa-th-large me-2" style="color:var(--primary);width:20px;"></i>Categories</a>
      <a href="#about" class="d-block px-4 py-2.5 text-decoration-none" style="color:var(--text);font-weight:500;border-radius:var(--radius-sm);transition:all 0.2s;" onclick="closeMobileMenu()" onmouseover="this.style.background='var(--bg-alt)'" onmouseout="this.style.background='transparent'"><i class="fas fa-info-circle me-2" style="color:var(--primary);width:20px;"></i>About</a>
      <a href="#contact" class="d-block px-4 py-2.5 text-decoration-none" style="color:var(--text);font-weight:500;border-radius:var(--radius-sm);transition:all 0.2s;" onclick="closeMobileMenu()" onmouseover="this.style.background='var(--bg-alt)'" onmouseout="this.style.background='transparent'"><i class="fas fa-envelope me-2" style="color:var(--primary);width:20px;"></i>Contact</a>
    </nav>
    <hr style="border-color:var(--border);margin:1rem 1.5rem;">
    <div style="padding:0 1.5rem;" id="mobile-country-select"></div>
  </div>
</div>

<!-- Main Content -->
<main id="main-content" role="main" style="min-height:100vh;"></main>

<!-- Footer -->
<footer id="main-footer" role="contentinfo">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-logo">Food<span>Scope</span></div>
        <p class="footer-about">Your ultimate destination for comparing food menus, prices, and deals from world-famous brands across multiple countries.</p>
        <div class="footer-social">
          <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
          <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>
      <div>
        <h4 class="footer-title">Quick Links</h4>
        <ul class="footer-links">
          <li><a href="#home">Home</a></li>
          <li><a href="#brands">All Brands</a></li>
          <li><a href="#offers">Offers</a></li>
          <li><a href="#categories">Categories</a></li>
          <li><a href="#about">About Us</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
      </div>
      <div>
        <h4 class="footer-title">Popular Brands</h4>
        <ul class="footer-links" id="footer-brands"></ul>
      </div>
      <div>
        <h4 class="footer-title">Categories</h4>
        <ul class="footer-links" id="footer-categories"></ul>
      </div>
      <div>
        <h4 class="footer-title">Stay Updated</h4>
        <p style="font-size:0.88rem;margin-bottom:1rem;">Get the latest deals and menu updates delivered to your inbox.</p>
        <div style="display:flex;gap:0.5rem;">
          <input type="email" placeholder="Your email" style="flex:1;padding:0.6rem 0.8rem;border-radius:var(--radius-sm);border:1px solid rgba(255,255,255,0.15);background:rgba(255,255,255,0.08);color:#fff;font-size:0.85rem;outline:none;" id="footer-email">
          <button onclick="subscribeFooter()" style="padding:0.6rem 1rem;border-radius:var(--radius-sm);background:var(--primary);color:#fff;border:none;cursor:pointer;font-weight:600;font-size:0.85rem;">Join</button>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; 2025 FoodScope. All rights reserved.</span>
      <div class="footer-bottom-links">
        <a href="#privacy">Privacy Policy</a>
        <a href="#terms">Terms &amp; Conditions</a>
        <a href="#sitemap">Sitemap</a>
      </div>
    </div>
  </div>
</footer>

<!-- Back to Top -->
<button id="back-to-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" aria-label="Back to top"><i class="fas fa-arrow-up"></i></button>

<!-- Toast Container -->
<div class="toast-container" id="toast-container"></div>

<!-- CDN Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

<script>
/* ==========================================================================
   DATA LAYER — Simulated JSON data source (normally loaded via AJAX)
   ========================================================================== */
const APP_DATA = {
  countries: [
    { id:'us', name:'United States', flag:'\u{1F1FA}\u{1F1F8}', currency:'USD', symbol:'$' },
    { id:'uk', name:'United Kingdom', flag:'\u{1F1EC}\u{1F1E7}', currency:'GBP', symbol:'\u00A3' },
    { id:'ae', name:'UAE', flag:'\u{1F1E6}\u{1F1EA}', currency:'AED', symbol:'AED' },
    { id:'sa', name:'Saudi Arabia', flag:'\u{1F1F8}\u{1F1E6}', currency:'SAR', symbol:'SAR' },
    { id:'in', name:'India', flag:'\u{1F1EE}\u{1F1F3}', currency:'INR', symbol:'\u20B9' },
    { id:'de', name:'Germany', flag:'\u{1F1E9}\u{1F1EA}', currency:'EUR', symbol:'\u20AC' }
  ],
  brands: [
    { id:'kfc', name:"KFC", logo:'assets/img/brands/KFC-Logo-2018.webp', cover:'assets/img/brands/KFC-Logo-2018.webp', desc:"Kentucky Fried Chicken is an American fast food restaurant chain specializing in fried chicken.", countries:['us','uk','ae','sa','in','de'], history:"Founded by Colonel Harland Sanders in 1952 in Kentucky, KFC has grown to become one of the largest fast food chains worldwide, known for its secret blend of 11 herbs and spices." },
    { id:'mcd', name:"McDonald's", logo:'assets/img/brands/mcdonalds-logo-1.webp', cover:'assets/img/brands/mcdonalds-logo-1.webp', desc:"The world's largest fast food restaurant chain, serving over 69 million customers daily.", countries:['us','uk','ae','sa','in','de'], history:"Founded in 1940 by Richard and Maurice McDonald, McDonald's revolutionized the fast food industry with its Speedee Service System." },
    { id:'bk', name:"Burger King", logo:'assets/img/brands/Burger-King-Logo.webp', cover:'assets/img/brands/Burger-King-Logo.webp', desc:"Known for the Whopper sandwich, Burger King is a global chain of hamburger fast food restaurants.", countries:['us','uk','ae','sa','in','de'], history:"Founded in 1953 in Jacksonville, Florida as Insta-Burger King, the company was renamed in 1959 and has since expanded globally." },
    { id:'subway', name:"Subway", logo:'assets/img/brands/Subway.webp', cover:'assets/img/brands/Subway.webp', desc:"American multi-national fast food restaurant franchise known for fresh submarine sandwiches.", countries:['us','uk','ae','sa','in','de'], history:"Founded in 1965 by Fred DeLuca and Peter Buck as Pete's Super Submarines in Bridgeport, Connecticut." },
    { id:'ph', name:"Pizza Hut", logo:'assets/img/brands/pizza-hut.webp', cover:'assets/img/brands/pizza-hut.webp', desc:"American restaurant chain and international franchise known for Italian-American cuisine including pizza and pasta.", countries:['us','uk','ae','sa','in','de'], history:"Founded in 1958 by brothers Dan and Frank Carney in Wichita, Kansas. Pizza Hut was the first national pizza chain in the United States." },
    { id:'dom', name:"Domino's", logo:'assets/img/brands/domino.webp', cover:'assets/img/brands/domino.webp', desc:"International pizza delivery corporation headquartered in Michigan, known for fast delivery.", countries:['us','uk','ae','sa','in','de'], history:"Founded in 1960 by Tom Monaghan and his brother James as DomiNick's. The name was changed to Domino's in 1965." },
    { id:'sbx', name:"Starbucks", logo:'assets/img/brands/starbugs.webp', cover:'assets/img/brands/starbugs.webp', desc:"Premier roaster and retailer of specialty coffee with over 35,000 stores worldwide.", countries:['us','uk','ae','sa','in','de'], history:"Founded in 1971 in Seattle's Pike Place Market as a single store selling whole bean coffee, tea, and spices." },
    { id:'fg', name:"Five Guys", logo:'assets/img/brands/Five-Guys-Logo.webp', cover:'assets/img/brands/Five-Guys-Logo.webp', desc:"American fast casual restaurant chain focused on hamburgers, hot dogs, and french fries.", countries:['us','uk','ae'], history:"Founded in 1986 by Janie and Jerry Murrell and their five sons in Arlington, Virginia." }
  ],
  categories: [
    { id:'burgers', name:'Burgers', image:'https://picsum.photos/seed/catburgers/600/400.jpg', desc:'Classic and gourmet burgers from top brands' },
    { id:'chicken', name:'Chicken', image:'https://picsum.photos/seed/catchicken/600/400.jpg', desc:'Fried, grilled, and crispy chicken options' },
    { id:'pizza', name:'Pizza', image:'https://picsum.photos/seed/catpizza/600/400.jpg', desc:'Hand-tossed and pan pizzas with various toppings' },
    { id:'sandwiches', name:'Sandwiches', image:'https://picsum.photos/seed/catsandwiches/600/400.jpg', desc:'Freshly made subs and sandwiches' },
    { id:'sides', name:'Sides', image:'https://picsum.photos/seed/catsides/600/400.jpg', desc:'Fries, coleslaw, salads, and more' },
    { id:'drinks', name:'Drinks', image:'https://picsum.photos/seed/catdrinks/600/400.jpg', desc:'Beverages, smoothies, and specialty drinks' },
    { id:'desserts', name:'Desserts', image:'https://picsum.photos/seed/catdesserts/600/400.jpg', desc:'Sweet treats and frozen desserts' },
    { id:'breakfast', name:'Breakfast', image:'https://picsum.photos/seed/catbreakfast/600/400.jpg', desc:'Morning meals and breakfast combos' },
    { id:'salads', name:'Salads', image:'https://picsum.photos/seed/catsalads/600/400.jpg', desc:'Fresh and healthy salad options' },
    { id:'combos', name:'Combo Meals', image:'https://picsum.photos/seed/catcombos/600/400.jpg', desc:'Value meals with main, side, and drink' }
  ],
  products: [
    { id:'kfc-or', name:'Original Recipe Chicken', brand:'kfc', category:'chicken', image:'https://picsum.photos/seed/kfcor/500/400.jpg', desc:'Our iconic chicken hand-breaded with the Colonel\'s secret blend of 11 herbs and spices. Pressure cooked to golden perfection.', calories:390, ingredients:['Chicken','Flour','Salt','MSG','Paprika','Garlic Powder','Onion Powder'], nutrition:{fat:21,carbs:12,protein:28,fiber:0,sugar:0,sodium:1020}, prices:{us:{r:5.99,d:4.49},uk:{r:4.49,d:null},ae:{r:22,d:18},sa:{r:23,d:null},in:{r:299,d:249},de:{r:5.49,d:4.29}} },
    { id:'kfc-zinger', name:'Zinger Burger', brand:'kfc', category:'burgers', image:'https://picsum.photos/seed/kfczinger/500/400.jpg', desc:'A spicy crunch of 100% chicken breast fillet with fresh lettuce and creamy mayonnaise in a toasted sesame bun.', calories:475, ingredients:['Chicken Breast','Bun','Lettuce','Mayonnaise','Spice Blend'], nutrition:{fat:24,carbs:39,protein:27,fiber:2,sugar:5,sodium:1150}, prices:{us:{r:6.49,d:5.49},uk:{r:5.29,d:4.49},ae:{r:28,d:24},sa:{r:29,d:25},in:{r:349,d:299},de:{r:5.99,d:4.99}} },
    { id:'kfc-cs', name:'Coleslaw', brand:'kfc', category:'sides', image:'https://picsum.photos/seed/kfccs/500/400.jpg', desc:'Freshly prepared coleslaw made with shredded cabbage, carrots, and a creamy dressing.', calories:170, ingredients:['Cabbage','Carrots','Mayonnaise','Sugar','Vinegar'], nutrition:{fat:11,carbs:16,protein:1,fiber:2,sugar:12,sodium:260}, prices:{us:{r:2.29,d:null},uk:{r:1.99,d:null},ae:{r:8,d:null},sa:{r:9,d:null},in:{r:99,d:null},de:{r:2.19,d:null}} },
    { id:'kfc-fries', name:'French Fries', brand:'kfc', category:'sides', image:'https://picsum.photos/seed/kfcfries/500/400.jpg', desc:'Thick-cut, crispy golden fries seasoned to perfection.', calories:290, ingredients:['Potatoes','Vegetable Oil','Salt','Dextrose'], nutrition:{fat:15,carbs:36,protein:3,fiber:3,sugar:0,sodium:580}, prices:{us:{r:2.49,d:1.99},uk:{r:2.19,d:null},ae:{r:10,d:8},sa:{r:11,d:null},in:{r:129,d:99},de:{r:2.39,d:null}} },
    { id:'kfc-bucket', name:'Family Bucket', brand:'kfc', category:'combos', image:'https://picsum.photos/seed/kfcbucket/500/400.jpg', desc:'8 pieces of our famous Original Recipe chicken, perfect for sharing with family and friends.', calories:3120, ingredients:['Chicken','Flour','Seasoning'], nutrition:{fat:168,carbs:96,protein:224,fiber:0,sugar:0,sodium:8160}, prices:{us:{r:24.99,d:19.99},uk:{r:18.99,d:15.99},ae:{r:89,d:75},sa:{r:95,d:null},in:{r:1199,d:999},de:{r:22.99,d:18.99}} },
    { id:'kfc-cc', name:'Chocolate Cookie', brand:'kfc', category:'desserts', image:'https://picsum.photos/seed/kfccc/500/400.jpg', desc:'Soft-baked chocolate chip cookie, warm and gooey in the center with crispy edges.', calories:250, ingredients:['Flour','Butter','Sugar','Chocolate Chips','Eggs','Vanilla'], nutrition:{fat:12,carbs:34,protein:2,fiber:1,sugar:22,sodium:180}, prices:{us:{r:1.49,d:null},uk:{r:1.29,d:null},ae:{r:6,d:null},sa:{r:7,d:null},in:{r:79,d:null},de:{r:1.39,d:null}} },
    { id:'mcd-bigmac', name:'Big Mac', brand:'mcd', category:'burgers', image:'https://picsum.photos/seed/mcdbigmac/500/400.jpg', desc:'Two all-beef patties, special sauce, lettuce, cheese, pickles, onions on a sesame seed bun.', calories:550, ingredients:['Beef Patties','Bun','Special Sauce','Lettuce','Cheese','Pickles','Onions'], nutrition:{fat:30,carbs:46,protein:25,fiber:3,sugar:9,sodium:1010}, prices:{us:{r:5.99,d:4.99},uk:{r:4.49,d:3.99},ae:{r:25,d:22},sa:{r:26,d:23},in:{r:299,d:249},de:{r:5.49,d:4.49}} },
    { id:'mcd-qp', name:'Quarter Pounder', brand:'mcd', category:'burgers', image:'https://picsum.photos/seed/mcdqp/500/400.jpg', desc:'A quarter pound of 100% fresh beef with cheese, onions, pickles, mustard, and ketchup on a sesame bun.', calories:520, ingredients:['Beef Patty','Cheese','Bun','Onions','Pickles','Mustard','Ketchup'], nutrition:{fat:26,carbs:42,protein:30,fiber:2,sugar:8,sodium:1050}, prices:{us:{r:6.49,d:null},uk:{r:4.99,d:4.49},ae:{r:27,d:null},sa:{r:28,d:null},in:{r:329,d:279},de:{r:5.99,d:null}} },
    { id:'mcd-nuggets', name:'Chicken McNuggets (10pc)', brand:'mcd', category:'chicken', image:'https://picsum.photos/seed/mcdnuggets/500/400.jpg', desc:'Tender, juicy chicken McNuggets made with white meat, breaded and cooked to golden perfection.', calories:440, ingredients:['Chicken Breast','Breading','Vegetable Oil'], nutrition:{fat:26,carbs:30,protein:22,fiber:1,sugar:0,sodium:900}, prices:{us:{r:4.99,d:3.99},uk:{r:4.29,d:3.79},ae:{r:20,d:17},sa:{r:21,d:18},in:{r:249,d:199},de:{r:4.79,d:3.99}} },
    { id:'mcd-fries', name:'World Famous Fries', brand:'mcd', category:'sides', image:'https://picsum.photos/seed/mcdfries/500/400.jpg', desc:'Crispy, golden, and perfectly salted fries made from premium potatoes.', calories:320, ingredients:['Potatoes','Vegetable Oil','Dextrose','Sodium Acid Pyrophosphate','Salt'], nutrition:{fat:15,carbs:43,protein:3,fiber:4,sugar:0,sodium:350}, prices:{us:{r:2.49,d:null},uk:{r:2.19,d:null},ae:{r:10,d:null},sa:{r:11,d:null},in:{r:129,d:null},de:{r:2.39,d:null}} },
    { id:'mcd-mcflurry', name:'McFlurry Oreo', brand:'mcd', category:'desserts', image:'https://picsum.photos/seed/mcdmcflurry/500/400.jpg', desc:'Creamy vanilla soft serve blended with Oreo cookie pieces.', calories:510, ingredients:['Vanilla Soft Serve','Oreo Cookies','Syrup'], nutrition:{fat:17,carbs:80,protein:7,fiber:1,sugar:64,sodium:280}, prices:{us:{r:3.99,d:2.99},uk:{r:2.99,d:2.49},ae:{r:15,d:12},sa:{r:16,d:13},in:{r:179,d:149},de:{r:3.49,d:2.79}} },
    { id:'mcd-fish', name:'Filet-O-Fish', brand:'mcd', category:'sandwiches', image:'https://picsum.photos/seed/mcdfish/500/400.jpg', desc:'A crispy fish fillet with tartar sauce and a half-slice of cheese on a steamed bun.', calories:380, ingredients:['Fish Fillet','Bun','Tartar Sauce','Cheese'], nutrition:{fat:18,carbs:38,protein:15,fiber:1,sugar:5,sodium:590}, prices:{us:{r:4.79,d:null},uk:{r:3.99,d:null},ae:{r:19,d:null},sa:{r:20,d:null},in:{r:229,d:null},de:{r:4.49,d:null}} },
    { id:'bk-whopper', name:'Whopper', brand:'bk', category:'burgers', image:'https://picsum.photos/seed/bkwhopper/500/400.jpg', desc:'Quarter-pound of flame-grilled beef topped with juicy tomatoes, fresh lettuce, creamy mayonnaise, ketchup, crunchy pickles, and sliced white onions on a soft sesame seed bun.', calories:657, ingredients:['Beef Patty','Sesame Bun','Tomato','Lettuce','Mayonnaise','Ketchup','Pickles','Onions'], nutrition:{fat:40,carbs:49,protein:28,fiber:2,sugar:11,sodium:980}, prices:{us:{r:6.79,d:5.49},uk:{r:5.49,d:4.49},ae:{r:29,d:24},sa:{r:30,d:25},in:{r:349,d:289},de:{r:6.29,d:5.19}} },
    { id:'bk-cr', name:'Chicken Royale', brand:'bk', category:'chicken', image:'https://picsum.photos/seed/bkcr/500/400.jpg', desc:'Crispy chicken fillet with fresh lettuce and creamy mayonnaise on a toasted sesame bun.', calories:490, ingredients:['Chicken Fillet','Bun','Lettuce','Mayonnaise'], nutrition:{fat:25,carbs:42,protein:24,fiber:2,sugar:5,sodium:1050}, prices:{us:{r:5.99,d:null},uk:{r:4.99,d:4.29},ae:{r:24,d:null},sa:{r:25,d:null},in:{r:299,d:null},de:{r:5.49,d:null}} },
    { id:'bk-or', name:'Onion Rings', brand:'bk', category:'sides', image:'https://picsum.photos/seed/bkonionrings/500/400.jpg', desc:'Golden, crispy onion rings seasoned to perfection.', calories:410, ingredients:['Onions','Breading','Vegetable Oil'], nutrition:{fat:22,carbs:46,protein:4,fiber:2,sugar:5,sodium:790}, prices:{us:{r:2.79,d:null},uk:{r:2.49,d:null},ae:{r:11,d:null},sa:{r:12,d:null},in:{r:149,d:null},de:{r:2.59,d:null}} },
    { id:'bk-stacker', name:'Triple Stacker', brand:'bk', category:'burgers', image:'https://picsum.photos/seed/bkstacker/500/400.jpg', desc:'Three quarter-pound flame-grilled beef patties with melted American cheese, thick-cut bacon, and Stackers sauce.', calories:1250, ingredients:['Beef Patties','Cheese','Bacon','Stackers Sauce','Bun'], nutrition:{fat:82,carbs:48,protein:72,fiber:1,sugar:9,sodium:2180}, prices:{us:{r:10.99,d:8.99},uk:{r:8.99,d:7.49},ae:{r:42,d:35},sa:{r:44,d:37},in:{r:549,d:449},de:{r:10.49,d:8.49}} },
    { id:'bk-fries', name:'King Fries', brand:'bk', category:'sides', image:'https://picsum.photos/seed/bkfries/500/400.jpg', desc:'Thick-cut crispy fries with that signature Burger King taste.', calories:380, ingredients:['Potatoes','Vegetable Oil','Salt'], nutrition:{fat:18,carbs:48,protein:4,fiber:4,sugar:0,sodium:580}, prices:{us:{r:2.49,d:null},uk:{r:2.29,d:null},ae:{r:10,d:null},sa:{r:11,d:null},in:{r:129,d:null},de:{r:2.39,d:null}} },
    { id:'sub-bmt', name:'Italian B.M.T.', brand:'subway', category:'sandwiches', image:'https://picsum.photos/seed/subbmt/500/400.jpg', desc:'Spicy pepperoni, salami, and ham with your choice of fresh vegetables and sauces on freshly baked bread.', calories:410, ingredients:['Italian Bread','Pepperoni','Salami','Ham','Lettuce','Tomatoes','Onions'], nutrition:{fat:16,carbs:46,protein:20,fiber:3,sugar:5,sodium:1480}, prices:{us:{r:7.49,d:5.99},uk:{r:5.99,d:4.99},ae:{r:30,d:25},sa:{r:31,d:26},in:{r:399,d:349},de:{r:6.99,d:5.49}} },
    { id:'sub-club', name:'Subway Club', brand:'subway', category:'sandwiches', image:'https://picsum.photos/seed/subclub/500/400.jpg', desc:'Sliced turkey breast, roast beef, and ham with your choice of fresh veggies and sauces.', calories:350, ingredients:['Wheat Bread','Turkey','Roast Beef','Ham','Vegetables'], nutrition:{fat:6,carbs:44,protein:26,fiber:4,sugar:5,sodium:1180}, prices:{us:{r:7.49,d:null},uk:{r:5.99,d:null},ae:{r:30,d:null},sa:{r:31,d:null},in:{r:399,d:null},de:{r:6.99,d:null}} },
    { id:'sub-mbm', name:'Meatball Marinara', brand:'subway', category:'sandwiches', image:'https://picsum.photos/seed/submbm/500/400.jpg', desc:'Juicy meatballs in marinara sauce with melted cheese on freshly baked bread.', calories:580, ingredients:['Italian Bread','Meatballs','Marinara Sauce','Cheese'], nutrition:{fat:24,carbs:62,protein:28,fiber:4,sugar:10,sodium:1360}, prices:{us:{r:6.99,d:5.49},uk:{r:5.49,d:4.49},ae:{r:28,d:23},sa:{r:29,d:24},in:{r:379,d:319},de:{r:6.49,d:5.29}} },
    { id:'sub-cookie', name:'Double Chocolate Cookie', brand:'subway', category:'desserts', image:'https://picsum.photos/seed/subcookie/500/400.jpg', desc:'Rich, chewy cookie loaded with chocolate chunks.', calories:220, ingredients:['Flour','Butter','Sugar','Chocolate Chips','Cocoa','Eggs'], nutrition:{fat:10,carbs:30,protein:2,fiber:1,sugar:20,sodium:150}, prices:{us:{r:1.29,d:null},uk:{r:1.09,d:null},ae:{r:5,d:null},sa:{r:6,d:null},in:{r:69,d:null},de:{r:1.19,d:null}} },
    { id:'ph-marg', name:'Margherita Pizza', brand:'ph', category:'pizza', image:'https://picsum.photos/seed/phmarg/500/400.jpg', desc:'Classic pizza with tangy tomato sauce, melted mozzarella cheese, and fresh basil on a hand-tossed crust.', calories:680, ingredients:['Pizza Dough','Tomato Sauce','Mozzarella','Basil'], nutrition:{fat:22,carbs:86,protein:28,fiber:3,sugar:8,sodium:1420}, prices:{us:{r:12.99,d:9.99},uk:{r:10.99,d:8.99},ae:{r:45,d:38},sa:{r:48,d:40},in:{r:549,d:449},de:{r:11.99,d:9.49}} },
    { id:'ph-pep', name:'Pepperoni Lovers', brand:'ph', category:'pizza', image:'https://picsum.photos/seed/phpep/500/400.jpg', desc:'Loaded with extra pepperoni on a bed of cheese and our signature tomato sauce.', calories:820, ingredients:['Pizza Dough','Tomato Sauce','Mozzarella','Pepperoni'], nutrition:{fat:36,carbs:82,protein:38,fiber:3,sugar:9,sodium:2180}, prices:{us:{r:15.99,d:12.99},uk:{r:13.99,d:10.99},ae:{r:55,d:46},sa:{r:58,d:48},in:{r:699,d:579},de:{r:14.99,d:11.99}} },
    { id:'ph-gb', name:'Garlic Bread', brand:'ph', category:'sides', image:'https://picsum.photos/seed/phgb/500/400.jpg', desc:'Warm, buttery garlic bread with a crispy crust and soft center.', calories:250, ingredients:['Bread','Butter','Garlic','Parsley'], nutrition:{fat:10,carbs:34,protein:5,fiber:1,sugar:2,sodium:420}, prices:{us:{r:3.99,d:null},uk:{r:3.49,d:null},ae:{r:14,d:null},sa:{r:15,d:null},in:{r:179,d:null},de:{r:3.79,d:null}} },
    { id:'ph-sup', name:'Supreme Pizza', brand:'ph', category:'pizza', image:'https://picsum.photos/seed/phsup/500/400.jpg', desc:'Loaded with pepperoni, sausage, green peppers, onions, and mushrooms.', calories:900, ingredients:['Pizza Dough','Tomato Sauce','Mozzarella','Pepperoni','Sausage','Vegetables'], nutrition:{fat:40,carbs:88,protein:40,fiber:4,sugar:10,sodium:2460}, prices:{us:{r:17.99,d:14.99},uk:{r:15.99,d:12.99},ae:{r:62,d:52},sa:{r:65,d:54},in:{r:799,d:669},de:{r:16.99,d:13.99}} },
    { id:'dom-marg', name:'Classic Margherita', brand:'dom', category:'pizza', image:'https://picsum.photos/seed/dommarg/500/400.jpg', desc:'Our signature pizza with vine-ripened tomato sauce, mozzarella, and oregano.', calories:640, ingredients:['Pizza Dough','Tomato Sauce','Mozzarella','Oregano'], nutrition:{fat:20,carbs:84,protein:26,fiber:3,sugar:7,sodium:1320}, prices:{us:{r:11.99,d:8.99},uk:{r:9.99,d:7.99},ae:{r:40,d:34},sa:{r:42,d:36},in:{r:499,d:399},de:{r:10.99,d:8.49}} },
    { id:'dom-pep', name:'Pepperoni Passion', brand:'dom', category:'pizza', image:'https://picsum.photos/seed/dompep/500/400.jpg', desc:'Double pepperoni on a classic base with extra cheese.', calories:780, ingredients:['Pizza Dough','Tomato Sauce','Mozzarella','Pepperoni'], nutrition:{fat:34,carbs:78,protein:36,fiber:3,sugar:8,sodium:2080}, prices:{us:{r:14.99,d:11.99},uk:{r:12.99,d:9.99},ae:{r:50,d:42},sa:{r:52,d:44},in:{r:649,d:529},de:{r:13.99,d:10.99}} },
    { id:'dom-ct', name:'Chicken Tikka', brand:'dom', category:'pizza', image:'https://picsum.photos/seed/domct/500/400.jpg', desc:'Spicy chicken tikka pieces with onions, peppers, and tikka sauce on a cheesy base.', calories:720, ingredients:['Pizza Dough','Tikka Sauce','Chicken','Onions','Peppers','Mozzarella'], nutrition:{fat:26,carbs:80,protein:34,fiber:3,sugar:8,sodium:1640}, prices:{us:{r:15.99,d:12.99},uk:{r:13.99,d:10.99},ae:{r:52,d:44},sa:{r:54,d:46},in:{r:699,d:579},de:{r:14.99,d:11.99}} },
    { id:'dom-gpb', name:'Garlic Pizza Bread', brand:'dom', category:'sides', image:'https://picsum.photos/seed/domgpb/500/400.jpg', desc:'Our famous garlic pizza bread with a buttery garlic spread and herbs.', calories:280, ingredients:['Pizza Dough','Garlic Butter','Herbs','Cheese'], nutrition:{fat:12,carbs:36,protein:8,fiber:1,sugar:2,sodium:480}, prices:{us:{r:3.99,d:null},uk:{r:3.49,d:null},ae:{r:14,d:null},sa:{r:15,d:null},in:{r:179,d:null},de:{r:3.79,d:null}} },
    { id:'sbx-cm', name:'Caramel Macchiato', brand:'sbx', category:'drinks', image:'https://picsum.photos/seed/sbxcm/500/400.jpg', desc:'Freshly steamed milk with vanilla-flavored syrup marked with espresso and topped with caramel drizzle.', calories:250, ingredients:['Espresso','Steamed Milk','Vanilla Syrup','Caramel Drizzle'], nutrition:{fat:7,carbs:34,protein:10,fiber:0,sugar:32,sodium:130}, prices:{us:{r:5.75,d:4.75},uk:{r:4.25,d:3.49},ae:{r:22,d:18},sa:{r:23,d:19},in:{r:399,d:329},de:{r:5.25,d:4.25}} },
    { id:'sbx-latte', name:'Caffe Latte', brand:'sbx', category:'drinks', image:'https://picsum.photos/seed/sbxlatte/500/400.jpg', desc:'Rich espresso combined with steamed milk for a perfectly balanced coffee experience.', calories:190, ingredients:['Espresso','Steamed Milk'], nutrition:{fat:7,carbs:19,protein:10,fiber:0,sugar:18,sodium:130}, prices:{us:{r:5.25,d:null},uk:{r:3.75,d:null},ae:{r:20,d:null},sa:{r:21,d:null},in:{r:349,d:null},de:{r:4.75,d:null}} },
    { id:'sbx-frap', name:'Caramel Frappuccino', brand:'sbx', category:'drinks', image:'https://picsum.photos/seed/sbxfrap/500/400.jpg', desc:'Blended coffee with milk, ice, and caramel syrup, topped with whipped cream and caramel drizzle.', calories:380, ingredients:['Coffee','Milk','Ice','Caramel Syrup','Whipped Cream'], nutrition:{fat:14,carbs:56,protein:5,fiber:0,sugar:54,sodium:200}, prices:{us:{r:6.25,d:4.99},uk:{r:4.75,d:3.99},ae:{r:25,d:20},sa:{r:26,d:21},in:{r:449,d:369},de:{r:5.75,d:4.49}} },
    { id:'sbx-crois', name:'Butter Croissant', brand:'sbx', category:'breakfast', image:'https://picsum.photos/seed/sbxcrois/500/400.jpg', desc:'Flaky, buttery croissant baked to golden perfection. A classic French pastry.', calories:280, ingredients:['Flour','Butter','Sugar','Yeast','Salt','Eggs'], nutrition:{fat:16,carbs:28,protein:5,fiber:1,sugar:6,sodium:340}, prices:{us:{r:3.45,d:null},uk:{r:2.75,d:null},ae:{r:14,d:null},sa:{r:15,d:null},in:{r:249,d:null},de:{r:3.15,d:null}} },
    { id:'fg-hb', name:'Hamburger', brand:'fg', category:'burgers', image:'https://picsum.photos/seed/fghb/500/400.jpg', desc:'Two fresh hand-formed patties with your choice of unlimited free toppings on a toasted sesame bun.', calories:840, ingredients:['Beef Patties','Sesame Bun','Toppings of Choice'], nutrition:{fat:45,carbs:39,protein:52,fiber:2,sugar:7,sodium:1060}, prices:{us:{r:10.49,d:8.99},uk:{r:9.49,d:7.99},ae:{r:42,d:36},sa:{r:null,d:null},in:{r:null,d:null},de:{r:null,d:null}} },
    { id:'fg-fries', name:'Cajun Fries', brand:'fg', category:'sides', image:'https://picsum.photos/seed/fgfries/500/400.jpg', desc:'Fresh-cut fries cooked in peanut oil and seasoned with Cajun spices.', calories:953, ingredients:['Potatoes','Peanut Oil','Cajun Seasoning'], nutrition:{fat:41,carbs:131,protein:17,fiber:12,sugar:1,sodium:1580}, prices:{us:{r:5.99,d:null},uk:{r:5.49,d:null},ae:{r:24,d:null},sa:{r:null,d:null},in:{r:null,d:null},de:{r:null,d:null}} },
    { id:'fg-shake', name:'Chocolate Milkshake', brand:'fg', category:'drinks', image:'https://picsum.photos/seed/fgshake/500/400.jpg', desc:'Hand-spun milkshake made with real milk and chocolate syrup, thick and creamy.', calories:670, ingredients:['Whole Milk','Chocolate Syrup','Whipped Cream'], nutrition:{fat:25,carbs:96,protein:16,fiber:2,sugar:88,sodium:380}, prices:{us:{r:5.49,d:4.49},uk:{r:4.99,d:3.99},ae:{r:22,d:18},sa:{r:null,d:null},in:{r:null,d:null},de:{r:null,d:null}} }
  ],
  offers: [
    { id:'o1', title:'Buy 1 Get 1 Free on All Burgers', desc:'Enjoy two burgers for the price of one at participating locations. Valid on all burger varieties.', brand:'kfc', discount:50, code:'BOGO50', countries:['us','uk','ae'] },
    { id:'o2', title:'20% Off Family Meals', desc:'Save 20% on all family meal combos this weekend. Perfect for family dinner.', brand:'mcd', discount:20, code:'FAMILY20', countries:['us','uk','in','de'] },
    { id:'o3', title:'Free Delivery on Orders Above $25', desc:'Order for $25 or more and get free delivery to your doorstep.', brand:'bk', discount:0, code:'FREEDEL', countries:['us','uk','ae','sa'] },
    { id:'o4', title:'30% Off Large Pizzas', desc:'Get 30% off on all large and extra-large pizzas every Tuesday.', brand:'ph', discount:30, code:'PIZZA30', countries:['us','uk','ae','in','de'] },
    { id:'o5', title:'Sub of the Day - $5.99', desc:'Enjoy a different sub every day of the week for just $5.99.', brand:'subway', discount:40, code:'SUB599', countries:['us','uk','de'] },
    { id:'o6', title:'Buy 2 Medium Pizzas Get 1 Free', desc:'Order any 2 medium pizzas and get a third one absolutely free.', brand:'dom', discount:33, code:'2FOR1', countries:['us','uk','ae','sa','in'] },
    { id:'o7', title:'Happy Hour - Half Price Drinks', desc:'All beverages at half price between 2-4 PM every weekday.', brand:'sbx', discount:50, code:'HAPPY50', countries:['us','uk','ae','sa','in','de'] },
    { id:'o8', title:'15% Off Your First Order', desc:'New to Five Guys? Get 15% off your first online order.', brand:'fg', discount:15, code:'NEW15', countries:['us','uk','ae'] }
  ],
  testimonials: [
    { name:'Sarah Mitchell', role:'Food Blogger', avatar:'https://picsum.photos/seed/avatar1/100/100.jpg', text:'FoodScope has completely changed how I compare prices across different countries. The interface is beautiful and the data is always accurate.', rating:5 },
    { name:'James Rodriguez', role:'Travel Enthusiast', avatar:'https://picsum.photos/seed/avatar2/100/100.jpg', text:'As someone who travels frequently, this is my go-to app for finding familiar food brands in new countries. The country switching feature is brilliant.', rating:5 },
    { name:'Emily Chen', role:'Digital Nomad', avatar:'https://picsum.photos/seed/avatar3/100/100.jpg', text:'I love being able to see what McDonald\'s offers in different countries and how prices compare. The detail on each product is impressive.', rating:4 },
    { name:'Michael Thompson', role:'Restaurant Analyst', avatar:'https://picsum.photos/seed/avatar4/100/100.jpg', text:'From a professional standpoint, the data accuracy and real-time pricing make FoodScope an invaluable tool for market research.', rating:5 },
    { name:'Aisha Patel', role:'Student', avatar:'https://picsum.photos/seed/avatar5/100/100.jpg', text:'Finding the best deals near my university is so easy now. The offers section saves me money every week!', rating:4 },
    { name:'David Kim', role:'Chef', avatar:'https://picsum.photos/seed/avatar6/100/100.jpg', text:'Even as a professional chef, I use FoodScope to stay informed about what global chains are offering. Great resource for menu inspiration.', rating:5 }
  ],
  faqs: [
    { q:'How does FoodScope get its pricing data?', a:'Our team regularly updates pricing information by collecting data directly from brand websites, restaurant menus, and verified user submissions. We strive to maintain the most accurate and up-to-date information possible.' },
    { q:'Can I compare prices across different countries?', a:'Absolutely! Simply use the country selector at the top of the page to switch between countries. All prices, products, and availability will update automatically to show you what\'s available in your selected country.' },
    { q:'How often are prices updated?', a:'We update our pricing data on a weekly basis. However, prices may vary by specific restaurant location within a country. Always confirm pricing at your local restaurant.' },
    { q:'Why are some products not available in my country?', a:'Food brands often tailor their menus to local tastes, regulations, and supply chain availability. We only show products that are officially available in your selected country.' },
    { q:'Is FoodScope free to use?', a:'Yes, FoodScope is completely free for all users. We believe everyone deserves access to transparent food pricing information.' },
    { q:'How can I report incorrect pricing?', a:'You can report any inaccuracies through our Contact page. Please include the product name, brand, country, and the correct price. Our team will verify and update the information promptly.' },
    { q:'Do you show nutritional information?', a:'Yes, we provide detailed nutritional information for most products including calories, fat, carbohydrates, protein, fiber, sugar, and sodium content.' },
    { q:'Can I filter by dietary requirements?', a:'Currently, we show calorie counts and full nutritional breakdowns. We are working on adding specific dietary filters (vegetarian, vegan, gluten-free) in a future update.' }
  ],
  blogs: [
    { title:'The Secret Behind KFC\'s 11 Herbs and Spices', excerpt:'Explore the history and mystery behind Colonel Sanders\' famous recipe that made KFC a global phenomenon.', image:'https://picsum.photos/seed/blog1/600/400.jpg', date:'Jan 15, 2025', category:'Brand Stories' },
    { title:'How McDonald\'s Menus Differ Around the World', excerpt:'From the McSpicy Paneer in India to the Teriyaki McBurger in Japan, discover how McDonald\'s adapts to local tastes.', image:'https://picsum.photos/seed/blog2/600/400.jpg', date:'Jan 12, 2025', category:'Global Food' },
    { title:'Top 10 Highest-Calorie Fast Food Items', excerpt:'We analyzed thousands of menu items to bring you the most calorie-dense options from popular fast food chains.', image:'https://picsum.photos/seed/blog3/600/400.jpg', date:'Jan 8, 2025', category:'Health' }
  ]
};

/* ==========================================================================
   APPLICATION STATE
   ========================================================================== */
const STATE = {
  country: 'us',
  favorites: new Set(),
  viewMode: 'grid',
  currentPage: 1,
  perPage: 12,
  searchQuery: '',
  sortBy: 'popular',
  activeFilters: { brand:[], category:[], maxPrice:999 },
  recentlyViewed: []
};

/* ==========================================================================
   UTILITY FUNCTIONS
   ========================================================================== */
const delay = ms => new Promise(r => setTimeout(r, ms));
const getCountry = id => APP_DATA.countries.find(c => c.id === id);
const getBrand = id => APP_DATA.brands.find(b => b.id === id);
const getCategory = id => APP_DATA.categories.find(c => c.id === id);
const getProduct = id => APP_DATA.products.find(p => p.id === id);

function getProductPrice(product) {
  const c = STATE.country;
  const p = product.prices[c];
  if (!p) return null;
  const country = getCountry(c);
  const current = p.d || p.r;
  const discountPercent = p.d ? Math.round((1 - p.d / p.r) * 100) : 0;
  return { regular: p.r, current, discount: p.d, discountPercent, symbol: country.symbol, available: true };
}

function getProductsForCountry() {
  return APP_DATA.products.filter(p => p.prices[STATE.country]);
}
function getBrandsForCountry() {
  return APP_DATA.brands.filter(b => b.countries.includes(STATE.country));
}
function getOffersForCountry() {
  return APP_DATA.offers.filter(o => o.countries.includes(STATE.country));
}
function getProductsByBrand(brandId) {
  return getProductsForCountry().filter(p => p.brand === brandId);
}
function getProductsByCategory(catId) {
  return getProductsForCountry().filter(p => p.category === catId);
}
function getCategoriesForBrand(brandId) {
  if (!getBrand(brandId)) return [];
  return APP_DATA.categories.filter(c => getProductsByBrand(brandId).some(p => p.category === c.id));
}

function sortProducts(products, sortBy) {
  const sorted = [...products];
  switch(sortBy) {
    case 'price-low': sorted.sort((a,b) => (getProductPrice(a)?.current||0) - (getProductPrice(b)?.current||0)); break;
    case 'price-high': sorted.sort((a,b) => (getProductPrice(b)?.current||0) - (getProductPrice(a)?.current||0)); break;
    case 'discount': sorted.sort((a,b) => (getProductPrice(b)?.discountPercent||0) - (getProductPrice(a)?.discountPercent||0)); break;
    case 'calories-low': sorted.sort((a,b) => a.calories - b.calories); break;
    case 'calories-high': sorted.sort((a,b) => b.calories - a.calories); break;
    case 'name': sorted.sort((a,b) => a.name.localeCompare(b.name)); break;
    default: sorted.sort((a,b) => b.calories - a.calories);
  }
  return sorted;
}

function filterProducts(products) {
  let filtered = [...products];
  const f = STATE.activeFilters;
  if (f.brand.length) filtered = filtered.filter(p => f.brand.includes(p.brand));
  if (f.category.length) filtered = filtered.filter(p => f.category.includes(p.category));
  if (f.maxPrice < 999) filtered = filtered.filter(p => { const pr = getProductPrice(p); return pr && pr.current <= f.maxPrice; });
  return filtered;
}

function paginate(items) {
  const start = (STATE.currentPage - 1) * STATE.perPage;
  return items.slice(start, start + STATE.perPage);
}

function showToast(msg, icon) {
  icon = icon || 'fa-check-circle';
  var t = $('<div class="toast-msg"><i class="fas ' + icon + '"></i> ' + msg + '</div>');
  $('#toast-container').append(t);
  setTimeout(function() { t.fadeOut(300, function() { $(this).remove(); }); }, 2500);
}

/* Simulate AJAX data loading with skeleton */
async function ajaxLoad(fn) {
  var args = Array.prototype.slice.call(arguments, 1);
  var $mc = $('#main-content');
  $mc.html('<div class="container" style="padding:5rem 0;"><div class="row g-3">' + '<div class="col-6 col-lg-3"><div class="skeleton skeleton-card"></div></div>'.repeat(8) + '</div></div>');
  await delay(350);
  var html = fn.apply(null, args);
  $mc.html(html);
  window.scrollTo({top: 0, behavior: 'smooth'});
  if (typeof AOS !== 'undefined') AOS.refresh();
  initSwipers();
}

/* ==========================================================================
   TEMPLATE: PRODUCT CARD
   ========================================================================== */
function productCardHTML(product) {
  var price = getProductPrice(product);
  if (!price) return '';
  var brand = getBrand(product.brand);
  var cat = getCategory(product.category);
  var isFav = STATE.favorites.has(product.id);
  return '<div class="product-card" data-aos="fade-up" data-aos-delay="50">' +
    '<div class="pc-image">' +
      '<img src="' + product.image + '" alt="' + product.name + '" loading="lazy">' +
      (price.discount ? '<div class="pc-discount-badge">-' + price.discountPercent + '%</div>' : '') +
      '<div class="pc-actions">' +
        '<button class="pc-action-btn ' + (isFav ? 'favorited' : '') + '" onclick="toggleFavorite(\'' + product.id + '\')" aria-label="Add to favorites"><i class="' + (isFav ? 'fas' : 'far') + ' fa-heart"></i></button>' +
        '<button class="pc-action-btn" onclick="quickView(\'' + product.id + '\')" aria-label="Quick view"><i class="far fa-eye"></i></button>' +
        '<button class="pc-action-btn" onclick="shareProduct(\'' + product.id + '\')" aria-label="Share"><i class="fas fa-share-alt"></i></button>' +
      '</div>' +
    '</div>' +
    '<div class="pc-body">' +
      '<div class="pc-brand"><img src="' + brand.logo + '" alt="' + brand.name + '" loading="lazy"><span>' + brand.name + '</span></div>' +
      '<div class="pc-name">' + product.name + '</div>' +
      '<div class="pc-category">' + cat.name + '</div>' +
      '<div class="pc-meta"><span><i class="fas fa-fire"></i> ' + product.calories + ' cal</span></div>' +
      '<div class="pc-footer">' +
        '<div class="pc-prices">' +
          (price.discount ? '<span class="pc-original-price">' + price.symbol + price.regular.toFixed(2) + '</span>' : '') +
          '<span class="pc-current-price">' + price.symbol + price.current.toFixed(2) + '</span>' +
        '</div>' +
        '<button class="pc-view-btn" onclick="navigate(\'#product/' + product.id + '\')">Details</button>' +
      '</div>' +
    '</div>' +
  '</div>';
}

/* ==========================================================================
   RENDER: HOME PAGE
   ========================================================================== */
function renderHome() {
  var country = getCountry(STATE.country);
  var featuredBrands = getBrandsForCountry().slice(0, 4);
  var trending = sortProducts(getProductsForCountry(), 'discount').slice(0, 8);
  var popular = sortProducts(getProductsForCountry(), 'popular').slice(0, 8);
  var offers = getOffersForCountry();
  var categories = APP_DATA.categories;
  var allBrands = getBrandsForCountry();
  var countries = APP_DATA.countries;
  var i, particles = '';
  for (i = 0; i < 12; i++) {
    var size = Math.random() * 20 + 8;
    var left = Math.random() * 100;
    var dur = Math.random() * 15 + 10;
    var del = Math.random() * 10;
    var colors = ['var(--primary)','var(--primary-light)','var(--accent)','rgba(255,255,255,0.3)'];
    particles += '<div class="hero-particle" style="width:' + size + 'px;height:' + size + 'px;left:' + left + '%;background:' + colors[i % 4] + ';animation-duration:' + dur + 's;animation-delay:' + del + 's;"></div>';
  }

  var featuredHTML = '';
  for (i = 0; i < featuredBrands.length; i++) {
    var brand = featuredBrands[i];
    var brandCats = getCategoriesForBrand(brand.id);
    var catsSlides = '';
    for (var j = 0; j < brandCats.length; j++) {
      catsSlides += '<div class="swiper-slide"><div class="fb-cat-pill" onclick="navigate(\'#category/' + brandCats[j].id + '\')">' + brandCats[j].name + '</div></div>';
    }
    var flags = '';
    for (var k = 0; k < brand.countries.length; k++) {
      flags += '<span class="fb-country-flag" title="' + getCountry(brand.countries[k]).name + '">' + getCountry(brand.countries[k]).flag + '</span>';
    }
    featuredHTML += '<div class="col-lg-6" data-aos="fade-up" data-aos-delay="' + (i * 100) + '">' +
      '<div class="featured-brand-card">' +
        '<div class="fb-cover"><img src="' + brand.cover + '" alt="' + brand.name + '" loading="lazy"></div>' +
        '<div class="fb-body">' +
          '<div class="fb-name">' + brand.name + '</div>' +
          '<div class="fb-desc">' + brand.desc + '</div>' +
          '<div class="fb-countries">' + flags + '</div>' +
          '<div class="fb-categories"><div class="swiper fb-cat-swiper-' + brand.id + '"><div class="swiper-wrapper">' + catsSlides + '</div></div></div>' +
          '<div class="fb-view-all" onclick="navigate(\'#brand/' + brand.id + '\')">View All Products <i class="fas fa-arrow-right"></i></div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  var catCardsHTML = '';
  for (i = 0; i < Math.min(8, categories.length); i++) {
    var cat = categories[i];
    var prodCount = getProductsByCategory(cat.id).length;
    var hasDiscount = getProductsByCategory(cat.id).some(function(p) { return getProductPrice(p) && getProductPrice(p).discount; });
    catCardsHTML += '<div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="' + (i * 60) + '">' +
      '<div class="category-card" onclick="navigate(\'#category/' + cat.id + '\')">' +
        '<img src="' + cat.image + '" alt="' + cat.name + '" loading="lazy">' +
        (hasDiscount ? '<div class="cc-discount">Sale</div>' : '') +
        '<div class="cc-content"><div class="cc-name">' + cat.name + '</div><div class="cc-info">' + prodCount + ' products</div></div>' +
      '</div>' +
    '</div>';
  }

  function productCarouselHTML(products, id) {
    var slides = '';
    for (var m = 0; m < products.length; m++) {
      slides += '<div class="swiper-slide" style="height:auto;">' + productCardHTML(products[m]) + '</div>';
    }
    return '<div class="swiper product-swiper-' + id + '" style="padding-bottom:2.5rem;">' +
      '<div class="swiper-wrapper">' + slides + '</div>' +
      '<div class="swiper-button-prev"></div><div class="swiper-button-next"></div>' +
    '</div>';
  }

  var offersSlides = '';
  for (i = 0; i < offers.length; i++) {
    var o = offers[i];
    var oBrand = getBrand(o.brand);
    offersSlides += '<div class="swiper-slide" style="height:auto;">' +
      '<div class="offer-card" data-aos="fade-up" data-aos-delay="' + (i * 80) + '">' +
        '<div class="oc-body">' +
          '<div class="oc-brand"><img src="' + oBrand.logo + '" alt="' + oBrand.name + '" loading="lazy"><span>' + oBrand.name + '</span></div>' +
          '<div class="oc-title">' + o.title + '</div>' +
          '<div class="oc-desc">' + o.desc + '</div>' +
          '<div class="oc-footer">' +
            '<div class="oc-discount">' + (o.discount ? o.discount + '% OFF' : 'FREE DELIVERY') + '</div>' +
            '<div class="oc-code">' + o.code + '</div>' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  var allBrandsHTML = '';
  for (i = 0; i < allBrands.length; i++) {
    var b = allBrands[i];
    var bProds = getProductsByBrand(b.id);
    var bCats = getCategoriesForBrand(b.id);
    var minPrice = bProds.length ? Math.min.apply(null, bProds.map(function(p) { return getProductPrice(p) ? getProductPrice(p).current : Infinity; })) : 0;
    var bFlags = '';
    for (var fi = 0; fi < b.countries.length; fi++) {
      bFlags += '<span style="font-size:0.9rem;">' + getCountry(b.countries[fi]).flag + '</span> ';
    }
    allBrandsHTML += '<div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="' + (i * 60) + '">' +
      '<div class="brand-card" onclick="navigate(\'#brand/' + b.id + '\')">' +
        '<div class="bc-cover"><img src="' + b.cover + '" alt="' + b.name + '" loading="lazy"></div>' +
        '<div class="bc-logo"><img src="' + b.logo + '" alt="' + b.name + '" loading="lazy"></div>' +
        '<div class="bc-body">' +
          '<div class="bc-name">' + b.name + '</div>' +
          '<div class="bc-desc">' + b.desc + '</div>' +
          '<div class="bc-stats">' +
            '<span class="bc-stat"><strong>' + bCats.length + '</strong> categories</span>' +
            '<span class="bc-stat"><strong>' + bProds.length + '</strong> products</span>' +
          '</div>' +
          '<div class="bc-footer">' +
            '<span class="bc-price">From <strong>' + country.symbol + minPrice.toFixed(2) + '</strong></span>' +
            '<button class="bc-btn">View</button>' +
          '</div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  var countriesHTML = '';
  for (i = 0; i < countries.length; i++) {
    var c = countries[i];
    var cBrandCount = APP_DATA.brands.filter(function(br) { return br.countries.includes(c.id); }).length;
    var cProdCount = APP_DATA.products.filter(function(pr) { return pr.prices[c.id]; }).length;
    countriesHTML += '<div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="' + (i * 50) + '">' +
      '<div class="country-card" onclick="changeCountry(\'' + c.id + '\')">' +
        '<span class="flag">' + c.flag + '</span>' +
        '<div class="name">' + c.name + '</div>' +
        '<div class="info">' + cBrandCount + ' brands &middot; ' + cProdCount + ' items</div>' +
      '</div>' +
    '</div>';
  }

  var testHTML = '';
  for (i = 0; i < APP_DATA.testimonials.length; i++) {
    var tt = APP_DATA.testimonials[i];
    var stars = '';
    for (var s = 0; s < tt.rating; s++) stars += '<i class="fas fa-star"></i> ';
    for (s = tt.rating; s < 5; s++) stars += '<i class="far fa-star"></i> ';
    testHTML += '<div class="swiper-slide" style="height:auto;">' +
      '<div class="testimonial-card" data-aos="fade-up" data-aos-delay="' + (i * 80) + '">' +
        '<div class="tc-stars">' + stars + '</div>' +
        '<div class="tc-text">"' + tt.text + '"</div>' +
        '<div class="tc-author">' +
          '<img class="tc-avatar" src="' + tt.avatar + '" alt="' + tt.name + '" loading="lazy">' +
          '<div><div class="tc-name">' + tt.name + '</div><div class="tc-role">' + tt.role + '</div></div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  var faqHTML = '';
  for (i = 0; i < APP_DATA.faqs.length; i++) {
    var f = APP_DATA.faqs[i];
    faqHTML += '<div class="faq-item" data-aos="fade-up" data-aos-delay="' + (i * 40) + '">' +
      '<button class="faq-question" onclick="toggleFAQ(this)">' + f.q + ' <i class="fas fa-chevron-down"></i></button>' +
      '<div class="faq-answer"><div class="faq-answer-inner">' + f.a + '</div></div>' +
    '</div>';
  }

  var blogHTML = '';
  for (i = 0; i < APP_DATA.blogs.length; i++) {
    var bl = APP_DATA.blogs[i];
    blogHTML += '<div class="col-md-4" data-aos="fade-up" data-aos-delay="' + (i * 100) + '">' +
      '<div class="blog-card">' +
        '<img src="' + bl.image + '" alt="' + bl.title + '" loading="lazy">' +
        '<div class="blog-card-body">' +
          '<div class="blog-card-meta"><span><i class="far fa-calendar"></i> ' + bl.date + '</span><span><i class="far fa-folder"></i> ' + bl.category + '</span></div>' +
          '<div class="blog-card-title">' + bl.title + '</div>' +
          '<div class="blog-card-excerpt">' + bl.excerpt + '</div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }

  var popSearches = ['Big Mac','Zinger Burger','Margherita Pizza','Caramel Macchiato','Family Bucket','Chicken McNuggets'];
  var popTags = '';
  for (i = 0; i < popSearches.length; i++) {
    popTags += '<span class="hero-popular-tag" onclick="heroSearchTag(\'' + popSearches[i] + '\')">' + popSearches[i] + '</span>';
  }

  var heroCountryOpts = '';
  var heroBrandOpts = '<option value="">All Brands</option>';
  var heroCatOpts = '<option value="">All Categories</option>';
  for (i = 0; i < countries.length; i++) {
    heroCountryOpts += '<option value="' + countries[i].id + '"' + (countries[i].id === STATE.country ? ' selected' : '') + '>' + countries[i].flag + ' ' + countries[i].name + '</option>';
  }
  for (i = 0; i < allBrands.length; i++) {
    heroBrandOpts += '<option value="' + allBrands[i].id + '">' + allBrands[i].name + '</option>';
  }
  for (i = 0; i < categories.length; i++) {
    heroCatOpts += '<option value="' + categories[i].id + '">' + categories[i].name + '</option>';
  }

  var wcuItems = [
    { icon:'fa-globe', title:'Global Coverage', desc:'Compare menus and prices across 6+ countries and 8+ major food brands.' },
    { icon:'fa-bolt', title:'Real-Time Prices', desc:'Up-to-date pricing that reflects current menu costs in each country.' },
    { icon:'fa-filter', title:'Smart Filters', desc:'Filter by brand, category, price, calories, and availability instantly.' },
    { icon:'fa-shield-halved', title:'Verified Data', desc:'All information is verified and regularly updated by our team.' }
  ];
  var wcuHTML = '';
  for (i = 0; i < wcuItems.length; i++) {
    var w = wcuItems[i];
    wcuHTML += '<div class="col-md-6 col-lg-3" data-aos="fade-up" data-aos-delay="' + (i * 100) + '">' +
      '<div class="wcu-card">' +
        '<div class="wcu-icon"><i class="fas ' + w.icon + '"></i></div>' +
        '<div class="wcu-title">' + w.title + '</div>' +
        '<div class="wcu-desc">' + w.desc + '</div>' +
      '</div>' +
    '</div>';
  }

  return '' +
  '<section class="hero-section" aria-label="Hero">' +
    '<div class="hero-bg"></div>' +
    '<div class="hero-particles">' + particles + '</div>' +
    '<div class="hero-content">' +
      '<div class="hero-badge" data-aos="fade-down"><i class="fas fa-sparkles"></i> Trusted by 2M+ users worldwide</div>' +
      '<h1 class="hero-title" data-aos="fade-up">Discover Global <span class="highlight">Food Flavors</span></h1>' +
      '<p class="hero-subtitle" data-aos="fade-up" data-aos-delay="100">Compare menus, prices, and deals from world-famous food brands across countries — all in one place.</p>' +
      '<div class="hero-search-box" data-aos="fade-up" data-aos-delay="200">' +
        '<div class="hero-search-row">' +
          '<input type="text" class="hero-search-input" placeholder="Search for burgers, pizza, coffee..." id="hero-search" aria-label="Search products">' +
          '<button class="hero-search-btn-main" onclick="heroSearch()"><i class="fas fa-search"></i> Search</button>' +
        '</div>' +
        '<div class="hero-filters">' +
          '<select class="hero-filter-select" id="hero-country" aria-label="Select country" onchange="changeCountry(this.value)">' + heroCountryOpts + '</select>' +
          '<select class="hero-filter-select" id="hero-brand" aria-label="Select brand" onchange="if(this.value)navigate(\'#brand/\'+this.value)">' + heroBrandOpts + '</select>' +
          '<select class="hero-filter-select" id="hero-cat" aria-label="Select category" onchange="if(this.value)navigate(\'#category/\'+this.value)">' + heroCatOpts + '</select>' +
        '</div>' +
      '</div>' +
      '<div class="hero-popular" data-aos="fade-up" data-aos-delay="300"><span>Popular:</span>' + popTags + '</div>' +
    '</div>' +
    '<div class="hero-scroll-indicator"><span style="font-size:0.75rem;">Scroll to explore</span><i class="fas fa-chevron-down"></i></div>' +
  '</section>' +

  '<section class="section-padding" style="background:var(--bg-alt);" aria-label="Featured brands"><div class="container">' +
    '<div class="section-header">' +
      '<div class="section-label" data-aos="fade-up">Featured Brands</div>' +
      '<h2 class="section-title" data-aos="fade-up">Explore Top Food Brands</h2>' +
      '<p class="section-desc" data-aos="fade-up">Discover menus from the world\'s most popular food chains available in ' + country.name + '</p>' +
    '</div>' +
    '<div class="row g-4">' + featuredHTML + '</div>' +
    '<div class="text-center mt-4" data-aos="fade-up"><a href="#brands" class="btn-primary-custom" style="display:inline-block;">View All Brands <i class="fas fa-arrow-right ms-1"></i></a></div>' +
  '</div></section>' +

  '<section class="section-padding" aria-label="Trending products"><div class="container">' +
    '<div class="section-header"><div class="section-label" data-aos="fade-up">Trending Now</div><h2 class="section-title" data-aos="fade-up">Biggest Discounts This Week</h2></div>' +
    productCarouselHTML(trending, 'trending') +
  '</div></section>' +

  '<section class="section-padding" style="background:var(--bg-alt);" aria-label="Categories"><div class="container">' +
    '<div class="section-header">' +
      '<div class="section-label" data-aos="fade-up">Categories</div>' +
      '<h2 class="section-title" data-aos="fade-up">Browse by Category</h2>' +
      '<p class="section-desc" data-aos="fade-up">Find exactly what you\'re craving</p>' +
    '</div>' +
    '<div class="row g-3">' + catCardsHTML + '</div>' +
    '<div class="text-center mt-4" data-aos="fade-up"><a href="#categories" class="btn-primary-custom" style="display:inline-block;">All Categories <i class="fas fa-arrow-right ms-1"></i></a></div>' +
  '</div></section>' +

  '<section class="section-padding" aria-label="Popular meals"><div class="container">' +
    '<div class="section-header"><div class="section-label" data-aos="fade-up">Most Popular</div><h2 class="section-title" data-aos="fade-up">Fan Favorites</h2></div>' +
    productCarouselHTML(popular, 'popular') +
  '</div></section>' +

  '<section class="section-padding" style="background:var(--bg-alt);" aria-label="Offers"><div class="container">' +
    '<div class="section-header"><div class="section-label" data-aos="fade-up">Hot Deals</div><h2 class="section-title" data-aos="fade-up">Latest Offers & Discounts</h2></div>' +
    '<div class="swiper offer-swiper" style="padding-bottom:2.5rem;"><div class="swiper-wrapper">' + offersSlides + '</div><div class="swiper-button-prev"></div><div class="swiper-button-next"></div></div>' +
    '<div class="text-center mt-3" data-aos="fade-up"><a href="#offers" class="btn-primary-custom" style="display:inline-block;">View All Offers <i class="fas fa-arrow-right ms-1"></i></a></div>' +
  '</div></section>' +

  '<section class="section-padding" aria-label="All brands"><div class="container">' +
    '<div class="section-header"><div class="section-label" data-aos="fade-up">All Brands</div><h2 class="section-title" data-aos="fade-up">Available in ' + country.name + '</h2></div>' +
    '<div class="row g-3">' + allBrandsHTML + '</div>' +
  '</div></section>' +

  '<section class="section-padding" style="background:var(--bg-alt);" aria-label="Countries"><div class="container">' +
    '<div class="section-header"><div class="section-label" data-aos="fade-up">Global Reach</div><h2 class="section-title" data-aos="fade-up">Countries We Cover</h2></div>' +
    '<div class="row g-3">' + countriesHTML + '</div>' +
  '</div></section>' +

  '<section class="section-padding" aria-label="Why choose us"><div class="container">' +
    '<div class="section-header"><div class="section-label" data-aos="fade-up">Why FoodScope</div><h2 class="section-title" data-aos="fade-up">Why Choose Us</h2></div>' +
    '<div class="row g-4">' + wcuHTML + '</div>' +
  '</div></section>' +

  '<section class="section-padding" style="background:var(--bg-alt);" aria-label="Testimonials"><div class="container">' +
    '<div class="section-header"><div class="section-label" data-aos="fade-up">Testimonials</div><h2 class="section-title" data-aos="fade-up">What Our Users Say</h2></div>' +
    '<div class="swiper testimonial-swiper" style="padding-bottom:2.5rem;"><div class="swiper-wrapper">' + testHTML + '</div><div class="swiper-pagination"></div></div>' +
  '</div></section>' +

  '<section class="section-padding" aria-label="Blog"><div class="container">' +
    '<div class="section-header"><div class="section-label" data-aos="fade-up">From Our Blog</div><h2 class="section-title" data-aos="fade-up">Food Insights & Stories</h2></div>' +
    '<div class="row g-4">' + blogHTML + '</div>' +
  '</div></section>' +

  '<section class="section-padding" style="background:var(--bg-alt);" aria-label="FAQ"><div class="container" style="max-width:800px;">' +
    '<div class="section-header"><div class="section-label" data-aos="fade-up">FAQ</div><h2 class="section-title" data-aos="fade-up">Frequently Asked Questions</h2></div>' +
    '<div>' + faqHTML + '</div>' +
  '</div></section>' +

  '<section class="newsletter-section" aria-label="Newsletter"><div class="newsletter-content">' +
    '<h2 class="newsletter-title" data-aos="fade-up">Stay Hungry, Stay Updated</h2>' +
    '<p class="newsletter-desc" data-aos="fade-up" data-aos-delay="100">Get exclusive deals, new menu alerts, and food insights delivered to your inbox weekly.</p>' +
    '<div class="newsletter-form" data-aos="fade-up" data-aos-delay="200">' +
      '<input type="email" class="newsletter-input" placeholder="Enter your email address" id="newsletter-email" aria-label="Email for newsletter">' +
      '<button class="newsletter-btn" onclick="subscribeNewsletter()">Subscribe <i class="fas fa-paper-plane ms-1"></i></button>' +
    '</div>' +
  '</div></section>' +

  '<section class="section-padding" aria-label="Download app"><div class="container">' +
    '<div class="app-banner" data-aos="fade-up">' +
      '<div class="app-banner-content">' +
        '<h3>Take FoodScope On The Go</h3>' +
        '<p>Download our app for the best experience. Compare prices, find deals, and discover new menus right from your pocket.</p>' +
        '<div class="app-buttons">' +
          '<a href="#" class="app-btn" aria-label="Download on App Store"><i class="fab fa-apple"></i><div class="app-btn-text">Download on the<small>App Store</small></div></a>' +
          '<a href="#" class="app-btn" aria-label="Get it on Google Play"><i class="fab fa-google-play"></i><div class="app-btn-text">Get it on<small>Google Play</small></div></a>' +
        '</div>' +
      '</div>' +
      '<div class="app-banner-visual"><div class="app-phone"><i class="fas fa-utensils"></i></div></div>' +
    '</div>' +
  '</div></section>';
}

/* ==========================================================================
   RENDER: BRANDS LISTING
   ========================================================================== */
function renderBrands() {
  var brands = getBrandsForCountry();
  var country = getCountry(STATE.country);
  var html = '<div class="page-banner"><div class="container page-banner-content">' +
    '<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="#home">Home</a></li><li class="breadcrumb-item active">All Brands</li></ol></nav>' +
    '<h1>All Food Brands</h1>' +
    '<p>Browse ' + brands.length + ' brands available in ' + country.name + '</p>' +
  '</div></div>' +
  '<section class="section-padding"><div class="container"><div class="row g-3">';
  for (var i = 0; i < brands.length; i++) {
    var b = brands[i];
    var prods = getProductsByBrand(b.id);
    var cats = getCategoriesForBrand(b.id);
    var minPrice = prods.length ? Math.min.apply(null, prods.map(function(p) { return getProductPrice(p) ? getProductPrice(p).current : Infinity; })) : 0;
    html += '<div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="' + (i * 60) + '">' +
      '<div class="brand-card" onclick="navigate(\'#brand/' + b.id + '\')">' +
        '<div class="bc-cover"><img src="' + b.cover + '" alt="' + b.name + '" loading="lazy"></div>' +
        '<div class="bc-logo"><img src="' + b.logo + '" alt="' + b.name + '" loading="lazy"></div>' +
        '<div class="bc-body">' +
          '<div class="bc-name">' + b.name + '</div>' +
          '<div class="bc-desc">' + b.desc + '</div>' +
          '<div class="bc-stats"><span class="bc-stat"><strong>' + cats.length + '</strong> categories</span><span class="bc-stat"><strong>' + prods.length + '</strong> products</span></div>' +
          '<div class="bc-footer"><span class="bc-price">From <strong>' + country.symbol + minPrice.toFixed(2) + '</strong></span><button class="bc-btn">View</button></div>' +
        '</div>' +
      '</div>' +
    '</div>';
  }
  html += '</div></div></section>';
  return html;
}

/* ==========================================================================
   RENDER: BRAND DETAIL
   ========================================================================== */
function renderBrandDetail(brandId) {
  var brand = getBrand(brandId);
  if (!brand) return render404();
  var country = getCountry(STATE.country);
  if (brand.countries.indexOf(STATE.country) === -1) {
    return '<div class="error-page"><div><div class="error-code">:(</div><h2 class="error-title">Not Available in ' + country.name + '</h2><p class="error-desc">' + brand.name + ' is not available in ' + country.name + '. Try switching to a different country.</p><a href="#brands" class="btn-primary-custom" style="display:inline-block;">Browse Brands</a></div></div>';
  }
  var cats = getCategoriesForBrand(brandId);
  var allProds = getProductsByBrand(brandId);
  var filtered = filterProducts(allProds);
  var sorted = sortProducts(filtered, STATE.sortBy);
  var paged = paginate(sorted);
  var totalPages = Math.ceil(sorted.length / STATE.perPage);
  var flags = '';
  for (var i = 0; i < brand.countries.length; i++) {
    flags += '<span style="font-size:1.3rem;margin-right:0.25rem;" title="' + getCountry(brand.countries[i]).name + '">' + getCountry(brand.countries[i]).flag + '</span>';
  }
  var catFilters = '';
  for (i = 0; i < cats.length; i++) {
    var c = cats[i];
    var cCount = getProductsByCategory(c.id).filter(function(p) { return p.brand === brandId; }).length;
    catFilters += '<label class="filter-option"><input type="checkbox" value="' + c.id + '"' + (STATE.activeFilters.category.indexOf(c.id) > -1 ? ' checked' : '') + ' onchange="toggleFilter(\'category\',\'' + c.id + '\')">' + c.name + '<span class="count">' + cCount + '</span></label>';
  }
  var sortOpts = [['popular','Most Popular'],['price-low','Price: Low to High'],['price-high','Price: High to Low'],['discount','Highest Discount'],['calories-low','Calories: Low to High'],['name','Alphabetical']];
  var sortSelect = '';
  for (i = 0; i < sortOpts.length; i++) {
    sortSelect += '<option value="' + sortOpts[i][0] + '"' + (STATE.sortBy === sortOpts[i][0] ? ' selected' : '') + '>' + sortOpts[i][1] + '</option>';
  }
  var productsHTML = '';
  for (i = 0; i < paged.length; i++) {
    productsHTML += '<div class="' + (STATE.viewMode === 'grid' ? 'col-6 col-md-4' : 'col-12') + '">' + productCardHTML(paged[i]) + '</div>';
  }
  if (sorted.length === 0) {
    productsHTML = '<div class="col-12"><div class="empty-state"><i class="fas fa-search"></i><h3>No Products Found</h3><p>Try adjusting your filters.</p></div></div>';
  }
  var pagHTML = '';
  if (totalPages > 1) {
    for (i = 1; i <= totalPages; i++) {
      pagHTML += '<button class="page-btn ' + (i === STATE.currentPage ? 'active' : '') + '" onclick="STATE.currentPage=' + i + ';refreshBrandProducts(\'' + brandId + '\')">' + i + '</button>';
    }
    pagHTML = '<div class="pagination-wrap">' + pagHTML + '</div>';
  }
  return '<div class="page-banner"><div class="container page-banner-content">' +
    '<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="#home">Home</a></li><li class="breadcrumb-item"><a href="#brands">Brands</a></li><li class="breadcrumb-item active">' + brand.name + '</li></ol></nav>' +
    '<div style="display:flex;align-items:center;gap:1rem;margin-bottom:0.75rem;"><img src="' + brand.logo + '" alt="' + brand.name + '" style="width:56px;height:56px;border-radius:var(--radius-md);background:#fff;padding:6px;" loading="lazy"><h1>' + brand.name + '</h1></div>' +
    '<p>' + brand.desc + '</p>' +
    '<div style="margin-top:1rem;display:flex;gap:1.5rem;flex-wrap:wrap;">' +
      '<span style="color:rgba(255,255,255,0.7);font-size:0.9rem;"><i class="fas fa-globe me-1" style="color:var(--primary);"></i> ' + flags + '</span>' +
      '<span style="color:rgba(255,255,255,0.7);font-size:0.9rem;"><i class="fas fa-layer-group me-1" style="color:var(--primary);"></i> ' + cats.length + ' categories</span>' +
      '<span style="color:rgba(255,255,255,0.7);font-size:0.9rem;"><i class="fas fa-utensils me-1" style="color:var(--primary);"></i> ' + allProds.length + ' products</span>' +
    '</div>' +
  '</div></div>' +
  '<section class="section-padding"><div class="container"><div class="row">' +
    '<div class="col-lg-3"><div class="filter-panel">' +
      '<div class="filter-title">Filters</div>' +
      '<div class="filter-group"><div class="filter-group-title">Categories</div>' + catFilters + '</div>' +
      '<div class="filter-group"><div class="filter-group-title">Price Range</div>' +
        '<div class="price-range-wrap">' +
          '<input type="range" min="0" max="100" value="' + STATE.activeFilters.maxPrice + '" oninput="STATE.activeFilters.maxPrice=+this.value;$(\'#price-max-label\').text(\'' + country.symbol + '\'+this.value);">' +
          '<div class="price-range-labels"><span>' + country.symbol + '0</span><span id="price-max-label">' + country.symbol + STATE.activeFilters.maxPrice + '</span></div>' +
        '</div>' +
      '</div>' +
      '<button class="filter-apply-btn" onclick="refreshBrandProducts(\'' + brandId + '\')">Apply Filters</button>' +
      '<button class="filter-reset-btn" onclick="resetFilters();refreshBrandProducts(\'' + brandId + '\')">Reset All</button>' +
    '</div></div>' +
    '<div class="col-lg-9">' +
      '<div class="toolbar">' +
        '<div class="toolbar-left"><span class="toolbar-count">Showing <strong>' + sorted.length + '</strong> products</span></div>' +
        '<div class="toolbar-right">' +
          '<select class="toolbar-sort" onchange="STATE.sortBy=this.value;refreshBrandProducts(\'' + brandId + '\')" aria-label="Sort products">' + sortSelect + '</select>' +
          '<button class="toolbar-view-btn ' + (STATE.viewMode === 'grid' ? 'active' : '') + '" onclick="STATE.viewMode=\'grid\';refreshBrandProducts(\'' + brandId + '\')" aria-label="Grid view"><i class="fas fa-th"></i></button>' +
          '<button class="toolbar-view-btn ' + (STATE.viewMode === 'list' ? 'active' : '') + '" onclick="STATE.viewMode=\'list\';refreshBrandProducts(\'' + brandId + '\')" aria-label="List view"><i class="fas fa-list"></i></button>' +
        '</div>' +
      '</div>' +
      '<div class="row g-3 ' + (STATE.viewMode === 'list' ? 'products-list' : '') + '" id="brand-products-grid">' + productsHTML + '</div>' +
      pagHTML +
    '</div>' +
  '</div></div></section>';
}

function refreshBrandProducts(brandId) {
  STATE.currentPage = 1;
  $('#main-content').html(renderBrandDetail(brandId));
  if (typeof AOS !== 'undefined') AOS.refresh();
}

/* ==========================================================================
   RENDER: CATEGORY DETAIL
   ========================================================================== */
function renderCategoryDetail(catId) {
  var cat = getCategory(catId);
  if (!cat) return render404();
  var allProds = getProductsByCategory(catId);
  var filtered = filterProducts(allProds);
  var sorted = sortProducts(filtered, STATE.sortBy);
  var paged = paginate(sorted);
  var totalPages = Math.ceil(sorted.length / STATE.perPage);
  var country = getCountry(STATE.country);
  var brandsMap = {};
  for (var i = 0; i < allProds.length; i++) { brandsMap[allProds[i].brand] = true; }
  var brands = Object.keys(brandsMap).map(getBrand);
  var brandFilters = '';
  for (i = 0; i < brands.length; i++) {
    var bCount = allProds.filter(function(p) { return p.brand === brands[i].id; }).length;
    brandFilters += '<label class="filter-option"><input type="checkbox" value="' + brands[i].id + '"' + (STATE.activeFilters.brand.indexOf(brands[i].id) > -1 ? ' checked' : '') + ' onchange="toggleFilter(\'brand\',\'' + brands[i].id + '\')">' + brands[i].name + '<span class="count">' + bCount + '</span></label>';
  }
  var sortOpts = [['popular','Most Popular'],['price-low','Price: Low to High'],['price-high','Price: High to Low'],['discount','Highest Discount'],['calories-low','Calories: Low to High'],['name','Alphabetical']];
  var sortSelect = '';
  for (i = 0; i < sortOpts.length; i++) {
    sortSelect += '<option value="' + sortOpts[i][0] + '"' + (STATE.sortBy === sortOpts[i][0] ? ' selected' : '') + '>' + sortOpts[i][1] + '</option>';
  }
  var productsHTML = '';
  for (i = 0; i < paged.length; i++) {
    productsHTML += '<div class="' + (STATE.viewMode === 'grid' ? 'col-6 col-md-4' : 'col-12') + '">' + productCardHTML(paged[i]) + '</div>';
  }
  if (sorted.length === 0) {
    productsHTML = '<div class="col-12"><div class="empty-state"><i class="fas fa-search"></i><h3>No Products Found</h3><p>Try adjusting your filters.</p></div></div>';
  }
  var pagHTML = '';
  if (totalPages > 1) {
    for (i = 1; i <= totalPages; i++) {
      pagHTML += '<button class="page-btn ' + (i === STATE.currentPage ? 'active' : '') + '" onclick="STATE.currentPage=' + i + ';refreshCategoryProducts(\'' + catId + '\')">' + i + '</button>';
    }
    pagHTML = '<div class="pagination-wrap">' + pagHTML + '</div>';
  }
  return '<div class="page-banner"><div class="container page-banner-content">' +
    '<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="#home">Home</a></li><li class="breadcrumb-item"><a href="#categories">Categories</a></li><li class="breadcrumb-item active">' + cat.name + '</li></ol></nav>' +
    '<h1>' + cat.name + '</h1>' +
    '<p>' + cat.desc + ' — ' + allProds.length + ' products available in ' + country.name + '</p>' +
  '</div></div>' +
  '<section class="section-padding"><div class="container"><div class="row">' +
    '<div class="col-lg-3"><div class="filter-panel">' +
      '<div class="filter-title">Filters</div>' +
      '<div class="filter-group"><div class="filter-group-title">Brands</div>' + brandFilters + '</div>' +
      '<div class="filter-group"><div class="filter-group-title">Price Range</div>' +
        '<div class="price-range-wrap">' +
          '<input type="range" min="0" max="100" value="' + STATE.activeFilters.maxPrice + '" oninput="STATE.activeFilters.maxPrice=+this.value;$(\'#cat-price-max\').text(\'' + country.symbol + '\'+this.value);">' +
          '<div class="price-range-labels"><span>' + country.symbol + '0</span><span id="cat-price-max">' + country.symbol + STATE.activeFilters.maxPrice + '</span></div>' +
        '</div>' +
      '</div>' +
      '<button class="filter-apply-btn" onclick="refreshCategoryProducts(\'' + catId + '\')">Apply Filters</button>' +
      '<button class="filter-reset-btn" onclick="resetFilters();refreshCategoryProducts(\'' + catId + '\')">Reset All</button>' +
    '</div></div>' +
    '<div class="col-lg-9">' +
      '<div class="toolbar">' +
        '<div class="toolbar-left"><span class="toolbar-count">Showing <strong>' + sorted.length + '</strong> products</span></div>' +
        '<div class="toolbar-right">' +
          '<select class="toolbar-sort" onchange="STATE.sortBy=this.value;refreshCategoryProducts(\'' + catId + '\')" aria-label="Sort products">' + sortSelect + '</select>' +
          '<button class="toolbar-view-btn ' + (STATE.viewMode === 'grid' ? 'active' : '') + '" onclick="STATE.viewMode=\'grid\';refreshCategoryProducts(\'' + catId + '\')" aria-label="Grid view"><i class="fas fa-th"></i></button>' +
          '<button class="toolbar-view-btn ' + (STATE.viewMode === 'list' ? 'active' : '') + '" onclick="STATE.viewMode=\'list\';refreshCategoryProducts(\'' + catId + '\')" aria-label="List view"><i class="fas fa-list"></i></button>' +
        '</div>' +
      '</div>' +
      '<div class="row g-3 ' + (STATE.viewMode === 'list' ? 'products-list' : '') + '">' + productsHTML + '</div>' +
      pagHTML +
    '</div>' +
  '</div></div></section>';
}

function refreshCategoryProducts(catId) {
  STATE.currentPage = 1;
  $('#main-content').html(renderCategoryDetail(catId));
  if (typeof AOS !== 'undefined') AOS.refresh();
}

/* ==========================================================================
   RENDER: PRODUCT DETAIL
   ========================================================================== */
function renderProductDetail(productId) {
  var product = getProduct(productId);
  if (!product || !product.prices[STATE.country]) return render404();
  var price = getProductPrice(product);
  var brand = getBrand(product.brand);
  var cat = getCategory(product.category);
  var country = getCountry(STATE.country);
  STATE.recentlyViewed = [productId].concat(STATE.recentlyViewed.filter(function(id) { return id !== productId; })).slice(0, 6);
  var related = getProductsByCategory(product.category).filter(function(p) { return p.id !== productId; }).slice(0, 4);
  var reviews = [
    { name:'Alex Johnson', avatar:'https://picsum.photos/seed/rev1/80/80.jpg', date:'Jan 10, 2025', rating:5, text:'Absolutely delicious! This is my go-to order every time. The flavors are perfectly balanced and the portion size is generous.' },
    { name:'Maria Garcia', avatar:'https://picsum.photos/seed/rev2/80/80.jpg', date:'Jan 8, 2025', rating:4, text:'Really good quality and taste. Only reason for 4 stars is I wish there were more options to customize.' },
    { name:'Tom Williams', avatar:'https://picsum.photos/seed/rev3/80/80.jpg', date:'Jan 5, 2025', rating:5, text:'Best in class! I\'ve tried similar items from other brands and nothing compares. Highly recommend.' }
  ];
  var images = [product.image, 'https://picsum.photos/seed/' + productId + 'b/500/400.jpg', 'https://picsum.photos/seed/' + productId + 'c/500/400.jpg', 'https://picsum.photos/seed/' + productId + 'd/500/400.jpg'];
  var thumbs = '';
  for (var i = 0; i < images.length; i++) {
    thumbs += '<div class="pd-thumb ' + (i === 0 ? 'active' : '') + '" onclick="document.getElementById(\'pd-main-img\').src=\'' + images[i] + '\';document.querySelectorAll(\'.pd-thumb\').forEach(function(t){t.classList.remove(\'active\')});this.classList.add(\'active\');"><img src="' + images[i] + '" alt="' + product.name + ' ' + (i + 1) + '" loading="lazy"></div>';
  }
  var ingTags = '';
  for (i = 0; i < product.ingredients.length; i++) {
    ingTags += '<li style="padding:0.35rem 0.75rem;border-radius:var(--radius-full);background:var(--bg-alt);font-size:0.85rem;color:var(--text-secondary);border:1px solid var(--border-light);">' + product.ingredients[i] + '</li>';
  }
  var countryPricing = '';
  for (i = 0; i < brand.countries.length; i++) {
    var cid = brand.countries[i];
    var cp = product.prices[cid];
    var co = getCountry(cid);
    if (!cp) continue;
    countryPricing += '<div class="col-6 col-md-4 col-lg-2"><div style="text-align:center;padding:1rem;border-radius:var(--radius-sm);background:var(--bg-alt);border:1px solid var(--border-light);">' +
      '<div style="font-size:2rem;margin-bottom:0.5rem;">' + co.flag + '</div>' +
      '<div style="font-weight:600;font-size:0.85rem;margin-bottom:0.25rem;">' + co.name + '</div>' +
      '<div style="font-size:1.1rem;font-weight:700;color:var(--primary);">' + co.symbol + (cp.d || cp.r).toFixed(2) + '</div>' +
      (cp.d ? '<div style="font-size:0.75rem;color:var(--muted);text-decoration:line-through;">' + co.symbol + cp.r.toFixed(2) + '</div>' : '') +
    '</div></div>';
  }
  var reviewsHTML = '';
  for (i = 0; i < reviews.length; i++) {
    var r = reviews[i];
    var rStars = '';
    for (var s = 0; s < r.rating; s++) rStars += '<i class="fas fa-star"></i> ';
    for (s = r.rating; s < 5; s++) rStars += '<i class="far fa-star"></i> ';
    reviewsHTML += '<div class="review-item">' +
      '<div class="review-header"><img class="review-avatar" src="' + r.avatar + '" alt="' + r.name + '" loading="lazy"><div><div class="review-name">' + r.name + '</div><div class="review-date">' + r.date + '</div></div></div>' +
      '<div class="review-stars">' + rStars + '</div>' +
      '<div class="review-text">' + r.text + '</div>' +
    '</div>';
  }
  var relSlides = '';
  for (i = 0; i < related.length; i++) {
    relSlides += '<div class="swiper-slide" style="height:auto;max-width:280px;">' + productCardHTML(related[i]) + '</div>';
  }
  var isFav = STATE.favorites.has(product.id);
  var randomRating = (4 + Math.floor(Math.random() * 4)) + '.' + (Math.floor(Math.random() * 9) + 1);
  return '<div class="page-banner" style="padding-bottom:1.5rem;"><div class="container page-banner-content">' +
    '<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="#home">Home</a></li><li class="breadcrumb-item"><a href="#brand/' + brand.id + '">' + brand.name + '</a></li><li class="breadcrumb-item"><a href="#category/' + cat.id + '">' + cat.name + '</a></li><li class="breadcrumb-item active">' + product.name + '</li></ol></nav>' +
  '</div></div>' +
  '<section class="section-padding" style="padding-top:1.5rem;"><div class="container">' +
    '<div class="row g-4">' +
      '<div class="col-lg-6"><div class="pd-gallery" data-aos="fade-right">' +
        '<div class="pd-main-image"><img src="' + images[0] + '" alt="' + product.name + '" id="pd-main-img"></div>' +
        '<div class="pd-thumbs">' + thumbs + '</div>' +
      '</div></div>' +
      '<div class="col-lg-6"><div class="pd-info" data-aos="fade-left">' +
        '<div class="pd-brand-row"><img src="' + brand.logo + '" alt="' + brand.name + '" loading="lazy"><span>' + brand.name + '</span></div>' +
        '<h1 class="pd-name">' + product.name + '</h1>' +
        '<span class="pd-category-tag"><i class="fas fa-tag me-1"></i>' + cat.name + '</span>' +
        '<p class="pd-desc">' + product.desc + '</p>' +
        '<div class="pd-pricing">' +
          '<div class="pd-pricing-row">' +
            (price.discount ? '<span class="pd-original">' + price.symbol + price.regular.toFixed(2) + '</span>' : '') +
            '<span class="pd-current">' + price.symbol + price.current.toFixed(2) + '</span>' +
            (price.discount ? '<span class="pd-save">Save ' + price.symbol + (price.regular - price.current).toFixed(2) + ' (' + price.discountPercent + '% off)</span>' : '') +
          '</div>' +
          '<div style="font-size:0.82rem;color:var(--muted);margin-top:0.25rem;"><i class="fas fa-map-marker-alt me-1"></i> Price in ' + country.name + ' (' + country.currency + ')</div>' +
        '</div>' +
        '<div class="pd-details-grid">' +
          '<div class="pd-detail-item"><i class="fas fa-fire"></i><span class="label">Calories</span><span class="value">' + product.calories + ' kcal</span></div>' +
          '<div class="pd-detail-item"><i class="fas fa-globe"></i><span class="label">Available</span><span class="value">' + brand.countries.length + ' countries</span></div>' +
          '<div class="pd-detail-item"><i class="fas fa-check-circle"></i><span class="label">Status</span><span class="value" style="color:var(--success);">In Stock</span></div>' +
          '<div class="pd-detail-item"><i class="fas fa-star"></i><span class="label">Rating</span><span class="value">' + randomRating + '/5</span></div>' +
        '</div>' +
        '<div class="pd-share">' +
          '<span style="font-size:0.88rem;color:var(--muted);margin-right:0.5rem;">Share:</span>' +
          '<button class="pd-share-btn" onclick="shareProduct(\'' + product.id + '\')" aria-label="Share on Facebook"><i class="fab fa-facebook-f"></i></button>' +
          '<button class="pd-share-btn" onclick="shareProduct(\'' + product.id + '\')" aria-label="Share on Twitter"><i class="fab fa-twitter"></i></button>' +
          '<button class="pd-share-btn" onclick="shareProduct(\'' + product.id + '\')" aria-label="Share on WhatsApp"><i class="fab fa-whatsapp"></i></button>' +
          '<button class="pd-share-btn" onclick="shareProduct(\'' + product.id + '\')" aria-label="Copy link"><i class="fas fa-link"></i></button>' +
          '<button class="pd-share-btn ' + (isFav ? 'favorited' : '') + '" style="margin-left:0.5rem;' + (isFav ? 'color:var(--danger);' : '') + '" onclick="toggleFavorite(\'' + product.id + '\');renderProductDetail(\'' + product.id + '\');" aria-label="Add to favorites"><i class="' + (isFav ? 'fas' : 'far') + ' fa-heart"></i></button>' +
        '</div>' +
      '</div></div>' +
    '</div>' +
    '<div class="row g-4 mt-1">' +
      '<div class="col-lg-6" data-aos="fade-up"><div style="background:var(--surface);border-radius:var(--radius-md);border:1px solid var(--border-light);padding:1.5rem;">' +
        '<h3 style="font-size:1.15rem;margin-bottom:1rem;"><i class="fas fa-list-ul me-2" style="color:var(--primary);"></i>Ingredients</h3>' +
        '<ul style="list-style:none;padding:0;display:flex;flex-wrap:wrap;gap:0.5rem;">' + ingTags + '</ul>' +
      '</div></div>' +
      '<div class="col-lg-6" data-aos="fade-up" data-aos-delay="100"><div style="background:var(--surface);border-radius:var(--radius-md);border:1px solid var(--border-light);padding:1.5rem;">' +
        '<h3 style="font-size:1.15rem;margin-bottom:1rem;"><i class="fas fa-chart-pie me-2" style="color:var(--primary);"></i>Nutrition Facts</h3>' +
        '<table class="nutrition-table"><thead><tr><th>Nutrient</th><th>Amount</th></tr></thead><tbody>' +
          '<tr><td>Total Fat</td><td>' + product.nutrition.fat + 'g</td></tr>' +
          '<tr><td>Carbohydrates</td><td>' + product.nutrition.carbs + 'g</td></tr>' +
          '<tr><td>Protein</td><td>' + product.nutrition.protein + 'g</td></tr>' +
          '<tr><td>Dietary Fiber</td><td>' + product.nutrition.fiber + 'g</td></tr>' +
          '<tr><td>Sugar</td><td>' + product.nutrition.sugar + 'g</td></tr>' +
          '<tr><td>Sodium</td><td>' + product.nutrition.sodium + 'mg</td></tr>' +
        '</tbody></table>' +
      '</div></div>' +
    '</div>' +
    '<div class="mt-4" data-aos="fade-up"><div style="background:var(--surface);border-radius:var(--radius-md);border:1px solid var(--border-light);padding:1.5rem;">' +
      '<h3 style="font-size:1.15rem;margin-bottom:1rem;"><i class="fas fa-globe-americas me-2" style="color:var(--primary);"></i>Available Countries & Pricing</h3>' +
      '<div class="row g-3">' + countryPricing + '</div>' +
    '</div></div>' +
    '<div class="mt-4" data-aos="fade-up"><div style="background:var(--surface);border-radius:var(--radius-md);border:1px solid var(--border-light);padding:1.5rem;">' +
      '<h3 style="font-size:1.15rem;margin-bottom:1.5rem;"><i class="fas fa-star me-2" style="color:var(--primary);"></i>Customer Reviews</h3>' +
      reviewsHTML +
    '</div></div>' +
    (related.length ? '<div class="mt-5" data-aos="fade-up"><h3 style="font-size:1.3rem;margin-bottom:1.5rem;">Related Products</h3><div class="swiper related-swiper" style="padding-bottom:2rem;"><div class="swiper-wrapper">' + relSlides + '</div><div class="swiper-button-prev"></div><div class="swiper-button-next"></div></div></div>' : '') +
  '</div></section>';
}

/* ==========================================================================
   RENDER: SEARCH RESULTS
   ========================================================================== */
function renderSearchResults(query) {
  var q = query.toLowerCase().trim();
  if (!q) return renderHome();
  var matchedProducts = getProductsForCountry().filter(function(p) {
    return p.name.toLowerCase().indexOf(q) > -1 || p.desc.toLowerCase().indexOf(q) > -1 || getBrand(p.brand).name.toLowerCase().indexOf(q) > -1 || getCategory(p.category).name.toLowerCase().indexOf(q) > -1;
  });
  var matchedBrands = getBrandsForCountry().filter(function(b) { return b.name.toLowerCase().indexOf(q) > -1 || b.desc.toLowerCase().indexOf(q) > -1; });
  var matchedCategories = APP_DATA.categories.filter(function(c) { return c.name.toLowerCase().indexOf(q) > -1 || c.desc.toLowerCase().indexOf(q) > -1; });
  var html = '<div class="page-banner"><div class="container page-banner-content">' +
    '<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="#home">Home</a></li><li class="breadcrumb-item active">Search Results</li></ol></nav>' +
    '<h1>Search Results for "' + query + '"</h1>' +
    '<p>' + (matchedProducts.length + matchedBrands.length + matchedCategories.length) + ' results found</p>' +
  '</div></div><section class="section-padding"><div class="container">';
  var i;
  if (matchedBrands.length) {
    html += '<h3 style="font-size:1.15rem;margin-bottom:1rem;" data-aos="fade-up"><i class="fas fa-store me-2" style="color:var(--primary);"></i>Brands (' + matchedBrands.length + ')</h3><div class="row g-3 mb-4">';
    for (i = 0; i < matchedBrands.length; i++) {
      var b = matchedBrands[i];
      html += '<div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="' + (i * 60) + '"><div class="brand-card" onclick="navigate(\'#brand/' + b.id + '\')"><div class="bc-cover"><img src="' + b.cover + '" alt="' + b.name + '" loading="lazy"></div><div class="bc-logo"><img src="' + b.logo + '" alt="' + b.name + '" loading="lazy"></div><div class="bc-body"><div class="bc-name">' + b.name + '</div><div class="bc-desc">' + b.desc + '</div></div></div></div>';
    }
    html += '</div>';
  }
  if (matchedCategories.length) {
    html += '<h3 style="font-size:1.15rem;margin-bottom:1rem;" data-aos="fade-up"><i class="fas fa-th-large me-2" style="color:var(--primary);"></i>Categories (' + matchedCategories.length + ')</h3><div class="row g-3 mb-4">';
    for (i = 0; i < matchedCategories.length; i++) {
      var c = matchedCategories[i];
      var cnt = getProductsByCategory(c.id).length;
      html += '<div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="' + (i * 60) + '"><div class="category-card" onclick="navigate(\'#category/' + c.id + '\')"><img src="' + c.image + '" alt="' + c.name + '" loading="lazy"><div class="cc-content"><div class="cc-name">' + c.name + '</div><div class="cc-info">' + cnt + ' products</div></div></div></div>';
    }
    html += '</div>';
  }
  if (matchedProducts.length) {
    html += '<h3 style="font-size:1.15rem;margin-bottom:1rem;" data-aos="fade-up"><i class="fas fa-utensils me-2" style="color:var(--primary);"></i>Products (' + matchedProducts.length + ')</h3><div class="row g-3">';
    for (i = 0; i < matchedProducts.length; i++) {
      html += '<div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="50">' + productCardHTML(matchedProducts[i]) + '</div>';
    }
    html += '</div>';
  }
  if (!matchedProducts.length && !matchedBrands.length && !matchedCategories.length) {
    html += '<div class="empty-state" data-aos="fade-up"><i class="fas fa-search"></i><h3>No Results Found</h3><p>We couldn\'t find anything matching "' + query + '". Try different keywords or browse our categories.</p><a href="#home" class="btn-primary-custom" style="display:inline-block;">Back to Home</a></div>';
  }
  html += '</div></section>';
  return html;
}

/* ==========================================================================
   RENDER: OFFERS PAGE
   ========================================================================== */
function renderOffers() {
  var offers = getOffersForCountry();
  var country = getCountry(STATE.country);
  var html = '<div class="page-banner"><div class="container page-banner-content">' +
    '<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="#home">Home</a></li><li class="breadcrumb-item active">Offers & Discounts</li></ol></nav>' +
    '<h1>Offers & Discounts</h1>' +
    '<p>' + offers.length + ' active deals available in ' + country.name + '</p>' +
  '</div></div><section class="section-padding"><div class="container"><div class="row g-4">';
  for (var i = 0; i < offers.length; i++) {
    var o = offers[i];
    var brand = getBrand(o.brand);
    var brandProds = getProductsByBrand(o.brand);
    var sampleProd = brandProds[0];
    html += '<div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="' + (i * 80) + '"><div class="offer-card" style="height:100%;">' +
      (sampleProd ? '<img src="' + sampleProd.image + '" alt="' + o.title + '" style="width:100%;height:200px;object-fit:cover;" loading="lazy">' : '<div style="height:4px;background:linear-gradient(90deg,var(--primary),var(--primary-light));"></div>') +
      '<div class="oc-body">' +
        '<div class="oc-brand"><img src="' + brand.logo + '" alt="' + brand.name + '" loading="lazy"><span>' + brand.name + '</span></div>' +
        '<div class="oc-title">' + o.title + '</div>' +
        '<div class="oc-desc">' + o.desc + '</div>' +
        '<div class="oc-footer"><div class="oc-discount">' + (o.discount ? o.discount + '% OFF' : 'FREE DELIVERY') + '</div><div class="oc-code" onclick="navigator.clipboard.writeText(\'' + o.code + '\');showToast(\'Code ' + o.code + ' copied!\',\'fa-copy\');" style="cursor:pointer;" title="Click to copy">' + o.code + '</div></div>' +
      '</div></div></div>';
  }
  if (offers.length === 0) {
    html += '<div class="col-12"><div class="empty-state"><i class="fas fa-tags"></i><h3>No Offers Available</h3><p>There are currently no active offers in ' + country.name + '. Check back later or try a different country.</p></div></div>';
  }
  html += '</div></div></section>';
  return html;
}

/* ==========================================================================
   RENDER: ALL CATEGORIES PAGE
   ========================================================================== */
function renderCategories() {
  var country = getCountry(STATE.country);
  var html = '<div class="page-banner"><div class="container page-banner-content">' +
    '<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="#home">Home</a></li><li class="breadcrumb-item active">All Categories</li></ol></nav>' +
    '<h1>All Categories</h1>' +
    '<p>Browse ' + APP_DATA.categories.length + ' food categories available in ' + country.name + '</p>' +
  '</div></div><section class="section-padding"><div class="container"><div class="row g-3">';
  for (var i = 0; i < APP_DATA.categories.length; i++) {
    var c = APP_DATA.categories[i];
    var count = getProductsByCategory(c.id).length;
    var hasDiscount = getProductsByCategory(c.id).some(function(p) { return getProductPrice(p) && getProductPrice(p).discount; });
    var minPrice = count ? Math.min.apply(null, getProductsByCategory(c.id).map(function(p) { return getProductPrice(p) ? getProductPrice(p).current : Infinity; })) : 0;
    html += '<div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="' + (i * 60) + '">' +
      '<div class="category-card" onclick="navigate(\'#category/' + c.id + '\')" style="height:240px;">' +
        '<img src="' + c.image + '" alt="' + c.name + '" loading="lazy">' +
        (hasDiscount ? '<div class="cc-discount">Sale</div>' : '') +
        '<div class="cc-content"><div class="cc-name">' + c.name + '</div><div class="cc-info">' + count + ' products &middot; from ' + country.symbol + minPrice.toFixed(2) + '</div></div>' +
      '</div></div>';
  }
  html += '</div></div></section>';
  return html;
}

/* ==========================================================================
   RENDER: ABOUT PAGE
   ========================================================================== */
function renderAbout() {
  return '<div class="page-banner"><div class="container page-banner-content">' +
    '<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="#home">Home</a></li><li class="breadcrumb-item active">About Us</li></ol></nav>' +
    '<h1>About FoodScope</h1><p>Your trusted global food menu and price comparison platform</p>' +
  '</div></div>' +
  '<section class="section-padding"><div class="container"><div class="static-content" data-aos="fade-up">' +
    '<div style="text-align:center;margin-bottom:3rem;"><img src="https://picsum.photos/seed/aboutteam/800/400.jpg" alt="FoodScope Team" style="border-radius:var(--radius-lg);margin-bottom:2rem;box-shadow:var(--shadow-lg);" loading="lazy"></div>' +
    '<h2 style="text-align:center;">Our Mission</h2>' +
    '<p style="text-align:center;">FoodScope was founded with a simple yet powerful mission: to make food pricing transparent and accessible across the globe. Whether you\'re a traveler wondering what a Big Mac costs in Dubai, a student hunting for the best deals, or a food enthusiast exploring international menus — we\'ve got you covered.</p>' +
    '<h2>What We Do</h2><p>We aggregate menu data from the world\'s most popular food brands and present it in a unified, easy-to-compare format. Our platform covers:</p>' +
    '<ul><li>Complete menus from 8+ major food brands</li><li>Real-time pricing across 6+ countries</li><li>Discount and offer tracking</li><li>Nutritional information for every product</li><li>Country-specific availability checks</li><li>Smart filtering and sorting tools</li></ul>' +
    '<h2>Our Story</h2><p>FoodScope started in 2023 as a side project by a group of food-loving developers who were frustrated by the lack of transparent, cross-country food pricing data. What began as a simple spreadsheet has evolved into a comprehensive platform serving over 2 million users worldwide.</p><p>Today, our team of 25+ people works tirelessly to ensure every price, every menu item, and every offer is accurate and up-to-date.</p>' +
    '<h2>Our Values</h2>' +
    '<div class="row g-4 mt-2 mb-4">' +
      '<div class="col-md-4"><div class="wcu-card"><div class="wcu-icon"><i class="fas fa-bullseye"></i></div><div class="wcu-title">Accuracy First</div><div class="wcu-desc">Every data point is verified before it reaches you. We never compromise on accuracy.</div></div></div>' +
      '<div class="col-md-4"><div class="wcu-card"><div class="wcu-icon"><i class="fas fa-users"></i></div><div class="wcu-title">User Focused</div><div class="wcu-desc">Every feature is designed with our users in mind. Your feedback shapes our roadmap.</div></div></div>' +
      '<div class="col-md-4"><div class="wcu-card"><div class="wcu-icon"><i class="fas fa-lock"></i></div><div class="wcu-title">Privacy Respected</div><div class="wcu-desc">We don\'t sell your data. Your browsing experience is private and secure.</div></div></div>' +
    '</div>' +
    '<h2>By The Numbers</h2>' +
    '<div class="row g-4 mt-2 mb-4">' +
      '<div class="col-6 col-md-3"><div class="stat-counter" data-aos="fade-up"><div class="stat-number" data-count="2000000">0</div><div class="stat-label">Monthly Users</div></div></div>' +
      '<div class="col-6 col-md-3"><div class="stat-counter" data-aos="fade-up" data-aos-delay="100"><div class="stat-number" data-count="500">0</div><div class="stat-label">Menu Items</div></div></div>' +
      '<div class="col-6 col-md-3"><div class="stat-counter" data-aos="fade-up" data-aos-delay="200"><div class="stat-number" data-count="6">0</div><div class="stat-label">Countries</div></div></div>' +
      '<div class="col-6 col-md-3"><div class="stat-counter" data-aos="fade-up" data-aos-delay="300"><div class="stat-number" data-count="8">0</div><div class="stat-label">Brand Partners</div></div></div>' +
    '</div>' +
  '</div></div></section>';
}

/* ==========================================================================
   RENDER: CONTACT PAGE
   ========================================================================== */
function renderContact() {
  return '<div class="page-banner"><div class="container page-banner-content">' +
    '<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="#home">Home</a></li><li class="breadcrumb-item active">Contact Us</li></ol></nav>' +
    '<h1>Get In Touch</h1><p>We\'d love to hear from you. Send us a message and we\'ll respond within 24 hours.</p>' +
  '</div></div>' +
  '<section class="section-padding"><div class="container"><div class="row g-4">' +
    '<div class="col-lg-5" data-aos="fade-right"><div style="background:var(--surface);border-radius:var(--radius-lg);border:1px solid var(--border-light);padding:2rem;height:100%;">' +
      '<h3 style="font-size:1.3rem;margin-bottom:1.5rem;">Contact Information</h3>' +
      '<div style="margin-bottom:1.5rem;">' +
        '<div style="display:flex;align-items:flex-start;gap:1rem;margin-bottom:1.25rem;"><div style="width:42px;height:42px;border-radius:var(--radius-sm);background:rgba(232,93,4,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--primary);"><i class="fas fa-map-marker-alt"></i></div><div><div style="font-weight:600;margin-bottom:0.15rem;">Office Address</div><div style="font-size:0.88rem;color:var(--text-secondary);">123 Food Street, Culinary District<br>New York, NY 10001, USA</div></div></div>' +
        '<div style="display:flex;align-items:flex-start;gap:1rem;margin-bottom:1.25rem;"><div style="width:42px;height:42px;border-radius:var(--radius-sm);background:rgba(232,93,4,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--primary);"><i class="fas fa-envelope"></i></div><div><div style="font-weight:600;margin-bottom:0.15rem;">Email Us</div><div style="font-size:0.88rem;color:var(--text-secondary);">hello@foodscope.com<br>support@foodscope.com</div></div></div>' +
        '<div style="display:flex;align-items:flex-start;gap:1rem;margin-bottom:1.25rem;"><div style="width:42px;height:42px;border-radius:var(--radius-sm);background:rgba(232,93,4,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--primary);"><i class="fas fa-phone"></i></div><div><div style="font-weight:600;margin-bottom:0.15rem;">Call Us</div><div style="font-size:0.88rem;color:var(--text-secondary);">+1 (555) 123-4567<br>Mon-Fri, 9AM-6PM EST</div></div></div>' +
        '<div style="display:flex;align-items:flex-start;gap:1rem;"><div style="width:42px;height:42px;border-radius:var(--radius-sm);background:rgba(232,93,4,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--primary);"><i class="fas fa-clock"></i></div><div><div style="font-weight:600;margin-bottom:0.15rem;">Business Hours</div><div style="font-size:0.88rem;color:var(--text-secondary);">Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 10:00 AM - 4:00 PM</div></div></div>' +
      '</div>' +
      '<hr style="border-color:var(--border-light);margin:1.5rem 0;">' +
      '<div><div style="font-weight:600;margin-bottom:0.75rem;">Follow Us</div><div class="footer-social"><a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a><a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a><a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a><a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a></div></div>' +
    '</div></div>' +
    '<div class="col-lg-7" data-aos="fade-left"><div style="background:var(--surface);border-radius:var(--radius-lg);border:1px solid var(--border-light);padding:2rem;">' +
      '<h3 style="font-size:1.3rem;margin-bottom:1.5rem;">Send Us a Message</h3>' +
      '<form class="contact-form" onsubmit="event.preventDefault();showToast(\'Message sent successfully! We\\\'ll get back to you soon.\',\'fa-paper-plane\');this.reset();">' +
        '<div class="row g-3">' +
          '<div class="col-md-6"><label class="form-label">Full Name</label><input type="text" class="form-control" placeholder="John Doe" required></div>' +
          '<div class="col-md-6"><label class="form-label">Email Address</label><input type="email" class="form-control" placeholder="john@example.com" required></div>' +
          '<div class="col-md-6"><label class="form-label">Phone Number</label><input type="tel" class="form-control" placeholder="+1 (555) 000-0000"></div>' +
          '<div class="col-md-6"><label class="form-label">Subject</label><select class="form-select form-control"><option>General Inquiry</option><option>Report Incorrect Data</option><option>Partnership</option><option>Technical Issue</option><option>Feedback</option></select></div>' +
          '<div class="col-12"><label class="form-label">Message</label><textarea class="form-control" rows="5" placeholder="Tell us how we can help..." required></textarea></div>' +
          '<div class="col-12"><button type="submit" class="btn-primary-custom"><i class="fas fa-paper-plane me-2"></i>Send Message</button></div>' +
        '</div>' +
      '</form>' +
    '</div></div>' +
  '</div></div></section>';
}

/* ==========================================================================
   RENDER: PRIVACY POLICY
   ========================================================================== */
function renderPrivacy() {
  return '<div class="page-banner"><div class="container page-banner-content">' +
    '<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="#home">Home</a></li><li class="breadcrumb-item active">Privacy Policy</li></ol></nav>' +
    '<h1>Privacy Policy</h1><p>Last updated: January 15, 2025</p>' +
  '</div></div>' +
  '<section class="section-padding"><div class="container"><div class="static-content" data-aos="fade-up">' +
    '<h2>Introduction</h2><p>FoodScope ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website.</p>' +
    '<h2>Information We Collect</h2><p>We may collect information about you in a variety of ways, including:</p>' +
    '<ul><li><strong>Personal Data:</strong> Name, email address, phone number, and other contact information you voluntarily provide when filling out forms.</li><li><strong>Usage Data:</strong> Information about how you use our website, including pages visited, time spent on pages, and navigation patterns.</li><li><strong>Device Data:</strong> Browser type, operating system, device type, IP address, and other technical information collected automatically.</li><li><strong>Cookies:</strong> We use cookies and similar tracking technologies to enhance your browsing experience.</li></ul>' +
    '<h2>How We Use Your Information</h2><p>We use the information we collect to:</p>' +
    '<ul><li>Provide, maintain, and improve our services</li><li>Personalize your experience on our platform</li><li>Respond to your inquiries and provide customer support</li><li>Send you newsletters and marketing communications (with your consent)</li><li>Analyze usage patterns to improve our website</li><li>Detect, prevent, and address technical issues</li></ul>' +
    '<h2>Data Sharing</h2><p>We do not sell your personal information to third parties. We may share your information with service providers who assist us in operating our website, conducting our business, or serving our users, so long as those parties agree to keep this information confidential.</p>' +
    '<h2>Data Security</h2><p>We implement appropriate technical and organizational security measures to protect your personal data against unauthorized access, alteration, disclosure, or destruction.</p>' +
    '<h2>Your Rights</h2><p>You have the right to access, correct, delete, or restrict the processing of your personal data. You may also object to the processing of your data or request data portability.</p>' +
    '<h2>Contact Us</h2><p>If you have questions about this Privacy Policy, please contact us at privacy@foodscope.com.</p>' +
  '</div></div></section>';
}

/* ==========================================================================
   RENDER: TERMS & CONDITIONS
   ========================================================================== */
function renderTerms() {
  return '<div class="page-banner"><div class="container page-banner-content">' +
    '<nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="#home">Home</a></li><li class="breadcrumb-item active">Terms & Conditions</li></ol></nav>' +
    '<h1>Terms & Conditions</h1><p>Last updated: January 15, 2025</p>' +
  '</div></div>' +
  '<section class="section-padding"><div class="container"><div class="static-content" data-aos="fade-up">' +
    '<h2>Acceptance of Terms</h2><p>By accessing and using FoodScope, you accept and agree to be bound by the terms and provisions of this agreement. If you do not agree to abide by these terms, please do not use this website.</p>' +
    '<h2>Use of Service</h2><p>FoodScope provides food menu and price comparison information for personal, non-commercial use. You agree not to:</p>' +
    '<ul><li>Use the service for any unlawful purpose</li><li>Reproduce, duplicate, or copy content from our website without permission</li><li>Use automated systems (bots, scrapers) to access our data</li><li>Attempt to interfere with the proper functioning of the website</li><li>Impersonate any person or entity or misrepresent your affiliation</li></ul>' +
    '<h2>Pricing Accuracy</h2><p>While we strive to provide accurate and up-to-date pricing information, prices may vary by location and are subject to change without notice. FoodScope is not responsible for any discrepancies between our listed prices and actual restaurant prices.</p>' +
    '<h2>Intellectual Property</h2><p>All content on this website, including text, graphics, logos, and images, is the property of FoodScope or its content suppliers and is protected by international copyright laws.</p>' +
    '<h2>Limitation of Liability</h2><p>FoodScope shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of the service.</p>' +
    '<h2>Changes to Terms</h2><p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting to the website. Your continued use of the service constitutes acceptance of the modified terms.</p>' +
    '<h2>Contact</h2><p>For questions about these Terms, contact us at legal@foodscope.com.</p>' +
  '</div></div></section>';
}

/* ==========================================================================
   RENDER: 404 PAGE
   ========================================================================== */
function render404() {
  return '<div class="error-page"><div data-aos="fade-up">' +
    '<div class="error-code">404</div>' +
    '<h2 class="error-title">Page Not Found</h2>' +
    '<p class="error-desc">The page you\'re looking for doesn\'t exist or has been moved. Let\'s get you back on track.</p>' +
    '<a href="#home" class="btn-primary-custom" style="display:inline-block;"><i class="fas fa-home me-2"></i>Back to Home</a>' +
  '</div></div>';
}

/* ==========================================================================
   ROUTER
   ========================================================================== */
function navigate(hash) {
  window.location.hash = hash;
}

function handleRoute() {
  var hash = window.location.hash.slice(1) || 'home';
  var parts = hash.split('/');
  var page = parts[0];
  var param = parts[1];
  if (['brand', 'category'].indexOf(page) === -1) {
    STATE.currentPage = 1;
    STATE.activeFilters = { brand: [], category: [], maxPrice: 999 };
    STATE.sortBy = 'popular';
    STATE.viewMode = 'grid';
  }
  $('#main-nav a').removeClass('active');
  var navLink = $('#main-nav a[data-page="' + page + '"]');
  if (navLink.length) navLink.addClass('active');
  else if (page === 'brand') $('#main-nav a[data-page="brands"]').addClass('active');
  else if (page === 'category') $('#main-nav a[data-page="categories"]').addClass('active');

  var renderMap = {
    home: function() { return renderHome(); },
    brands: function() { return renderBrands(); },
    brand: function() { return renderBrandDetail(param); },
    category: function() { return renderCategoryDetail(param); },
    product: function() { return renderProductDetail(param); },
    search: function() { return renderSearchResults(decodeURIComponent(param || '')); },
    offers: function() { return renderOffers(); },
    categories: function() { return renderCategories(); },
    about: function() { return renderAbout(); },
    contact: function() { return renderContact(); },
    privacy: function() { return renderPrivacy(); },
    terms: function() { return renderTerms(); },
    '404': function() { return render404(); }
  };

  var renderer = renderMap[page];
  if (renderer) {
    $('#main-content').html(renderer());
    window.scrollTo({ top: 0, behavior: 'smooth' });
    if (typeof AOS !== 'undefined') AOS.refresh();
    initSwipers();
    initCounters();
  } else {
    $('#main-content').html(render404());
  }
}

/* ==========================================================================
   SWIPER INITIALIZATION
   ========================================================================== */
function initSwipers() {
  $('.product-swiper-trending, .product-swiper-popular').each(function() {
    if (this.swiper) return;
    new Swiper(this, {
      slidesPerView: 1, spaceBetween: 16, loop: true,
      autoplay: { delay: 4000, disableOnInteraction: false, pauseOnMouseEnter: true },
      navigation: { nextEl: $(this).find('.swiper-button-next')[0], prevEl: $(this).find('.swiper-button-prev')[0] },
      breakpoints: { 576: { slidesPerView: 2 }, 992: { slidesPerView: 3 }, 1200: { slidesPerView: 4 } }
    });
  });
  APP_DATA.brands.forEach(function(b) {
    var el = document.querySelector('.fb-cat-swiper-' + b.id);
    if (el && !el.swiper) {
      new Swiper(el, { slidesPerView: 'auto', spaceBetween: 8, loop: false, freeMode: true });
    }
  });
  var offerSwiper = document.querySelector('.offer-swiper');
  if (offerSwiper && !offerSwiper.swiper) {
    new Swiper(offerSwiper, {
      slidesPerView: 1, spaceBetween: 16, loop: true,
      autoplay: { delay: 5000, disableOnInteraction: false, pauseOnMouseEnter: true },
      navigation: { nextEl: offerSwiper.querySelector('.swiper-button-next'), prevEl: offerSwiper.querySelector('.swiper-button-prev') },
      breakpoints: { 576: { slidesPerView: 2 }, 992: { slidesPerView: 3 } }
    });
  }
  var testSwiper = document.querySelector('.testimonial-swiper');
  if (testSwiper && !testSwiper.swiper) {
    new Swiper(testSwiper, {
      slidesPerView: 1, spaceBetween: 16, loop: true,
      autoplay: { delay: 6000, disableOnInteraction: false, pauseOnMouseEnter: true },
      pagination: { el: testSwiper.querySelector('.swiper-pagination'), clickable: true },
      breakpoints: { 768: { slidesPerView: 2 }, 1024: { slidesPerView: 3 } }
    });
  }
  var relSwiper = document.querySelector('.related-swiper');
  if (relSwiper && !relSwiper.swiper) {
    new Swiper(relSwiper, {
      slidesPerView: 1, spaceBetween: 16, loop: false,
      navigation: { nextEl: relSwiper.querySelector('.swiper-button-next'), prevEl: relSwiper.querySelector('.swiper-button-prev') },
      breakpoints: { 576: { slidesPerView: 2 }, 992: { slidesPerView: 3 } }
    });
  }
}

/* ==========================================================================
   COUNTER ANIMATION
   ========================================================================== */
function initCounters() {
  $('.stat-number[data-count]').each(function() {
    var $el = $(this);
    if ($el.data('counted')) return;
    var target = parseInt($el.data('count'));
    var duration = 2000;
    var step = target / (duration / 16);
    var current = 0;
    var timer = setInterval(function() {
      current += step;
      if (current >= target) { current = target; clearInterval(timer); }
      if (target >= 1000000) $el.text((current / 1000000).toFixed(1) + 'M+');
      else if (target >= 1000) $el.text(Math.floor(current).toLocaleString() + '+');
      else $el.text(Math.floor(current));
    }, 16);
    $el.data('counted', true);
  });
}

/* ==========================================================================
   COUNTRY CHANGE
   ========================================================================== */
async function changeCountry(countryId) {
  if (countryId === STATE.country) return;
  var country = getCountry(countryId);
  if (!country) return;
  $('#co-flag').text(country.flag);
  $('#co-name').text('Switching to ' + country.name + '...');
  $('#country-overlay').addClass('active');
  await delay(800);
  STATE.country = countryId;
  STATE.currentPage = 1;
  STATE.activeFilters = { brand: [], category: [], maxPrice: 999 };
  updateCountrySelector();
  updateFooterData();
  closeCountryDropdown();
  handleRoute();
  await delay(300);
  $('#country-overlay').removeClass('active');
  showToast('Switched to ' + country.name + ' (' + country.currency + ')', 'fa-globe');
}

function updateCountrySelector() {
  var c = getCountry(STATE.country);
  $('#cs-flag').text(c.flag);
  $('#cs-text').text(c.name);
  var ddHTML = '';
  APP_DATA.countries.forEach(function(co) {
    ddHTML += '<div class="country-dropdown-item ' + (co.id === STATE.country ? 'active' : '') + '" onclick="changeCountry(\'' + co.id + '\')" role="option"><span class="flag">' + co.flag + '</span><span class="name">' + co.name + '</span><span class="curr">' + co.currency + '</span></div>';
  });
  $('#country-dropdown').html(ddHTML);
  var mobileOpts = '';
  APP_DATA.countries.forEach(function(co) {
    mobileOpts += '<option value="' + co.id + '"' + (co.id === STATE.country ? ' selected' : '') + '>' + co.flag + ' ' + co.name + ' (' + co.currency + ')</option>';
  });
  $('#mobile-country-select').html('<label style="font-weight:600;font-size:0.88rem;color:var(--text);margin-bottom:0.5rem;display:block;">Select Country</label><select class="form-select form-control" onchange="changeCountry(this.value)" aria-label="Select country">' + mobileOpts + '</select>');
}

function updateFooterData() {
  var brands = getBrandsForCountry();
  var cats = APP_DATA.categories;
  var bHTML = '';
  for (var i = 0; i < Math.min(6, brands.length); i++) {
    bHTML += '<li><a href="#brand/' + brands[i].id + '">' + brands[i].name + '</a></li>';
  }
  $('#footer-brands').html(bHTML);
  var cHTML = '';
  for (i = 0; i < Math.min(6, cats.length); i++) {
    cHTML += '<li><a href="#category/' + cats[i].id + '">' + cats[i].name + '</a></li>';
  }
  $('#footer-categories').html(cHTML);
}

/* ==========================================================================
   COUNTRY DROPDOWN
   ========================================================================== */
function toggleCountryDropdown() {
  var dd = $('#country-dropdown');
  if (dd.hasClass('show')) closeCountryDropdown();
  else { dd.addClass('show'); $('#country-selector').attr('aria-expanded', 'true'); }
}
function closeCountryDropdown() {
  $('#country-dropdown').removeClass('show');
  $('#country-selector').attr('aria-expanded', 'false');
}
 $(document).on('click', function(e) {
  if (!$(e.target).closest('#country-selector').length) closeCountryDropdown();
});
 $('#country-selector').on('click keydown', function(e) {
  if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
  if (e.type === 'keydown') e.preventDefault();
  toggleCountryDropdown();
});

/* ==========================================================================
   SEARCH
   ========================================================================== */
function openSearch() {
  $('#search-overlay').addClass('active');
  setTimeout(function() { $('#search-input').focus(); }, 200);
}
function closeSearch() {
  $('#search-overlay').removeClass('active');
  $('#search-input').val('');
  $('#search-suggestions').html('<div class="search-empty">Start typing to search...</div>');
}
 $(document).on('keydown', function(e) {
  if (e.key === 'Escape') closeSearch();
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); openSearch(); }
});
var searchDebounce;
 $('#search-input').on('input', function() {
  clearTimeout(searchDebounce);
  var q = $(this).val().trim();
  searchDebounce = setTimeout(function() {
    if (!q) { $('#search-suggestions').html('<div class="search-empty">Start typing to search...</div>'); return; }
    var ql = q.toLowerCase();
    var brands = getBrandsForCountry().filter(function(b) { return b.name.toLowerCase().indexOf(ql) > -1; }).slice(0, 3);
    var cats = APP_DATA.categories.filter(function(c) { return c.name.toLowerCase().indexOf(ql) > -1; }).slice(0, 3);
    var products = getProductsForCountry().filter(function(p) { return p.name.toLowerCase().indexOf(ql) > -1 || getBrand(p.brand).name.toLowerCase().indexOf(ql) > -1; }).slice(0, 5);
    var html = '';
    var i;
    if (brands.length) {
      html += '<div class="search-suggestion-group"><div class="search-suggestion-group-title">Brands</div>';
      for (i = 0; i < brands.length; i++) {
        html += '<div class="search-suggestion-item" onclick="closeSearch();navigate(\'#brand/' + brands[i].id + '\')"><img src="' + brands[i].logo + '" alt="' + brands[i].name + '"><div class="info"><div class="name">' + brands[i].name + '</div><div class="sub">' + brands[i].countries.length + ' countries</div></div></div>';
      }
      html += '</div>';
    }
    if (cats.length) {
      html += '<div class="search-suggestion-group"><div class="search-suggestion-group-title">Categories</div>';
      for (i = 0; i < cats.length; i++) {
        html += '<div class="search-suggestion-item" onclick="closeSearch();navigate(\'#category/' + cats[i].id + '\')"><img src="' + cats[i].image + '" alt="' + cats[i].name + '"><div class="info"><div class="name">' + cats[i].name + '</div><div class="sub">' + getProductsByCategory(cats[i].id).length + ' products</div></div></div>';
      }
      html += '</div>';
    }
    if (products.length) {
      html += '<div class="search-suggestion-group"><div class="search-suggestion-group-title">Products</div>';
      for (i = 0; i < products.length; i++) {
        var pr = getProductPrice(products[i]);
        var br = getBrand(products[i].brand);
        html += '<div class="search-suggestion-item" onclick="closeSearch();navigate(\'#product/' + products[i].id + '\')"><img src="' + products[i].image + '" alt="' + products[i].name + '"><div class="info"><div class="name">' + products[i].name + '</div><div class="sub">' + br.name + ' &middot; ' + (pr ? pr.symbol + pr.current.toFixed(2) : 'N/A') + '</div></div></div>';
      }
      html += '</div>';
    }
    if (!html) html = '<div class="search-empty">No results found for "' + q + '"</div>';
    $('#search-suggestions').html(html);
  }, 200);
});
 $('#search-input').on('keydown', function(e) {
  if (e.key === 'Enter') {
    var q = $(this).val().trim();
    if (q) { closeSearch(); navigate('#search/' + encodeURIComponent(q)); }
  }
});
function heroSearch() {
  var q = $('#hero-search').val().trim();
  if (q) navigate('#search/' + encodeURIComponent(q));
}
function heroSearchTag(tag) {
  navigate('#search/' + encodeURIComponent(tag));
}
 $('#hero-search').on('keydown', function(e) { if (e.key === 'Enter') heroSearch(); });

/* ==========================================================================
   FILTERS & SORTING
   ========================================================================== */
function toggleFilter(type, value) {
  var arr = STATE.activeFilters[type];
  var idx = arr.indexOf(value);
  if (idx > -1) arr.splice(idx, 1);
  else arr.push(value);
}
function resetFilters() {
  STATE.activeFilters = { brand: [], category: [], maxPrice: 999 };
  STATE.sortBy = 'popular';
  STATE.viewMode = 'grid';
  STATE.currentPage = 1;
}

/* ==========================================================================
   FAVORITES
   ========================================================================== */
function toggleFavorite(productId) {
  if (STATE.favorites.has(productId)) {
    STATE.favorites.delete(productId);
    showToast('Removed from favorites', 'fa-heart-broken');
  } else {
    STATE.favorites.add(productId);
    showToast('Added to favorites', 'fa-heart');
  }
  $('.pc-action-btn').each(function() {
    var onclickStr = $(this).attr('onclick') || '';
    var match = onclickStr.match(/toggleFavorite\('([^']+)'\)/);
    if (match) {
      var id = match[1];
      if (STATE.favorites.has(id)) {
        $(this).addClass('favorited').find('i').removeClass('far').addClass('fas');
      } else {
        $(this).removeClass('favorited').find('i').removeClass('fas').addClass('far');
      }
    }
  });
}

/* ==========================================================================
   QUICK VIEW
   ========================================================================== */
function quickView(productId) {
  var product = getProduct(productId);
  if (!product || !product.prices[STATE.country]) return;
  var price = getProductPrice(product);
  var brand = getBrand(product.brand);
  var cat = getCategory(product.category);
  $('#qv-content').html(
    '<button class="qv-close" onclick="closeQuickView()" aria-label="Close"><i class="fas fa-times"></i></button>' +
    '<div class="row g-0">' +
      '<div class="col-md-5"><img src="' + product.image + '" alt="' + product.name + '" style="width:100%;height:100%;min-height:280px;object-fit:cover;" loading="lazy"></div>' +
      '<div class="col-md-7" style="padding:1.5rem;">' +
        '<div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.75rem;"><img src="' + brand.logo + '" alt="' + brand.name + '" style="width:24px;height:24px;object-fit:contain;"><span style="font-size:0.85rem;color:var(--primary);font-weight:600;">' + brand.name + '</span><span style="font-size:0.78rem;color:var(--muted);">&middot; ' + cat.name + '</span></div>' +
        '<h3 style="font-family:var(--font-display);font-size:1.3rem;font-weight:700;margin-bottom:0.5rem;">' + product.name + '</h3>' +
        '<p style="font-size:0.88rem;color:var(--text-secondary);margin-bottom:1rem;line-height:1.6;">' + product.desc + '</p>' +
        '<div style="display:flex;align-items:baseline;gap:0.75rem;margin-bottom:1rem;">' +
          (price.discount ? '<span style="font-size:1rem;color:var(--muted);text-decoration:line-through;">' + price.symbol + price.regular.toFixed(2) + '</span>' : '') +
          '<span style="font-size:1.5rem;font-weight:800;color:var(--primary);">' + price.symbol + price.current.toFixed(2) + '</span>' +
          (price.discount ? '<span style="font-size:0.82rem;color:var(--success);font-weight:600;">-' + price.discountPercent + '%</span>' : '') +
        '</div>' +
        '<div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;margin-bottom:1.25rem;">' +
          '<div style="display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;color:var(--text-secondary);"><i class="fas fa-fire" style="color:var(--primary);"></i>' + product.calories + ' cal</div>' +
          '<div style="display:flex;align-items:center;gap:0.5rem;font-size:0.85rem;color:var(--text-secondary);"><i class="fas fa-chart-bar" style="color:var(--primary);"></i>' + product.nutrition.protein + 'g protein</div>' +
        '</div>' +
        '<button class="btn-primary-custom" style="width:100%;" onclick="closeQuickView();navigate(\'#product/' + product.id + '\')">View Full Details <i class="fas fa-arrow-right ms-1"></i></button>' +
      '</div>' +
    '</div>'
  );
  $('#qv-modal').addClass('active');
  $('body').css('overflow', 'hidden');
}
function closeQuickView() {
  $('#qv-modal').removeClass('active');
  $('body').css('overflow', '');
}
 $('#qv-modal').on('click', function(e) {
  if (e.target === this) closeQuickView();
});

/* ==========================================================================
   SHARE
   ========================================================================== */
function shareProduct(productId) {
  var url = window.location.origin + window.location.pathname + '#product/' + productId;
  if (navigator.share) {
    navigator.share({ title: 'FoodScope - Product', url: url });
  } else {
    navigator.clipboard.writeText(url).then(function() { showToast('Link copied to clipboard!', 'fa-link'); });
  }
}

/* ==========================================================================
   FAQ TOGGLE
   ========================================================================== */
function toggleFAQ(btn) {
  var item = $(btn).closest('.faq-item');
  var answer = item.find('.faq-answer');
  var isActive = item.hasClass('active');
  $('.faq-item').removeClass('active');
  $('.faq-answer').css('max-height', '0');
  if (!isActive) {
    item.addClass('active');
    answer.css('max-height', answer[0].scrollHeight + 'px');
  }
}

/* ==========================================================================
   THEME TOGGLE
   ========================================================================== */
function initTheme() {
  var saved = localStorage.getItem('foodscope-theme');
  if (saved) {
    document.documentElement.setAttribute('data-theme', saved);
    updateThemeIcon(saved);
  } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.documentElement.setAttribute('data-theme', 'dark');
    updateThemeIcon('dark');
  }
}
function updateThemeIcon(theme) {
  var icon = theme === 'dark' ? 'fa-sun' : 'fa-moon';
  $('#theme-toggle').html('<i class="fas ' + icon + '"></i>');
}
 $('#theme-toggle').on('click', function() {
  var current = document.documentElement.getAttribute('data-theme');
  var next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('foodscope-theme', next);
  updateThemeIcon(next);
  showToast((next === 'dark' ? 'Dark' : 'Light') + ' mode activated', next === 'dark' ? 'fa-moon' : 'fa-sun');
});

/* ==========================================================================
   MOBILE MENU
   ========================================================================== */
function toggleMobileMenu() {
  var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('mobileMenu'));
  offcanvas.toggle();
}
function closeMobileMenu() {
  var offcanvas = bootstrap.Offcanvas.getOrCreateInstance(document.getElementById('mobileMenu'));
  offcanvas.hide();
}

/* ==========================================================================
   NEWSLETTER
   ========================================================================== */
function subscribeNewsletter() {
  var email = $('#newsletter-email').val().trim();
  if (!email || email.indexOf('@') === -1) { showToast('Please enter a valid email address', 'fa-exclamation-circle'); return; }
  showToast('Successfully subscribed! Welcome aboard.', 'fa-check-circle');
  $('#newsletter-email').val('');
}
function subscribeFooter() {
  var email = $('#footer-email').val().trim();
  if (!email || email.indexOf('@') === -1) { showToast('Please enter a valid email address', 'fa-exclamation-circle'); return; }
  showToast('Successfully subscribed!', 'fa-check-circle');
  $('#footer-email').val('');
}

/* ==========================================================================
   SCROLL EFFECTS
   ========================================================================== */
 $(window).on('scroll', function() {
  var st = $(this).scrollTop();
  if (st > 50) $('#main-header').addClass('scrolled');
  else $('#main-header').removeClass('scrolled');
  if (st > 500) $('#back-to-top').addClass('show');
  else $('#back-to-top').removeClass('show');
});

/* ==========================================================================
   INITIALIZATION
   ========================================================================== */
 $(document).ready(function() {
  initTheme();
  AOS.init({ duration: 700, once: true, offset: 60, easing: 'ease-out-cubic' });
  updateCountrySelector();
  updateFooterData();
  setTimeout(function() {
    $('#preloader').addClass('hidden');
    handleRoute();
  }, 2000);
  $(window).on('hashchange', handleRoute);
});
</script>