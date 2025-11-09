// script.js
let currentOpenId = null;

function openPwModal(id) {
  currentOpenId = id;
  document.getElementById('pwInput').value = '';
  document.getElementById('pwModal').classList.remove('hidden');
  setTimeout(()=>document.getElementById('pwInput').focus(), 120);
}

document.getElementById('pwCancel').addEventListener('click', ()=>{
  document.getElementById('pwModal').classList.add('hidden');
});

document.getElementById('pwSubmit').addEventListener('click', async ()=>{
  const pw = document.getElementById('pwInput').value || '';
  if (!currentOpenId) return;
  try {
    const fd = new FormData();
    fd.append('id', currentOpenId);
    fd.append('password', pw);
    const res = await fetch('verify.php', { method: 'POST', body: fd });
    const j = await res.json();
    if (j.ok) {
      // open player
      openPlayer(currentOpenId);
      document.getElementById('pwModal').classList.add('hidden');
    } else {
      alert(j.msg || 'Gagal memverifikasi');
    }
  } catch (e) {
    alert('Error: ' + e.message);
  }
});

function openPlayer(id) {
  const title = document.querySelector(`button[onclick="openPwModal('${id}')"]`).closest('.card').querySelector('h3').innerText;
  document.getElementById('playerTitle').innerText = title;
  const v = document.getElementById('player');
  v.src = `stream.php?id=${encodeURIComponent(id)}`;
  document.getElementById('playerModal').classList.remove('hidden');
  v.play().catch(()=>{ /* autoplay may be blocked */ });
}

document.getElementById('playerClose').addEventListener('click', ()=>{
  const v = document.getElementById('player');
  v.pause();
  v.src = '';
  document.getElementById('playerModal').classList.add('hidden');
});