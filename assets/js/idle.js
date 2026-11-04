(() => {
  const LIMIT=600000, WARNING=540000; let last=Date.now(), warned=false, dialog=null, interval=null;
  const touch=()=>{last=Date.now(); if(warned){warned=false; if(dialog)dialog.remove();dialog=null;}};
  ['mousemove','keydown','click','scroll','touchstart','user-activity'].forEach(e=>document.addEventListener(e,touch,{passive:true}));
  window.addEventListener('storage',e=>{if(e.key==='notary-last-activity'&&e.newValue){last=Number(e.newValue);touch();}});
  const sync=()=>{last=Date.now();localStorage.setItem('notary-last-activity',String(last));touch();fetch('keepalive.php',{headers:{Accept:'application/json'}}).catch(()=>{});};
  function warn(){if(warned)return;warned=true;dialog=document.createElement('div');dialog.className='modal timeout-dialog';dialog.innerHTML='<div class="modal-box"><h2>Still working?</h2><p>You will be signed out in <strong id="timeout-count">60</strong> seconds.</p><div class="actions"><button class="btn" id="stay-signed-in">Stay signed in</button><a class="btn light" href="logout.php?reason=timeout">Sign out now</a></div></div>';document.body.append(dialog);dialog.querySelector('#stay-signed-in').onclick=sync;let n=60;interval=setInterval(()=>{n--;const el=dialog?.querySelector('#timeout-count');if(el)el.textContent=n;if(n<=0){clearInterval(interval);location.href='logout.php?reason=timeout'}},1000)}
  setInterval(()=>{const elapsed=Date.now()-last;if(elapsed>=LIMIT)location.href='logout.php?reason=timeout';else if(elapsed>=WARNING)warn();},1000);sync();
})();
