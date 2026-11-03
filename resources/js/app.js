const live = document.querySelector('[data-live-endpoint]');
let timer;
let polling = false;
let leaving = false;

function write(selector, value) {
    document.querySelectorAll(selector).forEach((element) => {
        if (element.textContent !== String(value)) element.textContent = String(value);
    });
}

async function poll() {
    if (!live || polling || leaving) return;
    if (document.hidden) {
        timer = setTimeout(poll, 5000);
        return;
    }
    polling = true;
    const abort = new AbortController();
    const timeout = setTimeout(() => abort.abort(), 4000);
    try {
        const response = await fetch(live.dataset.liveEndpoint, {
            cache: 'no-store', headers: { Accept: 'application/json' }, signal: abort.signal,
        });
        if (!response.ok) throw new Error('status');
        const data = await response.json();
        if (leaving) return;
        if (data.date !== live.dataset.liveDay
            || (live.dataset.reloadVersion && data.version !== live.dataset.reloadVersion)) {
            window.location.reload();
            return;
        }
        write('[data-current-number]', data.current);
        write('[data-current-caption]', data.current_id
            ? 'Silakan menuju tempat pelayanan.' : 'Belum ada antrean yang dipanggil.');
        Object.entries(data.counts).forEach(([key, value]) => write(`[data-count="${key}"]`, value));
        const updated = new Intl.DateTimeFormat('id-ID', {
            hour: '2-digit', minute: '2-digit', second: '2-digit', timeZone: 'Asia/Jakarta',
        }).format(new Date(data.updated_at));
        write('[data-connection]', `Terakhir diperbarui ${updated} WIB · otomatis setiap 5 detik.`);
    } catch {
        write('[data-connection]', 'Pembaruan tertunda. Mencoba kembali secara otomatis…');
    } finally {
        clearTimeout(timeout);
        polling = false;
        if (!leaving) timer = setTimeout(poll, 5000);
    }
}

document.querySelectorAll('form[method="post"]').forEach((form) => {
    form.addEventListener('submit', () => {
        leaving = true;
        clearTimeout(timer);
        form.querySelectorAll('button[type="submit"]').forEach((button) => { button.disabled = true; });
    });
});
window.addEventListener('pageshow', (event) => { if (event.persisted) window.location.reload(); });
document.addEventListener('visibilitychange', () => {
    if (!document.hidden && live && !leaving) {
        clearTimeout(timer);
        poll();
    }
});
if (live) timer = setTimeout(poll, 5000);
