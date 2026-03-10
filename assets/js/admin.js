/**
 * Admin Panel JavaScript
 * Handles: sidebar toggle, AJAX deletes/status updates, table sorting,
 *          image preview, YouTube ID extraction
 */

'use strict';

// ── Toast Notifications ────────────────────────────────────────────────────────

function showToast(message, type = 'info', duration = 4000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const icons = { success: 'fa-check-circle', error: 'fa-exclamation-triangle', info: 'fa-info-circle' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.setAttribute('role', 'status');
    toast.innerHTML = `<i class="fas ${icons[type] || icons.info}" aria-hidden="true"></i><span>${message}</span>`;

    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity   = '0';
        toast.style.transform = 'translateX(40px)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 350);
    }, duration);
}

// ── Sidebar Toggle ─────────────────────────────────────────────────────────────

(function initSidebar() {
    const toggle  = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    if (!toggle || !sidebar) return;

    function open() {
        sidebar.classList.add('open');
        overlay && overlay.classList.add('active');
        toggle.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }

    function close() {
        sidebar.classList.remove('open');
        overlay && overlay.classList.remove('active');
        toggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    toggle.addEventListener('click', () => {
        sidebar.classList.contains('open') ? close() : open();
    });

    overlay && overlay.addEventListener('click', close);

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') close();
    });
})();

// ── Flash Message Dismiss ──────────────────────────────────────────────────────

document.querySelectorAll('.flash-close').forEach(btn => {
    btn.addEventListener('click', function () {
        const msg = this.closest('.flash-message');
        if (msg) { msg.style.opacity = '0'; setTimeout(() => msg.remove(), 300); }
    });
});

// ── Inline Confirm Delete (Category delete button) ────────────────────────────

document.addEventListener('click', (e) => {
    const btn = e.target.closest('.confirm-delete-inline');
    if (!btn) return;

    e.preventDefault();
    const name = btn.dataset.name || 'this item';
    if (confirm(`Are you sure you want to delete "${name}"? This cannot be undone.`)) {
        btn.closest('form')?.submit();
    }
});

// ── Delete Song (AJAX) ────────────────────────────────────────────────────────

document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-delete-song');
    if (!btn) return;

    const songId    = btn.dataset.songId;
    const songTitle = btn.dataset.songTitle || 'this song';

    if (!confirm(`Delete "${songTitle}"? This will remove the file and cannot be undone.`)) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    try {
        const csrf = document.querySelector('meta[name="csrf_token"]')?.content
            || document.querySelector('input[name="csrf_token"]')?.value
            || '';

        // Get CSRF from any form on the page
        const csrfInput = document.querySelector('input[name="csrf_token"]');
        const csrfToken = csrfInput ? csrfInput.value : '';

        const fd = new FormData();
        fd.append('song_id', songId);
        fd.append('csrf_token', csrfToken);

        const res  = await fetch('/dj-booking-platform/api/admin/delete-song.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            const row = document.getElementById(`song-row-${songId}`);
            if (row) {
                row.style.opacity = '0';
                row.style.transition = 'opacity 0.3s ease';
                setTimeout(() => row.remove(), 350);
            }
            showToast(`"${songTitle}" deleted successfully.`, 'success');
        } else {
            showToast(data.message || 'Failed to delete song.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash"></i> Delete';
        }
    } catch (err) {
        showToast('Network error. Please try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i> Delete';
    }
});

// ── Delete Video (AJAX) ───────────────────────────────────────────────────────

document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-delete-video');
    if (!btn) return;

    const videoId    = btn.dataset.videoId;
    const videoTitle = btn.dataset.videoTitle || 'this video';

    if (!confirm(`Delete "${videoTitle}"?`)) return;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    try {
        const csrfInput = document.querySelector('input[name="csrf_token"]');
        const csrfToken = csrfInput ? csrfInput.value : '';

        const fd = new FormData();
        fd.append('video_id', videoId);
        fd.append('csrf_token', csrfToken);

        const res  = await fetch('/dj-booking-platform/api/admin/delete-video.php', { method: 'POST', body: fd });
        const data = await res.json();

        if (data.success) {
            const row = document.getElementById(`video-row-${videoId}`);
            if (row) { row.style.opacity = '0'; setTimeout(() => row.remove(), 350); }
            showToast('Video deleted.', 'success');
        } else {
            showToast(data.message || 'Failed to delete.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-trash"></i>';
        }
    } catch (err) {
        showToast('Network error.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-trash"></i>';
    }
});

// ── Booking Delete Confirm ─────────────────────────────────────────────────────

document.querySelectorAll('.confirm-delete').forEach(btn => {
    btn.addEventListener('click', function () {
        const name   = this.dataset.name || 'this item';
        const formId = this.dataset.target;
        const form   = document.getElementById(formId);
        if (confirm(`Are you sure you want to delete "${name}"? This cannot be undone.`)) {
            form?.submit();
        }
    });
});

// ── Table Sorting ─────────────────────────────────────────────────────────────

(function initSortableTables() {
    document.querySelectorAll('.sortable-table').forEach(table => {
        const headers = table.querySelectorAll('th[data-sort]');
        let currentSort = { col: null, dir: 'asc' };

        headers.forEach(th => {
            th.style.cursor = 'pointer';
            th.addEventListener('click', function () {
                const col = this.dataset.sort;
                const dir = (currentSort.col === col && currentSort.dir === 'asc') ? 'desc' : 'asc';
                currentSort = { col, dir };

                // Update header classes
                headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
                this.classList.add(`sort-${dir}`);

                // Get column index
                const colIdx = Array.from(this.parentElement.children).indexOf(this);

                // Sort rows
                const tbody = table.querySelector('tbody');
                const rows  = Array.from(tbody.querySelectorAll('tr'));

                rows.sort((a, b) => {
                    const aText = a.children[colIdx]?.textContent.trim().toLowerCase() || '';
                    const bText = b.children[colIdx]?.textContent.trim().toLowerCase() || '';
                    const cmp   = aText.localeCompare(bText, undefined, { numeric: true });
                    return dir === 'asc' ? cmp : -cmp;
                });

                rows.forEach(row => tbody.appendChild(row));
            });
        });
    });
})();

// ── Image Preview on File Input ────────────────────────────────────────────────

(function initImagePreviews() {
    // Cover image preview on upload-music page
    const coverInput   = document.getElementById('cover_image');
    const coverPreview = document.getElementById('cover-preview');
    const coverWrap    = document.getElementById('cover-preview-wrap');

    if (coverInput && coverPreview) {
        coverInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    coverPreview.src          = e.target.result;
                    if (coverWrap) coverWrap.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Profile image preview
    const profileInput   = document.getElementById('profile_image');
    const profilePreview = document.getElementById('profile-img-preview');
    const profilePH      = document.getElementById('profile-img-preview-placeholder');

    if (profileInput && profilePreview) {
        profileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    profilePreview.src          = e.target.result;
                    profilePreview.style.display = 'block';
                    if (profilePH) profilePH.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Audio file name display
    const audioInput    = document.getElementById('audio_file');
    const audioFileName = document.getElementById('audio-file-name');

    if (audioInput && audioFileName) {
        audioInput.addEventListener('change', function () {
            const file = this.files[0];
            audioFileName.textContent = file ? `Selected: ${file.name} (${formatBytes(file.size)})` : '';
        });
    }

    function formatBytes(bytes) {
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
        if (bytes >= 1024)    return (bytes / 1024).toFixed(1) + ' KB';
        return bytes + ' B';
    }
})();

// ── YouTube URL → ID + Thumbnail Preview ──────────────────────────────────────

(function initYouTubePreview() {
    const urlInput   = document.getElementById('youtube_url');
    const preview    = document.getElementById('yt-preview');
    const thumbnail  = document.getElementById('yt-thumbnail');

    if (!urlInput || !preview || !thumbnail) return;

    function extractYouTubeId(url) {
        const pattern = /(?:youtube\.com\/(?:[^/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/;
        const match   = url.match(pattern);
        return match ? match[1] : null;
    }

    let debounceTimer;
    urlInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const ytId = extractYouTubeId(this.value.trim());
            if (ytId) {
                thumbnail.src          = `https://img.youtube.com/vi/${ytId}/hqdefault.jpg`;
                preview.style.display  = 'block';
            } else {
                preview.style.display  = 'none';
            }
        }, 500);
    });
})();
