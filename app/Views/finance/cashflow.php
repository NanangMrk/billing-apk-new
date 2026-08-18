<?php
$company = getDbConnection()->query("SELECT * FROM company_profile WHERE id = 1")->fetch();
$companyName = $company['company_name'] ?? 'NusantaraNet ISP';
$companyBrand = $company['brand_name'] ?? 'NusantaraNet';
$companyAddress = $company['address'] ?? '';
$companyPhone = $company['phone'] ?? '';
$companyEmail = $company['email'] ?? '';
$companyTax = $company['tax_number'] ?? '';
?>
<style>
  /* ==============================
     SCREEN: normal styles untouched
     ============================== */

  /* ==============================
     PRINT: Professional Report Layout
     ============================== */
  @media print {
    @page {
      size: A4 portrait;
      margin: 18mm 15mm 20mm 15mm;
    }

    * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      box-sizing: border-box;
    }

    html, body {
      font-family: 'Segoe UI', Arial, sans-serif !important;
      font-size: 10pt !important;
      color: #1e293b !important;
      background: white !important;
      margin: 0 !important;
      padding: 0 !important;
    }

  @media print {
    @page {
      size: A4 portrait;
      margin: 18mm 15mm 20mm 15mm;
    }

    * {
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      box-sizing: border-box;
    }

    html, body {
      font-family: 'Segoe UI', Arial, sans-serif !important;
      font-size: 10pt !important;
      color: #1e293b !important;
      background: white !important;
      margin: 0 !important;
      padding: 0 !important;
    }

    /* ===== HIDE: Sidebar, Navbar, Footer, Mobile Dock, Alerts, Print-hide ===== */
    /* sidebar.php wraps in <aside id="appSidebar"> */
    aside#appSidebar,
    /* navbar.php wraps in <nav> */
    nav,
    /* footer is inside .hidden.xl:block wrapper */
    body > div > div.hidden,
    /* mobile_nav.php: fixed bottom bar */
    body > div[id="mobileMenuSheet"],
    /* The fixed bottom dock */
    div.fixed.bottom-0,
    /* alerts partial */
    div[role="alert"],
    /* print-hide utility class */
    .print-hide,
    /* all form controls */
    form, button, select,
    input[type="submit"], input[type="button"] {
      display: none !important;
    }

    /* ===== MAIN CONTAINER: remove left margin added by xl:ml-72 ===== */
    /* The wrapper div that has xl:ml-72 */
    body > div:first-of-type { margin-left: 0 !important; }
    /* Tailwind generates this class but can't be selected as-is; use inline trick */
    [class*="ml-72"] { margin-left: 0 !important; }

    main {
      padding: 8pt !important;
      margin: 0 !important;
      max-width: 100% !important;
      padding-bottom: 0 !important;
    }

    /* ===== SHOW PRINT-ONLY ELEMENTS ===== */
    #print-header { display: block !important; }
    #print-footer { display: block !important; }

    /* ===== LAYOUT: grids & spacing ===== */
    .grid { display: grid !important; }
    .grid-cols-1 { grid-template-columns: 1fr !important; }
    .grid-cols-2 { grid-template-columns: 1fr 1fr !important; }
    .lg\:grid-cols-2 { grid-template-columns: 1fr 1fr !important; }
    .lg\:grid-cols-4 { grid-template-columns: 1fr 1fr 1fr 1fr !important; }
    .sm\:grid-cols-2 { grid-template-columns: 1fr 1fr !important; }
    .sm\:grid-cols-4 { grid-template-columns: 1fr 1fr 1fr 1fr !important; }
    .md\:grid-cols-3 { grid-template-columns: 1fr 1fr 1fr !important; }
    .gap-4, .gap-6 { gap: 8pt !important; }
    .mb-6, .mb-8 { margin-bottom: 10pt !important; }
    .mb-4 { margin-bottom: 8pt !important; }
    .p-5 { padding: 8pt !important; }
    .p-4, .p-6 { padding: 6pt !important; }

    /* ===== CARD STYLES ===== */
    .rounded-2xl, .rounded-xl, .rounded-3xl { border-radius: 4pt !important; }
    .shadow-soft-xl, .shadow-soft-sm, .shadow-soft-md { box-shadow: none !important; }
    .border { border: 1px solid #cbd5e1 !important; }
    .border-slate-200, .border-slate-100 { border-color: #cbd5e1 !important; }
    .border-l-4 { border-left-width: 4px !important; }
    .border-l-emerald-500 { border-left-color: #10b981 !important; }
    .border-l-rose-500 { border-left-color: #f43f5e !important; }
    .border-l-indigo-500 { border-left-color: #6366f1 !important; }

    /* ===== TEXT SIZES ===== */
    .text-xl, .text-2xl { font-size: 14pt !important; }
    .text-lg { font-size: 12pt !important; }
    .text-xs { font-size: 8.5pt !important; }
    .text-sm { font-size: 9pt !important; }
    .text-3xs, .text-2xs { font-size: 7pt !important; }

    /* ===== TEXT COLORS ===== */
    .text-emerald-600, .text-emerald-700, .text-emerald-800 { color: #059669 !important; }
    .text-rose-600, .text-rose-700, .text-rose-800 { color: #e11d48 !important; }
    .text-amber-600, .text-amber-700, .text-amber-800 { color: #d97706 !important; }
    .text-blue-600, .text-blue-700 { color: #2563eb !important; }
    .text-indigo-600 { color: #4f46e5 !important; }
    .text-slate-800 { color: #1e293b !important; }
    .text-slate-700 { color: #334155 !important; }
    .text-slate-600 { color: #475569 !important; }
    .text-slate-400, .text-slate-500 { color: #64748b !important; }

    /* ===== BACKGROUND COLORS ===== */
    .bg-emerald-50 { background-color: #ecfdf5 !important; }
    .bg-rose-50 { background-color: #fff1f2 !important; }
    .bg-amber-50 { background-color: #fffbeb !important; }
    .bg-blue-50 { background-color: #eff6ff !important; }
    .bg-slate-50 { background-color: #f8fafc !important; }
    .bg-white { background-color: #ffffff !important; }

    /* ===== TABLES ===== */
    table { width: 100% !important; border-collapse: collapse !important; font-size: 8.5pt !important; }
    th, td { padding: 5pt 6pt !important; border: 1px solid #e2e8f0 !important; }
    thead tr { background-color: #f1f5f9 !important; }
    tbody tr:nth-child(even) { background-color: #f8fafc !important; }
    .overflow-x-auto, .overflow-hidden { overflow: visible !important; }

    /* ===== TOTAL ROW ===== */
    .print-total-row td {
      font-weight: bold !important;
      background-color: #f1f5f9 !important;
      border-top: 2px solid #94a3b8 !important;
    }

    /* ===== PAGE BREAKS ===== */
    .page-break-before { page-break-before: always !important; }
    .no-break { page-break-inside: avoid !important; }
  }
</style>

<!-- Header & Month Filter -->
<div class="flex flex-wrap -mx-3 mb-6 print-hide">
  <div class="w-full max-w-full px-3">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl p-6 border border-slate-100">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <h5 class="font-bold text-slate-800 text-lg">Laporan Keuangan & Operasional</h5>
          <p class="text-xs text-slate-400">Ringkasan detail arus kas, hutang piutang, status pelanggan, dan gaji</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
          <form method="GET" action="<?php echo Helper::url('cashflow'); ?>" class="flex items-center gap-2">
            <input type="hidden" name="page" value="cashflow">
            <select id="preset_filter" class="text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white shadow-soft-xs cursor-pointer">
              <option value="this_month">Bulan Ini</option>
              <option value="last_month">Bulan Lalu</option>
              <option value="custom">Custom Rentang</option>
            </select>
            <div id="custom_date_range" class="flex items-center gap-2" style="display: none;">
              <input type="date" id="start_date" name="start_date" value="<?php echo Helper::e($startDate ?? date('Y-m-01')); ?>" class="text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white shadow-soft-xs">
              <span class="text-xs text-slate-400">s/d</span>
              <input type="date" id="end_date" name="end_date" value="<?php echo Helper::e($endDate ?? date('Y-m-t')); ?>" class="text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white shadow-soft-xs">
            </div>
            <button type="submit" class="px-4 py-2 bg-gradient-to-tl from-slate-700 to-slate-500 text-white rounded-xl text-xs font-bold shadow-soft-md hover:scale-105 transition-all">
              Filter
            </button>
          </form>
          <script>
            document.getElementById('preset_filter').addEventListener('change', function() {
              const start = document.getElementById('start_date');
              const end = document.getElementById('end_date');
              const customDiv = document.getElementById('custom_date_range');
              
              // Get current date formatted precisely in local timezone avoiding UTC shift
              const now = new Date();
              const y = now.getFullYear();
              const m = now.getMonth();
              
              const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
              };

              if (this.value === 'this_month') {
                customDiv.style.display = 'none';
                start.value = formatDate(new Date(y, m, 1));
                end.value = formatDate(new Date(y, m + 1, 0));
              } else if (this.value === 'last_month') {
                customDiv.style.display = 'none';
                start.value = formatDate(new Date(y, m - 1, 1));
                end.value = formatDate(new Date(y, m, 0));
              } else {
                customDiv.style.display = 'flex';
              }
            });
            
            window.addEventListener('DOMContentLoaded', () => {
              const start = document.getElementById('start_date').value;
              const end = document.getElementById('end_date').value;
              
              const now = new Date();
              const y = now.getFullYear();
              const m = now.getMonth();
              
              const formatDate = (date) => {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
              };

              let thisMonthStart = formatDate(new Date(y, m, 1));
              let thisMonthEnd = formatDate(new Date(y, m + 1, 0));
              let lastMonthStart = formatDate(new Date(y, m - 1, 1));
              let lastMonthEnd = formatDate(new Date(y, m, 0));
              
              const preset = document.getElementById('preset_filter');
              const customDiv = document.getElementById('custom_date_range');
              if (start === thisMonthStart && end === thisMonthEnd) {
                preset.value = 'this_month';
                customDiv.style.display = 'none';
              } else if (start === lastMonthStart && end === lastMonthEnd) {
                preset.value = 'last_month';
                customDiv.style.display = 'none';
              } else {
                preset.value = 'custom';
                customDiv.style.display = 'flex';
              }
            });
          </script>
          <button onclick="window.print()" class="px-4 py-2 bg-gradient-to-tl from-indigo-600 to-blue-500 text-white rounded-xl text-xs font-bold shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
            <i class="fa-solid fa-print"></i> Cetak Laporan
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Print Only Header (Kop Surat Profesional) -->
<div id="print-header" style="display:none" class="mb-6">
  <div style="display:flex; align-items:flex-start; justify-content:space-between; border-bottom:3px solid #7c3aed; padding-bottom:12pt; margin-bottom:10pt;">
    <div style="flex:1;">
      <div style="font-size:16pt; font-weight:900; color:#1e293b; letter-spacing:-0.5px; line-height:1.2;"><?php echo Helper::e($companyName); ?></div>
      <div style="font-size:9pt; color:#7c3aed; font-weight:700; margin-top:1pt;"><?php echo Helper::e($companyBrand); ?></div>
      <div style="font-size:8pt; color:#64748b; margin-top:4pt; line-height:1.5;">
        <?php if($companyAddress): ?><span><?php echo Helper::e($companyAddress); ?></span><br><?php endif; ?>
        <?php if($companyPhone): ?><span>Telp: <?php echo Helper::e($companyPhone); ?></span><?php if($companyEmail): ?> &nbsp;|&nbsp; <?php endif; ?><?php endif; ?>
        <?php if($companyEmail): ?><span><?php echo Helper::e($companyEmail); ?></span><?php endif; ?>
        <?php if($companyTax): ?><br><span>NPWP: <?php echo Helper::e($companyTax); ?></span><?php endif; ?>
      </div>
    </div>
    <div style="text-align:right;">
      <div style="background:linear-gradient(135deg, #7c3aed, #db2777); color:white; padding:8pt 14pt; border-radius:6pt; font-weight:900; font-size:11pt; letter-spacing:0.5px;">LAPORAN KEUANGAN</div>
      <div style="font-size:8pt; color:#475569; margin-top:6pt;">Dibuat: <?php echo date('d F Y, H:i'); ?></div>
      <div style="font-size:8pt; color:#475569;">Oleh: <?php echo Helper::e(AuthService::user()['name'] ?? 'Admin'); ?></div>
    </div>
  </div>

  <!-- Report Title Bar -->
  <div style="background:#f1f5f9; border-left:4px solid #7c3aed; padding:6pt 10pt; border-radius:3pt;">
    <div style="font-size:10pt; font-weight:700; color:#1e293b;">Laporan Operasional &amp; Arus Kas</div>
    <div style="font-size:8.5pt; color:#64748b;">Periode: <?php echo date('d F Y', strtotime($startDate ?? date('Y-m-01'))); ?> &mdash; <?php echo date('d F Y', strtotime($endDate ?? date('Y-m-t'))); ?></div>
  </div>
</div>

<!-- Core Financial Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
  <div class="p-5 bg-white rounded-2xl border border-slate-200">
    <div class="text-2xs font-bold uppercase tracking-wider text-slate-400 mb-2">Total Saldo Aktif</div>
    <h4 class="text-xl font-black text-slate-800"><?php echo Helper::formatRupiah($totalSaldo); ?></h4>
  </div>
  <div class="p-5 bg-white rounded-2xl border border-slate-200 border-l-4 border-l-emerald-500">
    <div class="text-2xs font-bold uppercase tracking-wider text-slate-400 mb-2">Total Pemasukan</div>
    <h4 class="text-xl font-black text-emerald-600"><?php echo Helper::formatRupiah($totalIncome); ?></h4>
  </div>
  <div class="p-5 bg-white rounded-2xl border border-slate-200 border-l-4 border-l-rose-500">
    <div class="text-2xs font-bold uppercase tracking-wider text-slate-400 mb-2">Total Pengeluaran</div>
    <h4 class="text-xl font-black text-rose-600"><?php echo Helper::formatRupiah($totalExpense); ?></h4>
  </div>
  <div class="p-5 bg-white rounded-2xl border border-slate-200 border-l-4 border-l-indigo-500">
    <div class="text-2xs font-bold uppercase tracking-wider text-slate-400 mb-2">Total Laporan Gaji</div>
    <h4 class="text-xl font-black text-indigo-600"><?php echo Helper::formatRupiah($totalPayroll); ?></h4>
  </div>
</div>

<!-- Operational Stats -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
  <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200">
    <div class="text-xs font-bold uppercase tracking-wider text-slate-600 mb-3"><i class="fa-solid fa-users mr-2"></i>Status Pelanggan</div>
    <div class="flex justify-between items-center mb-2">
      <span class="text-sm text-slate-600">Total Pelanggan:</span>
      <span class="font-bold text-slate-800"><?php echo $totalCustomers; ?></span>
    </div>
    <div class="flex justify-between items-center mb-2">
      <span class="text-sm text-emerald-600"><i class="fa-solid fa-circle text-2xs mr-1"></i>Aktif:</span>
      <span class="font-bold text-emerald-700"><?php echo $activeCustomers; ?></span>
    </div>
    <div class="flex justify-between items-center">
      <span class="text-sm text-rose-600"><i class="fa-solid fa-circle text-2xs mr-1"></i>Suspend:</span>
      <span class="font-bold text-rose-700"><?php echo $suspendedCustomers; ?></span>
    </div>
  </div>

  <div class="p-5 bg-amber-50 rounded-2xl border border-amber-200">
    <div class="text-xs font-bold uppercase tracking-wider text-amber-700 mb-3"><i class="fa-solid fa-hand-holding-dollar mr-2"></i>Posisi Hutang</div>
    <h4 class="text-2xl font-black text-amber-600 mb-1"><?php echo Helper::formatRupiah($totalDebt); ?></h4>
    <span class="text-xs text-amber-700">Hutang tercatat di bulan ini</span>
  </div>

  <div class="p-5 bg-blue-50 rounded-2xl border border-blue-200">
    <div class="text-xs font-bold uppercase tracking-wider text-blue-700 mb-3"><i class="fa-solid fa-file-invoice-dollar mr-2"></i>Posisi Piutang</div>
    <h4 class="text-2xl font-black text-blue-600 mb-1"><?php echo Helper::formatRupiah($totalReceivables); ?></h4>
    <span class="text-xs text-blue-700">Tagihan pelanggan belum lunas</span>
  </div>
</div>

<!-- Detailed Tables Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
  
  <!-- Rincian Pemasukan -->
  <div class="bg-white rounded-2xl shadow-soft-sm border border-slate-200 overflow-hidden">
    <div class="p-4 border-b border-slate-100 bg-emerald-50">
      <h6 class="font-bold text-emerald-800 m-0"><i class="fa-solid fa-arrow-down-short-wide mr-2"></i>Rincian Pemasukan</h6>
    </div>
    <div class="p-0 overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 text-slate-400 text-3xs uppercase tracking-wider">
            <th class="p-3 border-b">Tanggal</th>
            <th class="p-3 border-b">Keterangan</th>
            <th class="p-3 border-b text-right">Nominal</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($incomesDetail)): ?>
            <tr><td colspan="3" class="p-4 text-center text-xs text-slate-400">Tidak ada data pemasukan</td></tr>
          <?php else: foreach($incomesDetail as $inc): ?>
            <tr class="border-b border-slate-50 text-xs">
              <td class="p-3 text-slate-500 whitespace-nowrap"><?php echo date('d M Y', strtotime($inc['transaction_date'])); ?></td>
              <td class="p-3 text-slate-700">
                <div class="font-semibold"><?php echo Helper::e($inc['description']); ?></div>
                <div class="text-3xs text-slate-400"><?php echo Helper::e($inc['category_name']); ?></div>
              </td>
              <td class="p-3 text-emerald-600 font-bold text-right whitespace-nowrap"><?php echo Helper::formatRupiah($inc['amount'], false); ?></td>
            </tr>
          <?php endforeach; endif; ?>
          <?php if (!empty($incomesDetail)): ?>
          <tr class="print-total-row">
            <td colspan="2" class="p-3 text-right text-xs font-bold text-slate-700">TOTAL PEMASUKAN</td>
            <td class="p-3 text-right font-black text-emerald-700 whitespace-nowrap"><?php echo Helper::formatRupiah(array_sum(array_column($incomesDetail, 'amount')), false); ?></td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Rincian Pengeluaran -->
  <div class="bg-white rounded-2xl shadow-soft-sm border border-slate-200 overflow-hidden">
    <div class="p-4 border-b border-slate-100 bg-rose-50">
      <h6 class="font-bold text-rose-800 m-0"><i class="fa-solid fa-arrow-up-short-wide mr-2"></i>Rincian Pengeluaran</h6>
    </div>
    <div class="p-0 overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 text-slate-400 text-3xs uppercase tracking-wider">
            <th class="p-3 border-b">Tanggal</th>
            <th class="p-3 border-b">Keterangan</th>
            <th class="p-3 border-b text-right">Nominal</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($expensesDetail)): ?>
            <tr><td colspan="3" class="p-4 text-center text-xs text-slate-400">Tidak ada data pengeluaran</td></tr>
          <?php else: foreach($expensesDetail as $exp): ?>
            <tr class="border-b border-slate-50 text-xs">
              <td class="p-3 text-slate-500 whitespace-nowrap"><?php echo date('d M Y', strtotime($exp['transaction_date'])); ?></td>
              <td class="p-3 text-slate-700">
                <div class="font-semibold"><?php echo Helper::e($exp['description']); ?></div>
                <div class="text-3xs text-slate-400"><?php echo Helper::e($exp['category_name']); ?></div>
              </td>
              <td class="p-3 text-rose-600 font-bold text-right whitespace-nowrap"><?php echo Helper::formatRupiah($exp['amount'], false); ?></td>
            </tr>
          <?php endforeach; endif; ?>
          <?php if (!empty($expensesDetail)): ?>
          <tr class="print-total-row">
            <td colspan="2" class="p-3 text-right text-xs font-bold text-slate-700">TOTAL PENGELUARAN</td>
            <td class="p-3 text-right font-black text-rose-700 whitespace-nowrap"><?php echo Helper::formatRupiah(array_sum(array_column($expensesDetail, 'amount')), false); ?></td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Rincian Hutang -->
  <div class="bg-white rounded-2xl shadow-soft-sm border border-slate-200 overflow-hidden">
    <div class="p-4 border-b border-slate-100 bg-amber-50">
      <h6 class="font-bold text-amber-800 m-0"><i class="fa-solid fa-hand-holding-dollar mr-2"></i>Rincian Hutang</h6>
    </div>
    <div class="p-0 overflow-x-auto">
      <table class="w-full text-left border-collapse">
        <thead>
          <tr class="bg-slate-50 text-slate-400 text-3xs uppercase tracking-wider">
            <th class="p-3 border-b">Tanggal</th>
            <th class="p-3 border-b">Keterangan</th>
            <th class="p-3 border-b text-right">Nominal</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($debtsDetail)): ?>
            <tr><td colspan="3" class="p-4 text-center text-xs text-slate-400">Tidak ada rincian hutang</td></tr>
          <?php else: foreach($debtsDetail as $debt): ?>
            <tr class="border-b border-slate-50 text-xs">
              <td class="p-3 text-slate-500 whitespace-nowrap"><?php echo date('d M Y', strtotime($debt['transaction_date'])); ?></td>
              <td class="p-3 text-slate-700 font-semibold"><?php echo Helper::e($debt['description']); ?></td>
              <td class="p-3 text-amber-600 font-bold text-right whitespace-nowrap"><?php echo Helper::formatRupiah($debt['amount'], false); ?></td>
            </tr>
          <?php endforeach; endif; ?>
          <?php if (!empty($debtsDetail)): ?>
          <tr class="print-total-row">
            <td colspan="2" class="p-3 text-right text-xs font-bold text-slate-700">TOTAL HUTANG</td>
            <td class="p-3 text-right font-black text-amber-700 whitespace-nowrap"><?php echo Helper::formatRupiah(array_sum(array_column($debtsDetail, 'amount')), false); ?></td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- Print Footer: Net Cashflow Summary + Signature -->
<div id="print-footer" style="display:none; margin-top:24pt;">

  <!-- Net Summary Box -->
  <div style="border:2px solid #7c3aed; border-radius:6pt; overflow:hidden; margin-bottom:16pt;">
    <div style="background:linear-gradient(135deg,#7c3aed,#db2777); color:white; padding:8pt 12pt; font-weight:900; font-size:10pt;">Ringkasan Arus Kas Bersih</div>
    <table style="width:100%; border-collapse:collapse; font-size:9pt;">
      <tr>
        <td style="padding:6pt 12pt; border-bottom:1px solid #e2e8f0;">Total Pemasukan</td>
        <td style="padding:6pt 12pt; text-align:right; font-weight:700; color:#059669; border-bottom:1px solid #e2e8f0;"><?php echo Helper::formatRupiah($totalIncome); ?></td>
      </tr>
      <tr>
        <td style="padding:6pt 12pt; border-bottom:1px solid #e2e8f0;">Total Pengeluaran</td>
        <td style="padding:6pt 12pt; text-align:right; font-weight:700; color:#e11d48; border-bottom:1px solid #e2e8f0;"><?php echo Helper::formatRupiah($totalExpense); ?></td>
      </tr>
      <tr style="background:#f8fafc;">
        <td style="padding:8pt 12pt; font-weight:900; font-size:10pt;">Arus Kas Bersih</td>
        <td style="padding:8pt 12pt; text-align:right; font-weight:900; font-size:11pt; color:<?php echo ($totalIncome - $totalExpense) >= 0 ? '#059669' : '#e11d48'; ?>">
          <?php echo Helper::formatRupiah($totalIncome - $totalExpense); ?>
        </td>
      </tr>
    </table>
  </div>

  <!-- Signature Block -->
  <div style="display:grid; grid-template-columns:1fr 1fr; gap:20pt; margin-top:20pt;">
    <div style="text-align:center;">
      <div style="font-size:8.5pt; color:#64748b; margin-bottom:40pt;">Mengetahui,</div>
      <div style="border-top:1.5px solid #1e293b; padding-top:5pt;">
        <div style="font-size:9pt; font-weight:700; color:#1e293b;">Pimpinan / Direktur</div>
        <div style="font-size:8pt; color:#64748b;"><?php echo Helper::e($companyName); ?></div>
      </div>
    </div>
    <div style="text-align:center;">
      <div style="font-size:8.5pt; color:#64748b; margin-bottom:40pt;">Dibuat oleh,</div>
      <div style="border-top:1.5px solid #1e293b; padding-top:5pt;">
        <div style="font-size:9pt; font-weight:700; color:#1e293b;"><?php echo Helper::e(AuthService::user()['name'] ?? 'Admin'); ?></div>
        <div style="font-size:8pt; color:#64748b;"><?php echo Helper::e(AuthService::user()['role_display'] ?? 'Finance'); ?></div>
      </div>
    </div>
  </div>

  <!-- Disclaimer -->
  <div style="margin-top:16pt; padding:6pt 10pt; background:#f1f5f9; border-radius:4pt; font-size:7pt; color:#94a3b8; text-align:center;">
    Dokumen ini dicetak secara otomatis oleh sistem <?php echo Helper::e($companyBrand); ?> pada <?php echo date('d F Y H:i:s'); ?>.
    Laporan ini bersifat rahasia dan hanya untuk keperluan internal perusahaan.
  </div>
</div>
