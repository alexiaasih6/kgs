<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$page_data = get_page_by_slug($pdo, 'faq', [
    'title' => 'Pertanyaan Umum & Jawaban Teknis',
    'summary' => 'Informasi lengkap seputar legalitas izin, jaminan SLA, cakupan area, metodologi instalasi, dan prosedur kerjasama korporat bersama PT. Kreatif Global Solusindo.',
    'meta_title' => 'Tanya Jawab (FAQ) & Bantuan Teknis - PT. KGS',
    'meta_description' => 'Pertanyaan yang sering diajukan mengenai layanan ISP KGSNet, instalasi jaringan fiber optic, sertifikasi grounding < 2 Ohm, dan prosedur survei PT. KGS.'
]);

$page_title = $page_data['meta_title'] ?: $page_data['title'];
$page_desc = $page_data['meta_description'] ?: $page_data['summary'];
require_once __DIR__ . '/includes/header.php';

// Ambil daftar FAQ dinamis dari tabel database `faqs`
$faqs = get_faqs_list($pdo, true);
$wa_clean = format_wa_number(get_setting($pdo, 'whatsapp', '0811-782119'));
?>

<!-- Hero Header -->
<section class="bg-slate-900 text-white py-16 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl space-y-4">
            <span class="px-3 py-1 rounded-full bg-blue-500/15 border border-blue-400/30 text-blue-300 text-xs font-semibold uppercase tracking-wider">
                Pusat Informasi & Tanya Jawab (FAQ)
            </span>
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight" data-live-page-slug="faq" data-live-page-field="title"><?= e($page_data['title']) ?></h1>
            <p class="text-slate-300 text-base leading-relaxed" data-live-page-slug="faq" data-live-page-field="summary">
                <?= e($page_data['summary']) ?>
            </p>
            <?php if (!empty($page_data['content'])): ?>
                <div class="pt-2 text-xs text-slate-400 leading-relaxed prose prose-invert max-w-none" data-live-page-slug="faq" data-live-page-field="content">
                    <?= sanitize_rich_html($page_data['content']) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- FAQ List (Dinamis dari Tabel `faqs`) -->
<section class="py-16 bg-slate-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($faqs as $idx => $item):
                $faq_id = (int)($item['id'] ?? ($idx + 1));
                $cat = trim((string)($item['category'] ?? ($item['cat'] ?? 'Informasi Teknis')));
                $question = (string)($item['question'] ?? ($item['q'] ?? ''));
                $answer = (string)($item['answer'] ?? ($item['a'] ?? ''));
            ?>
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs hover:border-blue-300 transition space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200" data-live-record="faqs:<?= $faq_id ?>:category">
                            <?= e($cat) ?>
                        </span>
                        <span class="text-xs font-mono text-slate-400">Q<?= $idx + 1 ?></span>
                    </div>
                    <h2 class="text-base font-extrabold text-slate-900 leading-snug" data-live-record="faqs:<?= $faq_id ?>:question">
                        <?= e($question) ?>
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed" data-live-record="faqs:<?= $faq_id ?>:answer">
                        <?= e($answer) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Direct Engineer Help Box -->
        <div class="bg-slate-900 text-white rounded-3xl p-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="space-y-1.5">
                <h3 class="text-xl font-bold">Memiliki Pertanyaan Teknis Spesifik Lainnya?</h3>
                <p class="text-xs text-slate-300">Konsultasikan topologi jaringan, frekuensi radio, atau kebutuhan menara Anda langsung dengan tim ahli KGS.</p>
            </div>
            <div class="flex flex-wrap gap-3 shrink-0">
                <a href="kontak.php" class="px-5 py-3 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition">
                    Kirim Pertanyaan &rarr;
                </a>
                <a href="https://wa.me/<?= e($wa_clean) ?>" target="_blank" rel="noopener noreferrer" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition">
                    💬 Tanya via WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
