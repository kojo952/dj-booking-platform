/**
 * Music Player — Persistent sticky bottom audio player
 * Controls: play, pause, next, previous, volume, seek
 * Features: keyboard shortcuts, play count tracking, localStorage volume
 */

'use strict';

class MusicPlayer {
    /**
     * @param {HTMLElement} playerEl  The #music-player-bar element
     */
    constructor(playerEl) {
        if (!playerEl) return;

        this.playerEl   = playerEl;
        this.audio      = playerEl.querySelector('#audio-element');
        this.playlist   = [];  // Array of song objects
        this.currentIdx = 0;
        this.isPlaying  = false;
        this.isScrubbing = false;

        // UI elements
        this.ui = {
            cover:        playerEl.querySelector('#player-cover'),
            coverPH:      playerEl.querySelector('#player-cover-placeholder'),
            title:        playerEl.querySelector('#player-title'),
            artist:       playerEl.querySelector('#player-artist'),
            btnPlayPause: playerEl.querySelector('#btn-play-pause'),
            btnPrev:      playerEl.querySelector('#btn-prev'),
            btnNext:      playerEl.querySelector('#btn-next'),
            btnMute:      playerEl.querySelector('#btn-mute'),
            btnMinimize:  playerEl.querySelector('#btn-minimize'),
            playIcon:     playerEl.querySelector('#play-icon'),
            volumeIcon:   playerEl.querySelector('#volume-icon'),
            progressBar:  playerEl.querySelector('#player-progress-bar'),
            progressFill: playerEl.querySelector('#player-progress-fill'),
            currentTime:  playerEl.querySelector('#player-current-time'),
            duration:     playerEl.querySelector('#player-duration'),
            volumeSlider: playerEl.querySelector('#volume-slider'),
        };

        // Restore volume
        const savedVol = parseFloat(localStorage.getItem('player_volume') ?? '0.8');
        this.audio.volume = Math.min(1, Math.max(0, savedVol));
        if (this.ui.volumeSlider) this.ui.volumeSlider.value = this.audio.volume;

        this._bindEvents();
        this._bindKeyboard();
    }

    // ── Playlist ───────────────────────────────────────────────────────────────

    /** Build playlist from all .play-overlay buttons on the page */
    buildPlaylistFromPage() {
        const buttons = document.querySelectorAll('.play-overlay[data-song-id]');
        this.playlist = Array.from(buttons).map(btn => ({
            id:     parseInt(btn.dataset.songId, 10),
            title:  btn.dataset.songTitle  || 'Unknown',
            artist: btn.dataset.songArtist || 'Unknown',
            file:   btn.dataset.songFile   || '',
            cover:  btn.dataset.songCover  || '',
        }));
    }

    // ── Core playback ──────────────────────────────────────────────────────────

    /**
     * Load a song into the player by its object
     * @param {object} song
     * @param {boolean} autoPlay
     */
    loadSong(song, autoPlay = true) {
        if (!song || !song.file) return;

        this.audio.src      = song.file;
        this.audio.preload  = 'auto';

        // Update UI
        if (this.ui.title)  this.ui.title.textContent  = song.title;
        if (this.ui.artist) this.ui.artist.textContent = song.artist;

        if (song.cover && this.ui.cover) {
            this.ui.cover.src               = song.cover;
            this.ui.cover.style.display     = 'block';
            if (this.ui.coverPH) this.ui.coverPH.style.display = 'none';
        } else {
            if (this.ui.cover)  this.ui.cover.style.display  = 'none';
            if (this.ui.coverPH) this.ui.coverPH.style.display = 'flex';
        }

        // Reset progress
        this._setProgress(0);
        if (this.ui.currentTime) this.ui.currentTime.textContent = '0:00';
        if (this.ui.duration)    this.ui.duration.textContent    = '0:00';

        // Show player
        this.playerEl.style.display = 'block';
        this.playerEl.classList.remove('minimized');

        // Highlight active card
        this._updateActiveCard(song.id);

        if (autoPlay) this.play();
    }

    /**
     * Play a song by index in the current playlist
     * @param {number} idx
     */
    playAtIndex(idx) {
        if (idx < 0 || idx >= this.playlist.length) return;
        this.currentIdx = idx;
        this.loadSong(this.playlist[idx]);
    }

    /** Play / resume */
    play() {
        const playPromise = this.audio.play();
        if (playPromise !== undefined) {
            playPromise
                .then(() => {
                    this.isPlaying = true;
                    this._setPlayIcon(true);
                    this._trackPlayCount();
                })
                .catch(err => console.warn('Playback failed:', err));
        }
    }

    /** Pause */
    pause() {
        this.audio.pause();
        this.isPlaying = false;
        this._setPlayIcon(false);
    }

    /** Toggle play/pause */
    toggle() {
        this.isPlaying ? this.pause() : this.play();
    }

    /** Next track */
    next() {
        if (this.playlist.length === 0) return;
        this.currentIdx = (this.currentIdx + 1) % this.playlist.length;
        this.loadSong(this.playlist[this.currentIdx]);
    }

    /** Previous track */
    previous() {
        if (this.playlist.length === 0) return;
        // If more than 3s in, restart current; otherwise go back
        if (this.audio.currentTime > 3) {
            this.audio.currentTime = 0;
        } else {
            this.currentIdx = (this.currentIdx - 1 + this.playlist.length) % this.playlist.length;
            this.loadSong(this.playlist[this.currentIdx]);
        }
    }

    /**
     * Seek to a fraction of the track
     * @param {number} fraction 0–1
     */
    seek(fraction) {
        if (!isFinite(this.audio.duration)) return;
        this.audio.currentTime = fraction * this.audio.duration;
    }

    /**
     * Set volume 0–1
     * @param {number} vol
     */
    setVolume(vol) {
        const v = Math.min(1, Math.max(0, vol));
        this.audio.volume = v;
        localStorage.setItem('player_volume', v);
        this._updateVolumeIcon(v);
    }

    /** Toggle mute */
    toggleMute() {
        this.audio.muted = !this.audio.muted;
        this._updateVolumeIcon(this.audio.muted ? 0 : this.audio.volume);
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    _setPlayIcon(playing) {
        if (!this.ui.playIcon) return;
        this.ui.playIcon.className = playing
            ? 'fas fa-pause'
            : 'fas fa-play';
    }

    _setProgress(fraction) {
        if (this.ui.progressFill) {
            this.ui.progressFill.style.width = `${Math.min(100, fraction * 100)}%`;
        }
    }

    _updateVolumeIcon(vol) {
        if (!this.ui.volumeIcon) return;
        if (vol === 0 || this.audio.muted) {
            this.ui.volumeIcon.className = 'fas fa-volume-mute';
        } else if (vol < 0.5) {
            this.ui.volumeIcon.className = 'fas fa-volume-down';
        } else {
            this.ui.volumeIcon.className = 'fas fa-volume-up';
        }
    }

    _updateActiveCard(songId) {
        // Remove playing class from all cards
        document.querySelectorAll('.song-card').forEach(card => {
            card.classList.remove('playing');
        });
        // Add to the active one
        const activeCard = document.querySelector(`.song-card[data-song-id="${songId}"]`);
        if (activeCard) activeCard.classList.add('playing');
    }

    _formatTime(seconds) {
        if (!isFinite(seconds) || seconds < 0) return '0:00';
        const m = Math.floor(seconds / 60);
        const s = Math.floor(seconds % 60);
        return `${m}:${s.toString().padStart(2, '0')}`;
    }

    /** Track play count via API */
    _trackPlayCount() {
        const song = this.playlist[this.currentIdx];
        if (!song || !song.id) return;

        // Only track if played a bit (won't fire on skips)
        const TRACK_AFTER_SECONDS = 10;
        clearTimeout(this._playCountTimer);
        this._playCountTimer = setTimeout(() => {
            if (!this.isPlaying) return;
            fetch('/dj-booking-platform/api/music.php?action=increment_play', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${song.id}`,
            }).catch(() => {}); // Silent fail
        }, TRACK_AFTER_SECONDS * 1000);
    }

    // ── Event Binding ──────────────────────────────────────────────────────────

    _bindEvents() {
        // Audio events
        this.audio.addEventListener('timeupdate', () => {
            if (this.isScrubbing) return;
            const { currentTime, duration } = this.audio;
            if (isFinite(duration) && duration > 0) {
                this._setProgress(currentTime / duration);
            }
            if (this.ui.currentTime) this.ui.currentTime.textContent = this._formatTime(currentTime);
        });

        this.audio.addEventListener('loadedmetadata', () => {
            if (this.ui.duration) {
                this.ui.duration.textContent = this._formatTime(this.audio.duration);
            }
        });

        this.audio.addEventListener('ended', () => {
            this.next();
        });

        this.audio.addEventListener('error', (e) => {
            console.warn('Audio error:', e);
        });

        // Control buttons
        if (this.ui.btnPlayPause) {
            this.ui.btnPlayPause.addEventListener('click', () => this.toggle());
        }
        if (this.ui.btnPrev)  this.ui.btnPrev.addEventListener('click',  () => this.previous());
        if (this.ui.btnNext)  this.ui.btnNext.addEventListener('click',  () => this.next());
        if (this.ui.btnMute)  this.ui.btnMute.addEventListener('click',  () => this.toggleMute());

        // Minimize
        if (this.ui.btnMinimize) {
            this.ui.btnMinimize.addEventListener('click', () => {
                this.playerEl.classList.toggle('minimized');
                const isMin = this.playerEl.classList.contains('minimized');
                this.ui.btnMinimize.setAttribute('aria-label', isMin ? 'Expand player' : 'Minimize player');
            });
        }

        // Volume slider
        if (this.ui.volumeSlider) {
            this.ui.volumeSlider.addEventListener('input', (e) => {
                this.setVolume(parseFloat(e.target.value));
            });
        }

        // Progress bar scrub (click)
        if (this.ui.progressBar) {
            this.ui.progressBar.addEventListener('click', (e) => {
                const rect     = this.ui.progressBar.getBoundingClientRect();
                const fraction = (e.clientX - rect.left) / rect.width;
                this.seek(fraction);
            });

            // Drag scrub
            let scrubbing = false;
            this.ui.progressBar.addEventListener('mousedown', () => { scrubbing = true; this.isScrubbing = true; });
            document.addEventListener('mousemove', (e) => {
                if (!scrubbing) return;
                const rect     = this.ui.progressBar.getBoundingClientRect();
                const fraction = Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width));
                this._setProgress(fraction);
            });
            document.addEventListener('mouseup', (e) => {
                if (!scrubbing) return;
                scrubbing = false;
                this.isScrubbing = false;
                const rect     = this.ui.progressBar.getBoundingClientRect();
                const fraction = Math.min(1, Math.max(0, (e.clientX - rect.left) / rect.width));
                this.seek(fraction);
            });
        }

        // Play button clicks on song cards
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.play-overlay');
            if (!btn) return;

            e.preventDefault();

            const songData = {
                id:     parseInt(btn.dataset.songId,    10),
                title:  btn.dataset.songTitle   || 'Unknown',
                artist: btn.dataset.songArtist  || 'Unknown',
                file:   btn.dataset.songFile    || '',
                cover:  btn.dataset.songCover   || '',
            };

            // Rebuild playlist
            this.buildPlaylistFromPage();

            // Find index
            const idx = this.playlist.findIndex(s => s.id === songData.id);
            if (idx >= 0) {
                if (this.currentIdx === idx && this.isPlaying) {
                    this.pause();
                } else {
                    this.playAtIndex(idx);
                }
            } else {
                // Song not in playlist — play it directly
                this.playlist.push(songData);
                this.currentIdx = this.playlist.length - 1;
                this.loadSong(songData);
            }
        });
    }

    _bindKeyboard() {
        document.addEventListener('keydown', (e) => {
            // Only handle if not focused on an input
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;

            if (e.code === 'Space') {
                e.preventDefault();
                this.toggle();
            } else if (e.code === 'ArrowRight') {
                this.next();
            } else if (e.code === 'ArrowLeft') {
                this.previous();
            }
        });
    }
}

// ── Initialize ─────────────────────────────────────────────────────────────────

const playerEl = document.getElementById('music-player-bar');
if (playerEl) {
    window.musicPlayer = new MusicPlayer(playerEl);
    window.musicPlayer.buildPlaylistFromPage();
}
