<?php
require_once __DIR__ . '/config/database.php';

$about_page_data = get_page_by_slug($pdo, 'tentang-kami');
$page_title = ($about_page_data['title'] ?: 'Tentang Kami - Sejarah, Visi Misi & Legalitas') . ' - PT. Kreatif Global Solusindo';
$page_description = $about_page_data['summary'] ?: 'Profil resmi PT. Kreatif Global Solusindo (Creative Com). Berdiri sejak 1997 di Palembang, bertransformasi menjadi PT pada 2013, dan memegang izin resmi ISP KGSNet pada 2020.';

$timelines = get_company_timelines($pdo);
$certifications = get_company_certifications($pdo);
$partners = get_company_partners($pdo);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<section class="bg-slate-900 text-white py-16 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <span class="text-blue-400 font-bold text-xs uppercase tracking-widest">Profil Korporasi</span>
        <h1 data-live-page-slug="tentang-kami" data-live-page-field="title" class="text-3xl sm:text-4xl font-extrabold mt-2"><?= e($about_page_data['title'] ?: 'Tentang PT. Kreatif Global Solusindo') ?></h1>
        <p data-live-page-slug="tentang-kami" data-live-page-field="summary" class="text-slate-300 text-sm sm:text-base mt-3 max-w-3xl leading-relaxed">
            <?= e($about_page_data['summary'] ?: 'Perjalanan dedikasi teknologi sejak didirikan dengan nama Creative Com pada tahun 1997 di Palembang hingga bertransformasi menjadi System Integrator & ISP berlisensi resmi APJII.') ?>
        </p>
    </div>
</section>

<!-- Sejarah & Identitas Perusahaan -->
<section class="py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            <div class="lg:col-span-7 space-y-4 text-sm text-slate-600 leading-relaxed">
                <h2 class="text-2xl font-extrabold text-slate-900 mb-3" data-live-setting="about_intro_title">
                    <?= e(get_setting($pdo, 'about_intro_title', 'Sejak 1997 Melayani Kebutuhan Teknologi Korporasi')) ?>
                </h2>
                <p data-live-setting="about_intro_p1">
                    <?= e(get_setting($pdo, 'about_intro_p1', 'Creative Com adalah nama tempat usaha yang menjual produk IT dengan legalitas CV. CREATIVE, berdomisili di Palembang sejak 1997 dan sejak 2013 resmi bertransformasi menjadi PT. KREATIF GLOBAL SOLUSINDO (KGS).')) ?>
                </p>
                <p data-live-setting="about_intro_p2">
                    <?= e(get_setting($pdo, 'about_intro_p2', 'Pada awal berdirinya, Creative Com bergerak dalam bidang jasa perbaikan perangkat komputer dan jaringan. Kemudian di tahun 1998 merambah ke bidang penjualan perangkat wartel sebagai distributor merk QPHONE. Pada tahun 2002, Panasonic mempercayakan Creative Com sebagai salah satu dealer resminya di Palembang.')) ?>
                </p>
                <p data-live-setting="about_intro_p3">
                    <?= e(get_setting($pdo, 'about_intro_p3', 'Mengamati perkembangan teknologi dan kebutuhan industri, sejak 2004 kami melayani instalasi jaringan komputer (Cable/Wireless), radio komunikasi, PABX, serta pabrikasi tower antena. Sejak tahun 2020, PT. KGS resmi memiliki izin sebagai Penyelenggara Jasa Internet (ISP) dengan produk KGSNet dan tergabung menjadi anggota APJII.')) ?>
                </p>

                <div class="pt-4">
                    <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200">
                        <div class="text-xs text-blue-600 font-bold uppercase mb-1">Alamat Kantor</div>
                        <div class="text-xs font-semibold text-slate-800" data-live-setting="address_palembang"><?= e(get_setting($pdo, 'address_palembang', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113')) ?></div>
                        <div class="text-xs text-slate-500 mt-1">Telp: <span data-live-setting="phone_palembang"><?= e(get_setting($pdo, 'phone_palembang', '0711-316088 (Hunting)')) ?></span> | WA: <span data-live-setting="whatsapp"><?= e(get_setting($pdo, 'whatsapp', '0811-782119')) ?></span></div>
                    </div>
                </div>
            </div>

            <!-- Timeline Perjalanan (Dinamis dari Tabel `company_timelines`) -->
            <div class="lg:col-span-5 bg-slate-900 text-white rounded-2xl p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-lg font-bold">Sejarah Perjalanan Perusahaan</h3>
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

<!-- Visi, Misi, Budaya Kerja & Komitmen (Dinamis dari `site_settings`) -->
<section class="py-16 bg-slate-50 border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-7 rounded-2xl border border-slate-200 shadow-xs">
                <span class="px-3 py-1 bg-blue-50 text-blue-700 font-bold text-xs rounded-md">VISI</span>
                <h3 class="text-lg font-bold text-slate-900 mt-4 mb-2">Menjadi yang Terdepan & Terpercaya</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed" data-live-setting="vision">
                    <?= e(get_setting($pdo, 'vision', 'Menjadi perusahaan yang terdepan dan terpercaya serta mampu memberikan layanan yang baik dan berkualitas. Mampu memberikan solusi yang tepat sesuai dengan kebutuhan konsumen.')) ?>
                </p>
            </div>

            <div class="bg-white p-7 rounded-2xl border border-slate-200 shadow-xs">
                <span class="px-3 py-1 bg-blue-50 text-blue-700 font-bold text-xs rounded-md">MISI</span>
                <h3 class="text-lg font-bold text-slate-900 mt-4 mb-2">Kemandirian, Semangat & Kreativitas</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed" data-live-setting="mission">
                    <?= e(get_setting($pdo, 'mission', 'Membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kerja dan kreatifitas yang tinggi kepada setiap tim sehingga hasil yang dicapai berdampak positif bagi kemajuan dan kesejahteraan tim Creative Com (KGS) pada khususnya serta masyarakat luas pada umumnya.')) ?>
                </p>
            </div>

            <div class="bg-white p-7 rounded-2xl border border-slate-200 shadow-xs">
                <span class="px-3 py-1 bg-emerald-50 text-emerald-700 font-bold text-xs rounded-md">BUDAYA KERJA & ORGANISASI</span>
                <h3 class="text-lg font-bold text-slate-900 mt-4 mb-2">Organisasi Fungsional & Kekeluargaan</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed" data-live-setting="culture">
                    <?= e(get_setting($pdo, 'culture', 'Mengembangkan budaya kerja yang mandiri, kreatif, dan profesional serta didukung pola hubungan kerja yang positif atas azas kekeluargaan. Menganut model organisasi fungsional, setiap orang adalah bagian dari Tim yang menjalankan tugasnya berdasarkan deskripsi fungsi. Tidak tergantung perintah dan hirarki, setiap orang bebas berinisiatif.')) ?>
                </p>
            </div>

            <div class="bg-white p-7 rounded-2xl border border-slate-200 shadow-xs">
                <span class="px-3 py-1 bg-amber-50 text-amber-700 font-bold text-xs rounded-md">KOMITMEN KAMI</span>
                <h3 class="text-lg font-bold text-slate-900 mt-4 mb-2">Kepuasan Pelanggan Adalah Prioritas</h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed" data-live-setting="commitment">
                    <?= e(get_setting($pdo, 'commitment', 'Kami percaya kepuasan pelanggan diperoleh dari penyelesaian masalah secara konstruktif, cepat, dan tepat. Tugas kami memberikan solusi dan layanan terbaik sesuai kondisi setiap pelanggan. Kepuasan Pelanggan adalah target utama dan promosi yang efektif.')) ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Sertifikasi & Mitra Resmi (Dinamis dari Tabel `company_certifications` & `company_partners`) -->
<section class="py-16 bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-extrabold text-slate-900">Sertifikasi & Keanggotaan</h2>
                    <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                        <a href="admin/bagian-halaman.php?tab=sertifikasi_mitra" class="text-xs font-bold text-blue-600 hover:underline">+ Edit Sertifikasi</a>
                    <?php endif; ?>
                </div>
                <div class="space-y-3 text-xs">
                    <?php foreach ($certifications as $cert): ?>
                        <div class="p-4 rounded-xl border border-slate-200 flex items-center gap-4">
                            <span class="px-3 py-2 bg-blue-50 text-blue-700 font-extrabold rounded-lg shrink-0" data-live-record="company_certifications:<?= (int)$cert['id'] ?>:badge_code"><?= e($cert['badge_code']) ?></span>
                            <div>
                                <strong class="text-slate-900 block text-sm" data-live-record="company_certifications:<?= (int)$cert['id'] ?>:title"><?= e($cert['title']) ?></strong>
                                <span class="text-slate-500" data-live-record="company_certifications:<?= (int)$cert['id'] ?>:description"><?= e($cert['description']) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-extrabold text-slate-900">Mitra Kerja & Dukungan Brand</h2>
                    <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                        <a href="admin/bagian-halaman.php?tab=sertifikasi_mitra" class="text-xs font-bold text-blue-600 hover:underline">+ Edit Mitra</a>
                    <?php endif; ?>
                </div>
                <p class="text-xs text-slate-600 mb-4">Didukung oleh ekosistem vendor perangkat keras dan telekomunikasi terkemuka:</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs font-semibold text-slate-700">
                    <?php foreach ($partners as $pt): ?>
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-center">
                            <div class="font-bold text-slate-900" data-live-record="company_partners:<?= (int)$pt['id'] ?>:brand_name"><?= e($pt['brand_name']) ?></div>
                            <div class="text-[10px] text-blue-600 font-medium mt-0.5" data-live-record="company_partners:<?= (int)$pt['id'] ?>:role_text"><?= e($pt['role_text']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
