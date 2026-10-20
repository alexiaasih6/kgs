<?php
http_response_code(404);
$page_title = '404 Halaman Tidak Ditemukan';
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-24 text-center">
    <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-blue-50 text-blue-600 font-black text-3xl mb-6 border border-blue-200">
        404
    </div>
    <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight mb-3">
        Halaman Tidak Ditemukan
    </h1>
    <p class="text-slate-600 text-sm sm:text-base max-w-xl mx-auto leading-relaxed mb-8">
        Maaf, halaman yang Anda cari mungkin telah dipindahkan, diubah namanya, atau tidak tersedia. Silakan gunakan menu navigasi utama di bawah ini.
    </p>
    <div class="flex flex-wrap items-center justify-center gap-3">
        <a href="index.php" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
            &larr; Kembali ke Beranda
        </a>
        <a href="layanan.php" class="px-5 py-3 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 text-xs font-bold rounded-xl transition">
            Lihat Katalog Layanan
        </a>
        <a href="kontak.php" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition">
            Hubungi Tim KGS
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
