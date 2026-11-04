<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$default_harga_title = 'Paket Internet Service Provider (ISP) & Segmen Produk Layanan Lainnya';
$default_harga_summary = 'Daftar paket berlangganan 3 Segmen Internet Service Provider (Internet Dedicated, Internet Bisnis, dan Home Internet) berizin resmi Kominfo RI, serta solusi segmen Produk & Layanan lainnya (Security, Networking, PBX, dan Telecommunication) berbasis konsultasi teknis & survei lokasi.';

$page_data = get_page_by_slug($pdo, 'harga', [
    'title' => $default_harga_title,
    'summary' => $default_harga_summary,
    'meta_title' => 'Paket Internet Service Provider (ISP) & Segmen Produk Layanan Lainnya - PT. KGS',
    'meta_description' => 'Daftar paket 3 Segmen Internet Service Provider (Internet Dedicated, Internet Bisnis, Home Internet) serta solusi segmen Security, Networking, PBX, dan Telecommunication PT. Kreatif Global Solusindo.'
]);

$legacy_titles = [
    'Pilihan Paket Solusi & Layanan B2B',
    '3 Segmen Paket Internet & Solusi Product & Service',
    '3 Segmen Paket Internet Service Provider (ISP)',
    '3 Segmen Paket Internet Service Provider'
];
$legacy_summaries = [
    'Pilihan paket Internet Service Provider resmi Kominfo RI dalam 3 Segmen (Internet Dedicated, Internet Bisnis, dan Home Internet).',
    'Pilihan paket Internet Service Provider resmi Kominfo RI dalam 3 Segmen (Internet Dedicated, Internet Bisnis, dan Home Internet) serta solusi proyek terintegrasi.',
    'Pilihan paket Internet Dedicated (UMKM, Sekolah, Corporate), Internet Bisnis (10 Mbps – 200 Mbps), dan Home Internet (10 Mbps – 200 Mbps) berizin resmi Kominfo RI.'
];

$resolved_title = (!empty($page_data['title']) && !in_array(trim($page_data['title']), $legacy_titles, true))
    ? $page_data['title']
    : $default_harga_title;

$resolved_summary = (!empty($page_data['summary']) && !in_array(trim($page_data['summary']), $legacy_summaries, true))
    ? $page_data['summary']
    : $default_harga_summary;

$page_title = $resolved_title . ' - PT. Kreatif Global Solusindo';
$page_desc = $resolved_summary;
require_once __DIR__ . '/includes/header.php';

// Ambil daftar paket harga dinamis dari tabel database `pricing_packages`
$pricing_packages = get_pricing_packages($pdo, true);
$wa_clean = format_wa_number(get_setting($pdo, 'whatsapp', '0811-782119'));

// Segmen Produk dan Layanan Lainnya selain 3 Segmen Paket Internet Service Provider (ISP) — Tanpa Memasukkan Harga
$other_product_service_segments = [
    [
        'code' => 'SEC',
        'badge_class' => 'bg-rose-50 text-rose-700 border-rose-200',
        'icon_class' => 'bg-rose-50 text-rose-700',
        'segment_label' => 'Segmen Security • System Integrator',
        'name' => 'Security Solution',
        'subtitle' => 'Solusi sistem pengamanan gedung, pengawasan kamera tertutup, kontrol akses pintu elektronik, dan proteksi kebakaran terintegrasi.',
        'target_audience' => 'Gedung Perkantoran, Perbankan, Rumah Sakit, Pabrik & Kawasan Industri',
        'scheme_label' => 'Konsultasi Teknis & Survei Lokasi (Sesuai Kebutuhan Proyek)',
        'items' => [
            [
                'name' => 'FIRE & ALARM SECURITY',
                'description' => 'Instalasi Master Control Fire Alarm (MCFA), detektor asap & panas (Smoke/Heat Detector), Manual Call Point, sirine evakuasi, serta sensor alarm anti-intrusi.'
            ],
            [
                'name' => 'CCTV (Closed Circuit Television)',
                'description' => 'Sistem pengawasan kamera keamanan CCTV Analog HD & IP Network Camera beresolusi tinggi dengan konfigurasi NVR/DVR dan monitoring terpusat.'
            ],
            [
                'name' => 'Access Door (Access Control)',
                'description' => 'Sistem kunci pintu elektronik menggunakan akses Kartu ID (RFID Proximity/Mifare), PIN keypad, dan biometrik sidik jari/wajah.'
            ],
            [
                'name' => 'Video Door Phone',
                'description' => 'Sistem interkom komunikasi audio-visual pintu masuk dua arah untuk verifikasi tamu yang terintegrasi dengan kunci elektrik.'
            ]
        ],
        'brands' => ['Sony', 'Hikvision', 'Dahua', 'Panasonic', 'BOSCH', 'Honeywell', 'Rosslare', 'HID', 'Commax'],
        'recommended_for' => 'Pengamanan fasilitas vital, ruang server, brankas perbankan, rumah sakit, hotel, dan area pabrik industri'
    ],
    [
        'code' => 'NET',
        'badge_class' => 'bg-blue-50 text-blue-700 border-blue-200',
        'icon_class' => 'bg-blue-50 text-blue-700',
        'segment_label' => 'Segmen Networking • Infrastruktur IT',
        'name' => 'Networking & Fiber Optik',
        'subtitle' => 'Perancangan, instalasi, dan manajemen infrastruktur jaringan kabel serat optik (Fiber Optik), LAN, Router enterprise, dan transmisi Wireless.',
        'target_audience' => 'Perkantoran, Kampus, Rumah Sakit, Perkebunan, Pelabuhan & Gudang',
        'scheme_label' => 'Konsultasi Teknis & Survei Lokasi (Sesuai Topologi Jaringan)',
        'items' => [
            [
                'name' => 'Router & Manajemen Jaringan',
                'description' => 'Konfigurasi Router enterprise untuk manajemen bandwidth (QoS), pembagian VLAN, Firewall, VPN antar cabang, Load Balancing, Failover, dan routing BGP/OSPF.'
            ],
            [
                'name' => 'Wireless (Jaringan Nirkabel)',
                'description' => 'Instalasi jaringan Wireless LAN (Wi-Fi Indoor/Outdoor seamless roaming) serta link transmisi radio Point-to-Point (PTP) & Point-to-Multipoint (PTMP) jarak jauh.'
            ],
            [
                'name' => 'Fiber Optik & Structured Cabling',
                'description' => 'Penarikan, terminasi Fusion Splicing, dan uji OTDR jaringan kabel serat optik (Fiber Optik) backbone berkecepatan gigabit serta instalasi kabel LAN UTP/STP.'
            ]
        ],
        'brands' => ['MikroTik (Certified MTCNA)', 'Ubiquiti', 'Cisco', 'Ruijie', 'Cambium Networks', 'NETVIEL (Authorized Reseller)'],
        'recommended_for' => 'Interkoneksi jaringan antar gedung/pabrik, distribusi Wi-Fi korporat, dan link komunikasi data site remote'
    ],
    [
        'code' => 'PBX',
        'badge_class' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
        'icon_class' => 'bg-indigo-50 text-indigo-700',
        'segment_label' => 'Segmen PBX • Komunikasi Korporasi',
        'name' => 'PBX Communication System',
        'subtitle' => 'Sistem sentral komunikasi telepon internal dan eksternal kantor mulai dari PABX Analog, Hybrid, hingga IP – PBX terpadu.',
        'target_audience' => 'Kantor Operasional, Hotel, Rumah Sakit, Perbankan & Perusahaan Multi-Cabang',
        'scheme_label' => 'Konsultasi Teknis & Survei Lokasi (Sesuai Kapasitas Ekstensi)',
        'items' => [
            [
                'name' => 'ANALOGUE PBX',
                'description' => 'Sistem sentral telepon PABX Analog yang handal untuk distribusi ekstensi antar ruangan/lantai dengan fitur Call Transfer, Hunting Group, Toll Restriction & Billing.'
            ],
            [
                'name' => 'HYBRID PBX',
                'description' => 'Sistem sentral telepon Hybrid yang memadukan fleksibilitas jalur telepon analog, digital key telephone, Auto Attendant (DISA/IVR), NurseCall/Paging, dan ekstensi IP.'
            ],
            [
                'name' => 'IP – PBX',
                'description' => 'Perangkat switching komunikasi telepon dan data berbasis protokol internet (VoIP / SIP Trunk) yang menggabungkan jaringan suara, data, dan video antar cabang.'
            ]
        ],
        'brands' => ['Panasonic (Authorized Dealer)', 'NEC', 'DINSTAR', 'Grandstream', 'Yeastar'],
        'recommended_for' => 'Komunikasi telepon terpusat hotel, rumah sakit, kantor pemerintahan, dan interkoneksi suara gratis antar cabang'
    ],
    [
        'code' => 'TEL',
        'badge_class' => 'bg-amber-50 text-amber-700 border-amber-200',
        'icon_class' => 'bg-amber-50 text-amber-700',
        'segment_label' => 'Segmen Telecommunication • Proteksi Petir & Tower',
        'name' => 'Telecommunication, Lightning Protector & Tower',
        'subtitle' => 'Solusi perangkat telekomunikasi radio lapangan, sistem konferensi ruang rapat, instalasi penyalur petir & grounding, serta fabrikasi menara telekomunikasi.',
        'target_audience' => 'Perkebunan Sawit, Pertambangan, Pembangkit Listrik (PLN), BUMN & Pelabuhan',
        'scheme_label' => 'Konsultasi Teknis & Survei Lokasi (Sesuai Spesifikasi Lapangan)',
        'items' => [
            [
                'name' => 'LIGHTNING PROTECTOR (Penyalur Petir & Grounding)',
                'description' => 'Instalasi batang penyalur petir, kabel konduktor tembaga, tempat pembumian (grounding) dengan uji Earth Tester < 2 Ohm, serta Surge Arrester panel MDP.'
            ],
            [
                'name' => 'Handy Talky & Radio Komunikasi',
                'description' => 'Pengadaan dan instalasi radio komunikasi VHF/UHF/SSB, Base Station RIG, Marine Radio, stasiun Repeater, serta AIS (Automatic Identification System) Maritim.'
            ],
            [
                'name' => 'CONFERENCE SYSTEM',
                'description' => 'Instalasi sistem audio-video konferensi ruang rapat dan aula menggunakan mikrofon Chairman & Delegate, kamera PTZ, dan tata suara terintegrasi.'
            ],
            [
                'name' => 'Guy Wire Tower',
                'description' => 'Fabrikasi mandiri di workshop internal dan ereksi menara segitiga yang ditopang dengan tali-tali baja (guywire) yang diangkurkan pada landasan tanah.'
            ],
            [
                'name' => 'SELF SUPPORTING TOWER MONOPOLE',
                'description' => 'Konstruksi dan ereksi menara mandiri Self Supporting Tower (SST) serta tiang tunggal Monopole berintegritas struktural tinggi tanpa bentangan kawat skur.'
            ]
        ],
        'brands' => ['Creative Com Workshop Fabrication', 'KURN', 'ERICO', 'VIKING', 'Motorola', 'ICOM', 'Kenwood', 'BOSCH', 'TOA'],
        'recommended_for' => 'Pembangunan infrastruktur menara komunikasi, proteksi sambaran petir fasilitas industri, dan radio komunikasi jarak jauh'
    ]
];
?>

<!-- Hero Header -->
<section class="bg-slate-900 text-white py-16 border-b border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-7 space-y-4">
                <span class="px-3 py-1 rounded-full bg-blue-500/15 border border-blue-400/30 text-blue-300 text-xs font-semibold uppercase tracking-wider" data-live-setting="pricing_hero_kicker">
                    <?= e(get_setting($pdo, 'pricing_hero_kicker', 'Internet Service Provider (ISP) & Solusi Product & Service Terintegrasi')) ?>
                </span>
                <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight" data-live-page-slug="harga" data-live-page-field="title"><?= e($resolved_title) ?></h1>
                <p class="text-slate-300 text-base leading-relaxed" data-live-page-slug="harga" data-live-page-field="summary">
                    <?= e($resolved_summary) ?>
                </p>
            </div>

            <!-- Legalitas ISP Box -->
            <div class="lg:col-span-5 bg-slate-800/90 border border-slate-700 rounded-2xl p-6 space-y-3">
                <div class="flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-amber-300">
                    <span>📜 Legalitas Internet Service Provider:</span>
                </div>
                <ul class="space-y-2 text-xs text-slate-200 leading-relaxed">
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold shrink-0">–</span>
                        <span>Izin Penyelenggaraan Jasa Telekomunikasi Kominfo RI Nomor ; 674/TEL.02.02/2020 tanggal 19 Agustus 2020,</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold shrink-0">–</span>
                        <span>Surat Keterangan Laik Operasi Kominfo RI Nomor ; 375/TEL.04.02/2020 tanggal 27 Oktober 2020,</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold shrink-0">–</span>
                        <span>NIB (Nomor Induk Berusaha) ; 9120202100598.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- BAGIAN 1: 3 Segmen Paket Internet Service Provider (ISP) + BAGIAN 2: Segmen Produk & Layanan Lainnya -->
<section class="py-16 bg-slate-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">

        <!-- BAGIAN 1: 3 Segmen Paket Internet Service Provider (ISP) -->
        <div class="space-y-8">
            <div class="max-w-3xl">
                <span class="text-xs font-extrabold uppercase tracking-wider text-blue-600">
                    Bagian 1 · Layanan Koneksi Internet Resmi Kominfo RI
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">
                    3 Segmen Paket Internet Service Provider (ISP)
                </h2>
                <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                    Pilihan paket layanan koneksi internet cepat dan stabil dari PT. Kreatif Global Solusindo (KGSNet) untuk kebutuhan Dedicated (UMKM, Sekolah, Corporate), Bisnis, dan Rumah Tangga.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
                <!-- 1. Internet Dedicated -->
                <div class="bg-slate-900 text-white rounded-3xl border-2 border-blue-500 p-8 shadow-xl flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-blue-600 text-white text-[10px] font-extrabold uppercase tracking-widest px-3.5 py-1 rounded-bl-xl">
                        Segmen 1 • Dedicated
                    </div>
                    <div class="space-y-5">
                        <div class="flex items-center justify-between">
                            <span class="w-11 h-11 rounded-2xl bg-blue-600 text-white font-black flex items-center justify-center text-xs">ISP</span>
                            <span class="text-[11px] font-bold text-blue-300 bg-blue-500/20 px-3 py-1 rounded-full border border-blue-400/30">UMKM • Sekolah • Corporate</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-extrabold text-white">Internet Dedicated</h3>
                            <p class="text-xs text-slate-400 mt-1">Koneksi Internet Dedicated simetris berizin resmi Kominfo RI &amp; APJII.</p>
                        </div>
                        <div class="py-4 border-y border-slate-800">
                            <div class="text-xs text-slate-400 uppercase font-semibold">Harga Perbulan Mulai</div>
                            <div class="text-2xl font-extrabold text-white mt-0.5">Rp. 1.750.000 / bulan</div>
                            <div class="text-[11px] text-emerald-400 font-semibold mt-1">✓ Bandwidth Dedicated 1:1 &amp; SLA 99.5%</div>
                        </div>
                        <div class="space-y-3 text-xs">
                            <div class="p-3.5 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-between gap-2">
                                <div>
                                    <div class="font-extrabold text-white text-sm">10 Mbps</div>
                                    <div class="text-[11px] text-emerald-400 font-medium">Kebutuhan UMKM</div>
                                </div>
                                <div class="text-right font-extrabold text-blue-300">Harga Perbulan mulai 3.500.000</div>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-between gap-2">
                                <div>
                                    <div class="font-extrabold text-white text-sm">10 Mbps</div>
                                    <div class="text-[11px] text-emerald-400 font-medium">Kebutuhan Sekolah</div>
                                </div>
                                <div class="text-right font-extrabold text-blue-300">Harga Perbulan mulai 1.750.000</div>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-between gap-2">
                                <div>
                                    <div class="font-extrabold text-white text-sm">10 Mbps</div>
                                    <div class="text-[11px] text-emerald-400 font-medium">Kebutuhan Corporate</div>
                                </div>
                                <div class="text-right font-extrabold text-blue-300">Harga Perbulan mulai 1.750.000</div>
                            </div>
                        </div>
                    </div>
                    <div class="pt-8">
                        <a href="kontak.php?layanan=Internet+Dedicated" class="block text-center w-full py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs rounded-xl shadow-md transition">
                            Pesan Internet Dedicated &rarr;
                        </a>
                    </div>
                </div>

                <!-- 2. Internet Bisnis -->
                <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-xs hover:shadow-xl transition flex flex-col justify-between">
                    <div class="space-y-5">
                        <div class="flex items-center justify-between">
                            <span class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-700 font-black flex items-center justify-center text-xs">BIZ</span>
                            <span class="text-[11px] font-bold text-blue-700 bg-blue-50 px-3 py-1 rounded-full">Segmen 2 • Internet Bisnis</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-extrabold text-slate-900">Internet Bisnis</h3>
                            <p class="text-xs text-slate-500 mt-1">Pilihan paket kecepatan 10 Mbps hingga 200 Mbps untuk kantor &amp; bisnis.</p>
                        </div>
                        <div class="py-4 border-y border-slate-100">
                            <div class="text-xs text-slate-400 uppercase font-semibold">Harga Berlangganan</div>
                            <div class="text-2xl font-extrabold text-slate-900 mt-0.5">Mulai Rp. 407.000 / bulan</div>
                            <div class="text-[11px] text-blue-600 font-semibold mt-1">✓ Koneksi Bisnis Stabil &amp; Responsif</div>
                        </div>
                        <div class="space-y-2 text-xs">
                            <?php
                            $bisnis_list = [
                                ['10 Mbps', 'Rp. 407.000 / bulan'],
                                ['20 Mbps', 'Rp. 431.000 / bulan'],
                                ['30 Mbps', 'Rp. 453.000 / bulan'],
                                ['40 Mbps', 'Rp. 477.000 / bulan'],
                                ['50 Mbps', 'Rp. 511.000 / bulan'],
                                ['100 Mbps', 'Rp. 1.050.000 / bulan'],
                                ['200 Mbps', 'Rp. 1.560.000 / bulan'],
                            ];
                            foreach ($bisnis_list as $bl): ?>
                            <div class="px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-2">
                                <span class="font-extrabold text-slate-900"><?= e($bl[0]) ?></span>
                                <span class="font-bold text-blue-700"><?= e($bl[1]) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="pt-8">
                        <a href="kontak.php?layanan=Internet+Bisnis" class="block text-center w-full py-3.5 bg-slate-900 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition">
                            Pesan Internet Bisnis &rarr;
                        </a>
                    </div>
                </div>

                <!-- 3. Home Internet -->
                <div class="bg-white rounded-3xl border border-slate-200 p-8 shadow-xs hover:shadow-xl transition flex flex-col justify-between">
                    <div class="space-y-5">
                        <div class="flex items-center justify-between">
                            <span class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-700 font-black flex items-center justify-center text-xs">HOME</span>
                            <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full">Segmen 3 • Home Internet</span>
                        </div>
                        <div>
                            <h3 class="text-2xl font-extrabold text-slate-900">Home Internet</h3>
                            <p class="text-xs text-slate-500 mt-1">Koneksi internet rumah tangga cepat dan stabil untuk 5 hingga 20 perangkat.</p>
                        </div>
                        <div class="py-4 border-y border-slate-100">
                            <div class="text-xs text-slate-400 uppercase font-semibold">Harga Berlangganan</div>
                            <div class="text-2xl font-extrabold text-slate-900 mt-0.5">Mulai Rp. 252.000 / bulan</div>
                            <div class="text-[11px] text-emerald-600 font-semibold mt-1">✓ Optimal 5 hingga 20 Perangkat</div>
                        </div>
                        <div class="space-y-2.5 text-xs">
                            <?php
                            $home_list = [
                                ['10 Mbps', 'Rp. 252.000 / bulan', 'Optiman di 5 Perangkat'],
                                ['20 Mbps', 'Rp. 276.000 / bulan', 'Optiman di 7 Perangkat'],
                                ['30 Mbps', 'Rp. 298.000 / bulan', 'Optiman di 9 Perangkat'],
                                ['50 Mbps', 'Rp. 450.000 / bulan', 'Optiman di 12 Perangkat'],
                                ['100 Mbps', 'Rp. 710.000 / bulan', 'Optiman di 15 Perangkat'],
                                ['200 Mbps', 'Rp. 1.100.000 / bulan', 'Optiman di 20 Perangkat'],
                            ];
                            foreach ($home_list as $hl): ?>
                            <div class="px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between gap-2">
                                <div>
                                    <div class="font-extrabold text-slate-900"><?= e($hl[0]) ?></div>
                                    <div class="text-[11px] text-emerald-600 font-medium"><?= e($hl[2]) ?></div>
                                </div>
                                <span class="font-bold text-blue-700"><?= e($hl[1]) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="pt-8">
                        <a href="kontak.php?layanan=Home+Internet" class="block text-center w-full py-3.5 bg-slate-900 hover:bg-blue-700 text-white font-bold text-xs rounded-xl transition">
                            Pesan Home Internet &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- BAGIAN 2: Segmen Produk dan Layanan Lainnya (Selain 3 Segmen ISP) — Tanpa Harga -->
        <div class="pt-10 border-t border-slate-200 space-y-8">
            <div class="max-w-3xl">
                <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-700">
                    Bagian 2 · System Integrator, Security Solution &amp; Telecommunication
                </span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">
                    Segmen Produk &amp; Layanan Lainnya
                </h2>
                <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                    Selain 3 Segmen Paket Internet Service Provider (ISP), PT. Kreatif Global Solusindo melayani pengadaan, instalasi, dan integrasi produk serta layanan infrastruktur di bawah ini melalui konsultasi teknis dan survei lokasi sesuai kebutuhan proyek Anda.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-stretch">
                <?php foreach ($other_product_service_segments as $seg): ?>
                <div class="bg-white rounded-3xl border border-slate-200 p-7 sm:p-8 shadow-xs hover:shadow-xl hover:border-blue-400 transition flex flex-col justify-between">
                    <div class="space-y-5">
                        <!-- Top Bar -->
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <span class="w-11 h-11 rounded-2xl <?= e($seg['icon_class']) ?> font-black flex items-center justify-center text-xs shrink-0">
                                    <?= e($seg['code']) ?>
                                </span>
                                <span class="px-3 py-1 rounded-full text-[11px] font-extrabold border <?= e($seg['badge_class']) ?>">
                                    <?= e($seg['segment_label']) ?>
                                </span>
                            </div>
                            <span class="text-[11px] font-semibold text-slate-500">
                                <?= e($seg['target_audience']) ?>
                            </span>
                        </div>

                        <!-- Segment Name & Subtitle -->
                        <div>
                            <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900"><?= e($seg['name']) ?></h3>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mt-1.5">
                                <?= e($seg['subtitle']) ?>
                            </p>
                        </div>

                        <!-- Scheme Banner (No Price) -->
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center gap-2.5 text-xs text-slate-800">
                            <span class="text-blue-600 font-extrabold">✓</span>
                            <span class="font-bold"><?= e($seg['scheme_label']) ?></span>
                        </div>

                        <!-- Sub-items -->
                        <div class="space-y-3">
                            <div class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">
                                Daftar Produk &amp; Layanan dalam Segmen Ini:
                            </div>
                            <div class="space-y-2.5">
                                <?php foreach ($seg['items'] as $item): ?>
                                <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/80">
                                    <div class="flex items-center gap-2 font-extrabold text-slate-900 text-xs sm:text-sm">
                                        <span class="text-emerald-600 font-bold">✓</span>
                                        <span><?= e($item['name']) ?></span>
                                    </div>
                                    <p class="text-xs text-slate-600 leading-relaxed mt-1 pl-5">
                                        <?= e($item['description']) ?>
                                    </p>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Brands -->
                        <div>
                            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                                Merek &amp; Principal Pendukung:
                            </div>
                            <div class="flex flex-wrap gap-1.5">
                                <?php foreach ($seg['brands'] as $brand): ?>
                                <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 text-[11px] font-semibold border border-slate-200">
                                    <?= e($brand) ?>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 mt-6 border-t border-slate-100 space-y-4">
                        <div class="text-[11px] text-slate-500 italic">
                            <span class="font-semibold text-slate-700 not-italic">Peruntukan: </span>
                            <?= e($seg['recommended_for']) ?>
                        </div>
                        <a href="kontak.php?layanan=<?= rawurlencode('Konsultasi Segmen ' . $seg['name']) ?>" class="block text-center w-full py-3.5 bg-slate-900 hover:bg-blue-600 text-white font-bold text-xs rounded-xl transition">
                            Konsultasi &amp; Minta Penawaran <?= e($seg['name']) ?> &rarr;
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Custom Project / Tender BoQ CTA -->
        <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-10 shadow-xs flex flex-col lg:flex-row items-center justify-between gap-8">
            <div class="space-y-2 max-w-2xl">
                <span class="text-xs font-bold uppercase tracking-wider text-blue-600" data-live-setting="pricing_banner_kicker"><?= e(get_setting($pdo, 'pricing_banner_kicker', 'Product & Service • Pengadaan Kustom & Tender B2B')) ?></span>
                <h3 class="text-2xl font-extrabold text-slate-900" data-live-setting="pricing_banner_title"><?= e(get_setting($pdo, 'pricing_banner_title', 'Butuh Survei Lokasi & Penyusunan RAB / BoQ Terintegrasi?')) ?></h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed" data-live-setting="pricing_banner_desc">
                    <?= e(get_setting($pdo, 'pricing_banner_desc', 'Tim engineer PT. Kreatif Global Solusindo siap melakukan survei lokasi (on-site survey) dan menyusun spesifikasi teknis untuk kebutuhan Security Solution, Networking & Fiber Optik, PBX System, Telecommunication & Tower, maupun Internet Service Provider (ISP).')) ?>
                </p>
            </div>
            <div class="flex flex-wrap gap-3 shrink-0">
                <a href="kontak.php?layanan=Survei+dan+Penyusunan+BoQ+Proyek" class="px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition">
                    Jadwalkan Survei &amp; Konsultasi Teknis &rarr;
                </a>
                <a href="https://wa.me/<?= e($wa_clean) ?>?text=Halo%20PT.%20KGS,%20saya%20ingin%20berkonsultasi%20mengenai%20paket%20Internet%20atau%20Product%20%26%20Service." target="_blank" rel="noopener noreferrer" class="px-5 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition">
                    💬 Chat Sales Engineer
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
