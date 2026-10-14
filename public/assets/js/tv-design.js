/* Escala fiel 9:16 e rotação de conjuntos, sem alterar dados do Neon. */
(function(){
  'use strict';
  var stage=document.getElementById('tv-stage');
  function resize(){
    if (!stage) return;
    var s=Math.min(window.innerWidth/720,window.innerHeight/1280);
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
