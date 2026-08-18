<?php
// app/Controllers/AssetController.php - Company Fixed Assets and CPE Equipment Tracking

require_once __DIR__ . '/../Services/Database.php';
require_once __DIR__ . '/../Helpers/Helper.php';
require_once __DIR__ . '/../Middleware/AuthMiddleware.php';

class AssetController {

    public function index(): void {
        AuthMiddleware::handle('assets.view');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            AuthMiddleware::handle('assets.manage');
            if (!Helper::verifyCsrf()) {
                Helper::setFlash('error', 'Token CSRF tidak valid.');
                Helper::redirect('assets');
            }

            $action = $_POST['action'];

            // 1. Create Asset
            if ($action === 'save_asset') {
                $name = trim($_POST['name'] ?? '');
                $sn = trim($_POST['serial_number'] ?? '');
                $mac = trim($_POST['mac_address'] ?? '');
                $purchaseDate = trim($_POST['purchase_date'] ?? date('Y-m-d'));
                $price = (int)str_replace(['.', ',', ' '], '', $_POST['purchase_price'] ?? '0');
                $location = trim($_POST['location'] ?? '');
                $pic = trim($_POST['pic_name'] ?? '');
                $status = trim($_POST['status'] ?? 'available');

                if (!empty($name)) {
                    $count = (int)$pdo->query("SELECT COUNT(*) FROM assets")->fetchColumn() + 1;
                    $assetNo = 'AST-' . str_pad((string)$count, 6, '0', STR_PAD_LEFT);

                    $stmt = $pdo->prepare("
                        INSERT INTO assets (asset_no, name, serial_number, mac_address, purchase_date, purchase_price, current_value, location, pic_name, status)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$assetNo, $name, $sn, $mac, $purchaseDate, $price, $price, $location, $pic, $status]);

                    Helper::logActivity('ASSET', 'CREATE_ASSET', $assetNo, null, "Added asset $name ($assetNo)");
                    Helper::setFlash('success', "Aset $name ($assetNo) berhasil didaftarkan.");
                }
                Helper::redirect('assets');
            }

            // 2. Update Asset
            if ($action === 'update_asset') {
                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $sn = trim($_POST['serial_number'] ?? '');
                $mac = trim($_POST['mac_address'] ?? '');
                $price = (int)str_replace(['.', ',', ' '], '', $_POST['purchase_price'] ?? '0');
                $currentVal = (int)str_replace(['.', ',', ' '], '', $_POST['current_value'] ?? '0');
                if ($currentVal <= 0 && $price > 0) $currentVal = $price;
                $location = trim($_POST['location'] ?? '');
                $pic = trim($_POST['pic_name'] ?? '');
                $status = trim($_POST['status'] ?? 'available');

                if ($id > 0 && !empty($name)) {
                    $stmt = $pdo->prepare("
                        UPDATE assets 
                        SET name = ?, serial_number = ?, mac_address = ?, purchase_price = ?, current_value = ?, location = ?, pic_name = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $sn, $mac, $price, $currentVal, $location, $pic, $status, $id]);

                    Helper::logActivity('ASSET', 'UPDATE_ASSET', (string)$id, null, "Updated asset #$id: $name");
                    Helper::setFlash('success', "Data aset $name berhasil diperbarui.");
                }
                Helper::redirect('assets');
            }

            // 3. Delete Asset
            if ($action === 'delete_asset') {
                $id = (int)($_POST['id'] ?? 0);
                if ($id > 0) {
                    $stmt = $pdo->prepare("SELECT name, asset_no FROM assets WHERE id = ?");
                    $stmt->execute([$id]);
                    $ast = $stmt->fetch();

                    if ($ast) {
                        $delStmt = $pdo->prepare("DELETE FROM assets WHERE id = ?");
                        $delStmt->execute([$id]);

                        Helper::logActivity('ASSET', 'DELETE_ASSET', (string)$id, null, "Deleted asset #$id: {$ast['name']} ({$ast['asset_no']})");
                        Helper::setFlash('success', "Aset {$ast['name']} ({$ast['asset_no']}) berhasil dihapus.");
                    }
                }
                Helper::redirect('assets');
            }
        }

        $assets = $pdo->query("
            SELECT a.*, c.name as customer_name 
            FROM assets a 
            LEFT JOIN customers c ON a.customer_id = c.id 
            ORDER BY a.id DESC
        ")->fetchAll();

        $pageTitle = 'Manajemen Aset & Alat Kerja Perusahaan';

        ob_start();
        require __DIR__ . '/../Views/assets/index.php';
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/main.php';
    }

    /**
     * Export all assets to CSV format
     */
    public function exportCsv(): void {
        AuthMiddleware::handle('assets.view');
        $pdo = getDbConnection();

        $stmt = $pdo->query("
            SELECT a.*, c.name as customer_name 
            FROM assets a 
            LEFT JOIN customers c ON a.customer_id = c.id 
            ORDER BY a.id ASC
        ");
        $assets = $stmt->fetchAll();

        $filename = 'Data_Aset_Perusahaan_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, [
            'No. Aset', 
            'Nama Aset / Perangkat', 
            'Serial Number (SN)', 
            'MAC Address', 
            'Harga Beli (Rp)', 
            'Nilai Buku Estimasi (Rp)', 
            'Lokasi Penyimpanan', 
            'PIC Penanggung Jawab', 
            'Status Aset', 
            'Tanggal Pembelian'
        ]);

        $statusLabels = [
            'available' => 'Tersedia di Gudang',
            'in_use' => 'Sedang Digunakan',
            'assigned_customer' => 'Dipinjamkan Pelanggan',
            'maintenance' => 'Perbaikan / Servis',
            'damaged' => 'Rusak Fisik',
            'lost' => 'Hilang',
            'disposed' => 'Dihapus / Afkir'
        ];

        foreach ($assets as $ast) {
            $statusText = $statusLabels[$ast['status']] ?? ucfirst($ast['status'] ?? 'available');
            fputcsv($out, [
                $ast['asset_no'],
                $ast['name'],
                $ast['serial_number'] ?: '-',
                $ast['mac_address'] ?: '-',
                $ast['purchase_price'] ?? 0,
                $ast['current_value'] ?? 0,
                $ast['location'] ?: '-',
                $ast['pic_name'] ?: '-',
                $statusText,
                $ast['purchase_date'] ?: '-'
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * Download Sample CSV Template for Importing Assets
     */
    public function downloadTemplateCsv(): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Template_Import_Aset.csv"');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($out, [
            'Nama Aset',
            'Serial Number',
            'MAC Address',
            'Harga Beli',
            'Nilai Estimasi',
            'Lokasi',
            'PIC Penanggung Jawab',
            'Status'
        ]);

        // Sample Row 1: Core Splicer Tool
        fputcsv($out, [
            'Splicer Fiber Optic Fujikura 88S',
            'SN-FJK-88992',
            '',
            '45000000',
            '42000000',
            'Gudang HQ',
            'Ahmad Fauzi (Teknisi)',
            'available'
        ]);

        // Sample Row 2: OTDR Measurement
        fputcsv($out, [
            'OTDR Anritsu MT9083 Access Master',
            'SN-ANR-908311',
            '',
            '28000000',
            '25000000',
            'POP Sekembang',
            'Budi Santoso',
            'in_use'
        ]);

        // Sample Row 3: ONT Modem CPE
        fputcsv($out, [
            'ONT Huawei HG8245H GPON ONU',
            'SN-HW-778811',
            '48:8F:5A:99:88:11',
            '185000',
            '185000',
            'Rumah Pelanggan CUST-001',
            'Ahmad Fauzi (Teknisi)',
            'assigned_customer'
        ]);

        fclose($out);
        exit;
    }

    /**
     * Import Assets from CSV File
     */
    public function importCsv(): void {
        AuthMiddleware::handle('assets.manage');
        $pdo = getDbConnection();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Helper::redirect('assets');
        }

        if (!Helper::verifyCsrf()) {
            Helper::setFlash('error', 'Token CSRF tidak valid.');
            Helper::redirect('assets');
        }

        if (!isset($_FILES['csv_file']) || !is_uploaded_file($_FILES['csv_file']['tmp_name'])) {
            Helper::setFlash('error', 'Silakan pilih file CSV yang valid untuk diimpor.');
            Helper::redirect('assets');
        }

        $filepath = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($filepath, 'r');
        if (!$handle) {
            Helper::setFlash('error', 'Gagal membuka file CSV.');
            Helper::redirect('assets');
        }

        // Read header
        $header = fgetcsv($handle, 1000, ',');
        if (!$header) {
            fclose($handle);
            Helper::setFlash('error', 'File CSV kosong atau tidak memiliki format yang sesuai.');
            Helper::redirect('assets');
        }

        $imported = 0;
        $skipped = 0;

        $pdo->beginTransaction();

        try {
            $countStmt = $pdo->query("SELECT COUNT(*) FROM assets");
            $lastCount = (int)$countStmt->fetchColumn();

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (empty(array_filter($row))) continue; // skip blank rows

                $name = trim($row[0] ?? '');
                if (empty($name)) {
                    $skipped++;
                    continue;
                }

                $sn = trim($row[1] ?? '');
                $mac = trim($row[2] ?? '');
                $price = (int)str_replace(['.', ',', ' '], '', $row[3] ?? '0');
                $val = (int)str_replace(['.', ',', ' '], '', $row[4] ?? '0');
                if ($val <= 0 && $price > 0) $val = $price;
                $location = trim($row[5] ?? '');
                $pic = trim($row[6] ?? '');
                $status = trim(strtolower($row[7] ?? 'available'));

                $validStatuses = ['available', 'in_use', 'assigned_customer', 'maintenance', 'damaged', 'lost', 'disposed'];
                if (!in_array($status, $validStatuses, true)) {
                    $status = 'available';
                }

                $lastCount++;
                $assetNo = 'AST-' . str_pad((string)$lastCount, 6, '0', STR_PAD_LEFT);

                $stmt = $pdo->prepare("
                    INSERT INTO assets (asset_no, name, serial_number, mac_address, purchase_date, purchase_price, current_value, location, pic_name, status)
                    VALUES (?, ?, ?, ?, CURRENT_DATE, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$assetNo, $name, $sn, $mac, $price, $val, $location, $pic, $status]);

                $imported++;
            }

            $pdo->commit();
            fclose($handle);

            Helper::logActivity('ASSET', 'IMPORT_CSV', 'BULK', null, "Imported $imported assets from CSV");
            Helper::setFlash('success', "Berhasil mengimpor {$imported} data aset baru" . ($skipped > 0 ? " ({$skipped} baris dilewati karena tidak lengkap)." : "."));

        } catch (Exception $e) {
            $pdo->rollBack();
            fclose($handle);
            Helper::setFlash('error', 'Gagal memproses data CSV: ' . $e->getMessage());
        }

        Helper::redirect('assets');
    }

    /**
     * Generate Official Printable PDF View
     */
    public function exportPdf(): void {
        AuthMiddleware::handle('assets.view');
        $pdo = getDbConnection();

        $assets = $pdo->query("
            SELECT a.*, c.name as customer_name 
            FROM assets a 
            LEFT JOIN customers c ON a.customer_id = c.id 
            ORDER BY a.id ASC
        ")->fetchAll();

        $totalUnits = count($assets);
        $totalPurchase = array_sum(array_column($assets, 'purchase_price'));
        $totalValue = array_sum(array_column($assets, 'current_value'));

        $company = $pdo->query("SELECT * FROM company_profile LIMIT 1")->fetch() ?: [
            'name' => 'PT. Nusantara Fiber Network (NusantaraNet)',
            'address' => 'Gedung Cyber 1 Lt. 5, Jl. Kuningan Barat No. 8, Jakarta Selatan',
            'phone' => '021-5558900 / 0812-3456-7890',
            'email' => 'support@nusantaranet.id',
            'website' => 'www.nusantaranet.id'
        ];

        // Direct printable template
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
          <meta charset="UTF-8">
          <title>Laporan Inventaris Aset & Peralatan - <?php echo htmlspecialchars($company['name'] ?? 'NusantaraNet'); ?></title>
          <link rel="preconnect" href="https://fonts.googleapis.com">
          <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
          <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
          <style>
            * { box-sizing: border-box; font-family: 'Plus Jakarta Sans', Arial, sans-serif; }
            body { background-color: #f1f5f9; color: #1e293b; margin: 0; padding: 20px; font-size: 11px; }
            .report-container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 35px 40px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); }
            .header-table { width: 100%; border-bottom: 2px solid #0f172a; padding-bottom: 12px; margin-bottom: 20px; }
            .company-name { font-size: 18px; font-weight: 800; color: #0f172a; letter-spacing: -0.5px; }
            .company-sub { font-size: 10px; color: #64748b; margin-top: 3px; }
            .report-title { font-size: 16px; font-weight: 800; text-transform: uppercase; color: #4338ca; text-align: right; }
            .report-meta { font-size: 10px; color: #64748b; text-align: right; margin-top: 4px; }
            
            /* KPI Summary Cards */
            .kpi-grid { display: flex; gap: 15px; margin-bottom: 25px; }
            .kpi-card { flex: 1; padding: 12px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; }
            .kpi-title { font-size: 9px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
            .kpi-val { font-size: 15px; font-weight: 800; color: #0f172a; margin-top: 4px; font-family: monospace; }

            /* Assets Table */
            table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
            table.data-table th { background: #f8fafc; border-top: 1px solid #cbd5e1; border-bottom: 2px solid #cbd5e1; padding: 9px 8px; text-align: left; font-size: 9px; font-weight: 700; text-transform: uppercase; color: #475569; letter-spacing: 0.5px; }
            table.data-table td { padding: 9px 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; vertical-align: middle; }
            table.data-table tr:nth-child(even) { background-color: #fafafa; }
            .text-center { text-align: center; }
            .text-right { text-align: right; }
            .font-mono { font-family: monospace; }
            .font-bold { font-weight: 700; }
            
            .badge { display: inline-block; padding: 3px 8px; border-radius: 9999px; font-size: 8px; font-weight: 800; text-transform: uppercase; }
            .badge-available { background: #dcfce7; color: #166534; }
            .badge-in_use { background: #dbeafe; color: #1e40af; }
            .badge-assigned_customer { background: #f3e8ff; color: #6b21a8; }
            .badge-maintenance { background: #fef3c7; color: #92400e; }
            .badge-damaged, .badge-lost { background: #ffe4e6; color: #9f1239; }

            /* Signature Block */
            .signature-table { width: 100%; margin-top: 40px; page-break-inside: avoid; }
            .signature-box { text-align: center; width: 45%; }
            .sig-space { height: 60px; }
            
            /* Action Bar */
            .action-bar { max-width: 1000px; margin: 0 auto 15px auto; display: flex; justify-content: space-between; align-items: center; }
            .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 700; text-decoration: none; cursor: pointer; border: none; }
            .btn-print { background: #4f46e5; color: #fff; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3); }
            .btn-back { background: #e2e8f0; color: #334155; }
            .btn:hover { opacity: 0.9; }

            @media print {
              body { background: #fff; padding: 0; }
              .action-bar { display: none !important; }
              .report-container { box-shadow: none; border-radius: 0; padding: 0; }
            }
          </style>
        </head>
        <body>

          <!-- Action Bar (Hidden on Print) -->
          <div class="action-bar">
            <a href="<?php echo Helper::url('assets'); ?>" class="btn btn-back">&larr; Kembali ke Daftar Aset</a>
            <button onclick="window.print()" class="btn btn-print">
              <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/></svg>
              Cetak / Simpan sebagai PDF
            </button>
          </div>

          <!-- Document Container -->
          <div class="report-container">
            <!-- Header -->
            <table class="header-table">
              <tr>
                <td style="width: 55%; vertical-align: top;">
                  <div class="company-name"><?php echo htmlspecialchars($company['name'] ?? 'NusantaraNet'); ?></div>
                  <div class="company-sub"><?php echo htmlspecialchars($company['address'] ?? 'Kantor Pusat ISP'); ?></div>
                  <div class="company-sub">Telp: <?php echo htmlspecialchars($company['phone'] ?? '-'); ?> &bull; Email: <?php echo htmlspecialchars($company['email'] ?? '-'); ?></div>
                </td>
                <td style="width: 45%; vertical-align: top;">
                  <div class="report-title">Laporan Inventaris Aset</div>
                  <div class="report-meta">Tanggal Cetak: <?php echo date('d F Y, H:i'); ?> WIB</div>
                  <div class="report-meta">Dokumen No: REF-AST-<?php echo date('Ymd-His'); ?></div>
                </td>
              </tr>
            </table>

            <!-- KPI Cards -->
            <div class="kpi-grid">
              <div class="kpi-card">
                <div class="kpi-title">Total Unit Aset Terdaftar</div>
                <div class="kpi-val"><?php echo number_format($totalUnits, 0, ',', '.'); ?> <span style="font-size:11px; font-weight:normal; color:#64748b;">Unit / Alat</span></div>
              </div>
              <div class="kpi-card">
                <div class="kpi-title">Total Biaya Perolehan / Beli</div>
                <div class="kpi-val" style="color:#0284c7;"><?php echo Helper::formatRupiah($totalPurchase); ?></div>
              </div>
              <div class="kpi-card">
                <div class="kpi-title">Total Nilai Buku Saat Ini</div>
                <div class="kpi-val" style="color:#16a34a;"><?php echo Helper::formatRupiah($totalValue); ?></div>
              </div>
            </div>

            <!-- Assets Table -->
            <table class="data-table">
              <thead>
                <tr>
                  <th style="width: 4%;" class="text-center">No</th>
                  <th style="width: 14%;">No. Aset</th>
                  <th style="width: 25%;">Nama Aset & SN / MAC</th>
                  <th style="width: 18%;">Lokasi & PIC</th>
                  <th style="width: 13%;" class="text-right">Harga Beli</th>
                  <th style="width: 13%;" class="text-right">Nilai Buku</th>
                  <th style="width: 13%;" class="text-center">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($assets)): ?>
                <tr>
                  <td colspan="7" class="text-center" style="padding: 25px; color: #94a3b8;">Tidak ada data aset terdaftar</td>
                </tr>
                <?php else: ?>
                  <?php $no = 1; foreach ($assets as $ast): ?>
                  <tr>
                    <td class="text-center font-mono"><?php echo $no++; ?></td>
                    <td class="font-bold font-mono" style="color: #4338ca;"><?php echo htmlspecialchars($ast['asset_no']); ?></td>
                    <td>
                      <div class="font-bold"><?php echo htmlspecialchars($ast['name']); ?></div>
                      <?php if (!empty($ast['serial_number'])): ?>
                        <div style="font-size: 8.5px; color: #64748b; font-family: monospace;">SN: <?php echo htmlspecialchars($ast['serial_number']); ?></div>
                      <?php endif; ?>
                      <?php if (!empty($ast['mac_address'])): ?>
                        <div style="font-size: 8.5px; color: #64748b; font-family: monospace;">MAC: <?php echo htmlspecialchars($ast['mac_address']); ?></div>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="font-bold"><?php echo htmlspecialchars($ast['location'] ?: 'Kantor / Gudang'); ?></div>
                      <div style="font-size: 8.5px; color: #64748b;">PIC: <?php echo htmlspecialchars($ast['pic_name'] ?: '-'); ?></div>
                    </td>
                    <td class="text-right font-mono"><?php echo Helper::formatRupiah($ast['purchase_price']); ?></td>
                    <td class="text-right font-mono font-bold"><?php echo Helper::formatRupiah($ast['current_value'] ?: $ast['purchase_price']); ?></td>
                    <td class="text-center">
                      <span class="badge badge-<?php echo htmlspecialchars($ast['status'] ?? 'available'); ?>">
                        <?php 
                          $labels = [
                            'available' => 'Tersedia',
                            'in_use' => 'Digunakan',
                            'assigned_customer' => 'Di Pelanggan',
                            'maintenance' => 'Servis',
                            'damaged' => 'Rusak',
                            'lost' => 'Hilang'
                          ];
                          echo $labels[$ast['status']] ?? ucfirst($ast['status'] ?? 'available');
                        ?>
                      </span>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
              <tfoot>
                <tr style="background: #f8fafc; font-weight: 800; border-top: 2px solid #cbd5e1;">
                  <td colspan="4" class="text-right" style="padding: 10px 8px; text-transform: uppercase; font-size: 9.5px; color: #334155;">TOTAL NILAI INVENTARIS:</td>
                  <td class="text-right font-mono" style="padding: 10px 8px; color: #0284c7;"><?php echo Helper::formatRupiah($totalPurchase); ?></td>
                  <td class="text-right font-mono" style="padding: 10px 8px; color: #16a34a;"><?php echo Helper::formatRupiah($totalValue); ?></td>
                  <td></td>
                </tr>
              </tfoot>
            </table>

            <!-- Signature -->
            <table class="signature-table">
              <tr>
                <td class="signature-box">
                  <div style="font-size: 10px; color: #64748b;">Dibuat & Diperiksa Oleh:</div>
                  <div class="sig-space"></div>
                  <div style="font-weight: 800; text-decoration: underline; color: #0f172a;">( Staff Inventaris & Logistik )</div>
                  <div style="font-size: 9px; color: #64748b; margin-top: 2px;">Penanggung Jawab Aset</div>
                </td>
                <td style="width: 10%;"></td>
                <td class="signature-box">
                  <div style="font-size: 10px; color: #64748b;">Mengetahui & Menyetujui:</div>
                  <div class="sig-space"></div>
                  <div style="font-weight: 800; text-decoration: underline; color: #0f172a;">( Direktur / Manager Operasional )</div>
                  <div style="font-size: 9px; color: #64748b; margin-top: 2px;">Pimpinan Perusahaan</div>
                </td>
              </tr>
            </table>

          </div>

        </body>
        </html>
        <?php
        exit;
    }
}
