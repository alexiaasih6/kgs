<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

$about_page_data = get_page_by_slug($pdo, 'tentang-kami');
$page_title = ($about_page_data['title'] ?: 'Tentang Kami - Sejarah, Visi Misi & Legalitas') . ' - PT. Kreatif Global Solusindo';
$page_description = $about_page_data['summary'] ?: 'Profil resmi PT. Kreatif Global Solusindo (Creative Com). Berdiri sejak 1997 di Palembang, bertransformasi menjadi PT pada 2013, dan memegang izin resmi ISP KGSNet pada 2020.';

$timelines = get_company_timelines($pdo);
$certifications = get_company_certifications($pdo);
$partners = get_company_partners($pdo);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header: Profile -->
<section class="bg-slate-900 text-white py-16 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="text-blue-400 font-bold text-xs uppercase tracking-widest">PROFILE PERUSAHAAN</span>
        <h1 data-live-page-slug="tentang-kami" data-live-page-field="title" class="text-3xl sm:text-4xl font-extrabold mt-2"><?= e($about_page_data['title'] ?: 'Tentang PT. Kreatif Global Solusindo') ?></h1>
        <p data-live-page-slug="tentang-kami" data-live-page-field="summary" class="text-slate-300 text-sm sm:text-base mt-3 max-w-4xl leading-relaxed">
            <?= e($about_page_data['summary'] ?: 'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.') ?>
        </p>
    </div>
</section>

<!-- Profile, VISI, BUDAYA & KOMITMEN -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <!-- Profile Card -->
        <div class="p-7 rounded-2xl bg-slate-50 border border-slate-200">
            <span class="px-3 py-1 bg-blue-100 text-blue-800 font-extrabold text-xs rounded-md uppercase tracking-wider">Profile</span>
            <p class="text-base text-slate-800 leading-relaxed font-medium mt-4" data-live-setting="about_intro_p1">
                <?= e(get_setting($pdo, 'about_intro_p1', 'Creative Com, dengan badan usaha CV. Creative berkantor di Palembang berdiri sejak tahun 1997. Selanjutnya di 2013 ini ditingkatkan menjadi PT. Kreatif Global Solusindo. Dengan didukung team teknisi yang terlatih dan berpengalaman, melayani berbagai kebutuhan masyarakat, baik perseorangan maupun kebutuhan kantor.')) ?>
            </p>
        </div>

        <!-- VISI, BUDAYA & KOMITMEN Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-slate-50/80 p-7 rounded-2xl border border-slate-200 shadow-2xs">
                <span class="px-3 py-1 bg-blue-50 text-blue-700 font-extrabold text-xs rounded-md uppercase tracking-wider">VISI</span>
                <p class="text-sm sm:text-base text-slate-700 leading-relaxed mt-4" data-live-setting="vision">
                    <?= e(get_setting($pdo, 'vision', 'Menjadi perusahaan yang terdepan dan terpecaya serta mampu memberikan layanan yang baik dan berkualitas tinggi serta terjangkau oleh pasar, baik lokal maupun regional. Mampu memberikan solusi yang tepat kepada konsumen sesuai dengan kebutuhan konsumen. Misi kami membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kewirausahaan yang tinggi baik secara internal maupun eksternal sehingga memberikan dampak yang positif bagi kemajuan perekonomian masyarakat umumnya dan kepada anggota team Creative pada khususnya.')) ?>
                </p>
            </div>

            <div class="bg-slate-50/80 p-7 rounded-2xl border border-slate-200 shadow-2xs">
                <span class="px-3 py-1 bg-emerald-50 text-emerald-700 font-extrabold text-xs rounded-md uppercase tracking-wider">BUDAYA</span>
                <p class="text-sm sm:text-base text-slate-700 leading-relaxed mt-4" data-live-setting="culture">
                    <?= e(get_setting($pdo, 'culture', 'Mengembangkan budaya kerja yang mandiri, kreatif, dan profesional serta didukung pola hubungan kerja yang positif atas azas kekeluargaan. Menganut model organisasi fungsional, setiap orang adalah bagian dari Tim yang menjalankan tugasnya berdasarkan deskripsi fungsi. Tidak tergantung perintah dan hirarki, setiap orang bebas berinisiatif. Lingkungan mendukung setiap orang mencapai prestasi terbaik. Tim memberikan dorongan, stimulasi dan motivasi dalam orientasi pelaksanaan tugas secara efektif dan konstruktif. Tujuan tim meningkatkan produktifitas dan hasil maksimal kepada pelanggan.')) ?>
                </p>
            </div>

            <div class="bg-slate-50/80 p-7 rounded-2xl border border-slate-200 shadow-2xs">
                <span class="px-3 py-1 bg-amber-50 text-amber-700 font-extrabold text-xs rounded-md uppercase tracking-wider">KOMITMEN</span>
                <p class="text-sm sm:text-base text-slate-700 leading-relaxed mt-4" data-live-setting="commitment">
                    <?= e(get_setting($pdo, 'commitment', 'Kami percaya, kepuasan pelanggan diperoleh dari penyelesaian masalah secara konstruktif, cepat dan tepat. Tugas kami memberikan solusi dan layanan terbaik sesuai kondisi setiap pelanggan. Kepuasan Pelanggan adalah target utama dan promosi yang efektif.')) ?>
                </p>
            </div>
        </div>

        <!-- Sejarah Perjalanan & Alamat Kantor -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start pt-4">
            <div class="lg:col-span-5">
                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                    <div class="text-xs text-blue-600 font-extrabold uppercase tracking-wider">Alamat Kantor</div>
                    <div class="text-sm font-bold text-slate-900" data-live-setting="address_palembang"><?= e(get_setting($pdo, 'address_palembang', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113')) ?></div>
                    <div class="text-xs text-slate-600">Telp: <span data-live-setting="phone_palembang"><?= e(get_setting($pdo, 'phone_palembang', '0711-316088')) ?></span> | WA: <span data-live-setting="whatsapp"><?= e(get_setting($pdo, 'whatsapp', '0811-782119')) ?></span></div>
                </div>
            </div>

            <!-- Timeline Perjalanan (Dinamis dari Tabel `company_timelines`) -->
            <div class="lg:col-span-7 bg-slate-900 text-white rounded-2xl p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-bold" data-live-setting="about_timeline_heading"><?= e(get_setting($pdo, 'about_timeline_heading', 'Sejarah Perjalanan Perusahaan')) ?></h3>
                    <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                        <a href="admin/bagian-halaman.php?tab=sejarah_visi" class="text-[11px] font-bold text-emerald-400 hover:underline">Edit Sejarah &rarr;</a>
                    <?php endif; ?>
                </div>
                <div class="space-y-5 border-l-2 border-blue-500/40 pl-5 text-xs">
                    <?php foreach ($timelines as $tl): ?>
                        <div class="relative">
                            <span class="w-3 h-3 rounded-full <?= !empty($tl['is_highlighted']) ? 'bg-emerald-400' : 'bg-blue-500' ?> absolute -left-[27px] top-1"></span>
                            <strong class="<?= !empty($tl['is_highlighted']) ? 'text-emerald-400' : 'text-blue-400' ?> text-sm block" data-live-record="company_timelines:<?= (int)$tl['id'] ?>:year_label"><?= e($tl['year_label']) ?></strong>
                            <span class="font-semibold text-white" data-live-record="company_timelines:<?= (int)$tl['id'] ?>:title"><?= e($tl['title']) ?></span>
                            <p class="text-slate-400 mt-0.5" data-live-record="company_timelines:<?= (int)$tl['id'] ?>:description"><?= e($tl['description']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Sertifikasi & Mitra Resmi (Dinamis dari Tabel `company_certifications` & `company_partners`) -->
<?php
$about_raw_cert_badges = get_setting($pdo, 'footer_cert_badges', 'APJII Official Member :: https://apjii.or.id | KGSNet ISP Resmi :: https://www.kgs.net.id | Panasonic Authorized :: # | NETVIEL Certified :: #');
$about_parsed_badges = function_exists('parse_footer_cert_badges') ? parse_footer_cert_badges((string)$about_raw_cert_badges) : [];
?>
<section class="py-16 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <!-- Baris Lisensi, Sertifikasi & Keanggotaan (Tulisan, Link & Logo) -->
        <div class="p-6 rounded-2xl bg-slate-900 text-white border border-slate-800 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-blue-400" data-live-setting="footer_cert_heading">
                        <?= e(get_setting($pdo, 'footer_cert_heading', 'Lisensi, Sertifikasi & Keanggotaan')) ?>
                    </span>
                    <span class="text-[11px] font-bold text-emerald-400" data-live-setting="footer_cert_card_status">
                        <?= e(get_setting($pdo, 'footer_cert_card_status', '✓ Terverifikasi & Berlisensi Resmi')) ?>
                    </span>
                </div>
                <p class="text-xs text-slate-400 mt-1">Legalitas resmi penyelenggara jasa internet, keanggotaan asosiasi, dan otorisasi prinsipal perangkat.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <?php foreach ($about_parsed_badges as $b_idx => $b_item):
                    $b_label = $b_item['label'] ?? '';
                    $b_url = trim((string)($b_item['url'] ?? ''));
                    $b_logo = trim((string)($b_item['logo'] ?? ''));
                    if ($b_label === '') continue;
                    $has_valid_link = ($b_url !== '' && $b_url !== '#');
                ?>
                    <?php if ($has_valid_link): ?>
                        <a href="<?= e(sanitize_nav_url($b_url)) ?>" target="<?= preg_match('/^https?:\/\//i', $b_url) ? '_blank' : '_self' ?>" rel="noopener noreferrer" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-blue-950 border border-slate-700 hover:border-blue-500 text-slate-200 hover:text-white font-semibold inline-flex items-center gap-2 transition">
                            <?php if ($b_logo !== ''): ?>
                                <img src="<?= e($b_logo) ?>" alt="<?= e($b_label) ?>" class="w-4 h-4 object-contain rounded bg-white p-0.5">
                            <?php endif; ?>
                            <span><?= e($b_label) ?></span>
                            <span class="text-blue-400 text-[10px]">&nearr;</span>
                        </a>
                    <?php else: ?>
                        <span class="px-3 py-1.5 rounded-xl bg-slate-800 border border-slate-700 <?= $b_idx === 1 ? 'text-emerald-400' : 'text-slate-200' ?> font-semibold inline-flex items-center gap-2">
                            <?php if ($b_logo !== ''): ?>
                                <img src="<?= e($b_logo) ?>" alt="<?= e($b_label) ?>" class="w-4 h-4 object-contain rounded bg-white p-0.5">
                            <?php endif; ?>
                            <span><?= e($b_label) ?></span>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                    <a href="admin/bagian-halaman.php?tab=sertifikasi_mitra" class="px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-[11px] transition">+ Atur Link &amp; Tulisan</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-extrabold text-slate-900" data-live-setting="about_cert_heading"><?= e(get_setting($pdo, 'about_cert_heading', 'Sertifikasi & Keanggotaan')) ?></h2>
                    <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                        <a href="admin/bagian-halaman.php?tab=sertifikasi_mitra" class="text-xs font-bold text-blue-600 hover:underline">+ Edit Sertifikasi, Logo &amp; Link</a>
                    <?php endif; ?>
                </div>
                <div class="space-y-3 text-xs">
                    <?php foreach ($certifications as $cert):
                        $cert_logo = trim((string)($cert['logo_url'] ?? ''));
                        $cert_link = trim((string)($cert['link_url'] ?? ''));
                        $cert_sub  = trim((string)($cert['subtitle'] ?? ''));
                    ?>
                        <div class="p-4 rounded-xl border border-slate-200 hover:border-blue-300 transition flex items-start gap-4 bg-white shadow-2xs">
                            <?php if ($cert_logo !== ''): ?>
                                <img src="<?= e($cert_logo) ?>" alt="<?= e($cert['title']) ?>" class="w-12 h-12 rounded-xl object-contain bg-white border border-slate-200 p-1 shrink-0">
                            <?php else: ?>
                                <span class="px-3 py-2.5 bg-blue-50 text-blue-700 font-extrabold rounded-xl shrink-0 text-center min-w-[3.25rem]" data-live-record="company_certifications:<?= (int)$cert['id'] ?>:badge_code"><?= e($cert['badge_code']) ?></span>
                            <?php endif; ?>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <strong class="text-slate-900 block text-sm" data-live-record="company_certifications:<?= (int)$cert['id'] ?>:title"><?= e($cert['title']) ?></strong>
                                    <?php if ($cert_link !== ''): ?>
                                        <a href="<?= e(sanitize_nav_url($cert_link)) ?>" target="<?= preg_match('/^https?:\/\//i', $cert_link) ? '_blank' : '_self' ?>" rel="noopener noreferrer" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 shrink-0 inline-flex items-center gap-0.5">
                                            <span>Buka Link</span>
                                            <span>&nearr;</span>
                                        </a>
                                    <?php endif; ?>
                                </div>
                                <?php if ($cert_sub !== ''): ?>
                                    <div class="text-[11px] font-semibold text-blue-600 mt-0.5" data-live-record="company_certifications:<?= (int)$cert['id'] ?>:subtitle"><?= e($cert_sub) ?></div>
                                <?php endif; ?>
                                <span class="text-slate-500 block mt-0.5 leading-relaxed" data-live-record="company_certifications:<?= (int)$cert['id'] ?>:description"><?= e($cert['description']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-extrabold text-slate-900" data-live-setting="about_partner_heading"><?= e(get_setting($pdo, 'about_partner_heading', 'Mitra Kerja & Dukungan Brand')) ?></h2>
                    <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                        <a href="admin/bagian-halaman.php?tab=sertifikasi_mitra" class="text-xs font-bold text-blue-600 hover:underline">+ Edit Mitra, Logo &amp; Link</a>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-slate-600 mb-4" data-live-setting="about_partner_sub"><?= e(get_setting($pdo, 'about_partner_sub', 'Didukung oleh ekosistem vendor perangkat keras dan telekomunikasi terkemuka:')) ?></p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs font-semibold text-slate-700">
                    <?php foreach ($partners as $pt):
                        $pt_logo = trim((string)($pt['logo_url'] ?? ''));
                        $pt_link = trim((string)($pt['link_url'] ?? ''));
                    ?>
                        <div class="p-3.5 bg-slate-50 hover:bg-white border border-slate-200 hover:border-blue-300 rounded-xl text-center transition flex flex-col items-center justify-between gap-2 shadow-2xs">
                            <?php if ($pt_logo !== ''): ?>
                                <img src="<?= e($pt_logo) ?>" alt="<?= e($pt['brand_name']) ?>" class="h-10 w-auto max-w-[110px] object-contain mx-auto bg-white p-1 rounded-lg border border-slate-100">
                            <?php endif; ?>
                            <div>
                                <div class="font-bold text-slate-900" data-live-record="company_partners:<?= (int)$pt['id'] ?>:brand_name"><?= e($pt['brand_name']) ?></div>
                                <div class="text-[11px] text-blue-600 font-medium mt-0.5" data-live-record="company_partners:<?= (int)$pt['id'] ?>:role_text"><?= e($pt['role_text']) ?></div>
                            </div>
                            <?php if ($pt_link !== ''): ?>
                                <a href="<?= e(sanitize_nav_url($pt_link)) ?>" target="<?= preg_match('/^https?:\/\//i', $pt_link) ? '_blank' : '_self' ?>" rel="noopener noreferrer" class="text-[11px] font-bold text-blue-600 hover:underline inline-flex items-center gap-0.5 mt-0.5">
                                    <span>Kunjungi Mitra &nearr;</span>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
