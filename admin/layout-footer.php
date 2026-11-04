        </main>
    </div>

    <?php
    $__admin_media_items = [];
    if (isset($pdo) && $pdo instanceof PDO && function_exists('get_uploaded_media_library')) {
        try {
            $__admin_media_items = get_uploaded_media_library($pdo);
        } catch (Throwable $e) {
            $__admin_media_items = [];
        }
    }
    ?>

    <!-- Global Modal: Pilih dari Media & Upload Gambar -->
    <div id="kgsGlobalMediaPickerModal" class="fixed inset-0 z-50 hidden bg-slate-950/75 backdrop-blur-xs items-center justify-center p-4">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden text-xs">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center text-lg shrink-0">
                        🖼️
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 id="kgsMediaPickerTitle" class="text-sm sm:text-base font-extrabold">Pilih dari Media &amp; Upload Gambar</h3>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-[10px] font-bold">
                                <?= count($__admin_media_items) ?> File Tersedia
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-300 mt-0.5">
                            Klik gambar yang pernah di-upload pada <strong>🖼️ Media &amp; Upload Gambar</strong>, <strong>Slide Show</strong>, atau <strong>Portofolio</strong> untuk langsung menggunakannya tanpa upload ulang.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="media.php" target="_blank" class="px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-[11px] transition shrink-0">
                        + Kelola / Upload di Media.php &nearr;
                    </a>
                    <button type="button" onclick="window.closeKgsMediaPicker()" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-sm cursor-pointer">
                        &times;
                    </button>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="px-6 py-3 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-1.5" id="kgsMediaFilterTabs">
                    <button type="button" data-filter="all" onclick="window.filterKgsMediaPicker('all')" class="kgs-media-tab px-3 py-1.5 rounded-xl font-bold text-[11px] bg-indigo-600 text-white cursor-pointer">
                        Semua (<?= count($__admin_media_items) ?>)
                    </button>
                    <button type="button" data-filter="media" onclick="window.filterKgsMediaPicker('media')" class="kgs-media-tab px-3 py-1.5 rounded-xl font-bold text-[11px] bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 cursor-pointer">
                        🖼️ Media Terunggah
                    </button>
                    <button type="button" data-filter="slideshow" onclick="window.filterKgsMediaPicker('slideshow')" class="kgs-media-tab px-3 py-1.5 rounded-xl font-bold text-[11px] bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 cursor-pointer">
                        Slide Show (slideshow.php)
                    </button>
                    <button type="button" data-filter="portfolio" onclick="window.filterKgsMediaPicker('portfolio')" class="kgs-media-tab px-3 py-1.5 rounded-xl font-bold text-[11px] bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 cursor-pointer">
                        Portofolio Proyek
                    </button>
                </div>
                <div class="relative min-w-[220px]">
                    <input type="text" id="kgsMediaSearchInput" oninput="window.searchKgsMediaPicker(this.value)" placeholder="Cari nama file / kategori..." class="w-full pl-3 pr-3 py-1.5 rounded-xl border border-slate-300 bg-white text-xs text-slate-800">
                </div>
            </div>

            <!-- Media Grid -->
            <div class="p-6 overflow-y-auto flex-1 bg-slate-100/60">
                <?php if (empty($__admin_media_items)): ?>
                    <div class="text-center py-12 bg-white rounded-2xl border border-dashed border-slate-300 p-6">
                        <p class="font-bold text-slate-700 text-sm">Belum ada gambar di pustaka Media</p>
                        <p class="text-slate-500 mt-1">Silakan unggah gambar melalui tombol Choose File atau halaman <a href="media.php" class="text-indigo-600 underline font-bold">🖼️ Media &amp; Upload Gambar</a>.</p>
                    </div>
                <?php else: ?>
                    <div id="kgsMediaGridContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3.5">
                        <?php foreach ($__admin_media_items as $m_item): ?>
                            <div class="kgs-media-card group bg-white rounded-2xl border-2 border-slate-200 hover:border-indigo-600 overflow-hidden shadow-2xs hover:shadow-md transition cursor-pointer flex flex-col justify-between"
                                 data-source="<?= e($m_item['source']) ?>"
                                 data-search="<?= e(strtolower($m_item['title'] . ' ' . $m_item['category'] . ' ' . $m_item['url'])) ?>"
                                 data-url="<?= e($m_item['url']) ?>"
                                 data-preview="<?= e($m_item['preview_url']) ?>"
                                 data-title="<?= e($m_item['title']) ?>"
                                 onclick="window.selectKgsMediaCard(this)">
                                <div class="relative aspect-16/10 bg-slate-900 overflow-hidden">
                                    <img src="<?= e($m_item['preview_url']) ?>" alt="<?= e($m_item['title']) ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase tracking-wider bg-slate-900/80 text-white">
                                        <?= e($m_item['source'] === 'media' ? '🖼️ Media' : ($m_item['source'] === 'slideshow' ? 'Slide Show' : ($m_item['source'] === 'portfolio' ? 'Portofolio' : 'Slider'))) ?>
                                    </span>
                                    <div class="kgs-card-check hidden absolute top-2 right-2 w-6 h-6 rounded-full bg-indigo-600 text-white font-extrabold flex items-center justify-center shadow">
                                        ✓
                                    </div>
                                </div>
                                <div class="p-2.5">
                                    <div class="font-bold text-slate-900 truncate text-[11px]" title="<?= e($m_item['title']) ?>"><?= e($m_item['title']) ?></div>
                                    <div class="text-[10px] text-indigo-600 font-semibold truncate"><?= e($m_item['category']) ?></div>
                                    <div class="text-[9px] text-slate-400 font-mono truncate mt-0.5"><?= e($m_item['url']) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Modal Footer -->
            <div class="px-6 py-3.5 bg-white border-t border-slate-200 flex items-center justify-between gap-3">
                <span id="kgsMediaPickerHint" class="text-[11px] text-slate-500">
                    Klik salah satu gambar di atas untuk memilihnya.
                </span>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.closeKgsMediaPicker()" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 font-bold cursor-pointer">
                        Tutup
                    </button>
                    <button type="button" id="kgsMediaConfirmMultiBtn" onclick="window.confirmKgsMultiMediaSelection()" class="hidden px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold shadow-xs cursor-pointer">
                        ✓ Gunakan Gambar Terpilih
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 mt-auto text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-2">
            <span>&copy; <?= date('Y') ?> <strong><?= e($company_name ?? 'PT. KREATIF GLOBAL SOLUSINDO') ?></strong> — All Rights Reserved.</span>
            <span class="text-slate-400">Sistem Manajemen Konten Internal</span>
        </div>
    </footer>
    <script src="../assets/js/main.js"></script>
    <script>
    (function() {
        var currentPickerConfig = null;
        var currentFilter = 'all';
        var currentSearch = '';
        var selectedMultiUrls = [];

        function applyMediaFilter() {
            var cards = document.querySelectorAll('#kgsMediaGridContainer .kgs-media-card');
            cards.forEach(function(card) {
                var src = card.getAttribute('data-source') || '';
                var hay = card.getAttribute('data-search') || '';
                var matchTab = (currentFilter === 'all') || (src === currentFilter);
                var matchSearch = (currentSearch === '') || (hay.indexOf(currentSearch) !== -1);
                card.style.display = (matchTab && matchSearch) ? '' : 'none';
            });
        }

        window.filterKgsMediaPicker = function(filter) {
            currentFilter = filter;
            var tabs = document.querySelectorAll('#kgsMediaFilterTabs .kgs-media-tab');
            tabs.forEach(function(btn) {
                if (btn.getAttribute('data-filter') === filter) {
                    btn.className = 'kgs-media-tab px-3 py-1.5 rounded-xl font-bold text-[11px] bg-indigo-600 text-white cursor-pointer';
                } else {
                    btn.className = 'kgs-media-tab px-3 py-1.5 rounded-xl font-bold text-[11px] bg-white text-slate-600 border border-slate-200 hover:bg-slate-100 cursor-pointer';
                }
            });
            applyMediaFilter();
        };

        window.searchKgsMediaPicker = function(q) {
            currentSearch = (q || '').toLowerCase().trim();
            applyMediaFilter();
        };

        window.openKgsMediaPicker = function(config) {
            currentPickerConfig = config || {};
            selectedMultiUrls = [];
            var modal = document.getElementById('kgsGlobalMediaPickerModal');
            var confirmBtn = document.getElementById('kgsMediaConfirmMultiBtn');
            var hint = document.getElementById('kgsMediaPickerHint');

            document.querySelectorAll('#kgsMediaGridContainer .kgs-media-card').forEach(function(c) {
                c.classList.remove('ring-4', 'ring-indigo-500/40', 'border-indigo-600');
                var chk = c.querySelector('.kgs-card-check');
                if (chk) chk.classList.add('hidden');
            });

            if (currentPickerConfig.multiple) {
                if (confirmBtn) confirmBtn.classList.remove('hidden');
                if (hint) hint.textContent = 'Mode Multi-Gambar: Klik beberapa gambar lalu tekan tombol "Gunakan Gambar Terpilih".';
            } else {
                if (confirmBtn) confirmBtn.classList.add('hidden');
                if (hint) hint.textContent = 'Klik salah satu gambar di atas untuk langsung memilihnya.';
            }

            if (modal) modal.classList.remove('hidden');
        };

        window.closeKgsMediaPicker = function() {
            var modal = document.getElementById('kgsGlobalMediaPickerModal');
            if (modal) modal.classList.add('hidden');
            currentPickerConfig = null;
        };

        function applyChosenUrlToTarget(url, previewUrl, title) {
            if (!currentPickerConfig) return;

            if (currentPickerConfig.hiddenInput) {
                currentPickerConfig.hiddenInput.value = url;
            }
            if (currentPickerConfig.targetInputSelector) {
                var targetEl = document.querySelector(currentPickerConfig.targetInputSelector);
                if (targetEl) {
                    if (targetEl.tagName === 'TEXTAREA' && currentPickerConfig.appendLines) {
                        var cur = targetEl.value.trim();
                        targetEl.value = cur ? (cur + "\n" + url + ' :: ' + title) : (url + ' :: ' + title);
                    } else {
                        targetEl.value = url;
                    }
                }
            }
            if (currentPickerConfig.pairedTextInput) {
                var paired = currentPickerConfig.pairedTextInput;
                if (paired.tagName === 'TEXTAREA') {
                    var existing = paired.value.trim();
                    paired.value = existing ? (existing + "\n" + url + ' :: ' + title) : (url + ' :: ' + title);
                } else {
                    paired.value = url;
                }
            }
            if (currentPickerConfig.previewBadge) {
                var badge = currentPickerConfig.previewBadge;
                badge.innerHTML = '<img src="' + previewUrl.replace(/"/g, '&quot;') + '" class="w-8 h-8 rounded-lg object-cover border border-indigo-300 shrink-0">' +
                    '<div class="min-w-0 flex-1"><div class="text-[11px] font-bold text-indigo-900 truncate">✓ Dipilih dari Media: ' + title.replace(/</g, '&lt;') + '</div>' +
                    '<div class="text-[10px] font-mono text-indigo-600 truncate">' + url.replace(/</g, '&lt;') + '</div></div>';
                badge.classList.remove('hidden');
            }
        }

        window.selectKgsMediaCard = function(cardEl) {
            var url = cardEl.getAttribute('data-url') || '';
            var preview = cardEl.getAttribute('data-preview') || url;
            var title = cardEl.getAttribute('data-title') || '';

            if (currentPickerConfig && currentPickerConfig.multiple) {
                var idx = selectedMultiUrls.findIndex(function(x) { return x.url === url; });
                var chk = cardEl.querySelector('.kgs-card-check');
                if (idx >= 0) {
                    selectedMultiUrls.splice(idx, 1);
                    cardEl.classList.remove('ring-4', 'ring-indigo-500/40', 'border-indigo-600');
                    if (chk) chk.classList.add('hidden');
                } else {
                    selectedMultiUrls.push({ url: url, preview: preview, title: title });
                    cardEl.classList.add('ring-4', 'ring-indigo-500/40', 'border-indigo-600');
                    if (chk) chk.classList.remove('hidden');
                }
                var confirmBtn = document.getElementById('kgsMediaConfirmMultiBtn');
                if (confirmBtn) {
                    confirmBtn.textContent = '✓ Gunakan ' + selectedMultiUrls.length + ' Gambar Terpilih';
                }
                return;
            }

            applyChosenUrlToTarget(url, preview, title);
            window.closeKgsMediaPicker();
        };

        window.confirmKgsMultiMediaSelection = function() {
            if (!currentPickerConfig || selectedMultiUrls.length === 0) {
                window.closeKgsMediaPicker();
                return;
            }
            selectedMultiUrls.forEach(function(item) {
                applyChosenUrlToTarget(item.url, item.preview, item.title);
            });
            window.closeKgsMediaPicker();
        };

        // Otomatis tambahkan tombol "🖼️ Pilih dari Media & Upload Gambar" di samping semua <input type="file"> di Admin CMS
        document.addEventListener('DOMContentLoaded', function() {
            var fileInputs = document.querySelectorAll('input[type="file"]');
            fileInputs.forEach(function(fileInput) {
                if (fileInput.getAttribute('data-kgs-media-enhanced') === '1') return;
                // Jangan tambahkan pada halaman media.php itu sendiri jika form utamanya adalah upload media baru
                var form = fileInput.closest('form');
                if (window.location.pathname.indexOf('/media.php') !== -1) return;

                fileInput.setAttribute('data-kgs-media-enhanced', '1');
                var rawName = fileInput.getAttribute('name') || 'file';
                var cleanName = rawName.replace(/\[\]$/, '');
                var isMultiple = fileInput.hasAttribute('multiple') || rawName.indexOf('[]') !== -1;

                // Buat hidden input <input type="hidden" name="<cleanName>_from_media">
                var hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = cleanName + '_from_media';
                fileInput.parentNode.insertBefore(hiddenInput, fileInput.nextSibling);

                // Cari apakah di dalam form yang sama ada input URL/path pasangan
                var pairedTextInput = null;
                if (form) {
                    pairedTextInput = form.querySelector(
                        'input[name="image_url"], input[name="cover_image"], input[name="image_url_input"], input[name="image_path"], textarea[name="image_urls_text"]'
                    );
                }

                // Buat preview badge
                var previewBadge = document.createElement('div');
                previewBadge.className = 'hidden mt-2 p-2 rounded-xl bg-indigo-50 border border-indigo-200 flex items-center gap-2.5';

                // Buat tombol "🖼️ Pilih dari Media & Upload Gambar"
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'mt-1.5 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-[11px] shadow-2xs transition cursor-pointer';
                btn.innerHTML = '<span>🖼️ Pilih dari Media &amp; Upload Gambar</span>';
                btn.addEventListener('click', function() {
                    window.openKgsMediaPicker({
                        multiple: isMultiple && pairedTextInput && pairedTextInput.tagName === 'TEXTAREA',
                        hiddenInput: hiddenInput,
                        pairedTextInput: pairedTextInput,
                        previewBadge: previewBadge
                    });
                });

                hiddenInput.parentNode.insertBefore(btn, hiddenInput.nextSibling);
                btn.parentNode.insertBefore(previewBadge, btn.nextSibling);
            });
        });
    })();
    </script>
</body>
</html>

