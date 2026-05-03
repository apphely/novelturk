/**
 * Novel Türk - Frontend JavaScript
 * Slider, theme/font, TTS, settings, keyboard nav, status filter, copy protection
 */
(function () {
    'use strict';

    // ============================================
    // INKR-Style Carousel Slider with Center Focus
    // ============================================
    // ============================================
    // INKR Peek Slider v11 (Infinite)
    // ============================================
    var sliderTrack = document.getElementById('home-slider-track');
    var sliderOuter = document.getElementById('home-slider');
    if (sliderTrack && sliderOuter) {
        var prevBtn = document.getElementById('home-slider-prev');
        var nextBtn = document.getElementById('home-slider-next');
        var dotsContainer = document.getElementById('home-slider-dots');
        var originalGroups = Array.from(sliderTrack.querySelectorAll('.group'));
        var totalReal = originalGroups.length;
        // Clone and Append/Prepend (Multiple sets for wide screens)
        for (var i = 0; i < 2; i++) {
            originalGroups.forEach(function (g) {
                sliderTrack.insertBefore(g.cloneNode(true), sliderTrack.firstChild);
                sliderTrack.appendChild(g.cloneNode(true));
            });
        }
        var cloneCount = totalReal * 2;
        var isTransitioning = false;

        var currentIndex = cloneCount; // Start at first original

        function getGroupWidth() {
            var g = sliderTrack.querySelector('.group');
            return g ? g.offsetWidth + 5 : 430; // 5 is gap
        }

        function positionTrack(idx, animate) {
            sliderTrack.style.transition = animate ? 'transform .44s cubic-bezier(.25,.8,.25,1)' : 'none';
            var gw = getGroupWidth();
            sliderTrack.style.transform = 'translateX(-' + (idx * gw) + 'px)';
        }

        function updateDots() {
            if (!dotsContainer) return;
            var ri = ((currentIndex - cloneCount) % totalReal + totalReal) % totalReal;
            var dots = dotsContainer.querySelectorAll('.dot');
            dots.forEach(function (d, i) { d.classList.toggle('active', i === ri); });
        }

        function nav(dir) {
            if (isTransitioning) return;
            isTransitioning = true;
            currentIndex += dir;
            positionTrack(currentIndex, true);
            updateDots();
        }

        sliderTrack.addEventListener('transitionend', function () {
            if (currentIndex >= cloneCount + totalReal) {
                currentIndex = cloneCount;
                positionTrack(currentIndex, false);
            } else if (currentIndex < cloneCount) {
                currentIndex = cloneCount + totalReal - 1;
                positionTrack(currentIndex, false);
            }
            isTransitioning = false;
            updateDots();
        });

        // Initialize Dots
        if (dotsContainer) {
            dotsContainer.innerHTML = '';
            for (var i = 0; i < totalReal; i++) {
                (function (idx) {
                    var dot = document.createElement('div');
                    dot.className = 'dot' + (idx === 0 ? ' active' : '');
                    dot.addEventListener('click', function () { if (!isTransitioning) { currentIndex = cloneCount + idx; positionTrack(currentIndex, true); updateDots(); } });
                    dotsContainer.appendChild(dot);
                })(i);
            }
        }

        if (prevBtn) prevBtn.addEventListener('click', function () { nav(-1); });
        if (nextBtn) nextBtn.addEventListener('click', function () { nav(1); });

        // Swipe
        var sx = 0, moved = false;
        sliderOuter.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; moved = false; }, { passive: true });
        sliderOuter.addEventListener('touchmove', function (e) { if (Math.abs(e.touches[0].clientX - sx) > 10) moved = true; }, { passive: true });
        sliderOuter.addEventListener('touchend', function (e) {
            if (!moved) return;
            var dx = e.changedTouches[0].clientX - sx;
            if (Math.abs(dx) > 50) nav(dx < 0 ? 1 : -1);
        });

        // Initial Position
        setTimeout(function () { positionTrack(currentIndex, false); updateDots(); }, 100);

        window.addEventListener('resize', function () {
            positionTrack(currentIndex, false);
        });
    }

    // ============================================
    // Status Filter Tabs (Homepage)
    // ============================================
    var statusTabs = document.querySelectorAll('.status-tab');
    if (statusTabs.length > 0) {
        statusTabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var target = this.dataset.status;
                statusTabs.forEach(function (t) { t.classList.remove('active'); });
                this.classList.add('active');
                document.querySelectorAll('.novel-card[data-status]').forEach(function (card) {
                    if (target === 'all' || card.dataset.status === target) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }

    // ============================================
    // Popular Tabs
    // ============================================
    document.querySelectorAll('.popular-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.popular-tab').forEach(function (t) { t.classList.remove('active'); });
            this.classList.add('active');
            document.querySelectorAll('.popular-tab-content').forEach(function (c) { c.classList.remove('active'); });
            var content = document.getElementById('popular-' + this.dataset.tab);
            if (content) content.classList.add('active');
        });
    });

    // ============================================
    // Follow System (LocalStorage)
    // ============================================
    var webnovelFollow = {
        key: 'webnovel_follows',
        items: {},
        init: function () {
            try {
                var parsed = JSON.parse(localStorage.getItem(this.key) || '{}');
                // Cleanup "undefined" junk data
                for (var key in parsed) {
                    if (parsed[key].title === "undefined" || !parsed[key].title || parsed[key].thumb === "undefined") {
                        delete parsed[key];
                    }
                }
                this.items = parsed;
            } catch (e) {
                this.items = {};
            }
            this.setupListeners();
            this.renderBtn();
            this.renderList();
            this.updateNotifUI();

            // Periodically check for updates (only if follows exist)
            if (Object.keys(this.items).length > 0) {
                this.checkUpdates();
            }
        },
        setupListeners: function () {
            var _this = this;

            // Notification Dropdown Toggle
            var bell = document.getElementById('notif-bell');
            var dropdown = document.getElementById('notif-dropdown');
            if (bell && dropdown) {
                bell.onclick = function (e) {
                    e.stopPropagation();
                    dropdown.classList.toggle('active');
                };
                document.addEventListener('click', function (e) {
                    if (!dropdown.contains(e.target) && e.target !== bell) {
                        dropdown.classList.remove('active');
                    }
                });
            }

            // Clear All Notifications
            var clearAll = document.getElementById('notif-clear-all');
            if (clearAll) {
                clearAll.onclick = function () { _this.markAllAsRead(); };
            }

            var btn = document.getElementById('btn-follow-novel');
            if (btn) {
                btn.onclick = function () {
                    var id = btn.dataset.id;
                    var title = btn.dataset.title;
                    var thumb = btn.dataset.thumb;
                    var lastCh = btn.dataset.lastCh;
                    _this.toggle(id, title, thumb, lastCh);
                };
            }

            var clearBtn = document.getElementById('clear-all-follows');
            if (clearBtn) {
                clearBtn.onclick = function () {
                    if (confirm('Bütün takip ettiğiniz serileri silmek istediğinize emin misiniz?')) {
                        localStorage.removeItem(_this.key);
                        _this.items = {};
                        _this.renderList();
                        _this.updateNotifUI();
                        alert('Bütün bookmarklar temizlendi.');
                    }
                };
            }

            var exportBtn = document.getElementById('export-follows');
            if (exportBtn) {
                exportBtn.onclick = function () { _this.exportData(); };
            }

            var importBtn = document.getElementById('import-follows-btn');
            var importFile = document.getElementById('import-follows-file');
            if (importBtn && importFile) {
                importBtn.onclick = function () { importFile.click(); };
                importFile.onchange = function (e) {
                    var file = e.target.files[0];
                    if (!file) return;
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        _this.importData(e.target.result);
                    };
                    reader.readAsText(file);
                };
            }

            // Reader Save Button (Add to Library)
            var saveBtn = document.getElementById('btn-save');
            if (saveBtn) {
                // Update initial state
                var nid = saveBtn.dataset.novelId;
                if (_this.items[nid]) saveBtn.classList.add('active');

                saveBtn.onclick = function () {
                    var id = saveBtn.dataset.novelId;
                    var title = saveBtn.dataset.novelTitle;
                    var thumb = saveBtn.dataset.novelThumb;
                    var lastCh = saveBtn.dataset.lastCh;
                    _this.toggle(id, title, thumb, lastCh);

                    // Toggle active class visually
                    if (_this.items[id]) saveBtn.classList.add('active');
                    else saveBtn.classList.remove('active');
                };
            }
        },
        toggle: function (id, title, thumb, lastCh) {
            if (this.items[id]) {
                delete this.items[id];
                this.showToast('Takibi bıraktınız: ' + title);
            } else {
                this.items[id] = {
                    id: id,
                    title: title,
                    thumb: thumb,
                    last_seen_ch: lastCh,
                    has_update: false,
                    latest_ch_title: ''
                };
                this.showToast('Takibe alındı: ' + title);
            }
            this.save();
            this.renderBtn();
            this.updateNotifUI();
        },
        save: function () {
            localStorage.setItem(this.key, JSON.stringify(this.items));
        },
        renderBtn: function () {
            var btn = document.getElementById('btn-follow-novel');
            if (!btn) return;
            var id = btn.dataset.id;
            var isFollowing = !!this.items[id];
            btn.classList.toggle('active', isFollowing);

            var span = btn.querySelector('span');
            if (span) span.innerText = isFollowing ? 'Kütüphaneden Kaldır' : 'Kütüphaneye Ekle';

            var svg = btn.querySelector('svg');
            if (svg) svg.style.fill = isFollowing ? 'currentColor' : 'none';
        },
        renderList: function () {
            var grid = document.getElementById('followed-series-grid');
            var emptyMsg = document.getElementById('no-follows-msg');
            if (!grid) return;

            grid.innerHTML = '';
            var ids = Object.keys(this.items);
            if (ids.length === 0) {
                if (emptyMsg) emptyMsg.style.display = 'block';
                grid.style.display = 'none';
                return;
            }

            if (emptyMsg) emptyMsg.style.display = 'none';
            grid.style.display = 'grid';

            ids.forEach(function (id) {
                var item = webnovelFollow.items[id];
                var card = document.createElement('div');
                card.className = 'novel-card-premium';
                card.innerHTML = `
                    <div class="novel-card-cover" onclick="location.href='${item.url || '/novel/' + id}'">
                        <img src="${item.thumb}" alt="${item.title}">
                        ${item.has_update ? '<div class="update-badge">YENİ</div>' : ''}
                    </div>
                    <div class="novel-card-info">
                        <h3 class="novel-title" onclick="location.href='${item.url || '/novel/' + id}'">${item.title}</h3>
                        <p class="chapter-info">${item.latest_ch_title || 'Son okunan: ' + (item.last_seen_ch || 'Bilinmiyor')}</p>
                        <button class="btn-unfollow" onclick="webnovelFollow.toggle('${id}', '${item.title.replace(/'/g, "\\'")}')">
                            Takibi Bırak
                        </button>
                    </div>
                `;
                grid.appendChild(card);
            });
        },
        updateNotifUI: function () {
            var badge = document.getElementById('notif-badge');
            var list = document.getElementById('notif-list');
            if (!list) return;

            var unreadCount = 0;
            var html = '';

            for (var id in this.items) {
                var item = this.items[id];
                if (item.has_update) {
                    unreadCount++;
                    html += `
                        <div class="notif-item" onclick="webnovelFollow.markAsRead('${id}', true)">
                            <img src="${item.thumb}" alt="">
                            <div class="notif-content">
                                <span class="notif-title">${item.title}</span>
                                <span class="notif-desc">Yeni Bölüm: ${item.latest_ch_title || 'Yeni İçerik'}</span>
                            </div>
                        </div>
                    `;
                }
            }

            list.innerHTML = html || '<p class="empty-notif">Yeni bildirim yok.</p>';
            if (badge) badge.style.display = unreadCount > 0 ? 'block' : 'none';

            // Also update the generic nav badge if it exists
            var navBadge = document.getElementById('follow-nav-badge');
            if (navBadge) {
                navBadge.innerText = unreadCount;
                navBadge.style.display = unreadCount > 0 ? 'block' : 'none';
            }
        },
        markAsRead: function (id, redirectToNovel) {
            var item = this.items[id];
            if (item && item.has_update) {
                item.has_update = false;
                if (item.temp_latest_id) {
                    item.last_seen_ch = item.temp_latest_id;
                    delete item.temp_latest_id;
                }
                this.save();
                this.updateNotifUI();
                this.renderList();
            }
            if (redirectToNovel) {
                location.href = item.url || ('/novel/' + id);
            }
        },
        markAllAsRead: function () {
            for (var id in this.items) {
                var item = this.items[id];
                if (item.has_update) {
                    item.has_update = false;
                    if (item.temp_latest_id) {
                        item.last_seen_ch = item.temp_latest_id;
                        delete item.temp_latest_id;
                    }
                }
            }
            this.save();
            this.updateNotifUI();
            this.renderList();
            this.showToast('Tüm bildirimler temizlendi.');
        },
        checkUpdates: function () {
            var _this = this;
            var ids = Object.keys(this.items);
            if (ids.length === 0) return;

            // Use AJAX localized data from functions.php (webnovelReader)
            var ajaxTarget = (typeof webnovelReader !== 'undefined') ? webnovelReader.ajaxUrl : '/wp-admin/admin-ajax.php';

            var formData = new FormData();
            formData.append('action', 'webnovel_check_updates');
            ids.forEach(function (id) { formData.append('novel_ids[]', id); });

            fetch(ajaxTarget, { method: 'POST', body: formData })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        var updatesFound = false;
                        for (var id in data.data) {
                            var serverData = data.data[id];
                            var localItem = _this.items[id];
                            if (localItem && serverData.latest_id != localItem.last_seen_ch) {
                                if (!localItem.has_update) {
                                    _this.showToast(localItem.title + ' serisine yeni bölüm geldi!');
                                    localItem.has_update = true;
                                    updatesFound = true;
                                }
                                localItem.url = serverData.url;
                                localItem.latest_ch_title = serverData.chapter_title;
                                localItem.temp_latest_id = serverData.latest_id; // Keep until read
                            }
                        }
                        if (updatesFound) {
                            _this.save();
                            _this.updateNotifUI();
                            _this.renderList();
                        }
                    }
                })
                .catch(function (err) { console.error('Update check failed:', err); });
        },
        showToast: function (msg) {
            var container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'toast-container';
                document.body.appendChild(container);
            }
            var toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = '<span>🔔</span> ' + msg;
            container.appendChild(toast);
            setTimeout(function () {
                toast.style.opacity = '0';
                setTimeout(function () { toast.remove(); }, 300);
            }, 5000);
        },
        exportData: function () {
            var data = JSON.stringify(this.items);
            var blob = new Blob([data], { type: 'application/json' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = 'novel_takip_listesi.json';
            a.click();
        },
        importData: function (json) {
            try {
                var imported = JSON.parse(json);
                this.items = Object.assign(this.items, imported);
                this.save();
                this.renderList();
                this.updateNotifUI(); // Updated badge name in code
                alert('Liste başarıyla içe aktarıldı!');
            } catch (e) {
                alert('Hatalı dosya formatı!');
            }
        }
    };

    // ============================================
    // Reading History (LocalStorage)
    // ============================================
    var webnovelHistory = {
        key: 'webnovel_history',
        maxItems: 10,
        items: [],
        init: function () {
            try {
                this.items = JSON.parse(localStorage.getItem(this.key) || '[]');
            } catch (e) {
                this.items = [];
            }
            this.renderDropdown();

            // Auto-save current chapter if on chapter page
            var chapterData = document.getElementById('reader-text');
            if (chapterData) {
                var novelTitle = document.querySelector('h1')?.innerText || 'Bölüm';
                this.add({
                    id: chapterData.dataset.chapterId,
                    title: novelTitle,
                    url: window.location.pathname,
                    time: new Date().getTime()
                });
            }
        },
        add: function (item) {
            // Remove existing same ID to move to top
            this.items = this.items.filter(function (i) { return i.id != item.id; });
            this.items.unshift(item);
            if (this.items.length > this.maxItems) this.items.pop();
            this.save();
            this.renderDropdown();
        },
        save: function () {
            localStorage.setItem(this.key, JSON.stringify(this.items));
        },
        renderDropdown: function () {
            var list = document.getElementById('history-list');
            if (!list) return;
            if (this.items.length === 0) {
                list.innerHTML = '<p class="empty-msg">Henüz geçmiş yok.</p>';
                return;
            }
            var html = '';
            this.items.forEach(function (item) {
                html += `
                    <a href="${item.url}" class="dropdown-item">
                        <div class="item-info">
                            <span class="item-title">${item.title}</span>
                            <span class="item-meta">Son okunan</span>
                        </div>
                    </a>
                `;
            });
            list.innerHTML = html;
        }
    };

    // ============================================
    // Dropdown Toggles
    // ============================================
    function setupDropdowns() {
        var btns = ['kitaplik-btn', 'history-btn', 'header-dropdown-btn'];
        btns.forEach(function (id) {
            var btn = document.getElementById(id);
            var dropdownId = id.replace('-btn', '-dropdown');
            // Fix for header-dropdown-btn which should map to header-dropdown
            if (id === 'header-dropdown-btn') dropdownId = 'header-dropdown';

            var dropdown = document.getElementById(dropdownId);
            if (btn && dropdown) {
                btn.onclick = function (e) {
                    e.stopPropagation();
                    var isActive = dropdown.classList.contains('active');
                    document.querySelectorAll('.header-dropdown').forEach(function (d) { d.classList.remove('active'); });
                    if (!isActive) dropdown.classList.add('active');
                };
            }
        });
        document.addEventListener('click', function () {
            document.querySelectorAll('.header-dropdown').forEach(function (d) { d.classList.remove('active'); });
        });
    }

    // ============================================
    // Reading Progress & Scroll Controls
    // ============================================
    function setupReadingProgress() {
        var progressRing = document.getElementById('reader-progress-ring');
        var progressText = document.getElementById('reader-progress-text');
        if (progressRing) {
            var circumference = progressRing.getTotalLength();
            progressRing.style.strokeDasharray = circumference + ' ' + circumference;
            progressRing.style.strokeDashoffset = circumference;

            var ringZero = document.getElementById('ring-zero-state');
            var ringBg = document.querySelector('.progress-ring-bg');
            var ringSpike = document.getElementById('ring-spike-fill');
            var _arrowMode = false;

            function setArrowMode(on) {
                if (on === _arrowMode) return;
                _arrowMode = on;
                if (ringZero) ringZero.style.opacity = on ? '0' : '1';
                if (ringBg) ringBg.style.opacity = on ? '1' : '0';
                if (ringSpike) ringSpike.style.opacity = on ? '1' : '0';
            }
            setArrowMode(false);

            window.addEventListener('scroll', function () {
                var scrolled = 0;
                var wraps = document.querySelectorAll('.reader-text-wrap');
                for (var i = 0; i < wraps.length; i++) {
                    var r = wraps[i].getBoundingClientRect();
                    if (r.bottom > 0) {
                        var parent = wraps[i].closest('.infinite-chapter-block') || wraps[i].closest('.nt-card-body');
                        var titleEl = parent ? parent.querySelector('h1, h2') : null;
                        if (titleEl) {
                            var viewH = window.innerHeight;
                            var scrollStart = titleEl.getBoundingClientRect().top + window.scrollY;
                            var wrapAbsTop = r.top + window.scrollY;
                            var scrollEnd = wrapAbsTop + wraps[i].offsetHeight * 1.06 - viewH;
                            if (scrollEnd > scrollStart) {
                                scrolled = Math.min(100, Math.max(0, (window.scrollY - scrollStart) / (scrollEnd - scrollStart) * 100));
                            }
                        }
                        break;
                    }
                }
                setArrowMode(scrolled > 0);
                if (progressText) progressText.innerText = Math.round(scrolled) + '%';
                progressRing.style.strokeDashoffset = circumference - (scrolled / 100) * circumference;
            });
        }
    }

    // ============================================
    // Mobile Menu Toggle
    // ============================================

    // ============================================
    // Theme & Settings System (Advanced)
    // ============================================
    var KEYS = {
        theme: 'webnovel-theme',
        font: 'webnovel-font-size',
        family: 'webnovel-font-family',
        align: 'webnovel-text-align',
        weight: 'webnovel-font-weight',
        style: 'webnovel-font-style',
        height: 'webnovel-line-height',
        width: 'webnovel-max-width'
    };

    function loadPreferences() {
        applyTheme(localStorage.getItem(KEYS.theme) || 'dark');
        applyFontSize(localStorage.getItem(KEYS.font) || '18px');
        applyFontFamily(localStorage.getItem(KEYS.family) || 'inherit');
        applyTextAlign(localStorage.getItem(KEYS.align) || 'left');
        applyFontWeight(localStorage.getItem(KEYS.weight) || 'normal');
        applyFontStyle(localStorage.getItem(KEYS.style) || 'normal');
        applyLineHeight(localStorage.getItem(KEYS.height) || '1.8');
        applyMaxWidth(localStorage.getItem(KEYS.width) || '100');
    }

    function syncSelect(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = val;
    }

    // --- Specific Applicators ---
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem(KEYS.theme, theme);
        document.body.className = document.body.className.replace(/\btheme-\S+/g, '') + ' theme-' + theme;
        syncSelect('set-theme-mode', theme);
    }

    function applyFontSize(val) {
        document.querySelectorAll('.reader-text').forEach(function(rt) { rt.style.fontSize = val; });
        localStorage.setItem(KEYS.font, val);
        syncSelect('set-font-size', val);
    }

    function applyFontFamily(val) {
        document.querySelectorAll('.reader-text').forEach(function(rt) { rt.style.fontFamily = val; });
        localStorage.setItem(KEYS.family, val);
        syncSelect('set-font-family', val);
    }

    function applyTextAlign(val) {
        document.querySelectorAll('.reader-text').forEach(function(rt) { rt.style.textAlign = val; });
        localStorage.setItem(KEYS.align, val);
        syncSelect('set-text-align', val);
    }

    function applyFontWeight(val) {
        document.querySelectorAll('.reader-text').forEach(function(rt) { rt.style.fontWeight = val; });
        localStorage.setItem(KEYS.weight, val);
        syncSelect('set-font-weight', val);
    }

    function applyFontStyle(val) {
        document.querySelectorAll('.reader-text').forEach(function(rt) { rt.style.fontStyle = val; });
        localStorage.setItem(KEYS.style, val);
        syncSelect('set-font-style', val);
    }

    function applyLineHeight(val) {
        document.querySelectorAll('.reader-text').forEach(function(rt) { rt.style.lineHeight = val; });
        localStorage.setItem(KEYS.height, val);
        syncSelect('set-line-height', val);
    }

    function applyMaxWidth(val) {
        var rc = document.getElementById('reader-container');
        if (rc) rc.style.maxWidth = val == 100 ? 'none' : (val + '%');
        if (rc && val != 100) rc.style.margin = '0 auto';
        localStorage.setItem(KEYS.width, val);
        var slider = document.getElementById('set-max-width');
        if (slider) slider.value = val;
    }

    // Event Listeners for new selects
    ['set-font-size', 'set-font-family', 'set-text-align', 'set-font-weight', 'set-font-style', 'set-line-height'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function () {
                var v = this.value;
                if (id == 'set-font-size') applyFontSize(v);
                if (id == 'set-font-family') applyFontFamily(v);
                if (id == 'set-text-align') applyTextAlign(v);
                if (id == 'set-font-weight') applyFontWeight(v);
                if (id == 'set-font-style') applyFontStyle(v);
                if (id == 'set-line-height') applyLineHeight(v);
            });
        }
    });

    var mwSlider = document.getElementById('set-max-width');
    if (mwSlider) {
        mwSlider.addEventListener('input', function () { applyMaxWidth(this.value); });
    }

    // Save Button Feedback
    var settingsSave = document.getElementById('settings-save');
    if (settingsSave) {
        settingsSave.addEventListener('click', function () {
            var btn = this;
            btn.textContent = '✓ Kaydedildi!';
            btn.style.background = '#10b981';
            setTimeout(function () {
                btn.textContent = 'Ayarları Uygula ve Kaydet';
                btn.style.background = '#1d4ed8';
                document.getElementById('settings-close').click();
            }, 1000);
        });
    }

    // --- Settings Panel Toggle ---
    var settingsBtn = document.getElementById('btn-settings');
    var settingsPanel = document.getElementById('settings-panel');
    var settingsClose = document.getElementById('settings-close');
    var settingsOverlay = document.getElementById('settings-overlay');

    if (settingsBtn && settingsPanel) {
        settingsBtn.addEventListener('click', function () {
            settingsPanel.style.display = 'block';
            if (settingsOverlay) settingsOverlay.classList.add('active');
        });
    }

    if (settingsClose && settingsPanel) {
        settingsClose.addEventListener('click', function () {
            settingsPanel.style.display = 'none';
            if (settingsOverlay) settingsOverlay.classList.remove('active');
        });
    }

    if (settingsOverlay && settingsPanel) {
        settingsOverlay.addEventListener('click', function () {
            settingsPanel.style.display = 'none';
            settingsOverlay.classList.remove('active');
        });
    }


    // ============================================
    // Keyboard Navigation (Reader)
    // ============================================
    document.addEventListener('keydown', function (e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return;
        var prev = document.querySelector('[rel="prev"]');
        var next = document.querySelector('[rel="next"]');
        if (e.key === 'ArrowLeft' && prev && prev.href) { e.preventDefault(); window.location.href = prev.href; }
        if (e.key === 'ArrowRight' && next && next.href) { e.preventDefault(); window.location.href = next.href; }
    });

    // ============================================
    // Scroll to Top
    // ============================================
    var scrollTopBtn = document.getElementById('scroll-top');
    if (scrollTopBtn) {
        window.addEventListener('scroll', function () {
            scrollTopBtn.classList.toggle('visible', window.scrollY > 400);
        });
        scrollTopBtn.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
    }

    // ============================================
    // Chapter Sort Toggle
    // ============================================
    var sortBtn = document.getElementById('chapter-sort-btn');
    var chapterListEl = document.getElementById('chapter-list');
    if (sortBtn && chapterListEl) {
        sortBtn.addEventListener('click', function () {
            var currentOrder = this.dataset.order || 'asc';
            var newOrder = currentOrder === 'asc' ? 'desc' : 'asc';
            this.dataset.order = newOrder;
            this.textContent = newOrder === 'asc' ? '↕ Eski→Yeni' : '↕ Yeni→Eski';
            var items = Array.from(chapterListEl.children);
            items.sort(function (a, b) {
                return newOrder === 'asc' ? (parseInt(a.dataset.number) || 0) - (parseInt(b.dataset.number) || 0) : (parseInt(b.dataset.number) || 0) - (parseInt(a.dataset.number) || 0);
            });
            items.forEach(function (item) { chapterListEl.appendChild(item); });
        });
    }

    var mobileToggle = document.getElementById('mobile-menu-toggle');
    var mobileMenu = document.getElementById('mobile-menu');
    if (mobileToggle && mobileMenu) {
        mobileToggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            mobileMenu.classList.toggle('active');
            mobileToggle.innerHTML = mobileMenu.classList.contains('active') ? '✕' : '☰';
        });

        document.addEventListener('click', function (e) {
            if (mobileMenu.classList.contains('active') && !mobileMenu.contains(e.target) && e.target !== mobileToggle) {
                mobileMenu.classList.remove('active');
                mobileToggle.innerHTML = '☰';
            }
        });
    }

    // ============================================
    // Paragraph Copy Icons
    // ============================================
    var currentParaIcon = null;
    var paraNotif = null;

    function showParaCopyToast(success) {
        if (!paraNotif) {
            paraNotif = document.createElement('div');
            paraNotif.style.cssText = 'position:fixed;bottom:15%;left:50%;transform:translateX(-50%);color:#fff;padding:12px 24px;border-radius:10px;display:none;z-index:9999;font-size:15px;text-align:center;box-shadow:0 4px 10px rgba(0,0,0,.2);opacity:0;transition:opacity .5s ease;pointer-events:none;';
            document.body.appendChild(paraNotif);
        }
        paraNotif.textContent = success ? 'Paragraf yorum için kopyalandı.' : 'Kopyalama başarısız oldu.';
        paraNotif.style.background = success ? '#4CAF50' : '#f44336';
        paraNotif.style.display = 'block';
        paraNotif.style.opacity = '1';
        setTimeout(function () {
            paraNotif.style.opacity = '0';
            setTimeout(function () { paraNotif.style.display = 'none'; }, 500);
        }, success ? 2000 : 3000);
    }

    function setupParagraphCopyIcons(rt) {
        if (!rt) return;
        rt.querySelectorAll('p').forEach(function (p) {
            if (p.textContent.trim().length < 2) return;
            var icon = document.createElement('span');
            icon.className = 'para-copy-icon';
            icon.title = 'Paragrafı kopyala';
            icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" style="display:inline;vertical-align:middle"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21a9 9 0 1 0-9-9c0 1.488.36 2.89 1 4.127L3 21l4.873-1c1.236.639 2.64 1 4.127 1m0-11.999v6m-3-3h6" /></svg>';
            icon.style.cssText = 'cursor:pointer;margin-left:8px;display:none;opacity:0;transition:opacity .5s ease;color:var(--text-main);vertical-align:middle;line-height:1;';
            p._paraIcon = icon;
            (function (para, ic) {
                ic.addEventListener('click', function (e) {
                    e.stopPropagation();
                    var text = '>' + para.textContent.trim() + '<\n';
                    navigator.clipboard.writeText(text).then(function () {
                        showParaCopyToast(true);
                    }).catch(function () {
                        showParaCopyToast(false);
                    });
                });
            })(p, icon);
            p.appendChild(icon);
        });
    }

    // ============================================
    // Dynamic Content Loading
    // ============================================
    function fetchChapterContent() {
        var readerText = document.getElementById('reader-text');
        if (!readerText || !readerText.dataset.chapterId) return;

        if (typeof webnovelReader === 'undefined') return;

        var ajaxTarget = webnovelReader.ajaxUrl;
        var nonce = webnovelReader.nonce;
        var chapterId = readerText.dataset.chapterId;

        var formData = new FormData();
        formData.append('action', 'webnovel_get_chapter');
        formData.append('chapter_id', chapterId);
        formData.append('nonce', nonce);

        fetch(ajaxTarget, { method: 'POST', body: formData })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    readerText.innerHTML = b64DecodeUnicode(data.data.content);
                    loadPreferences();
                    setupParagraphCopyIcons(readerText);
                    readerText.querySelectorAll('.para-copy-icon').forEach(function (ic) {
                        ic.style.display = 'inline';
                        setTimeout(function () { ic.style.opacity = '1'; }, 10);
                    });
                    setTimeout(function () {
                        readerText.querySelectorAll('.para-copy-icon').forEach(function (ic) {
                            ic.style.opacity = '0';
                            setTimeout(function () { ic.style.display = 'none'; }, 500);
                        });
                    }, 2000);
                }
            })
            .catch(function (err) { console.error('Fetch error:', err); });
    }

    // --- Base64 UTF-8 Helper ---
    function b64DecodeUnicode(str) {
        return decodeURIComponent(atob(str).split('').map(function (c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
    }

    // ============================================
    // Infinite Scroll Between Chapters
    // ============================================
    var infiniteScroll = {
        loading: false,
        loadedIds: {},

        init: function () {
            var firstBlock = document.getElementById('reader-text');
            if (!firstBlock) return;
            this.loadedIds[firstBlock.dataset.chapterId] = true;
            this.observe();
        },

        observe: function () {
            var _this = this;
            var sentinel = document.getElementById('infinite-scroll-sentinel');
            if (!sentinel) return;

            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting && !_this.loading) {
                        _this.loadNext();
                    }
                });
            }, { rootMargin: '0px 0px 400px 0px' });

            observer.observe(sentinel);
        },

        getLastBlock: function () {
            var blocks = document.querySelectorAll('[data-chapter-id]');
            return blocks[blocks.length - 1] || null;
        },

        loadNext: function () {
            var _this = this;
            var lastBlock = this.getLastBlock();
            if (!lastBlock) return;

            var nextId = lastBlock.dataset.nextId;
            if (!nextId || this.loadedIds[nextId]) return;

            if (typeof webnovelReader === 'undefined') return;

            this.loading = true;
            var loader = document.getElementById('infinite-scroll-loader');
            if (loader) loader.style.display = 'block';

            var formData = new FormData();
            formData.append('action', 'webnovel_get_chapter');
            formData.append('chapter_id', nextId);
            formData.append('nonce', webnovelReader.nonce);

            fetch(webnovelReader.ajaxUrl, { method: 'POST', body: formData })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (loader) loader.style.display = 'none';
                    _this.loading = false;

                    if (!data.success) return;

                    _this.loadedIds[nextId] = true;
                    _this.appendChapter(nextId, data.data);
                })
                .catch(function () {
                    if (loader) loader.style.display = 'none';
                    _this.loading = false;
                });
        },

        appendChapter: function (chapterId, chData) {
            var container = document.getElementById('infinite-chapters-container');
            if (!container) return;

            // Separator
            var sep = document.createElement('div');
            sep.className = 'chapter-separator';
            sep.textContent = chData.volume ? 'Cilt ' + chData.volume + ' · Bölüm ' + chData.number : 'Bölüm ' + chData.number;
            container.appendChild(sep);

            // New chapter card
            var card = document.createElement('div');
            card.className = 'nt-card infinite-chapter-block';
            card.style.overflow = 'hidden';

            var titleHtml = '';
            if (chData.volume) {
                titleHtml += '<p>Cilt ' + chData.volume + '</p>';
            }
            titleHtml += '<h2>' + chData.title + '</h2>';

            var nextId  = chData.next ? chData.next.id  : '';
            var nextUrl = chData.next ? chData.next.url : '';
            var prevId  = chData.prev ? chData.prev.id  : '';
            var prevUrl = chData.prev ? chData.prev.url : '';

            card.innerHTML =
                '<div class="chapter-block-title">' + titleHtml + '</div>' +
                '<div class="nt-card-body" style="padding:24px 24px 32px;">' +
                    '<div class="reader-text-wrap" style="padding:20px 0;">' +
                        '<div class="reader-text" data-chapter-id="' + chapterId + '"' +
                            ' data-next-id="' + nextId + '"' +
                            ' data-next-url="' + nextUrl + '"' +
                            ' data-prev-id="' + prevId + '"' +
                            ' data-prev-url="' + prevUrl + '"' +
                            ' data-chapter-url="' + (chData.url || '') + '"' +
                            ' data-chapter-number="' + (chData.number || '') + '"' +
                            ' data-chapter-title="' + (function(t){var i=t?t.indexOf(' - '):-1;return(i!==-1?t.substring(i+3):t||'').replace(/"/g,'&quot;');})(chData.title||'') + '"' +
                            ' data-novel-title="' + (novelTitleGlobal||'').replace(/"/g,'&quot;') + '"' +
                            '>' +
                            b64DecodeUnicode(chData.content) +
                        '</div>' +
                    '</div>' +
                '</div>';

            container.appendChild(card);

            // Apply reading preferences to new block
            var newBlock = card.querySelector('.reader-text');
            if (newBlock) {
                var KEYS2 = { font: 'webnovel-font-size', family: 'webnovel-font-family', align: 'webnovel-text-align', weight: 'webnovel-font-weight', style: 'webnovel-font-style', height: 'webnovel-line-height' };
                newBlock.style.fontSize   = localStorage.getItem(KEYS2.font)   || '18px';
                newBlock.style.fontFamily = localStorage.getItem(KEYS2.family) || 'inherit';
                newBlock.style.textAlign  = localStorage.getItem(KEYS2.align)  || 'left';
                newBlock.style.fontWeight = localStorage.getItem(KEYS2.weight) || 'normal';
                newBlock.style.fontStyle  = localStorage.getItem(KEYS2.style)  || 'normal';
                newBlock.style.lineHeight = localStorage.getItem(KEYS2.height) || '1.8';
                newBlock.style.color      = 'var(--text-main)';
            }

            setupParagraphCopyIcons(card.querySelector('.reader-text'));
        }
    };

    // ============================================
    // Chapter Visibility — URL + Comments Sync
    // ============================================
    var currentChapterId  = null;
    var chapterScrollTimer = null;
    var novelTitleGlobal  = '';

    function applyChapterNav(block) {
        var url     = block.dataset.chapterUrl;
        var nextUrl = block.dataset.nextUrl;
        var prevUrl = block.dataset.prevUrl;

        if (url) history.replaceState(null, '', url);

        var navPrev = document.querySelector('[rel="prev"]');
        var navNext = document.querySelector('[rel="next"]');
        if (navPrev) {
            if (prevUrl) { navPrev.href = prevUrl; navPrev.style.opacity = ''; navPrev.style.cursor = ''; }
            else { navPrev.removeAttribute('href'); navPrev.style.opacity = '0.5'; navPrev.style.cursor = 'not-allowed'; }
        }
        if (navNext) {
            if (nextUrl) { navNext.href = nextUrl; navNext.style.opacity = ''; navNext.style.cursor = ''; }
            else { navNext.removeAttribute('href'); navNext.style.opacity = '0.5'; navNext.style.cursor = 'not-allowed'; }
        }

        document.querySelectorAll('.drawer-ch-item').forEach(function (item) {
            var isActive = url && item.href && item.href.indexOf(url) !== -1;
            item.classList.toggle('is-active', isActive);
            item.style.backgroundColor = isActive ? '#2563eb' : 'transparent';
            item.style.color = isActive ? '#ffffff' : '#cbd5e1';
        });

        updateBottomBarTitle(block);
    }

    function updateBottomBarTitle(block) {
        var textEl = document.querySelector('.bolum-baslik-text');
        if (!textEl) return;
        var novelTitle  = (block.dataset.novelTitle || novelTitleGlobal || '');
        var chNum       = block.dataset.chapterNumber || '';
        var innerTitle  = block.dataset.chapterTitle  || '';
        var display     = 'Novel Türk > ' + (novelTitle ? novelTitle + ' > ' : '') + 'Bölüm ' + chNum + ' ' + innerTitle;
        if (!display.trim()) return;
        textEl.textContent = display;
        var baslikDiv = document.getElementById('bolumBaslikDiv');
        if (baslikDiv) { baslikDiv.classList.remove('hidden'); localStorage.removeItem('bolumBaslikGizli'); }
        var wrapper = document.getElementById('bolumWrapper');
        if (!wrapper) return;
        wrapper.classList.remove('animate');
        wrapper.querySelectorAll('.clone').forEach(function(c) { c.remove(); });
        setTimeout(function() {
            var container = wrapper.parentElement;
            if (textEl.scrollWidth > container.clientWidth) {
                var clone = textEl.cloneNode(true);
                clone.classList.add('clone');
                wrapper.appendChild(clone);
                wrapper.classList.add('animate');
            }
        }, 100);
    }

    function loadCommentsForChapter(chapterId) {
        var body = document.getElementById('comments-drawer-body');
        if (!body || typeof webnovelReader === 'undefined') return;
        body.innerHTML = '<div style="text-align:center;padding:40px 0;color:#94a3b8;">Yükleniyor...</div>';
        var formData = new FormData();
        formData.append('action', 'webnovel_get_chapter_comments');
        formData.append('chapter_id', chapterId);
        formData.append('nonce', webnovelReader.nonce);
        fetch(webnovelReader.ajaxUrl, { method: 'POST', body: formData })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success) return;
                body.innerHTML = data.data.html;
                var subtitle = document.getElementById('comments-drawer-subtitle');
                if (subtitle) subtitle.textContent = data.data.title;
                body.querySelectorAll('script').forEach(function (old) {
                    var ns = document.createElement('script');
                    Array.from(old.attributes).forEach(function (a) { ns.setAttribute(a.name, a.value); });
                    ns.textContent = old.textContent;
                    old.parentNode.replaceChild(ns, old);
                });
            })
            .catch(function () {});
    }

    function detectCurrentChapter() {
        var allBlocks = document.querySelectorAll('.reader-text');
        if (!allBlocks.length) return;
        var midY = window.innerHeight * 0.4;
        var current = null;
        allBlocks.forEach(function (block) {
            if (block.getBoundingClientRect().top <= midY) current = block;
        });
        if (!current) current = allBlocks[0];
        var chId = current.dataset.chapterId;
        if (!chId || chId === currentChapterId) return;
        currentChapterId = chId;
        applyChapterNav(current);
        loadCommentsForChapter(chId);
    }

    // ============================================
    // Copy Protection
    // ============================================
    var readerContainer = document.getElementById('reader-container');
    var readerText = document.getElementById('reader-text');
    if (readerContainer) {
        var blockEvents = ['contextmenu', 'copy', 'cut', 'paste', 'selectstart', 'dragstart'];
        blockEvents.forEach(function (ev) {
            readerContainer.addEventListener(ev, function (e) {
                if (e.target.closest('.para-copy-icon')) return;
                e.preventDefault();
                return false;
            });
        });

        window.addEventListener('keydown', function (e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            if ((e.ctrlKey || e.metaKey) && (e.keyCode === 67 || e.keyCode === 86 || e.keyCode === 88 || e.keyCode === 65 || e.keyCode === 83 || e.keyCode === 85)) {
                e.preventDefault();
                return false;
            }
            if (e.keyCode === 123) { e.preventDefault(); return false; }
            if ((e.ctrlKey || e.metaKey) && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74 || e.keyCode === 67)) {
                e.preventDefault();
                return false;
            }
        });
    }

    // ============================================
    // Initialize
    // ============================================
    loadPreferences();
    webnovelFollow.init();
    webnovelHistory.init();
    setupDropdowns();
    setupReadingProgress();

    var progressWrap = document.getElementById('reader-progress-wrap');
    if (progressWrap) {
        progressWrap.style.cursor = 'pointer';
        progressWrap.addEventListener('click', function () {
            var target = currentChapterId
                ? document.querySelector('.reader-text[data-chapter-id="' + currentChapterId + '"]')
                : document.getElementById('reader-text');
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
    if (document.getElementById('reader-text')) {
        fetchChapterContent();
        infiniteScroll.init();
        currentChapterId = document.getElementById('reader-text').dataset.chapterId || null;
        novelTitleGlobal = document.getElementById('reader-text').dataset.novelTitle || '';
        window.addEventListener('scroll', function () {
            clearTimeout(chapterScrollTimer);
            chapterScrollTimer = setTimeout(detectCurrentChapter, 150);
        });
        var readerContainer = document.getElementById('reader-container');
        if (readerContainer) {
            readerContainer.addEventListener('click', function (e) {
                if (e.target.closest('.para-copy-icon')) return;
                var p = e.target.closest('.reader-text p');
                var icon = p ? p._paraIcon : null;
                if (currentParaIcon && currentParaIcon !== icon) {
                    currentParaIcon.style.opacity = '0';
                    setTimeout(function () { currentParaIcon.style.display = 'none'; }, 500);
                    currentParaIcon = null;
                }
                if (icon) {
                    if (icon.style.display === 'none' || icon.style.opacity === '0') {
                        icon.style.display = 'inline';
                        setTimeout(function () { icon.style.opacity = '1'; }, 10);
                        currentParaIcon = icon;
                    } else {
                        icon.style.opacity = '0';
                        setTimeout(function () { icon.style.display = 'none'; }, 500);
                        currentParaIcon = null;
                    }
                }
            });
        }
    }

})();
