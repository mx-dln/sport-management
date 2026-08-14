(function () {
    const root = document.querySelector('[data-admin-document-notifications]');
    if (!root) return;

    const endpoint = root.dataset.endpoint;
    const soundUrl = root.dataset.sound;
    const documentsUrl = root.dataset.documentsUrl;
    const storageKey = 'smis-last-document-upload-marker';
    const unreadKey = 'smis-unread-document-upload-ids';
    const audio = new Audio(soundUrl);
    audio.preload = 'auto';
    let unlocked = false;
    let polling = false;

    const badge = document.querySelector('[data-doc-notification-badge]');
    const countLabel = document.querySelector('[data-doc-notification-count]');
    const panel = document.querySelector('[data-notification-panel]');
    const list = document.querySelector('[data-notification-list]');
    const toggle = document.querySelector('[data-notification-toggle]');

    function unlockAudio() {
        if (unlocked) return;
        audio.volume = 0;
        audio.play()
            .then(() => {
                audio.pause();
                audio.currentTime = 0;
                audio.volume = 1;
                unlocked = true;
            })
            .catch(() => {});
    }

    document.addEventListener('click', unlockAudio, { once: true });

    function playSound() {
        const player = new Audio(soundUrl);
        player.volume = 1;
        player.play().catch(() => {});
    }

    function documentUrl(upload) {
        const separator = documentsUrl.includes('?') ? '&' : '?';
        return `${documentsUrl}${separator}document_athlete_id=${encodeURIComponent(upload.athlete_id || '')}`;
    }

    function formatDate(value) {
        if (!value) return '';
        const date = new Date(String(value).replace(' ', 'T'));
        if (Number.isNaN(date.getTime())) return value;
        return date.toLocaleString(undefined, {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit'
        });
    }

    function updateBadge(count) {
        if (!badge || !countLabel) return;
        badge.classList.toggle('hidden', count <= 0);
        countLabel.textContent = String(count);
    }

    function unreadIds() {
        try {
            const ids = JSON.parse(localStorage.getItem(unreadKey) || '[]');
            return Array.isArray(ids) ? ids.map(String) : [];
        } catch (error) {
            return [];
        }
    }

    function saveUnreadIds(ids) {
        const unique = Array.from(new Set(ids.map(String)));
        localStorage.setItem(unreadKey, JSON.stringify(unique));
        updateBadge(unique.length);
    }

    function addUnreadUploads(uploads) {
        const ids = unreadIds();
        uploads.forEach((upload) => {
            if (upload.id) ids.push(String(upload.id));
        });
        saveUnreadIds(ids);
    }

    function clearUnreadUploads() {
        saveUnreadIds([]);
    }

    function notificationItem(upload) {
        return `
            <a class="notification-item flex gap-3 rounded-xl p-3" href="${escapeHtml(documentUrl(upload))}">
                <span class="notification-item-icon grid h-12 w-12 shrink-0 place-items-center rounded-full">
                    <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"></path>
                        <path d="M14 2v6h6M9 15h6M9 18h4"></path>
                    </svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="notification-item-title block text-sm font-bold">${escapeHtml(upload.athlete_name || 'Athlete')}</span>
                    <span class="notification-item-body mt-0.5 block text-sm">Uploaded ${escapeHtml(upload.requirement_title || 'a requirement')}</span>
                    <span class="notification-item-meta mt-1 block truncate text-xs">${escapeHtml(upload.original_name || '')}</span>
                    <span class="notification-item-date mt-1 block text-xs">${escapeHtml(formatDate(upload.uploaded_at))}</span>
                </span>
            </a>
        `;
    }

    async function loadRecentNotifications() {
        if (!list) return;
        try {
            const marker = Number(localStorage.getItem(storageKey) || '0');
            const response = await fetch(`${endpoint}?marker=${encodeURIComponent(marker)}&recent=1`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            const recent = Array.isArray(data.recent) ? data.recent : [];
            list.innerHTML = recent.length
                ? recent.map(notificationItem).join('')
                : '<div class="notification-empty p-8 text-center text-sm">No document notifications yet.</div>';
        } catch (error) {
            list.innerHTML = '<div class="notification-error p-8 text-center text-sm">Unable to load notifications.</div>';
        }
    }

    if (toggle && panel) {
        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            unlockAudio();
            panel.classList.toggle('hidden');
            if (!panel.classList.contains('hidden')) {
                clearUnreadUploads();
                loadRecentNotifications();
            }
        });
        panel.addEventListener('click', (event) => event.stopPropagation());
        document.addEventListener('click', () => panel.classList.add('hidden'));
    }

    function showDocumentToast(upload) {
        const wrap = document.querySelector('[data-document-toast-stack]') || document.createElement('div');
        if (!wrap.dataset.documentToastStack) {
            wrap.dataset.documentToastStack = 'true';
            wrap.className = 'fixed bottom-5 right-5 z-[90] grid w-[min(360px,calc(100vw-2rem))] gap-3';
            document.body.appendChild(wrap);
        }

        const toast = document.createElement('a');
        toast.href = documentUrl(upload);
        toast.className = 'block rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl transition hover:-translate-y-0.5 dark-notification-toast';
        toast.innerHTML = `
            <div class="flex gap-3">
                <div class="grid h-11 w-11 shrink-0 place-items-center rounded-full text-white" style="background: var(--theme-color);">
                    <svg class="h-5 w-5" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"></path>
                        <path d="M14 2v6h6M9 15h6M9 18h4"></path>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-black text-slate-950">New document uploaded</p>
                    <p class="mt-1 truncate text-sm text-slate-600">${escapeHtml(upload.athlete_name || 'Athlete')} submitted ${escapeHtml(upload.requirement_title || 'a requirement')}</p>
                    <p class="mt-1 truncate text-xs text-slate-400">${escapeHtml(upload.original_name || '')}</p>
                    <p class="mt-1 truncate text-xs text-slate-400">${escapeHtml(formatDate(upload.uploaded_at))}</p>
                </div>
            </div>
        `;
        wrap.prepend(toast);
        setTimeout(() => toast.remove(), 9000);
    }

    async function poll() {
        if (polling) return;
        polling = true;
        try {
            const marker = Number(localStorage.getItem(storageKey) || '0');
            const response = await fetch(`${endpoint}?marker=${encodeURIComponent(marker)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await response.json();
            if (!data.ok) return;

            if (!marker) {
                localStorage.setItem(storageKey, String(data.latest_marker || 0));
                return;
            }

            const uploads = Array.isArray(data.uploads) ? data.uploads : [];
            if (uploads.length) {
                addUnreadUploads(uploads);
                if (list && panel && !panel.classList.contains('hidden')) {
                    loadRecentNotifications();
                }
                uploads.slice().reverse().forEach(showDocumentToast);
                playSound();
            }
            localStorage.setItem(storageKey, String(data.latest_marker || marker));
        } catch (error) {
            console.warn('Document notification check failed.', error);
        } finally {
            polling = false;
        }
    }

    updateBadge(unreadIds().length);
    poll();
    setInterval(poll, 15000);
})();
