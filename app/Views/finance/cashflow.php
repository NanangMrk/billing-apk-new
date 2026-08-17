<?php
// app/Views/finance/cashflow.php - Cashflow Statement
?>
<!-- Header & Month Filter -->
<div class="flex flex-wrap -mx-3 mb-6">
  <div class="w-full max-w-full px-3">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl p-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h5 class="font-bold text-slate-800 text-lg">Laporan Arus Kas (Cashflow)</h5>
          <p class="text-xs text-slate-400">Ringkasan arus kas masuk dan kas keluar per kategori</p>
        </div>

        <form method="GET" action="<?php echo Helper::url('cashflow'); ?>" class="flex items-center gap-2">
          <input type="hidden" name="page" value="cashflow">
          <input type="month" name="month" value="<?php echo Helper::e($month); ?>" class="text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
          <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold shadow-soft-xs hover:bg-slate-900 transition-all">
            Lihat Laporan
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Cashflow Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
  
  <div class="p-6 bg-white rounded-2xl shadow-soft-xl border border-slate-100 space-y-2">
    <div class="flex items-center justify-between">
      <span class="text-2xs font-bold uppercase tracking-wider text-slate-400">Total Kas Masuk (Inflow)</span>
      <div class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center text-xs">
        <i class="fa-solid fa-arrow-down-long"></i>
      </div>
    </div>
    <h4 class="text-2xl font-black text-green-600 mb-0"><?php echo Helper::formatRupiah($totalIncome); ?></h4>
    <span class="text-2xs text-slate-400">Penerimaan kas periode ini</span>
  </div>

  <div class="p-6 bg-white rounded-2xl shadow-soft-xl border border-slate-100 space-y-2">
    <div class="flex items-center justify-between">
      <span class="text-2xs font-bold uppercase tracking-wider text-slate-400">Total Kas Keluar (Outflow)</span>
      <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-xs">
        <i class="fa-solid fa-arrow-up-long"></i>
      </div>
    </div>
    <h4 class="text-2xl font-black text-red-600 mb-0"><?php echo Helper::formatRupiah($totalExpense); ?></h4>
    <span class="text-2xs text-slate-400">Pengeluaran & beban kas</span>
  </div>

  <div class="p-6 bg-white rounded-2xl shadow-soft-xl border border-slate-100 space-y-2">
    <div class="flex items-center justify-between">
      <span class="text-2xs font-bold uppercase tracking-wider text-slate-400">Arus Kas Bersih (Net Cashflow)</span>
      <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
        <i class="fa-solid fa-scale-balanced"></i>
      </div>
    </div>
    <h4 class="text-2xl font-black <?php echo $netCashflow >= 0 ? 'text-purple-700' : 'text-red-600'; ?> mb-0">
      <?php echo Helper::formatRupiah($netCashflow); ?>
    </h4>
    <span class="text-2xs text-slate-400">Surplus / Defisit kas bulanan</span>
  </div>

</div>

<!-- Breakdown Breakdown Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
  
  <!-- Inflow Breakdown -->
  <div class="p-6 bg-white rounded-2xl shadow-soft-xl border border-slate-100">
    <h6 class="font-bold text-slate-800 text-sm mb-4 text-green-700 flex items-center gap-2">
      <i class="fa-solid fa-circle-plus"></i> Rincian Sumber Kas Masuk
    </h6>
    <div class="space-y-3">
      <?php if (empty($incomes)): ?>
        <p class="text-xs text-slate-400 py-4 text-center">Belum ada transaksi kas masuk pada bulan ini.</p>
      <?php else: ?>
        <?php foreach ($incomes as $inc): ?>
        <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 text-xs">
          <span class="font-semibold text-slate-700"><?php echo Helper::e($inc['category_name']); ?></span>
          <span class="font-bold text-green-600 font-mono"><?php echo Helper::formatRupiah($inc['total_amount']); ?></span>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Outflow Breakdown -->
  <div class="p-6 bg-white rounded-2xl shadow-soft-xl border border-slate-100">
    <h6 class="font-bold text-slate-800 text-sm mb-4 text-red-600 flex items-center gap-2">
      <i class="fa-solid fa-circle-minus"></i> Rincian Beban & Kas Keluar
    </h6>
    <div class="space-y-3">
      <?php if (empty($expenses)): ?>
        <p class="text-xs text-slate-400 py-4 text-center">Belum ada transaksi kas keluar pada bulan ini.</p>
      <?php else: ?>
        <?php foreach ($expenses as $exp): ?>
        <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 text-xs">
          <span class="font-semibold text-slate-700"><?php echo Helper::e($exp['category_name']); ?></span>
          <span class="font-bold text-red-600 font-mono"><?php echo Helper::formatRupiah($exp['total_amount']); ?></span>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

</div>
