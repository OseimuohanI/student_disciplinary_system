<?php ?>
<style>
  :root{
    --bg:#f6f8fa;
    --card:#fff;
    --primary:#007bff;
    --muted:#6c757d;
    --text:#111827;
  }

  /* dark theme variables */
  [data-theme="dark"] {
    --bg: #0b1020;
    --card: #0f1724;
    --primary: #60a5fa;
    --muted: #94a3b8;
    --text: #ffffff;
    color-scheme: dark;
  }

  /* dark fixed background (no-repeat) */
  [data-theme="dark"] body {
    background-image: linear-gradient(135deg, #071226 0%, var(--bg) 100%) !important;
    background-repeat: no-repeat !important;
    background-attachment: fixed !important;
    background-size: cover !important;
  }

  /* make text readable in dark mode */
  [data-theme="dark"] body,
  [data-theme="dark"] .card,
  [data-theme="dark"] h1,h2,h3,p,label,th,td,input,select,textarea {
    color: var(--text) !important;
  }

  /* table header styling in dark mode */
  [data-theme="dark"] th {
    background: rgba(255,255,255,0.04) !important;
    color: var(--text) !important;
    box-shadow: inset 0 -1px 0 rgba(255,255,255,0.03);
    backdrop-filter: blur(6px);
  }
  [data-theme="dark"] .table-wrap { border-color: rgba(255,255,255,0.06) !important; }
  [data-theme="dark"] .muted { color: var(--muted) !important; }

  /* theme toggle button - styling only */
  .theme-toggle {
    position:fixed;
    right:16px;
    top:16px;
    z-index:11000;
    width:44px;
    height:44px;
    border-radius:10px;
    border:1px solid rgba(0,0,0,0.08);
    background:var(--card);
    display:inline-flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
    cursor:pointer;
    transition:transform .12s ease, box-shadow .12s ease;
    box-shadow:0 6px 18px rgba(2,6,23,0.06);
  }
  .theme-toggle:active{ transform:scale(.98) }
</style>