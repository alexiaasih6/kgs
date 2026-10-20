<?php
$footer_header_menus = get_navigation_menus($pdo, 'header', true);
$footer_bottom_menus = get_navigation_menus($pdo, 'footer', true);
$raw_footer_brand = trim(get_setting($pdo, 'footer_brand_label', get_setting($pdo, 'short_name', 'KGSNET')));
$footer_brand_label = (strtoupper($raw_footer_brand) === 'PT. KGS' || strtoupper($raw_footer_brand) === 'KGS' || $raw_footer_brand === '') ? 'KGSNET' : $raw_footer_brand;
$footer_desc = get_setting(
    $pdo,
    'footer_description',
    get_setting(
        $pdo,
        'company_profile',
        'Mitra teknologi B2B terpercaya sejak 1997 (Creative Com). Menyediakan infrastruktur jaringan komputer, telekomunikasi, surveillance keamanan, fabrikasi tower mandiri, grounding < 2 Ohm, dan layanan internet resmi KGSNet (Anggota APJII).'
    )
);
?>
    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-300 border-t border-slate-800 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">
            
            <!-- Kolom 1: Profil -->
            <div class="space-y-4">
                <div class="flex items-center gap-3">
                    <div class="px-3 h-10 bg-blue-600 text-white font-extrabold text-sm rounded-lg flex items-center justify-center shrink-0 tracking-tight" data-live-setting="footer_brand_label"><?= e($footer_brand_label) ?></div>
                    <div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded bg-blue-600/20 border border-blue-500/40 text-blue-400 font-extrabold text-[11px] tracking-wider uppercase" data-live-setting="footer_brand_label"><?= e($footer_brand_label) ?></span>
                            <span class="font-bold text-white text-base block leading-tight" data-live-setting="company_name"><?= e(get_setting($pdo, 'company_name', 'PT. KREATIF GLOBAL SOLUSINDO')) ?></span>
                        </div>
                        <span class="text-xs text-blue-400 font-medium block mt-0.5" data-live-setting="tagline"><?= e(get_setting($pdo, 'tagline', 'Your Solution & Partner')) ?></span>
                    </div>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed" data-live-setting="footer_description">
                    <?= e($footer_desc) ?>
                </p>
                <div class="pt-2 flex flex-wrap gap-2 text-[11px]">
                    <span class="px-2.5 py-1 bg-slate-800 text-blue-400 rounded-md font-semibold">ISP KGSNet</span>
                    <span class="px-2.5 py-1 bg-slate-800 text-emerald-400 rounded-md font-semibold">Anggota APJII</span>
                    <span class="px-2.5 py-1 bg-slate-800 text-slate-300 rounded-md">Panasonic Dealer</span>
                    <span class="px-2.5 py-1 bg-slate-800 text-slate-300 rounded-md">NETVIEL Certified</span>
                </div>
            </div>

            <!-- Kolom 2: Alamat Kantor & Kontak Resmi -->
            <div class="space-y-3 text-xs">
                <h3 class="text-white font-bold text-sm uppercase tracking-wider mb-4">Alamat Kantor & Kontak</h3>
                <ul class="space-y-3 text-slate-400">
                    <li class="flex items-start gap-2.5">
                        <span class="text-blue-400 mt-0.5">📍</span>
                        <div>
                            <strong class="text-slate-200 block">Alamat Kantor:</strong>
                            <span data-live-setting="address_palembang"><?= e(get_setting($pdo, 'address_palembang', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113')) ?></span>
                        </div>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="text-blue-400">📞</span>
                        <span>Telp: <span data-live-setting="phone_palembang"><?= e(get_setting($pdo, 'phone_palembang', '0711-316088 (Hunting)')) ?></span></span>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="text-emerald-400">💬</span>
                        <a href="https://wa.me/<?= e(format_wa_number(get_setting($pdo, 'whatsapp', '0811-782119'))) ?>" target="_blank" rel="noopener noreferrer" class="text-emerald-400 hover:underline font-semibold">
                            WhatsApp CS: <span data-live-setting="whatsapp"><?= e(get_setting($pdo, 'whatsapp', '0811-782119')) ?></span>
                        </a>
                    </li>
                    <li class="flex items-center gap-2.5">
                        <span class="text-blue-400">✉️</span>
                        <span>Email: <span data-live-setting="email"><?= e(get_setting($pdo, 'email', 'info@kgs.net.id')) ?></span></span>
                    </li>
                </ul>
            </div>

            <!-- Kolom 3: Navigasi Halaman (Dinamis dari Header Menus) -->
            <div class="space-y-3 text-xs">
                <h3 class="text-white font-bold text-sm uppercase tracking-wider mb-4">Navigasi Halaman</h3>
                <ul class="space-y-2.5 text-slate-400">
                    <?php foreach ($footer_header_menus as $menu_item): ?>
                        <?php
                        $m_url = sanitize_nav_url((string)($menu_item['url'] ?? '#'));
                        $m_title = (string)($menu_item['title'] ?? '');
                        $m_target = ($menu_item['target'] ?? '_self') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
                        ?>
                        <li>
                            <a href="<?= e($m_url) ?>"<?= $m_target ?> class="hover:text-blue-400 transition">
                                <?= e($m_title) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Kolom 4: Informasi & Akses Cepat (Dinamis dari Footer Menus) -->
            <div class="space-y-3 text-xs">
                <h3 class="text-white font-bold text-sm uppercase tracking-wider mb-4">Informasi & Akses Cepat</h3>
                <ul class="space-y-2.5 text-slate-400">
                    <?php foreach ($footer_bottom_menus as $menu_item): ?>
                        <?php
                        $m_url = sanitize_nav_url((string)($menu_item['url'] ?? '#'));
                        $m_title = (string)($menu_item['title'] ?? '');
                        $m_target = ($menu_item['target'] ?? '_self') === '_blank' ? ' target="_blank" rel="noopener noreferrer"' : '';
                        ?>
                        <li>
                            <a href="<?= e($m_url) ?>"<?= $m_target ?> class="hover:text-blue-400 transition">
                                <?= e($m_title) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="border-t border-slate-800 py-6 text-center text-xs text-slate-500">
            &copy; <?= date('Y') ?> <strong class="text-slate-300" data-live-setting="footer_brand_label"><?= e($footer_brand_label) ?></strong> — <span data-live-setting="company_name"><?= e(get_setting($pdo, 'company_name', 'PT. KREATIF GLOBAL SOLUSINDO')) ?></span>. <span data-live-setting="copyright_text"><?= e(get_setting($pdo, 'copyright_text', 'Hak Cipta Dilindungi Undang-Undang.')) ?></span>
        </div>
    </footer>

    <?php if (!empty($_SESSION['admin_logged_in'])): ?>
        <style id="kgsLiveEditStyles">
            body.kgs-live-edit-on [data-live-setting]:hover,
            body.kgs-live-edit-on [data-live-page-slug]:hover,
            body.kgs-live-edit-on [data-live-record]:hover,
            body.kgs-live-edit-on [data-live-img]:hover,
            body.kgs-live-edit-on main img:hover {
                outline: 2px dashed #10b981 !important;
                outline-offset: 4px !important;
                cursor: pointer !important;
                transition: outline 0.15s ease;
            }
        </style>

        <!-- Toast Notifikasi Simpan Live Edit -->
        <div id="kgsLiveToast" class="hidden fixed top-24 right-5 z-50 bg-emerald-600 text-white px-4 py-3 rounded-2xl shadow-2xl border border-emerald-400 text-xs font-bold">
            ✓ Perubahan tersimpan di Website & Database Admin CMS!
        </div>

        <!-- Modal Edit Teks / Gambar Langsung di Halaman Website -->
        <div id="kgsLiveEditModal" data-live-ui="true" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl border border-slate-200 max-w-lg w-full p-6 shadow-2xl space-y-4 text-xs text-slate-800">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <div>
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded font-bold text-[10px] uppercase">Live Edit Website &rarr; Database CMS</span>
                        <h3 id="kgsLiveModalTitle" class="font-extrabold text-slate-900 text-sm mt-1">Edit Konten Halaman</h3>
                    </div>
                    <button type="button" onclick="window.kgsCloseLiveModal()" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg font-bold text-base cursor-pointer">&times;</button>
                </div>

                <form id="kgsLiveEditForm" class="space-y-4">
                    <input type="hidden" id="kgsLiveCsrfToken" value="<?= e(generate_csrf_token()) ?>">
                    <div id="kgsLiveTextWrap">
                        <label class="block font-bold text-slate-700 mb-1">Isi Teks</label>
                        <textarea id="kgsLiveTextInput" rows="4" class="w-full border border-slate-300 rounded-xl p-3 font-medium text-slate-900 focus:outline-none focus:border-blue-600"></textarea>
                    </div>

                    <div id="kgsLiveImageWrap" class="hidden space-y-3">
                        <div class="h-44 rounded-2xl bg-slate-900 border border-slate-200 overflow-hidden flex items-center justify-center p-2">
                            <img id="kgsLiveImgPreview" src="" alt="Preview" class="max-h-full max-w-full object-contain">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Upload File Gambar Baru dari Perangkat</label>
                            <input type="file" id="kgsLiveImgFile" accept=".jpg,.jpeg,.png,.webp,.gif" class="w-full border border-slate-300 rounded-xl p-2 bg-slate-50">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Atau Masukkan URL Gambar</label>
                            <input type="text" id="kgsLiveImgUrl" class="w-full border border-slate-300 rounded-xl p-2.5 font-mono">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" onclick="window.kgsCloseLiveModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" id="kgsLiveSubmitBtn" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-md cursor-pointer">
                            Simpan ke Website & Admin CMS
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Floating Admin Live Visual Editor Bar -->
        <div id="kgsPhpLiveEditorDock" data-live-ui="true" class="fixed bottom-3 left-1/2 -translate-x-1/2 z-50 max-w-[96vw]">
            <div class="bg-slate-900/95 backdrop-blur-md text-white border border-slate-700 rounded-2xl px-3.5 py-2 shadow-2xl flex flex-wrap items-center gap-2 text-xs">
                <button type="button" id="btnTogglePhpLiveEdit" onclick="window.kgsTogglePhpLiveEdit()" class="px-3.5 py-2 rounded-xl bg-emerald-500 text-slate-950 font-extrabold cursor-pointer">
                    ✏️ Mode Edit Langsung: AKTIF (Klik Teks/Gambar)
                </button>
                <a href="admin/bagian-halaman.php" class="px-3 py-2 rounded-xl bg-emerald-700 hover:bg-emerald-600 text-white font-bold">
                    🧩 Bagian Halaman & Statistik
                </a>
                <a href="admin/tema-slider.php" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-300 font-bold">
                    🎨 Tema & Slider Beranda
                </a>
                <a href="admin/halaman.php" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold">
                    ⚙️ Admin CMS
                </a>
            </div>
        </div>

        <script>
            (function() {
                let liveActive = true;
                let activeElement = null;
                let activeMeta = null;

                document.body.classList.add('kgs-live-edit-on');

                window.kgsTogglePhpLiveEdit = function() {
                    liveActive = !liveActive;
                    document.body.classList.toggle('kgs-live-edit-on', liveActive);
                    const btn = document.getElementById('btnTogglePhpLiveEdit');
                    if (btn) {
                        btn.textContent = liveActive
                            ? '✏️ Mode Edit Langsung: AKTIF (Klik Teks/Gambar)'
                            : '✏️ Aktifkan Mode Edit Langsung';
                        btn.className = liveActive
                            ? 'px-3.5 py-2 rounded-xl bg-emerald-500 text-slate-950 font-extrabold cursor-pointer'
                            : 'px-3.5 py-2 rounded-xl bg-blue-600 text-white font-extrabold cursor-pointer';
                    }
                };

                window.kgsCloseLiveModal = function() {
                    document.getElementById('kgsLiveEditModal').classList.add('hidden');
                    activeElement = null;
                    activeMeta = null;
                };

                function showToast(msg) {
                    const t = document.getElementById('kgsLiveToast');
                    if (!t) return;
                    t.textContent = '✓ ' + (msg || 'Perubahan tersimpan di Website & Database Admin CMS!');
                    t.classList.remove('hidden');
                    setTimeout(() => t.classList.add('hidden'), 3200);
                }

                document.addEventListener('click', function(e) {
                    if (!liveActive) return;
                    if (e.target.closest('[data-live-ui="true"]')) return;

                    // 1. Cek apakah klik gambar
                    const imgEl = e.target.closest('img[data-live-img], main img');
                    if (imgEl) {
                        e.preventDefault();
                        e.stopPropagation();
                        activeElement = imgEl;
                        const attr = imgEl.getAttribute('data-live-img') || 'slider_new:0';
                        const parts = attr.split(':');
                        activeMeta = {
                            mode: 'image',
                            targetKind: parts[0] || 'slider_new',
                            recordId: parts[1] || '0'
                        };
                        document.getElementById('kgsLiveModalTitle').textContent = 'Ganti / Upload Gambar Website';
                        document.getElementById('kgsLiveTextWrap').classList.add('hidden');
                        document.getElementById('kgsLiveImageWrap').classList.remove('hidden');
                        document.getElementById('kgsLiveImgPreview').src = imgEl.src;
                        document.getElementById('kgsLiveImgUrl').value = imgEl.getAttribute('src') || '';
                        document.getElementById('kgsLiveImgFile').value = '';
                        document.getElementById('kgsLiveEditModal').classList.remove('hidden');
                        return;
                    }

                    // 2. Cek apakah klik elemen teks dengan atribut live-edit
                    const el = e.target.closest('[data-live-page-slug], [data-live-setting], [data-live-record]');
                    if (!el) return;

                    e.preventDefault();
                    e.stopPropagation();

                    activeElement = el;
                    const currentVal = el.innerText.trim();

                    if (el.hasAttribute('data-live-page-slug')) {
                        activeMeta = {
                            mode: 'page_field',
                            slug: el.getAttribute('data-live-page-slug'),
                            field: el.getAttribute('data-live-page-field') || 'title'
                        };
                        document.getElementById('kgsLiveModalTitle').textContent = 'Edit Konten Halaman (' + activeMeta.slug + ')';
                    } else if (el.hasAttribute('data-live-record')) {
                        const parts = (el.getAttribute('data-live-record') || '').split(':');
                        activeMeta = {
                            mode: 'db_record',
                            table: parts[0],
                            id: parts[1],
                            field: parts[2]
                        };
                        document.getElementById('kgsLiveModalTitle').textContent = 'Edit Data CMS (' + parts[0] + ' #' + parts[1] + ')';
                    } else {
                        activeMeta = {
                            mode: 'site_setting',
                            keyName: el.getAttribute('data-live-setting')
                        };
                        document.getElementById('kgsLiveModalTitle').textContent = 'Edit Teks & Pengaturan (' + activeMeta.keyName + ')';
                    }

                    document.getElementById('kgsLiveImageWrap').classList.add('hidden');
                    document.getElementById('kgsLiveTextWrap').classList.remove('hidden');
                    document.getElementById('kgsLiveTextInput').value = currentVal;
                    document.getElementById('kgsLiveEditModal').classList.remove('hidden');
                    document.getElementById('kgsLiveTextInput').focus();
                }, true);

                document.getElementById('kgsLiveEditForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (!activeElement || !activeMeta) return;

                    const fd = new FormData();
                    const csrfToken = (document.getElementById('kgsLiveCsrfToken') || {}).value || '';
                    fd.append('csrf_token', csrfToken);
                    const btn = document.getElementById('kgsLiveSubmitBtn');
                    btn.disabled = true;
                    btn.textContent = 'Menyimpan...';

                    if (activeMeta.mode === 'image') {
                        fd.append('edit_type', 'upload_image');
                        fd.append('target_kind', activeMeta.targetKind);
                        fd.append('record_id', activeMeta.recordId);
                        const fileInput = document.getElementById('kgsLiveImgFile');
                        if (fileInput.files && fileInput.files[0]) {
                            fd.append('image_file', fileInput.files[0]);
                        } else {
                            fd.append('image_url', document.getElementById('kgsLiveImgUrl').value.trim());
                        }
                    } else if (activeMeta.mode === 'page_field') {
                        fd.append('edit_type', 'page_field');
                        fd.append('slug', activeMeta.slug);
                        fd.append('field', activeMeta.field);
                        fd.append('value', document.getElementById('kgsLiveTextInput').value.trim());
                    } else if (activeMeta.mode === 'db_record') {
                        fd.append('edit_type', 'db_record');
                        fd.append('table', activeMeta.table);
                        fd.append('id', activeMeta.id);
                        fd.append('field', activeMeta.field);
                        fd.append('value', document.getElementById('kgsLiveTextInput').value.trim());
                    } else {
                        fd.append('edit_type', 'site_setting');
                        fd.append('key_name', activeMeta.keyName);
                        fd.append('value', document.getElementById('kgsLiveTextInput').value.trim());
                    }

                    fetch('admin/api-live-edit.php', {
                        method: 'POST',
                        headers: { 'X-CSRF-Token': csrfToken, 'Accept': 'application/json' },
                        body: fd
                    })
                        .then(r => r.json())
                        .then(res => {
                            btn.disabled = false;
                            btn.textContent = 'Simpan ke Website & Admin CMS';
                            if (res.ok) {
                                if (activeMeta.mode === 'image' && res.image_url) {
                                    activeElement.src = res.image_url;
                                } else {
                                    activeElement.innerText = document.getElementById('kgsLiveTextInput').value.trim();
                                }
                                window.kgsCloseLiveModal();
                                showToast(res.message);
                            } else {
                                alert(res.error || 'Gagal menyimpan perubahan');
                            }
                        })
                        .catch(() => {
                            btn.disabled = false;
                            btn.textContent = 'Simpan ke Website & Admin CMS';
                            alert('Gagal menghubungi server.');
                        });
                });
            })();
        </script>
    <?php endif; ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
