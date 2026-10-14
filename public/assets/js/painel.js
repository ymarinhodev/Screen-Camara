/* Painel da equipe: abas, envio por fetch, redução de fotos no navegador e prévia de QR Code. */
(function () {
  'use strict';

  var csrf = document.querySelector('meta[name="csrf"]').content;
  var aviso = document.getElementById('aviso');
  var timerAviso;

  function mostrar(msg, erro) {
    aviso.textContent = msg;
    aviso.className = erro ? 'erro' : '';
    aviso.hidden = false;
    clearTimeout(timerAviso);
    timerAviso = setTimeout(function () { aviso.hidden = true; }, erro ? 7000 : 3500);
  }

  /* ---------- abas (a aba atual fica no #hash para sobreviver ao recarregamento) ---------- */
  var abas = Array.prototype.slice.call(document.querySelectorAll('.aba'));
  function abrirAba(nome) {
    if (!document.getElementById('aba-' + nome)) { nome = 'vereadores'; }
    abas.forEach(function (b) { b.setAttribute('aria-selected', String(b.dataset.aba === nome)); });
    Array.prototype.forEach.call(document.querySelectorAll('.painel'), function (p) {
      p.hidden = p.id !== 'aba-' + nome;
    });
    if (location.hash !== '#' + nome) { history.replaceState(null, '', '#' + nome); }
  }
  abas.forEach(function (b) { b.addEventListener('click', function () { abrirAba(b.dataset.aba); }); });
  abrirAba(location.hash.replace('#', '') || 'vereadores');

  /* ---------- envio ---------- */
  function enviar(fd) {
    return fetch('/acoes.php', {
      method: 'POST',
      body: fd,
      credentials: 'same-origin',
      headers: { 'X-CSRF-Token': csrf }
    }).then(function (r) {
      return r.text().then(function (t) {
        var j;
        try { j = JSON.parse(t); } catch (e) { throw new Error('Resposta inesperada do servidor (' + r.status + ').'); }
        if (!j.ok) { throw new Error(j.msg || 'Não foi possível concluir.'); }
        return j;
      });
    });
  }

  /* Reduz a foto no navegador: o limite de upload da Vercel é pequeno (4,5 MB). */
  function reduzir(arquivo, lado) {
    if (!/^image\/(jpeg|png|webp|gif)$/.test(arquivo.type)) {
      return Promise.reject(new Error('Envie uma imagem JPG, PNG ou WEBP.'));
    }
    return createImageBitmap(arquivo).then(function (bmp) {
      var escala = Math.min(1, lado / Math.max(bmp.width, bmp.height));
      if (escala === 1 && arquivo.size <= 900 * 1024 && arquivo.type !== 'image/gif') { return arquivo; }
      var c = document.createElement('canvas');
      c.width = Math.max(1, Math.round(bmp.width * escala));
      c.height = Math.max(1, Math.round(bmp.height * escala));
      var ctx = c.getContext('2d');
      var png = arquivo.type === 'image/png';
      if (!png) { ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height); }
      ctx.drawImage(bmp, 0, 0, c.width, c.height);
      return new Promise(function (ok, falha) {
        c.toBlob(function (blob) {
          if (!blob) { return falha(new Error('Não consegui processar essa imagem.')); }
          if (blob.size > 3000000) { return falha(new Error('A imagem continua muito grande. Escolha uma menor.')); }
          ok(new File([blob], png ? 'imagem.png' : 'imagem.jpg', { type: blob.type }));
        }, png ? 'image/png' : 'image/jpeg', 0.86);
      });
    });
  }

  function preparar(form) {
    var fd = new FormData(form);
    var tarefas = [];
    Array.prototype.forEach.call(form.querySelectorAll('input[type=file]'), function (inp) {
      fd.delete(inp.name);
      if (inp.files && inp.files[0]) {
        tarefas.push(reduzir(inp.files[0], parseInt(inp.dataset.lado, 10) || 1000).then(function (f) {
          fd.set(inp.name, f, f.name);
        }));
      }
    });
    return Promise.all(tarefas).then(function () { return fd; });
  }

  function ocupado(botao, sim, texto) {
    if (!botao) { return; }
    if (sim) { botao.dataset.rotulo = botao.textContent; botao.textContent = texto; botao.disabled = true; }
    else { botao.textContent = botao.dataset.rotulo || botao.textContent; botao.disabled = false; }
  }

  function aposSucesso(j, form) {
    mostrar(j.msg || 'Pronto.');
    if (j.recarregar) { setTimeout(function () { location.reload(); }, 600); return; }
    if (form && j.foto) {
      var img = form.querySelector('.miniatura-foto, .miniatura-noticia');
      if (img) { img.src = j.foto; }
    }
    if (form && j.logo) {
      var logo = form.querySelector('.miniatura-logo');
      if (logo) { logo.src = j.logo; }
    }
    if (form) {
      Array.prototype.forEach.call(form.querySelectorAll('input[type=file]'), function (i) { i.value = ''; });
    }
  }

  // salvar
  Array.prototype.forEach.call(document.querySelectorAll('form[data-salvar]'), function (form) {
    form.addEventListener('submit', function (ev) {
      ev.preventDefault();
      var botao = form.querySelector('button[type=submit]');
      ocupado(botao, true, 'Salvando…');
      preparar(form).then(function (fd) {
        fd.set('acao', form.dataset.salvar);
        return enviar(fd);
      }).then(function (j) {
        aposSucesso(j, form);
        if (form.dataset.salvar === 'senha_alterar' || form.dataset.salvar === 'usuario_novo') { form.reset(); }
      }).catch(function (e) {
        mostrar(e.message, true);
      }).then(function () { ocupado(botao, false); });
    });
  });

  // excluir item
  Array.prototype.forEach.call(document.querySelectorAll('[data-botao-excluir]'), function (botao) {
    botao.addEventListener('click', function () {
      var form = botao.closest('form');
      if (!confirm('Excluir este item? Essa ação não pode ser desfeita.')) { return; }
      var fd = new FormData();
      fd.set('acao', form.dataset.excluir);
      fd.set('id', form.querySelector('[name=id]').value);
      ocupado(botao, true, 'Excluindo…');
      enviar(fd).then(function (j) { aposSucesso(j, null); })
        .catch(function (e) { mostrar(e.message, true); ocupado(botao, false); });
    });
  });

  // remover usuário
  Array.prototype.forEach.call(document.querySelectorAll('[data-botao-usuario]'), function (botao) {
    botao.addEventListener('click', function () {
      if (!confirm('Remover o acesso deste usuário?')) { return; }
      var fd = new FormData();
      fd.set('acao', 'usuario_excluir');
      fd.set('id', botao.closest('form').dataset.excluirUsuario);
      ocupado(botao, true, 'Removendo…');
      enviar(fd).then(function (j) { aposSucesso(j, null); })
        .catch(function (e) { mostrar(e.message, true); ocupado(botao, false); });
    });
  });

  // adicionar
  Array.prototype.forEach.call(document.querySelectorAll('[data-novo]'), function (botao) {
    botao.addEventListener('click', function () {
      var fd = new FormData();
      fd.set('acao', botao.dataset.novo);
      if (botao.dataset.tela) { fd.set('tela', botao.dataset.tela); }
      ocupado(botao, true, 'Criando…');
      enviar(fd).then(function (j) { aposSucesso(j, null); })
        .catch(function (e) { mostrar(e.message, true); ocupado(botao, false); });
    });
  });

  // prévia local ao escolher uma foto (antes de salvar)
  Array.prototype.forEach.call(document.querySelectorAll('.troca input[type=file]'), function (inp) {
    inp.addEventListener('change', function () {
      var f = inp.files && inp.files[0];
      var img = inp.parentNode.querySelector('img');
      if (f && img) { img.src = URL.createObjectURL(f); }
    });
  });

  // prévia do QR Code ao digitar o link (mesma API qrserver.com usada nas TVs)
  Array.prototype.forEach.call(document.querySelectorAll('.item-qr'), function (form) {
    var campo = form.querySelector('[name=link]');
    var img = form.querySelector('.qr-previa');
    var t;
    campo.addEventListener('input', function () {
      clearTimeout(t);
      t = setTimeout(function () {
        if (/^https?:\/\/\S+$/i.test(campo.value)) {
          img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=10&format=png&data=' + encodeURIComponent(campo.value);
        }
      }, 500);
    });
  });
}());
