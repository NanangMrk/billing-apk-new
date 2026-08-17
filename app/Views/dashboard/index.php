<?php
// app/Views/dashboard/index.php - Executive Dashboard View
?>

<!-- Row 1: Primary KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
  
  <!-- KPI 1: Active Customers -->
  <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-soft-xl hover:shadow-soft-2xl transition-all duration-200">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-3xs font-extrabold uppercase tracking-wider text-slate-400">Pelanggan Aktif</p>
        <h4 class="text-2xl font-black text-slate-800 tracking-tight mt-1">
          <?php echo $custActive; ?> <span class="text-xs font-semibold text-slate-400">/ <?php echo $custTotal; ?> Total</span>
        </h4>
        <p class="text-2xs text-slate-400 mt-1">
          <span class="font-bold text-red-500"><?php echo $custSuspended; ?></span> isolir / nonaktif
        </p>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-lg shadow-soft-md shrink-0">
        <i class="fa-solid fa-users"></i>
      </div>
    </div>
  </div>

  <!-- KPI 2: Monthly Billing -->
  <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-soft-xl hover:shadow-soft-2xl transition-all duration-200">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-3xs font-extrabold uppercase tracking-wider text-slate-400">Tagihan Bulan Ini</p>
        <h4 class="text-xl font-black text-slate-800 tracking-tight mt-1">
          <?php echo Helper::formatRupiah($billKpi['total_amount']); ?>
        </h4>
        <p class="text-2xs text-green-600 font-bold mt-1">
          Terbayar: <?php echo Helper::formatRupiah($billKpi['paid_amount']); ?>
        </p>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-tl from-green-600 to-lime-400 text-white flex items-center justify-center text-lg shadow-soft-md shrink-0">
        <i class="fa-solid fa-file-invoice-dollar"></i>
      </div>
    </div>
  </div>

  <!-- KPI 3: Cash & Bank -->
  <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-soft-xl hover:shadow-soft-2xl transition-all duration-200">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-3xs font-extrabold uppercase tracking-wider text-slate-400">Saldo Kas & Bank</p>
        <h4 class="text-xl font-black text-slate-800 tracking-tight mt-1">
          <?php echo Helper::formatRupiah($cashBalance); ?>
        </h4>
        <p class="text-2xs text-slate-400 mt-1">
          Total likuiditas aktif
        </p>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-tl from-blue-600 to-cyan-400 text-white flex items-center justify-center text-lg shadow-soft-md shrink-0">
        <i class="fa-solid fa-wallet"></i>
      </div>
    </div>
  </div>

  <!-- KPI 4: Net Profit -->
  <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-soft-xl hover:shadow-soft-2xl transition-all duration-200">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-3xs font-extrabold uppercase tracking-wider text-slate-400">Laba Bersih Bulan Ini</p>
        <h4 class="text-xl font-black <?php echo $netProfit >= 0 ? 'text-slate-800' : 'text-red-600'; ?> tracking-tight mt-1">
          <?php echo Helper::formatRupiah($netProfit); ?>
        </h4>
        <p class="text-2xs text-slate-400 mt-1">
          Pemasukan - Beban
        </p>
      </div>
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-lg shadow-soft-md shrink-0">
        <i class="fa-solid fa-chart-line"></i>
      </div>
    </div>
  </div>

</div>

<!-- Row 2: Secondary Operational KPIs -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  
  <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-soft-xs flex items-center justify-between">
    <div>
      <span class="text-3xs text-slate-400 font-extrabold uppercase block tracking-wider">Sisa Piutang Berjalan</span>
      <span class="text-base font-extrabold text-orange-600 font-mono"><?php echo Helper::formatRupiah($billKpi['unpaid_amount']); ?></span>
    </div>
    <div class="w-9 h-9 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm">
      <i class="fa-solid fa-clock-rotate-left"></i>
    </div>
  </div>

  <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-soft-xs flex items-center justify-between">
    <div>
      <span class="text-3xs text-slate-400 font-extrabold uppercase block tracking-wider">Nilai Stok & Aset</span>
      <span class="text-base font-extrabold text-slate-800 font-mono"><?php echo Helper::formatRupiah($inventoryValue + $assetValue); ?></span>
    </div>
    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm">
      <i class="fa-solid fa-boxes-stacked"></i>
    </div>
  </div>

  <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-soft-xs flex items-center justify-between">
    <div>
      <span class="text-3xs text-slate-400 font-extrabold uppercase block tracking-wider">Stok Menipis</span>
      <span class="text-base font-extrabold <?php echo $lowStockCount > 0 ? 'text-red-600' : 'text-slate-800'; ?>">
        <?php echo $lowStockCount; ?> Item
      </span>
    </div>
    <div class="w-9 h-9 rounded-xl bg-red-50 text-red-600 flex items-center justify-center text-sm">
      <i class="fa-solid fa-triangle-exclamation"></i>
    </div>
  </div>

  <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-soft-xs flex items-center justify-between">
    <div>
      <span class="text-3xs text-slate-400 font-extrabold uppercase block tracking-wider">Tiket Gangguan Terbuka</span>
      <span class="text-base font-extrabold text-blue-600"><?php echo $openTickets; ?> Kasus</span>
    </div>
    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
      <i class="fa-solid fa-ticket"></i>
    </div>
  </div>

</div>

<!-- Row 3: Charts and AI Advisor Shortcut -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
  
  <!-- Revenue & Expense Chart -->
  <div class="lg:col-span-7 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl flex flex-col justify-between">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
      <div>
        <h5 class="font-extrabold text-slate-900 text-base">Arus Keuangan & Beban Operasional</h5>
        <p class="text-2xs text-slate-400">Pemasukan riil vs pengeluaran bandwidth dan operasional</p>
      </div>
      <span class="text-xs font-bold px-3 py-1 rounded-xl bg-purple-50 text-purple-700 font-mono">Periode <?php echo date('F Y'); ?></span>
    </div>
    <div class="h-64 relative">
      <canvas id="financeChart" class="w-full h-full"></canvas>
    </div>
  </div>

  <!-- AI Advisor Card Banner -->
  <div class="lg:col-span-5 bg-gradient-to-br from-slate-900 via-indigo-950 to-purple-950 text-white rounded-3xl p-6 border border-purple-500/20 shadow-soft-2xl flex flex-col justify-between">
    <div>
      <div class="flex items-center gap-2 mb-3">
        <div class="w-8 h-8 rounded-xl bg-pink-500/20 text-pink-400 border border-pink-500/40 flex items-center justify-center text-sm">
          <i class="fa-solid fa-robot"></i>
        </div>
        <span class="text-xs font-bold uppercase tracking-wider text-pink-300">ISP AI Business Advisor</span>
      </div>
      <h4 class="text-lg font-black text-white mb-2 leading-snug">Simulasi Bisnis & Kelayakan Belanja Modal</h4>
      <p class="text-xs text-slate-300 leading-relaxed mb-4">
        AI Asisten menganalisis saldo kas riil, proyeksi piutang, dan kewajiban bulanan untuk memvalidasi kelayakan investasi ISP Anda.
      </p>

      <div class="space-y-2 text-2xs text-slate-200">
        <div class="p-2.5 rounded-xl bg-white/10 flex items-center gap-2 border border-white/10">
          <i class="fa-solid fa-circle-question text-pink-400"></i>
          <span>"Apakah kas kita cukup untuk membeli 1 unit OLT bulan ini?"</span>
        </div>
        <div class="p-2.5 rounded-xl bg-white/10 flex items-center gap-2 border border-white/10">
          <i class="fa-solid fa-circle-question text-pink-400"></i>
          <span>"Berapa total piutang yang jatuh tempo di atas 10 hari?"</span>
        </div>
      </div>
    </div>

    <div class="pt-6">
      <a href="<?php echo Helper::url('ai'); ?>" class="w-full inline-block py-3 px-4 text-center rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase shadow-soft-md hover:scale-105 transition-all">
        <i class="fa-solid fa-comments mr-1.5"></i> Buka AI Assistant & Advisor
      </a>
    </div>
  </div>

</div>

<!-- Row 4: Recent Invoices & Activity Log -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
  
  <!-- Invoices Table -->
  <div class="lg:col-span-8 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl">
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
      <div>
        <h5 class="font-extrabold text-slate-900 text-base">Tagihan & Invoice Terbaru</h5>
        <p class="text-2xs text-slate-400">Daftar tagihan yang diterbitkan bulan berjalan</p>
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
          <?php foreach ($recentInvoices as $inv): ?>
          <tr>
            <td class="py-3">
              <a href="<?php echo Helper::url('show_invoice', ['id' => $inv['id']]); ?>" class="font-bold text-purple-700 font-mono hover:underline block">
                <?php echo Helper::e($inv['invoice_no']); ?>
              </a>
              <span class="text-3xs text-slate-400"><?php echo Helper::e($inv['package_name_snapshot']); ?></span>
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
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Activity Feed -->
  <div class="lg:col-span-4 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl">
    <div class="border-b border-slate-100 pb-4 mb-4">
      <h5 class="font-extrabold text-slate-900 text-base">Aktivitas Sistem</h5>
      <p class="text-2xs text-slate-400">Jejak audit operasional terkini</p>
    </div>

    <div class="space-y-3.5">
      <?php foreach ($recentLogs as $log): ?>
      <div class="flex items-start gap-3 text-xs">
        <div class="w-7 h-7 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-2xs mt-0.5 shrink-0">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div>
          <p class="text-xs text-slate-700 leading-tight">
            <span class="font-bold text-slate-900"><?php echo Helper::e($log['user_name'] ?? 'System'); ?></span>
            - <?php echo Helper::e($log['old_value'] ?? $log['new_value'] ?? $log['action']); ?>
          </p>
          <span class="text-3xs text-slate-400 block mt-1"><?php echo Helper::formatDate($log['created_at'], 'd/m/Y H:i'); ?> &bull; Modul: <?php echo Helper::e($log['module']); ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- Chart Initialization Script -->
<script>
document.addEventListener("DOMContentLoaded", function() {
  const ctx = document.getElementById("financeChart");
  if (ctx) {
    new Chart(ctx, {
      type: "bar",
      data: {
        labels: ["Pendapatan Billing", "Penerimaan Kas", "Beban Bandwidth", "Listrik/POP", "Operasional"],
        datasets: [{
          label: "Nominal (Rp)",
          data: [
            <?php echo $billKpi['total_amount']; ?>,
            <?php echo $finKpi['total_income']; ?>,
            15000000,
            1850000,
            450000
          ],
          backgroundColor: [
            "rgba(121, 40, 202, 0.85)",
            "rgba(22, 163, 74, 0.85)",
            "rgba(220, 38, 38, 0.85)",
            "rgba(234, 88, 12, 0.85)",
            "rgba(14, 165, 233, 0.85)"
          ],
          borderRadius: 10
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function(value) {
                return "Rp " + (value / 1000000).toFixed(1) + "Jt";
              }
            }
          }
        }
      }
    });
  }
});
</script>
