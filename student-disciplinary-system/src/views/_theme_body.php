<?php ?>
<!-- theme toggle button (can be placed multiple times, script handles all) -->
<button class="theme-toggle" aria-pressed="false" aria-label="Toggle dark mode" title="Toggle dark mode">🌙</button>

<script>
(function(){
  var key = 'site-theme';

  function setTheme(theme){
    if (theme === 'dark') {
      document.documentElement.setAttribute('data-theme','dark');
    } else {
      document.documentElement.removeAttribute('data-theme');
    }
    try { localStorage.setItem(key, theme); } catch(e){}
    // update all buttons
    document.querySelectorAll('.theme-toggle').forEach(function(b){
      if (theme === 'dark') {
        b.textContent = '☀️';
        b.setAttribute('aria-pressed','true');
      } else {
        b.textContent = '🌙';
        b.setAttribute('aria-pressed','false');
      }
    });
  }

  function init(){
    var saved = null;
    try { saved = localStorage.getItem(key); } catch(e){}
    if (!saved) {
      if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) saved = 'dark';
      else saved = 'light';
    }
    setTheme(saved);

    // attach handlers to any existing or future .theme-toggle buttons
    document.querySelectorAll('.theme-toggle').forEach(function(btn){
      btn.addEventListener('click', function(){
        var cur = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        setTheme(cur === 'dark' ? 'light' : 'dark');
      });
      btn.addEventListener('keydown', function(e){
        if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); btn.click(); }
      });
    });
  }

  // wait for DOM ready so include order doesn't matter
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
</script>