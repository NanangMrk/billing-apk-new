<?php
// app/Views/profit_loss/index.php - Profit and Loss Statement View
?>
<div class="flex flex-wrap -mx-3 mb-6">
  <div class="w-full max-w-full px-3">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl p-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h5 class="font-bold text-slate-800 text-lg">Laporan Laba Rugi Komprehensif</h5>
          <p class="text-xs text-slate-400">Analisis kinerja keuangan berbasis realisasi transaksi kas dan beban</p>
        </div>

        <form method="GET" action="<?php echo Helper::url('profit_loss'); ?>" class="flex items-center gap-2">
          <input type="hidden" name="page" value="profit_loss">
          <input type="month" name="month" value="<?php echo Helper::e($month); ?>" class="text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
          <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-xl text-xs font-bold shadow-soft-xs hover:bg-slate-900 transition-all">
            Lihat Laporan
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Profit Summary KPIs -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
  
  <div class="p-6 bg-white rounded-2xl shadow-soft-xl border border-slate-100 space-y-1">
    <span class="text-2xs font-bold uppercase tracking-wider text-slate-400">Total Pendapatan Usaha</span>
    <h4 class="text-xl font-black text-slate-800 mb-0"><?php echo Helper::formatRupiah($totalRevenue); ?></h4>
    <span class="text-2xs text-slate-400">Pemasukan riil diterima</span>
  </div>

  <div class="p-6 bg-white rounded-2xl shadow-soft-xl border border-slate-100 space-y-1">
    <span class="text-2xs font-bold uppercase tracking-wider text-slate-400">Laba Kotor (Gross Profit)</span>
    <h4 class="text-xl font-black text-purple-700 mb-0"><?php echo Helper::formatRupiah($grossProfit); ?></h4>
    <span class="text-2xs text-slate-400">Setelah beban bandwidth</span>
  </div>

  <div class="p-6 bg-white rounded-2xl shadow-soft-xl border border-slate-100 space-y-1">
    <span class="text-2xs font-bold uppercase tracking-wider text-slate-400">Total Beban Operasional</span>
    <h4 class="text-xl font-black text-red-600 mb-0"><?php echo Helper::formatRupiah($totalOpex); ?></h4>
    <span class="text-2xs text-slate-400">Listrik, BBM & biaya umum</span>
  </div>

  <div class="p-6 bg-white rounded-2xl shadow-soft-xl border border-slate-100 space-y-1">
    <span class="text-2xs font-bold uppercase tracking-wider text-slate-400">Laba Bersih (Net Profit)</span>
    <h4 class="text-xl font-black <?php echo $netProfit >= 0 ? 'text-green-600' : 'text-red-600'; ?> mb-0">
      <?php echo Helper::formatRupiah($netProfit); ?>
    </h4>
    <span class="text-2xs font-bold <?php echo $profitMargin >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
      Margin Laba: <?php echo $profitMargin; ?>%
    </span>
  </div>

</div>

<!-- Detailed Statement Card -->
<div class="flex flex-wrap -mx-3">
  <div class="w-full max-w-full px-3 mx-auto lg:w-9/12">
    <div class="relative flex flex-col min-w-0 break-words bg-white shadow-soft-xl rounded-2xl p-8 border border-slate-100 space-y-6">
      
      <div class="border-b border-slate-200 pb-4 flex justify-between items-center">
        <div>
          <h6 class="font-bold text-slate-800 text-base">Laporan Laba Rugi Operasional</h6>
          <p class="text-xs text-slate-400">Periode: <?php echo Helper::formatDate($monthStart, 'd F Y'); ?> s/d <?php echo Helper::formatDate($monthEnd, 'd F Y'); ?></p>
        </div>
        <button onclick="window.print()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">
          <i class="fa-solid fa-print mr-1"></i> Cetak Laporan
        </button>
      </div>

      <div class="space-y-6 text-xs">
        
        <!-- 1. Revenue -->
        <div>
          <div class="flex justify-between font-bold text-slate-900 text-sm border-b border-slate-200 pb-2 mb-2">
            <span>1. PENDAPATAN OPERASIONAL</span>
            <span><?php echo Helper::formatRupiah($totalRevenue); ?></span>
          </div>
          <div class="space-y-2 pl-4 text-slate-600">
            <?php foreach ($revenues as $rev): ?>
            <div class="flex justify-between">
              <span><?php echo Helper::e($rev['category_name']); ?></span>
              <span class="font-mono"><?php echo Helper::formatRupiah($rev['total_amount']); ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- 2. COGS -->
        <div>
          <div class="flex justify-between font-bold text-slate-900 text-sm border-b border-slate-200 pb-2 mb-2">
            <span>2. BEBAN POKOK PENDAPATAN / BANDWIDTH UPSTREAM</span>
            <span class="text-red-600 font-mono">-<?php echo Helper::formatRupiah($totalCogs); ?></span>
          </div>
          <div class="space-y-2 pl-4 text-slate-600">
            <?php foreach ($cogsItems as $cogs): ?>
            <div class="flex justify-between">
              <span><?php echo Helper::e($cogs['category_name']); ?></span>
              <span class="font-mono"><?php echo Helper::formatRupiah($cogs['total_amount']); ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Gross Profit Bar -->
        <div class="p-3 rounded-xl bg-purple-50 flex justify-between font-bold text-purple-900 text-sm">
          <span>LABA KOTOR (GROSS PROFIT)</span>
          <span><?php echo Helper::formatRupiah($grossProfit); ?></span>
        </div>

        <!-- 3. Operating Expenses -->
        <div>
          <div class="flex justify-between font-bold text-slate-900 text-sm border-b border-slate-200 pb-2 mb-2">
            <span>3. BEBAN OPERASIONAL & UMUM</span>
            <span class="text-red-600 font-mono">-<?php echo Helper::formatRupiah($totalOpex); ?></span>
          </div>
          <div class="space-y-2 pl-4 text-slate-600">
            <?php foreach ($opexItems as $op): ?>
            <div class="flex justify-between">
              <span><?php echo Helper::e($op['category_name']); ?></span>
              <span class="font-mono"><?php echo Helper::formatRupiah($op['total_amount']); ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Net Profit Final -->
        <div class="p-4 rounded-xl <?php echo $netProfit >= 0 ? 'bg-gradient-to-r from-green-600 to-lime-600 text-white' : 'bg-gradient-to-r from-red-600 to-rose-600 text-white'; ?> flex justify-between items-center font-bold text-base shadow-soft-md">
          <span>LABA BERSIH (NET PROFIT) BULAN INI</span>
          <span class="text-lg"><?php echo Helper::formatRupiah($netProfit); ?></span>
        </div>

      </div>

    </div>
  </div>
</div>
