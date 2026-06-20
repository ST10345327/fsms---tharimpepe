(function(){
  const drawer = document.querySelector('[data-mobile-drawer]');
  if(!drawer) return;

  const openBtns = document.querySelectorAll('[data-mobile-drawer-open]');
  const closeBtns = document.querySelectorAll('[data-mobile-drawer-close]');

  function openDrawer(){
    drawer.setAttribute('data-open','true');
    drawer.setAttribute('aria-hidden','false');
  }

  function closeDrawer(){
    drawer.removeAttribute('data-open');
    drawer.setAttribute('aria-hidden','true');
  }

  openBtns.forEach(btn=> btn.addEventListener('click', openDrawer));
  closeBtns.forEach(btn=> btn.addEventListener('click', closeDrawer));

  // Close on ESC
  document.addEventListener('keydown', (e)=>{
    if(e.key === 'Escape') closeDrawer();
  });
})();

