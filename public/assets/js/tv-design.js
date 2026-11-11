/* Escala fiel 9:16 e rotação de conjuntos, sem alterar dados do Neon. */
(function(){
  'use strict';
  var stage=document.getElementById('tv-stage');
  function resize(){
    if (!stage) return;
    var landscape=window.innerWidth>window.innerHeight;
    stage.classList.toggle('is-rotated-right',landscape);
    var s=landscape
      ? Math.min(window.innerWidth/1280,window.innerHeight/720)
      : Math.min(window.innerWidth/720,window.innerHeight/1280);
    stage.style.setProperty('--tv-scale',String(Math.max(.05,s)));
  }
  window.addEventListener('resize',resize,{passive:true});
  window.addEventListener('orientationchange',resize,{passive:true});
  resize();
  function rotate(selector,interval){
    var nodes=Array.prototype.slice.call(document.querySelectorAll(selector));
    if (nodes.length<2) return;
    var active=0;
    window.setInterval(function(){
      nodes[active].hidden=true;
      nodes[active].classList.remove('is-active');
      nodes[active].setAttribute('aria-hidden','true');
      active=(active+1)%nodes.length;
      nodes[active].hidden=false;
      nodes[active].classList.add('is-active');
      nodes[active].setAttribute('aria-hidden','false');
    },interval);
  }
  rotate('[data-tv-people-page]',16000);
  rotate('[data-tv-news-page]',9500);
  rotate('[data-tv-qr-page]',12000);
  document.addEventListener('DOMContentLoaded',function(){
    window.setTimeout(function(){location.reload();},window.TV_REFRESH_MS || 300000);
  });
})();

/* Relógio em tempo real (fuso de Brasília) e clima de Alvorada/RS (Open-Meteo). Só front-end. */
(function(){
  'use strict';
  var clock=document.getElementById('tv-clock');
  if (!clock) return;
  var dateEl=document.getElementById('tv-date');
  var TZ='America/Sao_Paulo';
  var fHora=null,fData=null;
  try {
    fHora=new Intl.DateTimeFormat('pt-BR',{hour:'2-digit',minute:'2-digit',hour12:false,timeZone:TZ});
    fData=new Intl.DateTimeFormat('pt-BR',{weekday:'short',day:'2-digit',month:'short',timeZone:TZ});
  } catch(e) {}
  function dois(n){ return (n<10?'0':'')+n; }
  function tick(){
    var d=new Date();
    var hm=(fHora ? fHora.format(d) : dois(d.getHours())+':'+dois(d.getMinutes())).split(':');
    clock.innerHTML=hm[0]+'<span class="tv-colon">:</span>'+(hm[1]||'00');
    if (dateEl && fData) dateEl.textContent=fData.format(d).replace(/\./g,'');
  }
  tick();
  window.setInterval(tick,5000);

  var ICONES={
    sun:'<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
    moon:'<path d="M21 12.8A9 9 0 1 1 11.2 3 7 7 0 0 0 21 12.8z"/>',
    partly:'<circle cx="8" cy="8" r="3"/><path d="M8 1.5v1.5M2 8h1.5M3.8 3.8l1 1M12.2 3.8l-1 1"/><path d="M19 19H9a4 4 0 1 1 .8-7.9A5 5 0 0 1 19 15a2 2 0 0 1 0 4z"/>',
    cloud:'<path d="M18 10h-1.26A8 8 0 1 0 9 20h9a5 5 0 0 0 0-10z"/>',
    fog:'<path d="M17 15H7a4 4 0 1 1 .6-7.96A5.5 5.5 0 0 1 18 9.5 2.8 2.8 0 0 1 17 15z"/><path d="M5 19h14M8 22h8"/>',
    drizzle:'<path d="M20 16.58A5 5 0 0 0 18 7h-1.26A8 8 0 1 0 4 15.25"/><path d="M8 19v2M8 13v2M16 19v2M16 13v2"/>',
    rain:'<path d="M20 16.58A5 5 0 0 0 18 7h-1.26A8 8 0 1 0 4 15.25"/><path d="M8 19v2M8 13v2M16 19v2M16 13v2M12 21v2M12 15v2"/>',
    storm:'<path d="M19 16.9A5 5 0 0 0 18 7h-1.26a8 8 0 1 0-11.62 9"/><path d="M13 11l-4 6h6l-4 6"/>'
  };
  function mapear(code,dia){
    if (code===0) return {t:dia?'Céu limpo':'Noite limpa',i:dia?'sun':'moon'};
    if (code===1||code===2) return {t:'Parcialmente nublado',i:dia?'partly':'cloud'};
    if (code===3) return {t:'Nublado',i:'cloud'};
    if (code===45||code===48) return {t:'Neblina',i:'fog'};
    if (code>=51&&code<=57) return {t:'Garoa',i:'drizzle'};
    if ((code>=61&&code<=67)||(code>=80&&code<=82)) return {t:'Chuva',i:'rain'};
    if (code>=95) return {t:'Trovoadas',i:'storm'};
    return {t:'Tempo estável',i:dia?'sun':'moon'};
  }
  var box=document.getElementById('tv-weather');
  var icone=document.getElementById('tv-weather-icon');
  var temp=document.getElementById('tv-weather-temp');
  var desc=document.getElementById('tv-weather-desc');
  function clima(){
    if (!box || !window.fetch) return;
    var url='https://api.open-meteo.com/v1/forecast?latitude=-29.9914&longitude=-51.0809&current=temperature_2m,weather_code,is_day&timezone=America%2FSao_Paulo';
    fetch(url).then(function(r){ if(!r.ok) throw new Error('clima'); return r.json(); }).then(function(d){
      var c=d.current, info=mapear(c.weather_code,c.is_day===1);
      temp.textContent=Math.round(c.temperature_2m)+'°C';
      desc.textContent=info.t;
      icone.innerHTML=ICONES[info.i]||ICONES.cloud;
      box.hidden=false;
    }).catch(function(){ /* sem internet: mantém o último valor ou esconde o bloco */ });
  }
  clima();
  window.setInterval(clima,600000);
})();

/* Destaque rotativo: um vereador por vez ganha brilho dourado e sobe levemente. */
(function(){
  'use strict';
  var STEP=2600, idx=0, ultimo=null;
  function cards(){
    var pagina=document.querySelector('.tv-people-page.is-active');
    return pagina ? pagina.querySelectorAll('.tv-person') : [];
  }
  function passo(){
    if (ultimo) ultimo.classList.remove('is-spot');
    var lista=cards();
    if (!lista.length) { ultimo=null; return; }
    ultimo=lista[idx % lista.length];
    ultimo.classList.add('is-spot');
    idx++;
  }
  window.setTimeout(function(){ passo(); window.setInterval(passo,STEP); },1800);
})();
