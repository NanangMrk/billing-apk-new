<?php
// app/Views/dashboard/index.php - Executive Dashboard View with Full Valid Parameters
?>

<div class="space-y-6">

  <!-- Header Banner with Welcome & Quick Status -->
  <div class="bg-white p-5 md:p-6 rounded-3xl border border-slate-200/80 shadow-soft-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center gap-3.5">
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-xl font-black shadow-soft-md shrink-0">
        <i class="fa-solid fa-gauge-high"></i>
      </div>
      <div>
        <div class="flex items-center gap-2">
          <h4 class="text-lg md:text-xl font-black text-slate-900 tracking-tight">
            Selamat Datang, <?php echo Helper::e($_SESSION['user']['name'] ?? 'Administrator'); ?>!
          </h4>
          <span class="px-2.5 py-0.5 rounded-full text-3xs font-extrabold uppercase bg-purple-50 text-purple-700 border border-purple-200">
            <?php echo Helper::e($_SESSION['user']['role_name'] ?? 'Admin'); ?>
          </span>
        </div>
        <p class="text-slate-500 text-xs mt-0.5">
          Ikhtisar performa operasional, billing pelanggan, arus kas, dan proyek jaringan ISP per <span class="font-bold text-slate-700"><?php echo Helper::formatDate(date('Y-m-d'), 'd F Y'); ?></span>.
        </p>
      </div>
    </div>

    <!-- Quick Action / Date Badge -->
    <div class="flex items-center gap-2 self-start md:self-auto shrink-0">
      <span class="px-3.5 py-1.5 rounded-xl bg-slate-100/80 border border-slate-200 text-slate-700 font-bold text-xs flex items-center gap-2">
        <i class="fa-regular fa-calendar text-purple-600"></i>
        <span>Periode: <?php echo date('F Y'); ?></span>
      </span>
      <a href="<?php echo Helper::url('invoices'); ?>" class="px-4 py-1.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 font-bold text-xs transition-colors flex items-center gap-1.5">
        <i class="fa-solid fa-file-invoice"></i>
        <span>Kelola Tagihan</span>
      </a>
    </div>
  </div>

  <!-- Row 1: Primary Strategic KPI Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
    <!-- KPI 1: Active Customers -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft-xs hover:shadow-soft-md transition-all flex flex-col justify-between">
      <div class="flex items-start justify-between">
        <div>
          <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400 block">Pelanggan Aktif</span>
          <h3 class="text-2xl font-black text-slate-900 tracking-tight mt-1 font-mono">
            <?php echo number_format($custActive); ?>
            <span class="text-xs font-normal text-slate-400">/ <?php echo number_format($custTotal); ?> Total</span>
          </h3>
        </div>
        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-sm shrink-0">
          <i class="fa-solid fa-users"></i>
        </div>
      </div>
      <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-2xs">
        <span class="text-slate-500">Status Layanan:</span>
        <div class="flex items-center gap-2">
          <?php if ($custSuspended > 0): ?>
            <span class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 font-bold text-3xs border border-rose-200">
              <?php echo $custSuspended; ?> Isolir
            </span>
          <?php endif; ?>
          <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold text-3xs border border-emerald-200">
            <?php echo $custTotal > 0 ? round(($custActive / $custTotal) * 100, 1) : 0; ?>% Aktif
          </span>
        </div>
      </div>
    </div>

    <!-- KPI 2: Monthly Billing & Collection Rate -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft-xs hover:shadow-soft-md transition-all flex flex-col justify-between">
      <div class="flex items-start justify-between">
        <div>
          <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400 block">Tagihan Bulan Ini</span>
          <h3 class="text-xl font-black text-slate-900 tracking-tight mt-1 font-mono">
            <?php echo Helper::formatRupiah($billKpi['total_amount'] ?? 0); ?>
          </h3>
        </div>
        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tl from-emerald-600 to-teal-400 text-white flex items-center justify-center text-base shadow-soft-sm shrink-0">
          <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
      </div>
      <div class="mt-3 pt-3 border-t border-slate-100 space-y-1.5">
        <div class="flex items-center justify-between text-2xs">
          <span class="text-slate-500">Terbayar: <strong><?php echo Helper::formatRupiah($billKpi['paid_amount'] ?? 0); ?></strong></span>
          <span class="font-bold text-emerald-600 font-mono"><?php echo $collectionRate; ?>%</span>
        </div>
        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
          <div class="bg-gradient-to-r from-emerald-500 to-teal-400 h-1.5 rounded-full transition-all duration-500" style="width: <?php echo min(100, $collectionRate); ?>%"></div>
        </div>
      </div>
    </div>

    <!-- KPI 3: Cash & Bank Balance -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft-xs hover:shadow-soft-md transition-all flex flex-col justify-between">
      <div class="flex items-start justify-between">
        <div>
          <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400 block">Saldo Kas & Bank</span>
          <h3 class="text-xl font-black text-purple-700 tracking-tight mt-1 font-mono">
            <?php echo Helper::formatRupiah($cashBalance); ?>
          </h3>
        </div>
        <div class="w-11 h-11 rounded-2xl bg-gradient-to-tl from-blue-600 to-cyan-400 text-white flex items-center justify-center text-base shadow-soft-sm shrink-0">
          <i class="fa-solid fa-vault"></i>
        </div>
      </div>
      <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-2xs">
        <span class="text-slate-500">Rekening Kas Aktif:</span>
        <span class="font-bold text-slate-800 bg-slate-100 px-2 py-0.5 rounded-md text-3xs font-mono">
          <?php echo $accountCount; ?> Akun Terdaftar
        </span>
      </div>
    </div>

    <!-- KPI 4: Monthly Net Cashflow / Profit -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft-xs hover:shadow-soft-md transition-all flex flex-col justify-between">
      <div class="flex items-start justify-between">
        <div>
          <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400 block">Net Arus Kas (Bulan Ini)</span>
          <h3 class="text-xl font-black <?php echo $netProfit >= 0 ? 'text-emerald-700' : 'text-rose-600'; ?> tracking-tight mt-1 font-mono">
            <?php echo Helper::formatRupiah($netProfit); ?>
          </h3>
        </div>
        <div class="w-11 h-11 rounded-2xl <?php echo $netProfit >= 0 ? 'bg-gradient-to-tl from-emerald-500 to-lime-400' : 'bg-gradient-to-tl from-rose-500 to-pink-500'; ?> text-white flex items-center justify-center text-base shadow-soft-sm shrink-0">
          <i class="fa-solid <?php echo $netProfit >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'; ?>"></i>
        </div>
      </div>
      <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-3xs font-mono text-slate-500">
        <span class="text-emerald-600 font-bold">+<?php echo Helper::formatRupiah($finKpi['total_income'] ?? 0); ?></span>
        <span class="text-rose-500 font-bold">-<?php echo Helper::formatRupiah($finKpi['total_expense'] ?? 0); ?></span>
      </div>
    </div>

  </div>

  <!-- Row 2: Secondary Operational & Alert Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
    <!-- Sisa Piutang Berjalan -->
    <a href="<?php echo Helper::url('receivables'); ?>" class="p-4 bg-white rounded-3xl border border-slate-200/80 shadow-soft-xs hover:shadow-soft-md transition-all flex items-center justify-between group">
      <div>
        <span class="text-3xs text-slate-400 font-extrabold uppercase block tracking-wider">Piutang Tertunda</span>
        <span class="text-base font-black text-rose-600 font-mono block mt-0.5"><?php echo Helper::formatRupiah($billKpi['unpaid_amount'] ?? 0); ?></span>
        <span class="text-3xs text-slate-400"><?php echo $billKpi['unpaid_count'] ?? 0; ?> Invoice Belum Lunas</span>
      </div>
      <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 group-hover:bg-rose-600 group-hover:text-white transition-all flex items-center justify-center text-sm shadow-soft-xs">
        <i class="fa-solid fa-clock-rotate-left"></i>
      </div>
    </a>

    <!-- Proyek & RAB Jaringan -->
    <a href="<?php echo Helper::url('rab'); ?>" class="p-4 bg-white rounded-3xl border border-slate-200/80 shadow-soft-xs hover:shadow-soft-md transition-all flex items-center justify-between group">
      <div>
        <span class="text-3xs text-slate-400 font-extrabold uppercase block tracking-wider">Proyek & RAB Jaringan</span>
        <span class="text-base font-black text-amber-700 font-mono block mt-0.5"><?php echo $rabPendingCount; ?> Menunggu</span>
        <span class="text-3xs text-slate-400"><?php echo $rabActiveCount; ?> Proyek Berjalan</span>
      </div>
      <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-700 group-hover:bg-amber-600 group-hover:text-white transition-all flex items-center justify-center text-sm shadow-soft-xs">
        <i class="fa-solid fa-folder-tree"></i>
      </div>
    </a>

    <!-- Stok Gudang Menipis -->
    <a href="<?php echo Helper::url('inventory'); ?>" class="p-4 bg-white rounded-3xl border border-slate-200/80 shadow-soft-xs hover:shadow-soft-md transition-all flex items-center justify-between group">
      <div>
        <span class="text-3xs text-slate-400 font-extrabold uppercase block tracking-wider">Logistik & Gudang</span>
        <span class="text-base font-black <?php echo $lowStockCount > 0 ? 'text-rose-600' : 'text-slate-800'; ?> block mt-0.5">
          <?php echo $lowStockCount > 0 ? "{$lowStockCount} Item Menipis" : 'Persediaan Aman'; ?>
        </span>
        <span class="text-3xs text-slate-400">Total Nilai: <?php echo Helper::formatRupiah($inventoryValue); ?></span>
      </div>
      <div class="w-10 h-10 rounded-2xl <?php echo $lowStockCount > 0 ? 'bg-rose-50 text-rose-600 group-hover:bg-rose-600' : 'bg-purple-50 text-purple-700 group-hover:bg-purple-700'; ?> group-hover:text-white transition-all flex items-center justify-center text-sm shadow-soft-xs">
        <i class="fa-solid fa-boxes-stacked"></i>
      </div>
    </a>

    <!-- Helpdesk / Tiket NOC -->
    <a href="<?php echo Helper::url('tickets'); ?>" class="p-4 bg-white rounded-3xl border border-slate-200/80 shadow-soft-xs hover:shadow-soft-md transition-all flex items-center justify-between group">
      <div>
        <span class="text-3xs text-slate-400 font-extrabold uppercase block tracking-wider">Tiket Gangguan NOC</span>
        <span class="text-base font-black <?php echo $openTickets > 0 ? 'text-blue-600' : 'text-emerald-600'; ?> block mt-0.5">
          <?php echo $openTickets > 0 ? "{$openTickets} Kasus Terbuka" : 'NOC Normal / Nihil'; ?>
        </span>
        <span class="text-3xs text-slate-400">Layanan pelanggan ISP</span>
      </div>
      <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-all flex items-center justify-center text-sm shadow-soft-xs">
        <i class="fa-solid fa-ticket"></i>
      </div>
    </a>

  </div>

  <!-- Row 3: Live Financial Category Chart & Package Distribution & AI Advisor -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    
    <!-- Financial Transactions Breakdown Chart (Real Live Data) -->
    <div class="lg:col-span-7 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-sm flex flex-col justify-between">
      <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
        <div>
          <h5 class="font-black text-slate-900 text-base">Arus Keuangan & Distribusi Beban Riil</h5>
          <p class="text-2xs text-slate-400">Rincian pemasukan dan pengeluaran berdasarkan transaksi terdaftar</p>
        </div>
        <a href="<?php echo Helper::url('transactions'); ?>" class="px-3 py-1 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 font-bold text-2xs transition-colors flex items-center gap-1">
          <span>Lihat Kas</span>
          <i class="fa-solid fa-arrow-right text-3xs"></i>
        </a>
      </div>
      
      <div class="h-64 relative">
        <canvas id="financeCategoryChart" class="w-full h-full"></canvas>
      </div>

      <div class="mt-4 pt-3 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-4 gap-2 text-2xs text-center font-mono">
        <div class="p-2 rounded-xl bg-slate-50">
          <span class="text-3xs text-slate-400 block font-sans">Total Transaksi:</span>
          <span class="font-bold text-slate-900"><?php echo count($chartData); ?> Kategori</span>
        </div>
        <div class="p-2 rounded-xl bg-slate-50">
          <span class="text-3xs text-slate-400 block font-sans">Pemasukan:</span>
          <span class="font-bold text-emerald-600"><?php echo Helper::formatRupiah($finKpi['total_income'] ?? 0); ?></span>
        </div>
        <div class="p-2 rounded-xl bg-slate-50">
          <span class="text-3xs text-slate-400 block font-sans">Pengeluaran:</span>
          <span class="font-bold text-rose-500"><?php echo Helper::formatRupiah($finKpi['total_expense'] ?? 0); ?></span>
        </div>
        <div class="p-2 rounded-xl bg-slate-50">
          <span class="text-3xs text-slate-400 block font-sans">Net Cash:</span>
          <span class="font-bold <?php echo $netProfit >= 0 ? 'text-purple-700' : 'text-rose-600'; ?>"><?php echo Helper::formatRupiah($netProfit); ?></span>
        </div>
      </div>
    </div>

    <!-- AI Advisor & Package Distribution Card -->
    <div class="lg:col-span-5 flex flex-col gap-6">
      
      <!-- AI Executive Banner -->
      <div class="bg-gradient-to-br from-slate-950 via-purple-950 to-indigo-950 text-white rounded-3xl p-6 border border-purple-500/30 shadow-soft-md flex flex-col justify-between">
        <div>
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-xl bg-pink-500/20 text-pink-400 border border-pink-500/40 flex items-center justify-center text-sm">
                <i class="fa-solid fa-brain"></i>
              </div>
              <span class="text-xs font-black uppercase tracking-wider text-pink-300">AI Business Advisor</span>
            </div>
            <span class="px-2 py-0.5 rounded-full text-3xs font-extrabold uppercase bg-purple-500/20 text-purple-300 border border-purple-400/30">
              <?php echo strtoupper($aiProvider); ?> ENGINE
            </span>
          </div>

          <h5 class="text-base font-black text-white leading-snug">Simulasi Bisnis & Analisis Kelayakan Capex</h5>
          <p class="text-2xs text-slate-300 leading-relaxed mt-1">
            Konsultasikan kelayakan pembelian OLT, ekspansi jaringan, atau analisis penagihan piutang dengan kalkulasi data riil sistem.
          </p>

          <div class="space-y-1.5 mt-3 text-2xs text-slate-200">
            <a href="<?php echo Helper::url('ai'); ?>" class="p-2 rounded-xl bg-white/10 hover:bg-white/20 transition-all flex items-center gap-2 border border-white/10 block">
              <i class="fa-solid fa-wand-magic-sparkles text-pink-400 text-3xs"></i>
              <span class="truncate">"Apakah saldo kas aman beli OLT Rp 35jt bulan ini?"</span>
            </a>
          </div>
        </div>

        <div class="pt-4 mt-2">
          <a href="<?php echo Helper::url('ai'); ?>" class="w-full inline-block py-2.5 px-4 text-center rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase shadow-soft-sm hover:scale-105 transition-all">
            <i class="fa-solid fa-comments mr-1.5"></i> Buka AI Assistant & Setting API
          </a>
        </div>
      </div>

      <!-- Package Distribution -->
      <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-soft-sm">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5 mb-3">
          <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400">Paket Internet Terpopuler</span>
          <a href="<?php echo Helper::url('packages'); ?>" class="text-3xs font-bold text-purple-700 hover:underline">Semua Paket &rarr;</a>
        </div>

        <div class="space-y-2.5">
          <?php foreach ($packageDist as $pkg): 
            $pct = ($custTotal > 0) ? round(($pkg['user_count'] / $custTotal) * 100, 1) : 0;
          ?>
          <div>
            <div class="flex items-center justify-between text-2xs mb-1">
              <span class="font-bold text-slate-800"><?php echo Helper::e($pkg['package_name']); ?> (<?php echo $pkg['download_speed']; ?> Mbps)</span>
              <span class="font-mono text-slate-500 font-bold"><?php echo $pkg['user_count']; ?> User (<?php echo $pct; ?>%)</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
              <div class="bg-purple-600 h-1.5 rounded-full" style="width: <?php echo $pct; ?>%"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>

  </div>

  <!-- Row 4: Recent Invoices & Live Activity Log -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    
    <!-- Invoices Table -->
    <div class="lg:col-span-8 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-sm">
      <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
        <div>
          <h5 class="font-black text-slate-900 text-base">Tagihan & Invoice Terbaru</h5>
          <p class="text-2xs text-slate-400">Daftar tagihan yang diterbitkan pada sistem</p>
        </div>
        <a href="<?php echo Helper::url('invoices'); ?>" class="text-xs font-bold text-purple-700 hover:text-purple-900 flex items-center gap-1">
          <span>Semua Invoice</span>
          <i class="fa-solid fa-arrow-right text-2xs"></i>
        </a>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left">
          <thead>
            <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
              <th class="py-2.5 font-bold">No. Invoice</th>
              <th class="py-2.5 font-bold">Pelanggan</th>
              <th class="py-2.5 font-bold text-right">Total Tagihan</th>
              <th class="py-2.5 font-bold text-center">Status</th>
              <th class="py-2.5 font-bold text-center">Jatuh Tempo</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (empty($recentInvoices)): ?>
              <tr>
                <td colspan="5" class="py-6 text-center text-slate-400 italic">Belum ada data tagihan.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($recentInvoices as $inv): ?>
              <tr class="hover:bg-slate-50/60 transition-colors">
                <td class="py-3">
                  <a href="<?php echo Helper::url('show_invoice', ['id' => $inv['id']]); ?>" class="font-bold text-purple-700 font-mono hover:underline block">
                    <?php echo Helper::e($inv['invoice_no']); ?>
                  </a>
                  <span class="text-3xs text-slate-400"><?php echo Helper::e($inv['package_name_snapshot'] ?: 'Paket Internet'); ?></span>
                </td>
                <td class="py-3">
                  <span class="font-bold text-slate-800 block"><?php echo Helper::e($inv['customer_name']); ?></span>
                  <span class="text-3xs text-slate-400"><?php echo Helper::e($inv['customer_no']); ?></span>
                </td>
                <td class="py-3 text-right font-bold text-slate-800 font-mono">
                  <?php echo Helper::formatRupiah($inv['grand_total']); ?>
                </td>
                <td class="py-3 text-center">
                  <?php echo Helper::statusBadge($inv['payment_status']); ?>
                </td>
                <td class="py-3 text-center text-slate-500 font-mono text-2xs">
                  <?php echo Helper::formatDate($inv['due_date']); ?>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Recent Activity Audit Stream -->
    <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-sm flex flex-col justify-between">
      <div>
        <div class="border-b border-slate-100 pb-4 mb-4 flex items-center justify-between">
          <div>
            <h5 class="font-black text-slate-900 text-base">Aktivitas Sistem</h5>
            <p class="text-2xs text-slate-400">Jejak audit operasional terkini</p>
          </div>
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
        </div>

        <div class="space-y-3.5">
          <?php if (empty($recentLogs)): ?>
            <p class="text-2xs text-slate-400 italic">Belum ada log aktivitas.</p>
          <?php else: ?>
            <?php foreach ($recentLogs as $log): ?>
            <div class="flex items-start gap-3 text-xs">
              <div class="w-7 h-7 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xs mt-0.5 shrink-0 border border-purple-100">
                <i class="fa-solid fa-shield-halved"></i>
              </div>
              <div class="flex-1">
                <p class="text-xs text-slate-700 leading-tight">
                  <span class="font-bold text-slate-900"><?php echo Helper::e($log['user_name'] ?? 'System'); ?></span>
                  &mdash; <span class="text-slate-600"><?php echo Helper::e($log['new_value'] ?? $log['old_value'] ?? $log['action']); ?></span>
                </p>
                <div class="flex items-center gap-2 mt-1">
                  <span class="text-3xs text-slate-400"><?php echo Helper::formatDate($log['created_at'], 'd/m/Y H:i'); ?></span>
                  <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-bold text-3xs uppercase"><?php echo Helper::e($log['module']); ?></span>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="pt-4 mt-4 border-t border-slate-100 text-center">
        <a href="<?php echo Helper::url('settings_company'); ?>" class="text-2xs font-bold text-slate-500 hover:text-purple-700">
          Lihat Pengaturan & Profil Sistem &rarr;
        </a>
      </div>
    </div>

  </div>

</div>

<!-- Chart.js Live Rendering Script -->
<script>
document.addEventListener("DOMContentLoaded", function() {
  const ctx = document.getElementById("financeCategoryChart");
  if (ctx && typeof Chart !== 'undefined') {
    const labels = <?php echo json_encode($chartLabels); ?>;
    const dataValues = <?php echo json_encode($chartData); ?>;
    const bgColors = <?php echo json_encode($chartColors); ?>;

    new Chart(ctx, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [{
          label: "Nominal (Rp)",
          data: dataValues,
          backgroundColor: bgColors,
          borderRadius: 8,
          borderSkipped: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: function(context) {
                const val = context.raw || 0;
                return "Rp " + new Intl.NumberFormat('id-ID').format(val);
              }
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: {
              font: { size: 10, weight: '600' },
              color: '#64748b'
            }
          },
          y: {
            beginAtZero: true,
            grid: { color: 'rgba(226, 232, 240, 0.6)' },
            ticks: {
              font: { size: 10 },
              color: '#94a3b8',
              callback: function(value) {
                if (value >= 1000000) {
                  return "Rp " + (value / 1000000).toFixed(1) + " Jt";
                } else if (value >= 1000) {
                  return "Rp " + (value / 1000).toFixed(0) + " Rb";
                }
                return "Rp " + value;
              }
            }
          }
        }
      }
    });
  }
});
</script>
