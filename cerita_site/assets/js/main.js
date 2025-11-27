// main.js - theme selector + remember selection in localStorage
(function(){
  const sel = document.getElementById('themeSelector');
  function applySaved(){
    try{
      const saved = localStorage.getItem('theme') || 'default-theme';
      document.body.className = saved;
      if(sel) sel.value = saved;
    }catch(e){}
  }
  if(sel){
    sel.addEventListener('change', function(){
      document.body.className = this.value;
      localStorage.setItem('theme', this.value);
    });
  }
  window.addEventListener('load', applySaved);

 function setTheme(theme) {
  const body = document.getElementById('pageBody');
  body.className = `theme-${theme}`;
  localStorage.setItem('theme', theme);
}

window.onload = () => {
  const saved = localStorage.getItem('theme') || 'floral';
  setTheme(saved);
};

  
})();

