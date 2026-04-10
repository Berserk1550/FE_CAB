//ESTE SCRIPT SE ENCUENTRA DENTRO DEL SEGUNDO HTML

  // Copiar URL al portapapeles
  document.addEventListener('click', async (e)=>{
    const btn = e.target.closest('button[data-copy]');
    if(!btn) return;
    try{
      await navigator.clipboard.writeText(btn.dataset.copy);
      btn.classList.add('copied');
      const old = btn.textContent;
      btn.textContent = '¡Copiado!';
      setTimeout(()=>{ btn.textContent = old; btn.classList.remove('copied'); }, 1200);
    }catch(err){ alert('No se pudo copiar: ' + err); }
  });