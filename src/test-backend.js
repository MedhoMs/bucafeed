console.log('🚀 TelamoNet Backend Test: Assets cargados correctamente.');
document.addEventListener('DOMContentLoaded', () => {
    const statusEl = document.createElement('div');
    statusEl.style.cssText = 'position:fixed;bottom:10px;right:10px;padding:5px 10px;background:#10b981;color:white;border-radius:5px;font-size:12px;z-index:9999;';
    statusEl.innerHTML = '✅ JS Assets OK';
    document.body.appendChild(statusEl);
});
