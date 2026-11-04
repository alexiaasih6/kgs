<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$page_data = get_page_by_slug($pdo, 'faq', [
    'title' => 'Pertanyaan Umum & Jawaban Teknis',
    'summary' => 'Informasi lengkap seputar legalitas izin, jaminan SLA, cakupan area, metodologi instalasi, dan prosedur kerjasama korporat bersama PT. Kreatif Global Solusindo.',
    'meta_title' => 'Frequently Asked Questions (FAQ) - PT. KGS',
    'meta_description' => 'Pertanyaan umum & jawaban teknis seputar layanan ISP KGSNet, Security, Networking, PBX, Telecommunication, dan prosedur survei PT. KGS.'
]);

$page_title = $page_data['meta_title'] ?: $page_data['title'];
$page_desc = $page_data['meta_description'] ?: $page_data['summary'];
require_once __DIR__ . '/includes/header.php';

// Ambil daftar FAQ dinamis dari tabel database `faqs`
$faqs_raw = get_faqs_list($pdo, true);
$wa_clean = format_wa_number(get_setting($pdo, 'whatsapp', '0811-782119'));

// Normalisasi kategori agar sesuai dengan tab preview ('Semua', 'Umum', 'Internet ISP', 'Keamanan & Tower', 'Kontrak & Legalitas')
$faq_categories = ['Semua', 'Umum', 'Internet ISP', 'Keamanan & Tower', 'Kontrak & Legalitas'];
$faqs = [];
foreach ($faqs_raw as $idx => $item) {
    $raw_cat = trim((string)($item['category'] ?? ($item['cat'] ?? 'Umum')));
    if (in_array($raw_cat, ['Umum', 'Internet ISP', 'Keamanan & Tower', 'Kontrak & Legalitas'], true)) {
        $norm_cat = $raw_cat;
    } elseif (stripos($raw_cat, 'ISP') !== false || stripos($raw_cat, 'Internet') !== false || stripos($raw_cat, 'SLA') !== false) {
        $norm_cat = 'Internet ISP';
    } elseif (stripos($raw_cat, 'Grounding') !== false || stripos($raw_cat, 'Tower') !== false || stripos($raw_cat, 'Keamanan') !== false) {
        $norm_cat = 'Keamanan & Tower';
    } elseif (stripos($raw_cat, 'Legalitas') !== false || stripos($raw_cat, 'Tender') !== false || stripos($raw_cat, 'Survei') !== false || stripos($raw_cat, 'Kontrak') !== false) {
        $norm_cat = 'Kontrak & Legalitas';
    } else {
        $norm_cat = 'Umum';
    }
    $item['normalized_category'] = $norm_cat;
    $faqs[] = $item;
}
?>

<section id="faq" class="py-20 bg-white border-b border-slate-200">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header (Persis seperti React Preview FaqSection.tsx) -->
        <div class="text-center max-w-2xl mx-auto mb-12">
            <div class="flex items-center justify-center gap-2 text-xs font-semibold text-blue-700 uppercase tracking-wider mb-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <span data-live-setting="faq_hero_kicker"><?= e(get_setting($pdo, 'faq_hero_kicker', 'Frequently Asked Questions')) ?></span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight" data-live-page-slug="faq" data-live-page-field="title">
                <?= e($page_data['title'] ?: 'Pertanyaan Umum & Jawaban Teknis') ?>
            </h1>
            <p class="text-sm text-slate-600 mt-3 leading-relaxed" data-live-page-slug="faq" data-live-page-field="summary">
                <?= e($page_data['summary'] ?: 'Informasi lengkap seputar legalitas izin, jaminan SLA, cakupan area, metodologi instalasi, dan prosedur kerjasama korporat bersama PT. Kreatif Global Solusindo.') ?>
            </p>
            <?php if (!empty($page_data['content'])): ?>
                <div class="mt-3 text-xs text-slate-600 leading-relaxed prose max-w-none" data-live-page-slug="faq" data-live-page-field="content">
                    <?= sanitize_rich_html($page_data['content']) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Category Tabs -->
        <div class="flex flex-wrap items-center justify-center gap-2 mb-10" id="faqCategoryTabs">
            <?php foreach ($faq_categories as $c_idx => $cat_label): ?>
                <button
                    type="button"
                    data-faq-cat="<?= e($cat_label) ?>"
                    class="faq-tab-btn px-4 py-2 text-xs font-medium rounded-lg transition-colors cursor-pointer <?= $c_idx === 0 ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200' ?>"
                >
                    <?= e($cat_label) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- FAQ Accordion List -->
        <div class="space-y-3" id="faqAccordionList">
            <?php foreach ($faqs as $idx => $item):
                $faq_id = (int)($item['id'] ?? ($idx + 1));
                $cat = $item['normalized_category'];
                $question = (string)($item['question'] ?? ($item['q'] ?? ''));
                $answer = (string)($item['answer'] ?? ($item['a'] ?? ''));
                $is_open = ($idx < 2);
            ?>
                <div
                    class="faq-item border rounded-xl transition-all duration-200 overflow-hidden <?= $is_open ? 'border-blue-300 bg-blue-50/20 shadow-xs' : 'border-slate-200 bg-white hover:border-slate-300' ?>"
                    data-faq-item-cat="<?= e($cat) ?>"
                    data-faq-open="<?= $is_open ? '1' : '0' ?>"
                >
                    <button
                        type="button"
                        class="faq-toggle-btn w-full text-left px-6 py-5 flex items-center justify-between gap-4 cursor-pointer"
                        aria-expanded="<?= $is_open ? 'true' : 'false' ?>"
                    >
                        <div class="space-y-1">
                            <span class="text-[11px] font-semibold text-blue-600 uppercase tracking-wider block" data-live-record="faqs:<?= $faq_id ?>:category">
                                <?= e($cat) ?>
                            </span>
                            <h3 class="text-base font-bold text-slate-900" data-live-record="faqs:<?= $faq_id ?>:question">
                                <?= e($question) ?>
                            </h3>
                        </div>
                        <div class="faq-chevron w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-transform duration-200 <?= $is_open ? 'rotate-180 bg-blue-600 text-white' : 'bg-slate-100 text-slate-500' ?>">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>

                    <div class="faq-answer px-6 pb-6 pt-1 text-slate-600 text-xs sm:text-sm leading-relaxed border-t border-slate-100 <?= $is_open ? '' : 'hidden' ?>">
                        <p data-live-record="faqs:<?= $faq_id ?>:answer"><?= e($answer) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Support & Direct Help Box -->
        <div class="mt-14 p-6 sm:p-8 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="space-y-1 text-center sm:text-left">
                <h4 class="text-base font-bold text-slate-900" data-live-setting="faq_banner_title">
                    <?= e(get_setting($pdo, 'faq_banner_title', 'Masih memiliki pertanyaan teknis atau butuh estimasi RAB proyek?')) ?>
                </h4>
                <p class="text-xs text-slate-600" data-live-setting="faq_banner_desc">
                    <?= e(get_setting($pdo, 'faq_banner_desc', 'Tim engineer dan konsultan teknis PT. KGS siap membantu memberikan solusi terbaik sesuai kondisi lapangan Anda.')) ?>
                </p>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <a
                    href="https://wa.me/<?= e($wa_clean) ?>?text=Halo%20PT%20Kreatif%20Global%20Solusindo,%20saya%20ingin%20konsultasi%20solusi%20teknologi"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg flex items-center gap-2 shadow-xs transition"
                >
                    <span>💬 WhatsApp Official</span>
                </a>
                <a
                    href="kontak.php#konsultasi"
                    class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg flex items-center gap-2 shadow-xs transition"
                >
                    <span>Form Konsultasi &nearr;</span>
                </a>
            </div>
        </div>
    </div>
</section>

<script>
(function() {
    var tabBtns = document.querySelectorAll('.faq-tab-btn');
    var faqItems = document.querySelectorAll('.faq-item');
    var activeCat = 'Semua';

    tabBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            activeCat = btn.getAttribute('data-faq-cat') || 'Semua';
            tabBtns.forEach(function(b) {
                var isAct = (b === btn);
                b.className = 'faq-tab-btn px-4 py-2 text-xs font-medium rounded-lg transition-colors cursor-pointer ' +
                    (isAct ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:text-slate-900 hover:bg-slate-200');
            });
            faqItems.forEach(function(item) {
                var itemCat = item.getAttribute('data-faq-item-cat') || 'Umum';
                if (activeCat === 'Semua' || itemCat === activeCat) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        });
    });

    faqItems.forEach(function(item) {
        var toggleBtn = item.querySelector('.faq-toggle-btn');
        var answerEl = item.querySelector('.faq-answer');
        var chevronEl = item.querySelector('.faq-chevron');
        if (!toggleBtn || !answerEl || !chevronEl) return;

        toggleBtn.addEventListener('click', function() {
            var isOpen = item.getAttribute('data-faq-open') === '1';
            var nextOpen = !isOpen;
            item.setAttribute('data-faq-open', nextOpen ? '1' : '0');
            toggleBtn.setAttribute('aria-expanded', nextOpen ? 'true' : 'false');
            if (nextOpen) {
                item.className = 'faq-item border rounded-xl transition-all duration-200 overflow-hidden border-blue-300 bg-blue-50/20 shadow-xs';
                answerEl.classList.remove('hidden');
                chevronEl.className = 'faq-chevron w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-transform duration-200 rotate-180 bg-blue-600 text-white';
            } else {
                item.className = 'faq-item border rounded-xl transition-all duration-200 overflow-hidden border-slate-200 bg-white hover:border-slate-300';
                answerEl.classList.add('hidden');
                chevronEl.className = 'faq-chevron w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-transform duration-200 bg-slate-100 text-slate-500';
            }
        });
    });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
