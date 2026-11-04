<?php
$admin_page_title = 'Kelola Semua Bagian Halaman & Statistik Website';
require_once __DIR__ . '/layout-header.php';
require_once __DIR__ . '/../includes/upload.php';

try {
    ensure_site_settings_table($pdo);
    ensure_company_timelines_table($pdo);
    ensure_company_certifications_table($pdo);
    ensure_company_partners_table($pdo);
    ensure_client_sectors_table($pdo);
} catch (Throwable $e) {
    // Abaikan jika user database tidak memiliki hak DDL
}

$active_tab = $_GET['tab'] ?? 'beranda';
$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $form_action = $_POST['form_action'] ?? '';

    try {
        // 1. Simpan Pengaturan Bagian Beranda (Badge, 4 Keunggulan, 4 Statistik, Mengapa KGS)
        if ($form_action === 'save_beranda_sections') {
            $keys = [
                'hero_badge_text',
                'hero_cta_primary',
                'hero_cta_secondary',
                'hero_highlight_1',
                'hero_highlight_2',
                'hero_highlight_3',
                'hero_highlight_4',
                'hero_stat_1_val',
                'hero_stat_1_lbl',
                'hero_stat_2_val',
                'hero_stat_2_lbl',
                'hero_stat_3_val',
                'hero_stat_3_lbl',
                'hero_stat_4_val',
                'hero_stat_4_lbl',
                'noc_card_title',
                'noc_status_badge',
                'why_kgs_title',
                'why_kgs_desc',
                'kgsnet_box_title',
                'kgsnet_box_desc'
            ];
            foreach ($keys as $k) {
                if (isset($_POST[$k])) {
                    save_setting($pdo, $k, trim((string)$_POST[$k]), 'homepage');
                }
            }
            log_audit($pdo, 'Edit Bagian Beranda', 'Memperbarui badge, 4 keunggulan, 4 statistik utama, dan seksi Beranda');
            $msg = 'Seluruh bagian Beranda & 4 Angka Statistik berhasil disimpan dan langsung berubah di website!';
            $active_tab = 'beranda';
        }

        // 2. Simpan Visi, Misi, Budaya Kerja & Komitmen (Halaman Tentang Kami)
        elseif ($form_action === 'save_visi_misi_budaya') {
            $keys = ['vision', 'mission', 'culture', 'commitment', 'about_intro_title', 'about_intro_p1', 'about_intro_p2', 'about_intro_p3'];
            foreach ($keys as $k) {
                if (isset($_POST[$k])) {
                    save_setting($pdo, $k, trim((string)$_POST[$k]), 'about');
                }
            }
            log_audit($pdo, 'Edit Visi Misi & Budaya', 'Memperbarui Visi, Misi, Budaya Kerja, Komitmen & Profil Tentang Kami');
            $msg = 'Profil, Visi, Misi, Budaya Kerja & Komitmen berhasil disimpan dan tersinkron ke halaman Tentang Kami!';
            $active_tab = 'sejarah_visi';
        }

        // 3. CRUD Timeline Sejarah Perusahaan
        elseif ($form_action === 'add_timeline') {
            $year_label = clean_plain_text((string)($_POST['year_label'] ?? ''), 30);
            $title = clean_plain_text((string)($_POST['title'] ?? ''), 200);
            $description = clean_plain_text((string)($_POST['description'] ?? ''), 1500);
            $sort_order = (int)($_POST['sort_order'] ?? 1);
            $is_highlighted = isset($_POST['is_highlighted']) ? 1 : 0;
            if ($year_label !== '' && $title !== '') {
                $stmt = $pdo->prepare("INSERT INTO company_timelines (year_label, title, description, sort_order, is_highlighted) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$year_label, $title, $description, $sort_order, $is_highlighted]);
                invalidate_live_preview_json('timelines');
                $msg = "Sejarah tahun {$year_label} berhasil ditambahkan!";
            }
            $active_tab = 'sejarah_visi';
        } elseif ($form_action === 'edit_timeline') {
            $id = (int)($_POST['id'] ?? 0);
            $year_label = clean_plain_text((string)($_POST['year_label'] ?? ''), 30);
            $title = clean_plain_text((string)($_POST['title'] ?? ''), 200);
            $description = clean_plain_text((string)($_POST['description'] ?? ''), 1500);
            $sort_order = (int)($_POST['sort_order'] ?? 1);
            $is_highlighted = isset($_POST['is_highlighted']) ? 1 : 0;
            if ($id > 0 && $year_label !== '' && $title !== '') {
                $stmt = $pdo->prepare("UPDATE company_timelines SET year_label = ?, title = ?, description = ?, sort_order = ?, is_highlighted = ? WHERE id = ?");
                $stmt->execute([$year_label, $title, $description, $sort_order, $is_highlighted, $id]);
                invalidate_live_preview_json('timelines');
                $msg = "Sejarah tahun {$year_label} berhasil diperbarui!";
            }
            $active_tab = 'sejarah_visi';
        } elseif ($form_action === 'delete_timeline') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare("DELETE FROM company_timelines WHERE id = ?")->execute([$id]);
                invalidate_live_preview_json('timelines');
                $msg = 'Milestone sejarah berhasil dihapus.';
            }
            $active_tab = 'sejarah_visi';
        }

        // 4. CRUD Sertifikasi, Lisensi & Keanggotaan (Tulisan, Link & Logo)
        elseif ($form_action === 'add_certification') {
            $badge_code  = clean_plain_text((string)($_POST['badge_code'] ?? 'CERT'), 50);
            $title       = clean_plain_text((string)($_POST['title'] ?? ''), 180);
            $subtitle    = clean_plain_text((string)($_POST['subtitle'] ?? ''), 180);
            $description = clean_plain_text((string)($_POST['description'] ?? ''), 1500);
            $raw_link    = trim((string)($_POST['link_url'] ?? ''));
            $link_url    = ($raw_link !== '' && $raw_link !== '#') ? sanitize_nav_url($raw_link) : '';
            if ($link_url === '#') {
                $link_url = '';
            }
            $logo_url    = sanitize_image_url((string)($_POST['logo_url'] ?? ''), '');
            $sort_order  = (int)($_POST['sort_order'] ?? 1);

            if (!empty($_FILES['cert_logo_file']['name'])) {
                $upload = handle_image_upload($_FILES['cert_logo_file'], 'general', $pdo);
                if ($upload['success']) {
                    $logo_url = $upload['filepath'];
                } else {
                    $error = $upload['error'];
                }
            } elseif (!empty($_POST['cert_logo_file_from_media'])) {
                $media_sel = sanitize_image_url((string)$_POST['cert_logo_file_from_media'], '');
                if ($media_sel !== '') {
                    $logo_url = $media_sel;
                }
            }

            if ($error === '' && $title !== '') {
                if ($badge_code === '') {
                    $badge_code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $title), 0, 5)) ?: 'CERT';
                }
                $stmt = $pdo->prepare("INSERT INTO company_certifications (badge_code, title, subtitle, description, logo_url, link_url, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$badge_code, $title, $subtitle, $description, $logo_url ?: null, $link_url ?: null, $sort_order]);
                invalidate_live_preview_json('certifications');
                log_audit($pdo, 'Tambah Sertifikasi', "Menambahkan sertifikasi/lisensi: {$title}");
                $msg = "Lisensi / Sertifikasi \"{$title}\" berhasil ditambahkan!";
            }
            $active_tab = 'sertifikasi_mitra';
        } elseif ($form_action === 'edit_certification') {
            $id          = (int)($_POST['id'] ?? 0);
            $badge_code  = clean_plain_text((string)($_POST['badge_code'] ?? 'CERT'), 50);
            $title       = clean_plain_text((string)($_POST['title'] ?? ''), 180);
            $subtitle    = clean_plain_text((string)($_POST['subtitle'] ?? ''), 180);
            $description = clean_plain_text((string)($_POST['description'] ?? ''), 1500);
            $raw_link    = trim((string)($_POST['link_url'] ?? ''));
            $link_url    = ($raw_link !== '' && $raw_link !== '#') ? sanitize_nav_url($raw_link) : '';
            if ($link_url === '#') {
                $link_url = '';
            }
            $logo_url    = sanitize_image_url((string)($_POST['logo_url'] ?? ''), '');
            $sort_order  = (int)($_POST['sort_order'] ?? 1);

            if (!empty($_POST['remove_cert_logo'])) {
                $logo_url = '';
            }
            if (!empty($_FILES['cert_logo_file']['name'])) {
                $upload = handle_image_upload($_FILES['cert_logo_file'], 'general', $pdo);
                if ($upload['success']) {
                    $logo_url = $upload['filepath'];
                } else {
                    $error = $upload['error'];
                }
            } elseif (!empty($_POST['cert_logo_file_from_media'])) {
                $media_sel = sanitize_image_url((string)$_POST['cert_logo_file_from_media'], '');
                if ($media_sel !== '') {
                    $logo_url = $media_sel;
                }
            }

            if ($error === '' && $id > 0 && $title !== '') {
                if ($badge_code === '') {
                    $badge_code = 'CERT';
                }
                $stmt = $pdo->prepare("UPDATE company_certifications SET badge_code = ?, title = ?, subtitle = ?, description = ?, logo_url = ?, link_url = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$badge_code, $title, $subtitle, $description, $logo_url ?: null, $link_url ?: null, $sort_order, $id]);
                invalidate_live_preview_json('certifications');
                log_audit($pdo, 'Edit Sertifikasi', "Memperbarui sertifikasi/lisensi #{$id}: {$title}");
                $msg = "Lisensi / Sertifikasi \"{$title}\" berhasil diperbarui!";
            }
            $active_tab = 'sertifikasi_mitra';
        } elseif ($form_action === 'delete_certification') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare("DELETE FROM company_certifications WHERE id = ?")->execute([$id]);
                invalidate_live_preview_json('certifications');
                log_audit($pdo, 'Hapus Sertifikasi', "Menghapus sertifikasi #{$id}");
                $msg = 'Data sertifikasi berhasil dihapus.';
            }
            $active_tab = 'sertifikasi_mitra';
        }

        // 5. CRUD Mitra Kerja & Brand (Tulisan, Link & Logo)
        elseif ($form_action === 'add_partner') {
            $brand_name  = clean_plain_text((string)($_POST['brand_name'] ?? ''), 150);
            $role_text   = clean_plain_text((string)($_POST['role_text'] ?? ''), 200);
            $description = clean_plain_text((string)($_POST['description'] ?? ''), 500);
            $raw_link    = trim((string)($_POST['link_url'] ?? ''));
            $link_url    = ($raw_link !== '' && $raw_link !== '#') ? sanitize_nav_url($raw_link) : '';
            if ($link_url === '#') {
                $link_url = '';
            }
            $logo_url    = sanitize_image_url((string)($_POST['logo_url'] ?? ''), '');
            $sort_order  = (int)($_POST['sort_order'] ?? 1);

            if (!empty($_FILES['partner_logo_file']['name'])) {
                $upload = handle_image_upload($_FILES['partner_logo_file'], 'general', $pdo);
                if ($upload['success']) {
                    $logo_url = $upload['filepath'];
                } else {
                    $error = $upload['error'];
                }
            } elseif (!empty($_POST['partner_logo_file_from_media'])) {
                $media_sel = sanitize_image_url((string)$_POST['partner_logo_file_from_media'], '');
                if ($media_sel !== '') {
                    $logo_url = $media_sel;
                }
            }

            if ($error === '' && $brand_name !== '') {
                $stmt = $pdo->prepare("INSERT INTO company_partners (brand_name, role_text, description, logo_url, link_url, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$brand_name, $role_text ?: 'Mitra Resmi KGS', $description, $logo_url ?: null, $link_url ?: null, $sort_order]);
                invalidate_live_preview_json('partners');
                log_audit($pdo, 'Tambah Mitra', "Menambahkan mitra kerja: {$brand_name}");
                $msg = "Mitra \"{$brand_name}\" berhasil ditambahkan!";
            }
            $active_tab = 'sertifikasi_mitra';
        } elseif ($form_action === 'edit_partner') {
            $id          = (int)($_POST['id'] ?? 0);
            $brand_name  = clean_plain_text((string)($_POST['brand_name'] ?? ''), 150);
            $role_text   = clean_plain_text((string)($_POST['role_text'] ?? ''), 200);
            $description = clean_plain_text((string)($_POST['description'] ?? ''), 500);
            $raw_link    = trim((string)($_POST['link_url'] ?? ''));
            $link_url    = ($raw_link !== '' && $raw_link !== '#') ? sanitize_nav_url($raw_link) : '';
            if ($link_url === '#') {
                $link_url = '';
            }
            $logo_url    = sanitize_image_url((string)($_POST['logo_url'] ?? ''), '');
            $sort_order  = (int)($_POST['sort_order'] ?? 1);

            if (!empty($_POST['remove_partner_logo'])) {
                $logo_url = '';
            }
            if (!empty($_FILES['partner_logo_file']['name'])) {
                $upload = handle_image_upload($_FILES['partner_logo_file'], 'general', $pdo);
                if ($upload['success']) {
                    $logo_url = $upload['filepath'];
                } else {
                    $error = $upload['error'];
                }
            } elseif (!empty($_POST['partner_logo_file_from_media'])) {
                $media_sel = sanitize_image_url((string)$_POST['partner_logo_file_from_media'], '');
                if ($media_sel !== '') {
                    $logo_url = $media_sel;
                }
            }

            if ($error === '' && $id > 0 && $brand_name !== '') {
                $stmt = $pdo->prepare("UPDATE company_partners SET brand_name = ?, role_text = ?, description = ?, logo_url = ?, link_url = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$brand_name, $role_text, $description, $logo_url ?: null, $link_url ?: null, $sort_order, $id]);
                invalidate_live_preview_json('partners');
                log_audit($pdo, 'Edit Mitra', "Memperbarui mitra kerja #{$id}: {$brand_name}");
                $msg = "Mitra \"{$brand_name}\" berhasil diperbarui!";
            }
            $active_tab = 'sertifikasi_mitra';
        } elseif ($form_action === 'delete_partner') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare("DELETE FROM company_partners WHERE id = ?")->execute([$id]);
                invalidate_live_preview_json('partners');
                log_audit($pdo, 'Hapus Mitra', "Menghapus mitra kerja #{$id}");
                $msg = 'Mitra berhasil dihapus.';
            }
            $active_tab = 'sertifikasi_mitra';
        }

        // 5B. Pengaturan "Lisensi, Sertifikasi & Keanggotaan" & Visibilitas Footer
        elseif ($form_action === 'save_footer_cert_settings') {
            if (isset($_POST['footer_cert_heading'])) {
                save_setting($pdo, 'footer_cert_heading', clean_plain_text((string)$_POST['footer_cert_heading'], 150), 'footer');
            }
            if (isset($_POST['footer_cert_card_title'])) {
                save_setting($pdo, 'footer_cert_card_title', clean_plain_text((string)$_POST['footer_cert_card_title'], 150), 'footer');
            }
            if (isset($_POST['footer_cert_card_status'])) {
                save_setting($pdo, 'footer_cert_card_status', clean_plain_text((string)$_POST['footer_cert_card_status'], 150), 'footer');
            }
            if (isset($_POST['footer_show_cert_section'])) {
                $val_sec = ($_POST['footer_show_cert_section'] === '0') ? '0' : '1';
                save_setting($pdo, 'footer_show_cert_section', $val_sec, 'footer');
            }
            if (isset($_POST['footer_show_cert_status'])) {
                $val_st = ($_POST['footer_show_cert_status'] === '0') ? '0' : '1';
                save_setting($pdo, 'footer_show_cert_status', $val_st, 'footer');
            }
            if (isset($_POST['footer_show_cert_badges'])) {
                $val_bg = ($_POST['footer_show_cert_badges'] === '0') ? '0' : '1';
                save_setting($pdo, 'footer_show_cert_badges', $val_bg, 'footer');
            }
            if (isset($_POST['footer_cert_badges'])) {
                save_setting($pdo, 'footer_cert_badges', clean_plain_text((string)$_POST['footer_cert_badges'], 2000), 'footer');
            }
            log_audit($pdo, 'Edit Lisensi & Sertifikasi Footer', 'Memperbarui judul, status tampil/sembunyi, dan daftar link/tulisan Lisensi, Sertifikasi & Keanggotaan');
            $msg = 'Pengaturan tampilan & teks Sertifikasi, Lisensi & Keanggotaan Resmi di Footer berhasil disimpan!';
            $active_tab = 'sertifikasi_mitra';
        } elseif ($form_action === 'add_footer_cert_badge') {
            $badge_label = clean_plain_text((string)($_POST['badge_label'] ?? ''), 120);
            $raw_burl    = trim((string)($_POST['badge_url'] ?? ''));
            $badge_url   = ($raw_burl !== '' && $raw_burl !== '#') ? sanitize_nav_url($raw_burl) : '';
            if ($badge_url === '#') {
                $badge_url = '';
            }
            $badge_logo  = sanitize_image_url((string)($_POST['badge_logo'] ?? ''), '');

            if (!empty($_FILES['badge_logo_file']['name'])) {
                $upload = handle_image_upload($_FILES['badge_logo_file'], 'general', $pdo);
                if ($upload['success']) {
                    $badge_logo = $upload['filepath'];
                } else {
                    $error = $upload['error'];
                }
            } elseif (!empty($_POST['badge_logo_file_from_media'])) {
                $media_sel = sanitize_image_url((string)$_POST['badge_logo_file_from_media'], '');
                if ($media_sel !== '') {
                    $badge_logo = $media_sel;
                }
            }

            if ($error === '' && $badge_label !== '') {
                $current_raw = get_setting($pdo, 'footer_cert_badges', 'APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified');
                $items = parse_footer_cert_badges($current_raw);
                $items[] = [
                    'label' => $badge_label,
                    'url'   => $badge_url,
                    'logo'  => $badge_logo,
                ];
                $serialized = [];
                foreach ($items as $it) {
                    $entry = $it['label'];
                    if (!empty($it['url']) || !empty($it['logo'])) {
                        $entry .= ' :: ' . ($it['url'] ?? '');
                    }
                    if (!empty($it['logo'])) {
                        $entry .= ' :: ' . $it['logo'];
                    }
                    $serialized[] = $entry;
                }
                save_setting($pdo, 'footer_cert_badges', implode(' | ', $serialized), 'footer');
                log_audit($pdo, 'Tambah Badge Lisensi Footer', "Menambahkan item Lisensi/Keanggotaan: {$badge_label}");
                $msg = "Item Lisensi/Keanggotaan \"{$badge_label}\" berhasil ditambahkan!";
            }
            $active_tab = 'sertifikasi_mitra';
        } elseif ($form_action === 'edit_footer_cert_badge') {
            $badge_idx   = (int)($_POST['badge_idx'] ?? -1);
            $badge_label = clean_plain_text((string)($_POST['badge_label'] ?? ''), 120);
            $raw_burl    = trim((string)($_POST['badge_url'] ?? ''));
            $badge_url   = ($raw_burl !== '' && $raw_burl !== '#') ? sanitize_nav_url($raw_burl) : '';
            if ($badge_url === '#') {
                $badge_url = '';
            }
            $badge_logo  = sanitize_image_url((string)($_POST['badge_logo'] ?? ''), '');

            if (!empty($_POST['remove_badge_logo'])) {
                $badge_logo = '';
            }
            if (!empty($_FILES['badge_logo_file']['name'])) {
                $upload = handle_image_upload($_FILES['badge_logo_file'], 'general', $pdo);
                if ($upload['success']) {
                    $badge_logo = $upload['filepath'];
                } else {
                    $error = $upload['error'];
                }
            } elseif (!empty($_POST['badge_logo_file_from_media'])) {
                $media_sel = sanitize_image_url((string)$_POST['badge_logo_file_from_media'], '');
                if ($media_sel !== '') {
                    $badge_logo = $media_sel;
                }
            }

            $current_raw = get_setting($pdo, 'footer_cert_badges', 'APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified');
            $items = parse_footer_cert_badges($current_raw);
            if ($error === '' && isset($items[$badge_idx]) && $badge_label !== '') {
                $items[$badge_idx] = [
                    'label' => $badge_label,
                    'url'   => $badge_url,
                    'logo'  => $badge_logo,
                ];
                $serialized = [];
                foreach ($items as $it) {
                    $entry = $it['label'];
                    if (!empty($it['url']) || !empty($it['logo'])) {
                        $entry .= ' :: ' . ($it['url'] ?? '');
                    }
                    if (!empty($it['logo'])) {
                        $entry .= ' :: ' . $it['logo'];
                    }
                    $serialized[] = $entry;
                }
                save_setting($pdo, 'footer_cert_badges', implode(' | ', $serialized), 'footer');
                log_audit($pdo, 'Edit Badge Lisensi Footer', "Memperbarui item Lisensi/Keanggotaan: {$badge_label}");
                $msg = "Item Lisensi/Keanggotaan \"{$badge_label}\" berhasil diperbarui!";
            }
            $active_tab = 'sertifikasi_mitra';
        } elseif ($form_action === 'delete_footer_cert_badge') {
            $badge_idx = (int)($_POST['badge_idx'] ?? -1);
            $current_raw = get_setting($pdo, 'footer_cert_badges', 'APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified');
            $items = parse_footer_cert_badges($current_raw);
            if (isset($items[$badge_idx])) {
                $removed = $items[$badge_idx]['label'];
                array_splice($items, $badge_idx, 1);
                $serialized = [];
                foreach ($items as $it) {
                    $entry = $it['label'];
                    if (!empty($it['url']) || !empty($it['logo'])) {
                        $entry .= ' :: ' . ($it['url'] ?? '');
                    }
                    if (!empty($it['logo'])) {
                        $entry .= ' :: ' . $it['logo'];
                    }
                    $serialized[] = $entry;
                }
                save_setting($pdo, 'footer_cert_badges', implode(' | ', $serialized), 'footer');
                log_audit($pdo, 'Hapus Badge Lisensi Footer', "Menghapus item Lisensi/Keanggotaan: {$removed}");
                $msg = "Item \"{$removed}\" berhasil dihapus.";
            }
            $active_tab = 'sertifikasi_mitra';
        }

        // 6. Simpan Banner Bawah (Harga, FAQ) & Teks Header/Footer
        elseif ($form_action === 'save_banners_footer') {
            $keys = [
                'pricing_banner_title',
                'pricing_banner_desc',
                'faq_banner_title',
                'faq_banner_desc',
                'footer_brand_label',
                'address_palembang',
                'operational_hours',
                'footer_description',
                'copyright_text',
                'website_corporate',
                'website_isp',
                'footer_cert_card_title',
                'footer_cert_card_status',
                'footer_show_cert_section',
                'footer_show_cert_status',
                'footer_show_cert_badges'
            ];
            foreach ($keys as $k) {
                if (isset($_POST[$k])) {
                    save_setting($pdo, $k, trim((string)$_POST[$k]), 'banners');
                }
            }
            if (isset($_POST['footer_brand_label'])) {
                save_setting($pdo, 'short_name', trim((string)$_POST['footer_brand_label']), 'identity');
            }
            log_audit($pdo, 'Edit Banner & Footer', 'Memperbarui banner halaman Harga, FAQ, Label KGSNET, Alamat Kantor, visibilitas Sertifikasi Footer, dan teks Header/Footer');
            $msg = 'Banner halaman Harga, FAQ, Label KGSNET, Alamat Kantor, visibilitas Sertifikasi Footer, dan teks Header/Footer berhasil disimpan!';
            $active_tab = 'banner_footer';
        } elseif ($form_action === 'add_sector') {
            $badge_code = clean_plain_text((string)($_POST['badge_code'] ?? 'BUMN'), 30);
            $badge_color = clean_plain_text((string)($_POST['badge_color'] ?? 'blue'), 30);
            $sector_name = clean_plain_text((string)($_POST['sector_name'] ?? ''), 150);
            $summary = clean_plain_text((string)($_POST['summary'] ?? ''), 1000);
            $highlight_clients = clean_plain_text((string)($_POST['highlight_clients'] ?? ''), 1000) ?: $summary;
            $sort_order = (int)($_POST['sort_order'] ?? 1);
            if ($sector_name !== '' && $summary !== '') {
                $stmt = $pdo->prepare("INSERT INTO client_sectors (badge_code, badge_label, badge_color, name, sector_name, sector_title, description, summary, highlight_clients, client_list, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([$badge_code, $badge_code, $badge_color, $sector_name, $sector_name, $sector_name, $summary, $summary, $highlight_clients, $summary, $sort_order]);
                invalidate_live_preview_json('client_sectors');
                log_audit($pdo, 'Tambah Sektor Klien', "Menambahkan sektor klien: {$sector_name}");
                $msg = "Sektor klien {$sector_name} berhasil ditambahkan!";
            }
            $active_tab = 'klien_sektor';
        } elseif ($form_action === 'edit_sector') {
            $id = (int)($_POST['id'] ?? 0);
            $badge_code = clean_plain_text((string)($_POST['badge_code'] ?? 'BUMN'), 30);
            $badge_color = clean_plain_text((string)($_POST['badge_color'] ?? 'blue'), 30);
            $sector_name = clean_plain_text((string)($_POST['sector_name'] ?? ''), 150);
            $summary = clean_plain_text((string)($_POST['summary'] ?? ''), 1000);
            $highlight_clients = clean_plain_text((string)($_POST['highlight_clients'] ?? ''), 1000) ?: $summary;
            $sort_order = (int)($_POST['sort_order'] ?? 1);
            if ($id > 0 && $sector_name !== '') {
                $stmt = $pdo->prepare("UPDATE client_sectors SET badge_code = ?, badge_label = ?, badge_color = ?, name = ?, sector_name = ?, sector_title = ?, description = ?, summary = ?, highlight_clients = ?, client_list = ?, sort_order = ? WHERE id = ?");
                $stmt->execute([$badge_code, $badge_code, $badge_color, $sector_name, $sector_name, $sector_name, $summary, $summary, $highlight_clients, $summary, $sort_order, $id]);
                invalidate_live_preview_json('client_sectors');
                log_audit($pdo, 'Edit Sektor Klien', "Memperbarui sektor klien #{$id}: {$sector_name}");
                $msg = "Sektor klien {$sector_name} berhasil diperbarui!";
            }
            $active_tab = 'klien_sektor';
        } elseif ($form_action === 'delete_sector') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare("DELETE FROM client_sectors WHERE id = ?")->execute([$id]);
                invalidate_live_preview_json('client_sectors');
                log_audit($pdo, 'Hapus Sektor Klien', "Menghapus sektor klien #{$id}");
                $msg = 'Sektor klien berhasil dihapus.';
            }
            $active_tab = 'klien_sektor';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$timelines = [];
$certifications = [];
$partners = [];
$client_sectors_list = [];
try {
    $timelines = get_company_timelines($pdo);
} catch (Throwable $e) {}
try {
    $certifications = get_company_certifications($pdo);
} catch (Throwable $e) {}
try {
    $partners = get_company_partners($pdo);
} catch (Throwable $e) {}
try {
    $client_sectors_list = get_client_sectors($pdo);
} catch (Throwable $e) {}
?>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 rounded-full text-[11px] font-bold uppercase tracking-wider">
            Sinkronisasi Penuh Seluruh Halaman Website
        </span>
        <h1 class="text-xl font-extrabold text-slate-900 mt-1">
            Kelola Semua Bagian Halaman, Statistik Beranda, Sejarah & Mitra
        </h1>
        <p class="text-xs text-slate-500">
            Atur seluruh teks, 4 poin keunggulan, 4 angka statistik utama Beranda, timeline sejarah, visi-misi, sertifikasi, mitra kerja, hingga banner bawah di setiap halaman.
        </p>
    </div>
    <a href="../index.php" target="_blank" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl transition shrink-0">
        Lihat & Live Edit Website &nearr;
    </a>
</div>

<?php if ($msg !== ''): ?>
    <div class="bg-emerald-100 border border-emerald-300 text-emerald-800 text-xs p-4 rounded-xl font-bold">
        ✓ <?= e($msg) ?>
    </div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="bg-rose-100 border border-rose-300 text-rose-800 text-xs p-4 rounded-xl font-bold">
        ⚠️ <?= e($error) ?>
    </div>
<?php endif; ?>

<!-- Navigasi Tab Bagian Halaman -->
<div class="flex flex-wrap gap-2 bg-white p-2 rounded-2xl border border-slate-200 shadow-xs text-xs">
    <a href="?tab=beranda" class="px-4 py-2.5 rounded-xl font-bold transition <?= $active_tab === 'beranda' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
        🏠 1. Bagian Beranda & 4 Statistik Utama
    </a>
    <a href="?tab=sejarah_visi" class="px-4 py-2.5 rounded-xl font-bold transition <?= $active_tab === 'sejarah_visi' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
        📜 2. Tentang Kami: Sejarah, Visi, Misi & Budaya (<?= count($timelines) ?>)
    </a>
    <a href="?tab=sertifikasi_mitra" class="px-4 py-2.5 rounded-xl font-bold transition <?= $active_tab === 'sertifikasi_mitra' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
        🏅 3. Sertifikasi Resmi & Mitra Brand (<?= count($certifications) + count($partners) ?>)
    </a>
    <a href="?tab=banner_footer" class="px-4 py-2.5 rounded-xl font-bold transition <?= $active_tab === 'banner_footer' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
        📢 4. Banner Harga, FAQ & Teks Footer
    </a>
    <a href="?tab=klien_sektor" class="px-4 py-2.5 rounded-xl font-bold transition <?= $active_tab === 'klien_sektor' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' ?>">
        🏢 5. Sektor Industri Klien (<?= count($client_sectors_list) ?>)
    </a>
    <a href="?tab=panduan" class="px-4 py-2.5 rounded-xl font-bold transition <?= $active_tab === 'panduan' ? 'bg-emerald-600 text-white shadow-xs' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100' ?>">
        🗺️ Peta Lokasi Edit Semua Halaman
    </a>
</div>

<?php if ($active_tab === 'beranda'): ?>
<!-- =========================================================================
     TAB 1: BAGIAN BERANDA (BADGE, 4 KEUNGGULAN, 4 STATISTIK UTAMA & SEKSI KGS)
========================================================================== -->
<form method="POST" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-6 text-xs">
    <?= csrf_field() ?>
    <input type="hidden" name="form_action" value="save_beranda_sections">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b pb-4">
        <div>
            <h2 class="text-base font-extrabold text-slate-900">
                Pengaturan Bagian Beranda (`index.php`) — Badge, 4 Keunggulan & 4 Statistik Utama
            </h2>
            <p class="text-slate-500 mt-0.5">
                Untuk mengubah Judul Utama (H1) & Subjudul Hero Beranda, Anda juga dapat mengeditnya di <a href="halaman.php" class="text-blue-600 font-bold underline">Kelola Isi Halaman (Beranda)</a> atau klik langsung teksnya di website.
            </p>
        </div>
        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs cursor-pointer shrink-0">
            Simpan Perubahan Beranda
        </button>
    </div>

    <!-- A. Badge Atas & Tombol CTA -->
    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-4">
        <h3 class="font-bold text-slate-900 text-sm">A. Badge Atas & Teks Tombol Hero Beranda</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Teks Badge Atas (Di atas Judul Utama)</label>
                <input type="text" name="hero_badge_text" value="<?= e(get_setting($pdo, 'hero_badge_text', 'Berdiri Sejak 1997 • PT. Kreatif Global Solusindo')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Teks Tombol Utama (Biru)</label>
                <input type="text" name="hero_cta_primary" value="<?= e(get_setting($pdo, 'hero_cta_primary', 'Konsultasi & Penawaran Gratis')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Teks Tombol Kedua</label>
                <input type="text" name="hero_cta_secondary" value="<?= e(get_setting($pdo, 'hero_cta_secondary', 'Jelajahi 5 Segmen Layanan')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
            </div>
        </div>
    </div>

    <!-- B. 4 Poin Keunggulan (Checklist Hijau di Bawah Tombol) -->
    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-4">
        <h3 class="font-bold text-slate-900 text-sm">B. 4 Poin Keunggulan Utama (Checklist Hijau di Hero Beranda)</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Poin Keunggulan #1</label>
                <input type="text" name="hero_highlight_1" value="<?= e(get_setting($pdo, 'hero_highlight_1', 'ISP Resmi KGSNet & Anggota APJII')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Poin Keunggulan #2</label>
                <input type="text" name="hero_highlight_2" value="<?= e(get_setting($pdo, 'hero_highlight_2', 'Pabrikasi & Instalasi Tower Guywire Mandiri')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Poin Keunggulan #3</label>
                <input type="text" name="hero_highlight_3" value="<?= e(get_setting($pdo, 'hero_highlight_3', 'Garansi Uji Tahanan Grounding < 2 Ohm')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Poin Keunggulan #4</label>
                <input type="text" name="hero_highlight_4" value="<?= e(get_setting($pdo, 'hero_highlight_4', 'Klien Korporasi, BUMN, Perbankan & Rumah Sakit')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
            </div>
        </div>
    </div>

    <!-- C. 4 Angka Statistik Utama di Bawah Hero Beranda -->
    <div class="p-4 rounded-xl bg-blue-50/60 border border-blue-200 space-y-4">
        <div>
            <h3 class="font-bold text-slate-900 text-sm">C. 4 Kotak Statistik Utama (Baris Statistik di Bawah Hero Beranda)</h3>
            <p class="text-[11px] text-slate-600">Bagian ini menampilkan 4 angka/pencapaian utama perusahaan tepat di bawah banner utama Beranda.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-3.5 rounded-xl border border-slate-200 space-y-2">
                <span class="text-[10px] font-bold uppercase text-blue-600">Statistik #1</span>
                <div>
                    <label class="block text-[11px] font-semibold mb-1">Angka / Judul Besar</label>
                    <input type="text" name="hero_stat_1_val" value="<?= e(get_setting($pdo, 'hero_stat_1_val', 'Sejak 1997')) ?>" class="w-full border border-slate-300 rounded-lg p-2 font-bold">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold mb-1">Keterangan Bawah</label>
                    <input type="text" name="hero_stat_1_lbl" value="<?= e(get_setting($pdo, 'hero_stat_1_lbl', 'Pengalaman Lebih dari 27 Tahun')) ?>" class="w-full border border-slate-300 rounded-lg p-2">
                </div>
            </div>

            <div class="bg-white p-3.5 rounded-xl border border-slate-200 space-y-2">
                <span class="text-[10px] font-bold uppercase text-blue-600">Statistik #2</span>
                <div>
                    <label class="block text-[11px] font-semibold mb-1">Angka / Judul Besar</label>
                    <input type="text" name="hero_stat_2_val" value="<?= e(get_setting($pdo, 'hero_stat_2_val', '5 Segmen')) ?>" class="w-full border border-slate-300 rounded-lg p-2 font-bold">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold mb-1">Keterangan Bawah</label>
                    <input type="text" name="hero_stat_2_lbl" value="<?= e(get_setting($pdo, 'hero_stat_2_lbl', 'Solusi Teknologi Terintegrasi')) ?>" class="w-full border border-slate-300 rounded-lg p-2">
                </div>
            </div>

            <div class="bg-white p-3.5 rounded-xl border border-slate-200 space-y-2">
                <span class="text-[10px] font-bold uppercase text-blue-600">Statistik #3</span>
                <div>
                    <label class="block text-[11px] font-semibold mb-1">Angka / Judul Besar</label>
                    <input type="text" name="hero_stat_3_val" value="<?= e(get_setting($pdo, 'hero_stat_3_val', 'KGSNet')) ?>" class="w-full border border-slate-300 rounded-lg p-2 font-bold">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold mb-1">Keterangan Bawah</label>
                    <input type="text" name="hero_stat_3_lbl" value="<?= e(get_setting($pdo, 'hero_stat_3_lbl', 'Penyelenggara ISP & Anggota APJII')) ?>" class="w-full border border-slate-300 rounded-lg p-2">
                </div>
            </div>

            <div class="bg-white p-3.5 rounded-xl border border-slate-200 space-y-2">
                <span class="text-[10px] font-bold uppercase text-blue-600">Statistik #4</span>
                <div>
                    <label class="block text-[11px] font-semibold mb-1">Angka / Judul Besar</label>
                    <input type="text" name="hero_stat_4_val" value="<?= e(get_setting($pdo, 'hero_stat_4_val', '< 2 Ohm')) ?>" class="w-full border border-slate-300 rounded-lg p-2 font-bold">
                </div>
                <div>
                    <label class="block text-[11px] font-semibold mb-1">Keterangan Bawah</label>
                    <input type="text" name="hero_stat_4_lbl" value="<?= e(get_setting($pdo, 'hero_stat_4_lbl', 'Standar Uji Tahanan Grounding')) ?>" class="w-full border border-slate-300 rounded-lg p-2">
                </div>
            </div>
        </div>
    </div>

    <!-- D. Seksi Mengapa Memilih PT. KGS & Kotak KGSNet di Beranda -->
    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-4">
        <h3 class="font-bold text-slate-900 text-sm">D. Seksi "Mengapa Memilih PT. KGS?" & Kartu KGSNet di Beranda</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Judul Seksi Mengapa KGS</label>
                <input type="text" name="why_kgs_title" value="<?= e(get_setting($pdo, 'why_kgs_title', 'Kemitraan Jangka Panjang dengan Standar Teknis Terukur')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Judul Kotak KGSNet (Kanan)</label>
                <input type="text" name="kgsnet_box_title" value="<?= e(get_setting($pdo, 'kgsnet_box_title', 'KGSNet Dedicated Internet')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Deskripsi Mengapa KGS</label>
                <textarea name="why_kgs_desc" rows="3" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white"><?= e(get_setting($pdo, 'why_kgs_desc', 'Kami tidak sekadar menjual perangkat. Tim engineer PT. KGS mendampingi klien mulai dari survei lokasi (on-site survey), perancangan topologi, fabrikasi di workshop internal, instalasi berstandar K3, hingga pemeliharaan berkelanjutan.')) ?></textarea>
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Deskripsi Kotak KGSNet</label>
                <textarea name="kgsnet_box_desc" rows="3" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white"><?= e(get_setting($pdo, 'kgsnet_box_desc', 'Layanan akses internet dedicated fiber optic dan wireless point-to-point dengan rasio bandwidth simetris 1:1, garansi SLA 99.5%, serta dukungan teknis lokal yang responsif.')) ?></textarea>
            </div>
        </div>
    </div>

    <div class="flex justify-end">
        <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-xs cursor-pointer">
            Simpan Seluruh Bagian Beranda
        </button>
    </div>
</form>

<?php elseif ($active_tab === 'sejarah_visi'): ?>
<!-- =========================================================================
     TAB 2: TENTANG KAMI — TIMELINE SEJARAH, VISI, MISI, BUDAYA & KOMITMEN
========================================================================== -->
<div class="space-y-6">
    <!-- Form Visi, Misi, Budaya & Komitmen + Paragraf Profil -->
    <form method="POST" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4 text-xs">
        <?= csrf_field() ?>
        <input type="hidden" name="form_action" value="save_visi_misi_budaya">
        <div class="flex items-center justify-between border-b pb-3">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Profil Perusahaan, Visi, Misi, Budaya Kerja & Komitmen (`tentang.php`)</h2>
                <p class="text-slate-500">Perubahan pada form ini langsung tampil pada halaman Tentang Kami.</p>
            </div>
            <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl cursor-pointer">
                Simpan Profil & Visi-Misi
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block font-bold text-slate-700 mb-1">Subjudul Profil Tentang Kami</label>
                <input type="text" name="about_intro_title" value="<?= e(get_setting($pdo, 'about_intro_title', 'Sejak 1997 Melayani Kebutuhan Teknologi Korporasi')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Paragraf Profil 1</label>
                <textarea name="about_intro_p1" rows="3" class="w-full border border-slate-300 rounded-lg p-2.5"><?= e(get_setting($pdo, 'about_intro_p1', 'Creative Com adalah nama tempat usaha yang menjual produk IT dengan legalitas CV. CREATIVE, berdomisili di Palembang sejak 1997 dan sejak 2013 resmi bertransformasi menjadi PT. KREATIF GLOBAL SOLUSINDO (KGS).')) ?></textarea>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Paragraf Profil 2</label>
                <textarea name="about_intro_p2" rows="3" class="w-full border border-slate-300 rounded-lg p-2.5"><?= e(get_setting($pdo, 'about_intro_p2', 'Pada awal berdirinya, Creative Com bergerak dalam bidang jasa perbaikan perangkat komputer dan jaringan. Kemudian di tahun 1998 merambah ke bidang penjualan perangkat wartel sebagai distributor merk QPHONE. Pada tahun 2002, Panasonic mempercayakan Creative Com sebagai salah satu dealer resminya di Palembang.')) ?></textarea>
            </div>
            <div class="md:col-span-2">
                <label class="block font-bold text-slate-700 mb-1">Paragraf Profil 3</label>
                <textarea name="about_intro_p3" rows="2" class="w-full border border-slate-300 rounded-lg p-2.5"><?= e(get_setting($pdo, 'about_intro_p3', 'Mengamati perkembangan teknologi dan kebutuhan industri, sejak 2004 kami melayani instalasi jaringan komputer (Cable/Wireless), radio komunikasi, PABX, serta pabrikasi tower antena. Sejak tahun 2020, PT. KGS resmi memiliki izin sebagai Penyelenggara Jasa Internet (ISP) dengan produk KGSNet dan tergabung menjadi anggota APJII.')) ?></textarea>
            </div>
            <div>
                <label class="block font-bold text-blue-700 mb-1">Visi Perusahaan *</label>
                <textarea name="vision" rows="3" required class="w-full border border-slate-300 rounded-lg p-2.5"><?= e(get_setting($pdo, 'vision', 'Menjadi perusahaan yang terdepan dan terpercaya serta mampu memberikan layanan yang baik dan berkualitas. Mampu memberikan solusi yang tepat sesuai dengan kebutuhan konsumen.')) ?></textarea>
            </div>
            <div>
                <label class="block font-bold text-blue-700 mb-1">Misi Perusahaan *</label>
                <textarea name="mission" rows="3" required class="w-full border border-slate-300 rounded-lg p-2.5"><?= e(get_setting($pdo, 'mission', 'Membangun, mengembangkan dan meningkatkan kemandirian melalui semangat kerja dan kreatifitas yang tinggi kepada setiap tim sehingga hasil yang dicapai berdampak positif bagi kemajuan dan kesejahteraan tim Creative Com (KGS) pada khususnya serta masyarakat luas pada umumnya.')) ?></textarea>
            </div>
            <div>
                <label class="block font-bold text-emerald-700 mb-1">Budaya Kerja & Organisasi *</label>
                <textarea name="culture" rows="3" required class="w-full border border-slate-300 rounded-lg p-2.5"><?= e(get_setting($pdo, 'culture', 'Mengembangkan budaya kerja yang mandiri, kreatif, dan profesional serta didukung pola hubungan kerja yang positif atas azas kekeluargaan. Menganut model organisasi fungsional, setiap orang adalah bagian dari Tim yang menjalankan tugasnya berdasarkan deskripsi fungsi.')) ?></textarea>
            </div>
            <div>
                <label class="block font-bold text-emerald-700 mb-1">Komitmen Kepuasan Pelanggan *</label>
                <textarea name="commitment" rows="3" required class="w-full border border-slate-300 rounded-lg p-2.5"><?= e(get_setting($pdo, 'commitment', 'Kami percaya kepuasan pelanggan diperoleh dari penyelesaian masalah secara konstruktif, cepat, dan tepat. Tugas kami memberikan solusi dan layanan terbaik sesuai kondisi setiap pelanggan. Kepuasan Pelanggan adalah target utama dan promosi yang efektif.')) ?></textarea>
            </div>
        </div>
    </form>

    <!-- CRUD Timeline Sejarah Perusahaan -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5 text-xs">
        <h2 class="text-base font-extrabold text-slate-900 border-b pb-3">
            Kelola Timeline Sejarah Perjalanan Perusahaan (1997 – Sekarang)
        </h2>

        <!-- Form Tambah Milestone Baru -->
        <form method="POST" class="p-4 rounded-xl bg-slate-50 border border-slate-200 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
            <?= csrf_field() ?>
            <input type="hidden" name="form_action" value="add_timeline">
            <div class="md:col-span-2">
                <label class="block font-semibold mb-1">Tahun *</label>
                <input type="text" name="year_label" required placeholder="2024" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
            </div>
            <div class="md:col-span-3">
                <label class="block font-semibold mb-1">Judul Milestone *</label>
                <input type="text" name="title" required placeholder="Ekspansi Jaringan..." class="w-full border border-slate-300 rounded-lg p-2 bg-white">
            </div>
            <div class="md:col-span-4">
                <label class="block font-semibold mb-1">Deskripsi Singkat *</label>
                <input type="text" name="description" required placeholder="Keterangan sejarah..." class="w-full border border-slate-300 rounded-lg p-2 bg-white">
            </div>
            <div class="md:col-span-1">
                <label class="block font-semibold mb-1">Urutan</label>
                <input type="number" name="sort_order" value="<?= count($timelines) + 1 ?>" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="w-full py-2 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg cursor-pointer">
                    + Tambah Sejarah
                </button>
            </div>
        </form>

        <!-- Daftar Timeline -->
        <div class="space-y-3">
            <?php foreach ($timelines as $tl): ?>
                <form method="POST" class="p-3.5 rounded-xl border border-slate-200 grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                    <?= csrf_field() ?>
                    <input type="hidden" name="form_action" value="edit_timeline">
                    <input type="hidden" name="id" value="<?= (int)$tl['id'] ?>">
                    <div class="md:col-span-2">
                        <input type="text" name="year_label" value="<?= e($tl['year_label']) ?>" class="w-full border border-slate-300 rounded-lg p-2 font-bold text-blue-700">
                    </div>
                    <div class="md:col-span-3">
                        <input type="text" name="title" value="<?= e($tl['title']) ?>" class="w-full border border-slate-300 rounded-lg p-2 font-semibold">
                    </div>
                    <div class="md:col-span-4">
                        <input type="text" name="description" value="<?= e($tl['description']) ?>" class="w-full border border-slate-300 rounded-lg p-2">
                    </div>
                    <div class="md:col-span-1">
                        <input type="number" name="sort_order" value="<?= (int)$tl['sort_order'] ?>" class="w-full border border-slate-300 rounded-lg p-2 text-center">
                    </div>
                    <div class="md:col-span-2 flex items-center gap-1.5">
                        <button type="submit" class="flex-1 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg cursor-pointer">
                            Simpan
                        </button>
                        <button type="submit" name="form_action" value="delete_timeline" onclick="return confirm('Hapus sejarah tahun ini?');" class="px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-lg cursor-pointer">
                            Hapus
                        </button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php elseif ($active_tab === 'sertifikasi_mitra'):
    $footer_cert_raw = get_setting($pdo, 'footer_cert_badges', 'APJII Official Member | KGSNet ISP Resmi | Panasonic Authorized | NETVIEL Certified');
    $footer_cert_items = parse_footer_cert_badges($footer_cert_raw);
?>
<!-- =========================================================================
     TAB 3: LISENSI, SERTIFIKASI, KEANGGOTAAN & MITRA KERJA (LOGO, LINK & TULISAN)
========================================================================== -->
<div class="space-y-8 text-xs">

    <!-- PANEL A: PENGATURAN LISENSI, SERTIFIKASI & KEANGGOTAAN DI FOOTER & IDENTITAS (TULISAN, LINK & LOGO) -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-4">
            <div>
                <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 rounded-md font-bold text-[10px] uppercase tracking-wider">
                    Tampil di Footer Website & Kredensial Resmi
                </span>
                <h2 class="text-base font-extrabold text-slate-900 mt-1">
                    1. Pengaturan "Lisensi, Sertifikasi & Keanggotaan" (Tambah Tulisan, Link & Logo)
                </h2>
                <p class="text-slate-500 mt-0.5">
                    Kelola judul bagian, teks status verifikasi, serta daftar item lisensi/keanggotaan (mendukung teks tulisan, URL link tujuan saat diklik, dan logo/ikon).
                </p>
            </div>
        </div>

        <!-- Form Judul, Status & Pilihan Tampil/Sembunyi Seksi Sertifikasi & Keanggotaan Footer -->
        <?php
            $cur_show_cert_section = get_setting($pdo, 'footer_show_cert_section', '1');
            $cur_show_cert_status  = get_setting($pdo, 'footer_show_cert_status', '1');
            $cur_show_cert_badges  = get_setting($pdo, 'footer_show_cert_badges', '1');
        ?>
        <form method="POST" class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="form_action" value="save_footer_cert_settings">

            <div class="p-3.5 rounded-xl bg-white border border-blue-200 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-extrabold text-slate-900 text-xs">
                        👁️ Pengaturan Visibilitas Footer: "Sertifikasi &amp; Keanggotaan Resmi — ✓ Terverifikasi &amp; Berlisensi Resmi"
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?= $cur_show_cert_section !== '0' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' ?>">
                        Status Saat Ini: <?= $cur_show_cert_section !== '0' ? 'Ditampilkan di Footer' : 'Tidak Ditampilkan (Disembunyikan)' ?>
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">1. Baris "Sertifikasi &amp; Keanggotaan Resmi" di Footer</label>
                        <select name="footer_show_cert_section" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-bold text-slate-900">
                            <option value="1" <?= $cur_show_cert_section !== '0' ? 'selected' : '' ?>>✓ Ditampilkan di Footer</option>
                            <option value="0" <?= $cur_show_cert_section === '0' ? 'selected' : '' ?>>✕ Tidak Ditampilkan (Sembunyikan)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">2. Label "✓ Terverifikasi &amp; Berlisensi Resmi"</label>
                        <select name="footer_show_cert_status" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-bold text-slate-900">
                            <option value="1" <?= $cur_show_cert_status !== '0' ? 'selected' : '' ?>>✓ Ditampilkan</option>
                            <option value="0" <?= $cur_show_cert_status === '0' ? 'selected' : '' ?>>✕ Tidak Ditampilkan (Sembunyikan)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">3. Badge "Lisensi, Sertifikasi &amp; Keanggotaan" (Kolom 1)</label>
                        <select name="footer_show_cert_badges" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-bold text-slate-900">
                            <option value="1" <?= $cur_show_cert_badges !== '0' ? 'selected' : '' ?>>✓ Ditampilkan di Kolom 1 Footer</option>
                            <option value="0" <?= $cur_show_cert_badges === '0' ? 'selected' : '' ?>>✕ Tidak Ditampilkan (Sembunyikan)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Judul Bagian Lisensi (Kolom 1 Footer)</label>
                    <input type="text" name="footer_cert_heading" value="<?= e(get_setting($pdo, 'footer_cert_heading', 'Lisensi, Sertifikasi & Keanggotaan')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Judul Baris Kartu Sertifikasi Footer</label>
                    <input type="text" name="footer_cert_card_title" value="<?= e(get_setting($pdo, 'footer_cert_card_title', 'Sertifikasi & Keanggotaan Resmi')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Label Status Verifikasi (Kanan Atas)</label>
                    <input type="text" name="footer_cert_card_status" value="<?= e(get_setting($pdo, 'footer_cert_card_status', '✓ Terverifikasi & Berlisensi Resmi')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white text-emerald-700 font-semibold">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg cursor-pointer">
                    Simpan Pengaturan Tampilan &amp; Judul Sertifikasi Footer
                </button>
            </div>
        </form>

        <!-- Form Tambah Item Badge Lisensi / Sertifikasi / Keanggotaan Baru -->
        <form method="POST" enctype="multipart/form-data" class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200 space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="form_action" value="add_footer_cert_badge">
            <div class="font-extrabold text-emerald-950 text-xs">
                + Tambah Item Lisensi, Sertifikasi & Keanggotaan Baru (Tulisan, Link & Logo)
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Tulisan / Nama Lisensi & Keanggotaan *</label>
                    <input type="text" name="badge_label" required placeholder="Contoh: APJII Official Member" class="w-full border border-slate-300 rounded-lg p-2 bg-white font-bold">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Link URL Tujuan (Opsional)</label>
                    <input type="text" name="badge_url" placeholder="https://apjii.or.id atau tentang.php" class="w-full border border-slate-300 rounded-lg p-2 bg-white font-mono">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Upload Logo / Ikon Kecil (Opsional)</label>
                    <input type="file" name="badge_logo_file" accept=".jpg,.jpeg,.png,.webp,.svg" class="w-full border border-slate-300 rounded-lg p-1.5 bg-white text-[11px]">
                </div>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg cursor-pointer">
                    + Tambahkan ke Daftar Lisensi & Keanggotaan
                </button>
            </div>
        </form>

        <!-- Daftar Item Badge Lisensi / Keanggotaan Saat Ini -->
        <div class="space-y-2.5">
            <div class="font-bold text-slate-700">Daftar Item Lisensi, Sertifikasi & Keanggotaan Aktif (<?= count($footer_cert_items) ?> Item):</div>
            <?php foreach ($footer_cert_items as $b_idx => $b_item): ?>
                <form method="POST" enctype="multipart/form-data" class="p-3.5 rounded-xl border border-slate-200 bg-white hover:border-slate-300 transition space-y-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="form_action" value="edit_footer_cert_badge">
                    <input type="hidden" name="badge_idx" value="<?= (int)$b_idx ?>">
                    <input type="hidden" name="badge_logo" value="<?= e($b_item['logo'] ?? '') ?>">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-2.5 items-center">
                        <div class="md:col-span-4">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">Tulisan Lisensi / Keanggotaan</label>
                            <input type="text" name="badge_label" required value="<?= e($b_item['label']) ?>" class="w-full border border-slate-300 rounded-lg p-2 font-bold text-slate-900">
                        </div>
                        <div class="md:col-span-4">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">Link URL (Klik Menuju)</label>
                            <input type="text" name="badge_url" value="<?= e($b_item['url'] ?? '') ?>" placeholder="https://... atau kosongkan" class="w-full border border-slate-300 rounded-lg p-2 font-mono text-blue-700">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-0.5">Logo / Ganti Logo</label>
                            <div class="flex items-center gap-2">
                                <?php if (!empty($b_item['logo'])): ?>
                                    <img src="../<?= e(ltrim($b_item['logo'], '/')) ?>" alt="Logo" class="h-7 w-7 object-contain rounded border border-slate-200 bg-slate-50 p-0.5 shrink-0">
                                <?php endif; ?>
                                <input type="file" name="badge_logo_file" accept=".jpg,.jpeg,.png,.webp,.svg" class="w-full text-[10px] file:mr-1 file:py-1 file:px-2 file:rounded file:border-0 file:bg-slate-100 file:text-slate-700">
                            </div>
                        </div>
                        <div class="md:col-span-2 flex items-center justify-end gap-1.5 pt-3 md:pt-4">
                            <button type="submit" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg cursor-pointer">
                                Simpan
                            </button>
                            <button type="submit" name="form_action" value="delete_footer_cert_badge" onclick="return confirm('Hapus item lisensi/keanggotaan ini?');" class="px-2.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-lg cursor-pointer">
                                Hapus
                            </button>
                        </div>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- PANEL B & C: KARTU SERTIFIKASI RESMI & MITRA KERJA BRAND (DENGAN UPLOAD LOGO, LINK & TULISAN) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Kolom Kiri: Sertifikasi & Lisensi Resmi (Halaman Tentang Kami) -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div class="border-b border-slate-200 pb-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600">Halaman Tentang Kami (`tentang.php`)</span>
                <h2 class="text-base font-extrabold text-slate-900 mt-0.5">
                    2. Kartu Sertifikasi, Lisensi & Otorisasi Brand (Logo, Link & Tulisan)
                </h2>
            </div>

            <form method="POST" enctype="multipart/form-data" class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="form_action" value="add_certification">
                <div class="font-bold text-slate-900">+ Tambah Kartu Sertifikasi / Lisensi Baru</div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block font-semibold mb-1">Kode Badge *</label>
                        <input type="text" name="badge_code" required placeholder="APJII / SONY / PANA" class="w-full border border-slate-300 rounded-lg p-2 bg-white font-bold">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Subjudul / Kategori</label>
                        <input type="text" name="subtitle" placeholder="Lisensi Resmi ISP" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Urutan</label>
                        <input type="number" name="sort_order" value="<?= count($certifications) + 1 ?>" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Judul Sertifikasi / Lisensi (Tulisan Utama) *</label>
                    <input type="text" name="title" required placeholder="Contoh: Certificate of Appointment SONY System Integrator" class="w-full border border-slate-300 rounded-lg p-2 bg-white font-bold">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Deskripsi / Keterangan Lengkap *</label>
                    <textarea name="description" rows="2" required placeholder="Keterangan lisensi atau sertifikasi..." class="w-full border border-slate-300 rounded-lg p-2 bg-white"></textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold mb-1">Link URL Sertifikasi / Verifikasi (Opsional)</label>
                        <input type="text" name="link_url" placeholder="https://..." class="w-full border border-slate-300 rounded-lg p-2 bg-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Upload Logo Sertifikasi / Brand (Opsional)</label>
                        <input type="file" name="cert_logo_file" accept=".jpg,.jpeg,.png,.webp,.svg" class="w-full border border-slate-300 rounded-lg p-1.5 bg-white text-[11px]">
                        <input type="text" name="logo_url" placeholder="Atau tempel URL logo..." class="w-full border border-slate-300 rounded-lg p-1.5 bg-white mt-1 text-[11px]">
                    </div>
                </div>
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl cursor-pointer">
                    + Tambah Sertifikasi & Simpan
                </button>
            </form>

            <div class="space-y-3 max-h-[680px] overflow-y-auto pr-1">
                <?php foreach ($certifications as $c): ?>
                    <form method="POST" enctype="multipart/form-data" class="p-4 rounded-xl border border-slate-200 bg-white space-y-2.5 shadow-2xs">
                        <?= csrf_field() ?>
                        <input type="hidden" name="form_action" value="edit_certification">
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2.5">
                                <?php if (!empty($c['logo_url'])): ?>
                                    <img src="<?= e(preg_match('/^https?:\/\//i', $c['logo_url']) ? $c['logo_url'] : '../' . ltrim($c['logo_url'], '/')) ?>" alt="<?= e($c['title']) ?>" class="h-9 w-9 object-contain rounded-lg border border-slate-200 bg-slate-50 p-1">
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-black text-xs"><?= e($c['badge_code']) ?></span>
                                <?php endif; ?>
                                <span class="font-bold text-slate-800 line-clamp-1"><?= e($c['title']) ?></span>
                            </div>
                            <span class="text-[10px] text-slate-400">Urutan #<?= (int)$c['sort_order'] ?></span>
                        </div>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[10px] text-slate-400 font-bold uppercase">Kode Badge</label>
                                <input type="text" name="badge_code" value="<?= e($c['badge_code']) ?>" class="w-full border border-slate-300 rounded-lg p-2 font-bold text-blue-700">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 font-bold uppercase">Subjudul</label>
                                <input type="text" name="subtitle" value="<?= e($c['subtitle'] ?? '') ?>" placeholder="Subjudul..." class="w-full border border-slate-300 rounded-lg p-2">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 font-bold uppercase">Urutan</label>
                                <input type="number" name="sort_order" value="<?= (int)$c['sort_order'] ?>" class="w-full border border-slate-300 rounded-lg p-2 text-center">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] text-slate-400 font-bold uppercase">Judul Sertifikasi / Lisensi</label>
                            <input type="text" name="title" value="<?= e($c['title']) ?>" class="w-full border border-slate-300 rounded-lg p-2 font-semibold">
                        </div>
                        <div>
                            <label class="block text-[10px] text-slate-400 font-bold uppercase">Deskripsi / Keterangan</label>
                            <textarea name="description" rows="2" class="w-full border border-slate-300 rounded-lg p-2"><?= e($c['description']) ?></textarea>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] text-slate-400 font-bold uppercase">Link URL Sertifikasi</label>
                                <input type="text" name="link_url" value="<?= e($c['link_url'] ?? '') ?>" placeholder="https://..." class="w-full border border-slate-300 rounded-lg p-2 font-mono">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 font-bold uppercase">Logo URL / Upload Logo Baru</label>
                                <input type="text" name="logo_url" value="<?= e($c['logo_url'] ?? '') ?>" placeholder="URL Logo..." class="w-full border border-slate-300 rounded-lg p-1.5 text-[11px] mb-1">
                                <input type="file" name="cert_logo_file" accept=".jpg,.jpeg,.png,.webp,.svg" class="w-full text-[10px]">
                                <?php if (!empty($c['logo_url'])): ?>
                                    <label class="inline-flex items-center gap-1 text-[10px] text-rose-600 mt-1 cursor-pointer">
                                        <input type="checkbox" name="remove_cert_logo" value="1"> Hapus logo saat disimpan
                                    </label>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg cursor-pointer">Simpan Perubahan</button>
                            <button type="submit" name="form_action" value="delete_certification" onclick="return confirm('Hapus sertifikasi ini?');" class="px-3 py-2 bg-rose-50 text-rose-700 border border-rose-200 font-bold rounded-lg cursor-pointer">Hapus</button>
                        </div>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Kolom Kanan: Mitra Kerja & Dukungan Brand -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
            <div class="border-b border-slate-200 pb-3">
                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600">Halaman Tentang Kami (`tentang.php`)</span>
                <h2 class="text-base font-extrabold text-slate-900 mt-0.5">
                    3. Mitra Kerja & Dukungan Brand (Logo, Link & Tulisan)
                </h2>
            </div>

            <form method="POST" enctype="multipart/form-data" class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                <?= csrf_field() ?>
                <input type="hidden" name="form_action" value="add_partner">
                <div class="font-bold text-slate-900">+ Tambah Mitra Kerja / Brand Baru</div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block font-semibold mb-1">Nama Mitra / Brand (Tulisan) *</label>
                        <input type="text" name="brand_name" required placeholder="Contoh: PT. Telkom Indonesia / Cisco" class="w-full border border-slate-300 rounded-lg p-2 bg-white font-bold">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Urutan</label>
                        <input type="number" name="sort_order" value="<?= count($partners) + 1 ?>" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Peran / Kategori Kemitraan (Tulisan) *</label>
                    <input type="text" name="role_text" required placeholder="Contoh: Lastmile Support for ISP / Guywire Tower Support" class="w-full border border-slate-300 rounded-lg p-2 bg-white">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold mb-1">Link Website Mitra (Opsional)</label>
                        <input type="text" name="link_url" placeholder="https://..." class="w-full border border-slate-300 rounded-lg p-2 bg-white font-mono">
                    </div>
                    <div>
                        <label class="block font-semibold mb-1">Upload Logo Mitra / Brand (Opsional)</label>
                        <input type="file" name="partner_logo_file" accept=".jpg,.jpeg,.png,.webp,.svg" class="w-full border border-slate-300 rounded-lg p-1.5 bg-white text-[11px]">
                        <input type="text" name="logo_url" placeholder="Atau tempel URL logo..." class="w-full border border-slate-300 rounded-lg p-1.5 bg-white mt-1 text-[11px]">
                    </div>
                </div>
                <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl cursor-pointer">
                    + Tambah Mitra / Brand & Simpan
                </button>
            </form>

            <div class="space-y-3 max-h-[680px] overflow-y-auto pr-1">
                <?php foreach ($partners as $p): ?>
                    <form method="POST" enctype="multipart/form-data" class="p-4 rounded-xl border border-slate-200 bg-white space-y-2.5 shadow-2xs">
                        <?= csrf_field() ?>
                        <input type="hidden" name="form_action" value="edit_partner">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2">
                            <div class="flex items-center gap-2.5">
                                <?php if (!empty($p['logo_url'])): ?>
                                    <img src="<?= e(preg_match('/^https?:\/\//i', $p['logo_url']) ? $p['logo_url'] : '../' . ltrim($p['logo_url'], '/')) ?>" alt="<?= e($p['brand_name']) ?>" class="h-9 w-9 object-contain rounded-lg border border-slate-200 bg-slate-50 p-1">
                                <?php else: ?>
                                    <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 font-extrabold flex items-center justify-center text-xs">
                                        <?= e(strtoupper(substr($p['brand_name'], 0, 2))) ?>
                                    </span>
                                <?php endif; ?>
                                <span class="font-bold text-slate-800"><?= e($p['brand_name']) ?></span>
                            </div>
                            <span class="text-[10px] text-slate-400">Urutan #<?= (int)$p['sort_order'] ?></span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div class="sm:col-span-2">
                                <label class="block text-[10px] text-slate-400 font-bold uppercase">Nama Mitra / Brand</label>
                                <input type="text" name="brand_name" value="<?= e($p['brand_name']) ?>" class="w-full border border-slate-300 rounded-lg p-2 font-bold">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 font-bold uppercase">Urutan</label>
                                <input type="number" name="sort_order" value="<?= (int)$p['sort_order'] ?>" class="w-full border border-slate-300 rounded-lg p-2 text-center">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] text-slate-400 font-bold uppercase">Peran / Keterangan Kerjasama</label>
                            <input type="text" name="role_text" value="<?= e($p['role_text']) ?>" class="w-full border border-slate-300 rounded-lg p-2">
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] text-slate-400 font-bold uppercase">Link URL Mitra</label>
                                <input type="text" name="link_url" value="<?= e($p['link_url'] ?? '') ?>" placeholder="https://..." class="w-full border border-slate-300 rounded-lg p-2 font-mono">
                            </div>
                            <div>
                                <label class="block text-[10px] text-slate-400 font-bold uppercase">Logo URL / Upload Logo Baru</label>
                                <input type="text" name="logo_url" value="<?= e($p['logo_url'] ?? '') ?>" placeholder="URL Logo..." class="w-full border border-slate-300 rounded-lg p-1.5 text-[11px] mb-1">
                                <input type="file" name="partner_logo_file" accept=".jpg,.jpeg,.png,.webp,.svg" class="w-full text-[10px]">
                                <?php if (!empty($p['logo_url'])): ?>
                                    <label class="inline-flex items-center gap-1 text-[10px] text-rose-600 mt-1 cursor-pointer">
                                        <input type="checkbox" name="remove_partner_logo" value="1"> Hapus logo saat disimpan
                                    </label>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg cursor-pointer">Simpan Perubahan</button>
                            <button type="submit" name="form_action" value="delete_partner" onclick="return confirm('Hapus mitra ini?');" class="px-3 py-2 bg-rose-50 text-rose-700 border border-rose-200 font-bold rounded-lg cursor-pointer">Hapus</button>
                        </div>
                    </form>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php elseif ($active_tab === 'banner_footer'): ?>
<!-- =========================================================================
     TAB 4: BANNER BAWAH (HARGA, FAQ) & TEKS HEADER/FOOTER
========================================================================== -->
<form method="POST" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-6 text-xs">
    <?= csrf_field() ?>
    <input type="hidden" name="form_action" value="save_banners_footer">

    <div class="flex items-center justify-between border-b pb-3">
        <div>
            <h2 class="text-base font-extrabold text-slate-900">Banner Bawah Halaman (Harga & FAQ) serta Teks Header/Footer</h2>
            <p class="text-slate-500">Atur teks ajakan konsultasi di bawah halaman Harga & FAQ, serta jam operasional dan deskripsi Footer.</p>
        </div>
        <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl cursor-pointer">
            Simpan Perubahan
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
            <h3 class="font-bold text-slate-900">1. Banner Bawah Halaman Harga (`harga.php`)</h3>
            <div>
                <label class="block font-semibold mb-1">Judul Banner Kustom / Tender</label>
                <input type="text" name="pricing_banner_title" value="<?= e(get_setting($pdo, 'pricing_banner_title', 'Butuh Spesifikasi Khusus, BoQ Tender, atau Koneksi Multi-Site?')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
            </div>
            <div>
                <label class="block font-semibold mb-1">Deskripsi Banner Harga</label>
                <textarea name="pricing_banner_desc" rows="3" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white"><?= e(get_setting($pdo, 'pricing_banner_desc', 'Setiap lokasi industri memiliki topografi dan tantangan berbeda. Tim teknisi PT. KGS siap melakukan survei lapangan (Site Survey & Line of Sight Test) untuk menyusun Bill of Quantity (BoQ) yang paling efisien bagi anggaran perusahaan Anda.')) ?></textarea>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
            <h3 class="font-bold text-slate-900">2. Banner Bawah Halaman FAQ (`faq.php`)</h3>
            <div>
                <label class="block font-semibold mb-1">Judul Banner Bantuan FAQ</label>
                <input type="text" name="faq_banner_title" value="<?= e(get_setting($pdo, 'faq_banner_title', 'Masih Memiliki Pertanyaan Teknis Lainnya?')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
            </div>
            <div>
                <label class="block font-semibold mb-1">Deskripsi Banner FAQ</label>
                <textarea name="faq_banner_desc" rows="3" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white"><?= e(get_setting($pdo, 'faq_banner_desc', 'Hubungi tim engineer kami secara langsung untuk mendiskusikan topologi jaringan, ketersediaan sinyal KGSNet di lokasi Anda, atau jadwal survei lapangan.')) ?></textarea>
            </div>
        </div>

        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3 md:col-span-2">
            <h3 class="font-bold text-slate-900">3. Label Brand Footer (KGSNET), Alamat Kantor & Teks Footer</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block font-semibold mb-1">Label Brand di Footer (Default: KGSNET)</label>
                    <input type="text" name="footer_brand_label" value="<?= e(get_setting($pdo, 'footer_brand_label', get_setting($pdo, 'short_name', 'KGSNET'))) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white font-bold">
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold mb-1">Alamat Kantor (Tampil di Footer & Kontak)</label>
                    <input type="text" name="address_palembang" value="<?= e(get_setting($pdo, 'address_palembang', 'Jln. Mayor Ruslan No.175C Kel.9 Ilir Palembang 30113')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Jam Operasional (Topbar Header)</label>
                    <input type="text" name="operational_hours" value="<?= e(get_setting($pdo, 'operational_hours', 'Senin - Sabtu: 08:00 - 17:00 WIB')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Domain Website Korporat</label>
                    <input type="text" name="website_corporate" value="<?= e(get_setting($pdo, 'website_corporate', 'www.creative.co.id')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
                </div>
                <div>
                    <label class="block font-semibold mb-1">Domain Website ISP KGSNet</label>
                    <input type="text" name="website_isp" value="<?= e(get_setting($pdo, 'website_isp', 'www.kgs.net.id')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
                </div>
                <div class="md:col-span-2">
                    <label class="block font-semibold mb-1">Deskripsi Singkat Perusahaan di Footer</label>
                    <textarea name="footer_description" rows="2" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white"><?= e(get_setting($pdo, 'footer_description', 'Mitra teknologi B2B terpercaya sejak 1997 (Creative Com). Menyediakan infrastruktur jaringan komputer, telekomunikasi, surveillance keamanan, fabrikasi tower mandiri, grounding < 2 Ohm, dan layanan internet resmi KGSNet (Anggota APJII).')) ?></textarea>
                </div>
                <div>
                    <label class="block font-semibold mb-1">Teks Hak Cipta (Copyright Footer)</label>
                    <input type="text" name="copyright_text" value="<?= e(get_setting($pdo, 'copyright_text', 'Hak Cipta Dilindungi Undang-Undang.')) ?>" class="w-full border border-slate-300 rounded-lg p-2.5 bg-white">
                </div>
            </div>
        </div>
    </div>
</form>

<?php elseif ($active_tab === 'klien_sektor'): ?>
<!-- =========================================================================
     TAB 5: SEKTOR INDUSTRI KLIEN (klien.php & index.php)
========================================================================== -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 text-xs">
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs h-fit space-y-4">
        <h2 class="text-sm font-extrabold text-slate-900 border-b pb-3">Tambah Sektor Industri Klien</h2>
        <form method="POST" class="space-y-3">
            <?= csrf_field() ?>
            <input type="hidden" name="form_action" value="add_sector">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Sektor Industri *</label>
                <input type="text" name="sector_name" required placeholder="Contoh: Perbankan & Keuangan" class="w-full border border-slate-300 rounded-xl p-2.5">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Ringkasan Solusi Sektor *</label>
                <textarea name="summary" rows="3" required placeholder="Deskripsi solusi KGS pada sektor ini..." class="w-full border border-slate-300 rounded-xl p-2.5"></textarea>
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Contoh Klien Utama (Highlight)</label>
                <input type="text" name="highlight_clients" placeholder="Contoh: Bank Sumsel Babel, Bank Mandiri, BRI" class="w-full border border-slate-300 rounded-xl p-2.5">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Urutan Tampil</label>
                <input type="number" name="sort_order" value="<?= count($client_sectors_list) + 1 ?>" class="w-full border border-slate-300 rounded-xl p-2.5">
            </div>
            <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl cursor-pointer">
                + Tambah Sektor Klien
            </button>
        </form>
    </div>

    <div class="lg:col-span-2 space-y-4">
        <?php foreach ($client_sectors_list as $sec): ?>
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-3">
                <form method="POST" class="space-y-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="form_action" value="edit_sector">
                    <input type="hidden" name="id" value="<?= (int)$sec['id'] ?>">
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <div class="sm:col-span-3">
                            <label class="block font-bold text-slate-700 mb-1">Nama Sektor</label>
                            <input type="text" name="sector_name" value="<?= e($sec['sector_name']) ?>" required class="w-full border border-slate-300 rounded-lg p-2 font-bold">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Urutan</label>
                            <input type="number" name="sort_order" value="<?= (int)($sec['sort_order'] ?? 1) ?>" class="w-full border border-slate-300 rounded-lg p-2">
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Ringkasan Solusi</label>
                        <textarea name="summary" rows="2" required class="w-full border border-slate-300 rounded-lg p-2"><?= e($sec['summary']) ?></textarea>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Highlight Klien Utama</label>
                        <input type="text" name="highlight_clients" value="<?= e($sec['highlight_clients'] ?? '') ?>" class="w-full border border-slate-300 rounded-lg p-2">
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg cursor-pointer">
                            Simpan Perubahan Sektor
                        </button>
                    </div>
                </form>
                <form method="POST" onsubmit="return confirm('Hapus sektor klien ini?');" class="flex justify-end -mt-9">
                    <?= csrf_field() ?>
                    <input type="hidden" name="form_action" value="delete_sector">
                    <input type="hidden" name="id" value="<?= (int)$sec['id'] ?>">
                    <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold rounded-lg cursor-pointer">
                        Hapus
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php else: ?>
<!-- =========================================================================
     TAB 6: PETA LOKASI EDIT SELURUH HALAMAN WEBSITE
========================================================================== -->
<div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4 text-xs">
    <h2 class="text-base font-extrabold text-slate-900 border-b pb-3">
        Peta Lengkap Lokasi Edit Seluruh Halaman Website di Admin CMS
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded font-bold text-[10px]">1. Halaman Beranda (index.php)</span>
            <h3 class="font-bold text-slate-900 text-sm">Judul Hero, Slider Foto, 4 Keunggulan & 4 Statistik</h3>
            <p class="text-slate-600">Badge atas, 4 checklist keunggulan, 4 kotak angka statistik ("Sejak 1997", "5 Segmen", "KGSNet", "< 2 Ohm"), dan seksi Mengapa KGS.</p>
            <div class="flex flex-wrap gap-1.5 pt-2">
                <a href="?tab=beranda" class="px-3 py-1.5 bg-blue-600 text-white font-bold rounded-lg">Edit Statistik & Hero</a>
                <a href="tema-slider.php" class="px-3 py-1.5 bg-slate-800 text-white font-bold rounded-lg">Foto Slider & Tema</a>
                <a href="halaman.php" class="px-3 py-1.5 bg-slate-200 text-slate-800 font-bold rounded-lg">Judul H1 Beranda</a>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded font-bold text-[10px]">2. Halaman Tentang Kami (tentang.php)</span>
            <h3 class="font-bold text-slate-900 text-sm">Profil, Sejarah (1997–Kini), Visi-Misi, Sertifikasi & Mitra</h3>
            <p class="text-slate-600">Paragraf sejarah berdiri, timeline perjalanan tahunan, Visi, Misi, Budaya Kerja, Komitmen, Sertifikasi APJII/NETVIEL, dan Mitra Brand.</p>
            <div class="flex flex-wrap gap-1.5 pt-2">
                <a href="?tab=sejarah_visi" class="px-3 py-1.5 bg-blue-600 text-white font-bold rounded-lg">Edit Sejarah & Visi-Misi</a>
                <a href="?tab=sertifikasi_mitra" class="px-3 py-1.5 bg-slate-800 text-white font-bold rounded-lg">Sertifikasi & Mitra</a>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded font-bold text-[10px]">3. Halaman Layanan (layanan.php)</span>
            <h3 class="font-bold text-slate-900 text-sm">5 Segmen Bisnis & 22 Katalog Layanan</h3>
            <p class="text-slate-600">Seluruh kartu layanan yang tampil di Beranda dan halaman Layanan membaca langsung dari tabel Layanan & Kategori Segmen.</p>
            <div class="flex flex-wrap gap-1.5 pt-2">
                <a href="layanan.php" class="px-3 py-1.5 bg-blue-600 text-white font-bold rounded-lg">Kelola Layanan</a>
                <a href="kategori.php" class="px-3 py-1.5 bg-slate-800 text-white font-bold rounded-lg">5 Kategori Segmen</a>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded font-bold text-[10px]">4. Halaman Harga (harga.php)</span>
            <h3 class="font-bold text-slate-900 text-sm">Paket Solusi B2B & Banner BoQ Tender</h3>
            <p class="text-slate-600">Tambah/edit kartu paket harga B2B beserta rincian fitur teknisnya dan banner konsultasi tender di bawah halaman.</p>
            <div class="flex flex-wrap gap-1.5 pt-2">
                <a href="harga.php" class="px-3 py-1.5 bg-blue-600 text-white font-bold rounded-lg">Kelola Paket Harga</a>
                <a href="?tab=banner_footer" class="px-3 py-1.5 bg-slate-200 text-slate-800 font-bold rounded-lg">Banner Bawah Harga</a>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded font-bold text-[10px]">5. Halaman FAQ (faq.php)</span>
            <h3 class="font-bold text-slate-900 text-sm">Daftar Pertanyaan Teknis & Jawaban</h3>
            <p class="text-slate-600">Tambah, edit, urutkan, atau nonaktifkan daftar tanya jawab pelanggan serta banner bantuan teknis.</p>
            <div class="flex flex-wrap gap-1.5 pt-2">
                <a href="faq.php" class="px-3 py-1.5 bg-blue-600 text-white font-bold rounded-lg">Kelola Tanya Jawab</a>
                <a href="?tab=banner_footer" class="px-3 py-1.5 bg-slate-200 text-slate-800 font-bold rounded-lg">Banner Bawah FAQ</a>
            </div>
        </div>

        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
            <span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded font-bold text-[10px]">6. Portofolio, Artikel & Kontak</span>
            <h3 class="font-bold text-slate-900 text-sm">Dokumentasi Proyek, Klien, Blog & Alamat Kantor</h3>
            <p class="text-slate-600">Seluruh foto proyek lapangan, daftar klien, artikel edukasi, dan alamat kantor operasional.</p>
            <div class="flex flex-wrap gap-1.5 pt-2">
                <a href="portofolio.php" class="px-3 py-1.5 bg-blue-600 text-white font-bold rounded-lg">Proyek</a>
                <a href="klien.php" class="px-3 py-1.5 bg-slate-800 text-white font-bold rounded-lg">Klien</a>
                <a href="?tab=klien_sektor" class="px-3 py-1.5 bg-indigo-600 text-white font-bold rounded-lg">Sektor Klien</a>
                <a href="blog.php" class="px-3 py-1.5 bg-slate-800 text-white font-bold rounded-lg">Artikel</a>
                <a href="pengaturan.php" class="px-3 py-1.5 bg-emerald-600 text-white font-bold rounded-lg">Alamat & Kontak</a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/layout-footer.php'; ?>
