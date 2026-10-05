(function(){
  'use strict';
  var root=document.documentElement;
  var themeKey='sct-theme';
  root.classList.add('sct-vs4-frontend');
  try{if(localStorage.getItem(themeKey)==='dark')root.classList.add('sct-theme-dark');}catch(e){}

  function syncTheme(){
    var dark=root.classList.contains('sct-theme-dark');
    document.querySelectorAll('[data-sct-v3-theme]').forEach(function(btn){
      var icon=btn.querySelector('.sct-settings-menu__row-icon i') || btn.querySelector('i');
      if(icon) icon.className=dark?'bi bi-sun':'bi bi-moon-stars';
      btn.setAttribute('aria-pressed',dark?'true':'false');
    });
  }
  document.querySelectorAll('[data-sct-v3-theme]').forEach(function(btn){
    btn.addEventListener('click',function(){
      root.classList.toggle('sct-theme-dark');
      try{localStorage.setItem(themeKey,root.classList.contains('sct-theme-dark')?'dark':'light');}catch(e){}
      syncTheme();
    });
  });
  syncTheme();

  var toggle=document.querySelector('[data-sct-menu-toggle]');
  var menu=document.querySelector('[data-sct-main-menu]');
  var backdrop=document.querySelector('[data-sct-menu-backdrop]');
  var lastFocus=null;
  function setMenu(open,returnFocus){
    if(!toggle||!menu)return;
    if(open){
      lastFocus=document.activeElement;
      menu.classList.add('is-open');
      menu.setAttribute('aria-hidden','false');
      toggle.setAttribute('aria-expanded','true');
      var icon=toggle.querySelector('i'); if(icon)icon.className='bi bi-x-lg';
      if(backdrop){backdrop.hidden=false;requestAnimationFrame(function(){backdrop.classList.add('is-open');});}
      document.body.classList.add('sct-menu-open');
      var first=menu.querySelector('a,button'); if(first)first.focus();
    }else{
      menu.classList.remove('is-open');
      menu.setAttribute('aria-hidden','true');
      toggle.setAttribute('aria-expanded','false');
      var icon2=toggle.querySelector('i'); if(icon2)icon2.className='bi bi-list';
      if(backdrop){backdrop.classList.remove('is-open');setTimeout(function(){backdrop.hidden=true;},180);}
      document.body.classList.remove('sct-menu-open');
      if(returnFocus!==false && toggle)toggle.focus();
    }
  }
  if(toggle)toggle.addEventListener('click',function(){setMenu(toggle.getAttribute('aria-expanded')!=='true');});
  if(backdrop)backdrop.addEventListener('click',function(){setMenu(false);});
  document.addEventListener('keydown',function(ev){
    if(ev.key==='Escape'){
      if(menu&&menu.classList.contains('is-open')){ev.preventDefault();setMenu(false);return;}
      closeMutual();
    }
    if(ev.key==='Tab'&&menu&&menu.classList.contains('is-open')){
      var focusables=Array.prototype.slice.call(menu.querySelectorAll('a[href],button:not([disabled])')).filter(function(el){return el.offsetParent!==null;});
      if(!focusables.length)return;
      var first=focusables[0],last=focusables[focusables.length-1];
      if(ev.shiftKey&&document.activeElement===first){ev.preventDefault();last.focus();}
      else if(!ev.shiftKey&&document.activeElement===last){ev.preventDefault();first.focus();}
    }
  });
  document.querySelectorAll('[data-sct-menu-link]').forEach(function(link){link.addEventListener('click',function(){setMenu(false,false);});});

  var mutual=document.querySelector('[data-sct-mutual]');
  var mutualTrigger=mutual?mutual.querySelector('[data-sct-mutual-trigger]'):null;
  var mutualMenu=mutual?mutual.querySelector('[data-sct-mutual-menu]'):null;
  function closeMutual(){if(!mutualTrigger||!mutualMenu)return;mutualTrigger.setAttribute('aria-expanded','false');mutualMenu.hidden=true;}
  if(mutualTrigger&&mutualMenu){
    mutualTrigger.addEventListener('click',function(ev){
      ev.stopPropagation();
      var open=mutualMenu.hidden;
      mutualMenu.hidden=!open;mutualTrigger.setAttribute('aria-expanded',open?'true':'false');
      if(open){var first=mutualMenu.querySelector('button');if(first)first.focus();}
    });
    document.addEventListener('click',function(ev){if(!mutual.contains(ev.target))closeMutual();});
    mutualMenu.querySelectorAll('[data-mutual-code]').forEach(function(btn){
      btn.addEventListener('click',function(){saveMutual(btn);});
    });
  }

  function saveMutual(btn){
    if(!mutual)return;
    var endpoint=mutual.getAttribute('data-endpoint');
    var csrf=mutual.getAttribute('data-csrf');
    var status=mutual.querySelector('[data-sct-mutual-status]');
    var code=btn.getAttribute('data-mutual-code');
    btn.disabled=true;
    fetch(endpoint,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json'},credentials:'same-origin',body:JSON.stringify({csrf_token:csrf,mutual_code:code})})
      .then(function(r){return r.json().then(function(data){return {ok:r.ok,data:data};});})
      .then(function(result){
        if(!result.ok||!result.data.success)throw new Error((result.data&&result.data.message)||'error');
        var logoWrap=mutual.querySelector('[data-sct-mutual-logo-wrap]');var label=mutual.querySelector('[data-sct-mutual-label]');
        if(logoWrap){
          var logoUrl=btn.getAttribute('data-mutual-logo')||'';var sigla=btn.getAttribute('data-mutual-sigla')||'';
          while(logoWrap.firstChild)logoWrap.removeChild(logoWrap.firstChild);
          if(logoUrl){var img=document.createElement('img');img.setAttribute('data-sct-mutual-logo','');img.src=logoUrl;img.alt=sigla;logoWrap.appendChild(img);}
          else{var placeholder=document.createElement('i');placeholder.className='bi bi-shield-check';placeholder.setAttribute('data-sct-mutual-placeholder','');logoWrap.appendChild(placeholder);}
        }
        if(label)label.textContent=btn.getAttribute('data-mutual-name')||'';
        mutualMenu.querySelectorAll('[data-mutual-code]').forEach(function(b){b.setAttribute('aria-checked',b===btn?'true':'false');});
        if(status){status.textContent=(window.SCT_NAV_I18N&&window.SCT_NAV_I18N.mutualSaved)||'';status.className='sct-mutual__status is-success';setTimeout(function(){status.textContent='';},2500);}
        closeMutual();
      })
      .catch(function(){if(status){status.textContent=(window.SCT_NAV_I18N&&window.SCT_NAV_I18N.mutualError)||'';status.className='sct-mutual__status is-error';}})
      .then(function(){btn.disabled=false;});
  }
})();
