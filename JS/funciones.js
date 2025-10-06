// uploader.js
(function () {
  const fileInput = document.getElementById('file-input');
  const dropzone  = document.getElementById('dropzone');
  const namesBox  = document.getElementById('file-names');
  const btnSubir  = document.getElementById('btn-subir');

  // Helpers
  const fmtSize = (bytes) => {
    if (bytes === 0 || isNaN(bytes)) return '0 B';
    const k = 1024, units = ['B','KB','MB','GB','TB'];
    const i = Math.floor(Math.log(bytes)/Math.log(k));
    return `${(bytes/Math.pow(k,i)).toFixed(i ? 1 : 0)} ${units[i]}`;
  };

  const renderSelected = (file) => {
    if (!file) {
      namesBox.textContent = 'No se ha seleccionado ningún archivo';
      btnSubir?.setAttribute('disabled', 'true');
      return;
    }
  
    namesBox.innerHTML = `
      <div style="display:flex;align-items:center;gap:.6rem;">
        <div style="width:28px;height:28px;border-radius:6px;background:#eef2ff;display:grid;place-items:center;flex:0 0 28px">
          <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
            <path fill="#6366f1" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <path fill="#a5b4fc" d="M14 2v6h6"/>
          </svg>
        </div>
        <div style="display:grid;gap:2px;">
          <strong style="font:600 14px/1.2 ui-sans-serif, system-ui;">${file.name}</strong>
          <span style="font:12px/1.2 ui-sans-serif, system-ui; color:#6b7280;">${fmtSize(file.size)}</span>
        </div>
      </div>`;
    btnSubir?.removeAttribute('disabled');
  };

  fileInput?.addEventListener('change', () => {
    renderSelected(fileInput.files?.[0]);
  });

  ['dragenter','dragover'].forEach(evt =>
    dropzone?.addEventListener(evt, (e) => {
      e.preventDefault(); e.stopPropagation();
      dropzone.classList.add('is-dragover');
    })
  );
  ['dragleave','drop'].forEach(evt =>
    dropzone?.addEventListener(evt, (e) => {
      e.preventDefault(); e.stopPropagation();
      dropzone.classList.remove('is-dragover');
    })
  );
  dropzone?.addEventListener('drop', (e) => {
    const dt = e.dataTransfer;
    if (!dt || !dt.files?.length) return;
    // Asigna el archivo dropped al input real
    const file = dt.files[0];
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    fileInput.files = dataTransfer.files;
    renderSelected(file);
  });

  dropzone?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      fileInput.click();
    }
  });
})();
