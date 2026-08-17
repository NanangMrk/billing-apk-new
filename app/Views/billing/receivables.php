<?php
// app/Views/billing/receivables.php - Aging Piutang & Collection Analysis with Sleek Filter Modal & Pagination
$activeFilterCount = 0;
if (!empty($_GET['aging_bucket'])) $activeFilterCount++;
if (!empty($_GET['period'])) $activeFilterCount++;
if (!empty($_GET['package_id'])) $activeFilterCount++;
if (!empty($_GET['location_id'])) $activeFilterCount++;
if (!empty($_GET['pic_id'])) $activeFilterCount++;

$hasActiveFilters = ($activeFilterCount > 0 || !empty($_GET['search']));
$limitValue = $limitValue ?? '10';
$perPage = $perPage ?? 10;
$totalUnpaidCount = $totalUnpaidCount ?? count($unpaidInvoices);
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$offset = $offset ?? 0;
?>
<div class="space-y-6">

  <!-- KPI Aging Summary Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Card 1: 1 - 7 Hari -->
    <a href="<?php echo Helper::url('receivables', ['aging_bucket' => '1_7']); ?>" class="p-4 bg-white rounded-3xl shadow-soft-xl border border-slate-100/80 hover:scale-[1.02] transition-all group">
      <div class="flex items-center justify-between mb-2">
        <span class="text-3xs font-extrabold text-slate-400 uppercase tracking-wider">Jatuh Tempo 1 - 7 Hari</span>
        <span class="w-6 h-6 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-xs group-hover:bg-orange-600 group-hover:text-white transition-colors">
          <i class="fa-solid fa-clock"></i>
        </span>
      </div>
      <h5 class="text-lg font-black text-orange-600 font-mono"><?php echo Helper::formatRupiah($bucket1_7); ?></h5>
      <span class="text-3xs text-slate-400">Peringatan ramah WhatsApp</span>
    </a>

    <!-- Card 2: 8 - 30 Hari -->
    <a href="<?php echo Helper::url('receivables', ['aging_bucket' => '8_30']); ?>" class="p-4 bg-white rounded-3xl shadow-soft-xl border border-slate-100/80 hover:scale-[1.02] transition-all group">
      <div class="flex items-center justify-between mb-2">
        <span class="text-3xs font-extrabold text-slate-400 uppercase tracking-wider">Jatuh Tempo 8 - 30 Hari</span>
        <span class="w-6 h-6 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-xs group-hover:bg-red-500 group-hover:text-white transition-colors">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </span>
      </div>
      <h5 class="text-lg font-black text-red-500 font-mono"><?php echo Helper::formatRupiah($bucket8_30); ?></h5>
      <span class="text-3xs text-slate-400">Prioritas isolir layanan</span>
    </a>

    <!-- Card 3: > 30 Hari -->
    <a href="<?php echo Helper::url('receivables', ['aging_bucket' => '31_60']); ?>" class="p-4 bg-white rounded-3xl shadow-soft-xl border border-slate-100/80 hover:scale-[1.02] transition-all group">
      <div class="flex items-center justify-between mb-2">
        <span class="text-3xs font-extrabold text-slate-400 uppercase tracking-wider">Jatuh Tempo &gt; 30 Hari</span>
        <span class="w-6 h-6 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs group-hover:bg-rose-600 group-hover:text-white transition-colors">
          <i class="fa-solid fa-person-walking"></i>
        </span>
      </div>
      <h5 class="text-lg font-black text-rose-600 font-mono"><?php echo Helper::formatRupiah($bucket31_60 + $bucketOver60); ?></h5>
      <span class="text-3xs text-slate-400">Kunjungan penagihan lapangan</span>
    </a>

    <!-- Card 4: Total Piutang -->
    <div class="p-4 bg-gradient-to-tl from-purple-700 to-pink-500 text-white rounded-3xl shadow-soft-xl">
      <div class="flex items-center justify-between mb-2">
        <span class="text-3xs font-extrabold text-purple-200 uppercase tracking-wider">Total Piutang Berjalan</span>
        <span class="w-6 h-6 rounded-xl bg-white/20 text-white flex items-center justify-center text-xs">
          <i class="fa-solid fa-file-invoice-dollar"></i>
        </span>
      </div>
      <h5 class="text-lg font-black font-mono"><?php echo Helper::formatRupiah($totalOverdue); ?></h5>
      <span class="text-3xs text-purple-200">Semua tagihan belum lunas</span>
    </div>
  </div>

  <!-- Main Table Card -->
  <div class="bg-white rounded-3xl p-6 shadow-soft-xl space-y-5 relative">
    
    <!-- Card Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
      <div>
        <h4 class="font-black text-slate-900 text-lg tracking-tight">Daftar Tagihan Belum Terbayar & Overdue</h4>
        <p class="text-2xs text-slate-400">Pengawasan umur piutang (*aging*), reminder WhatsApp, dan tindakan penagihan</p>
      </div>

      <div class="flex items-center gap-2">
        <span class="text-3xs font-bold text-slate-400 uppercase">Total Piutang Filter:</span>
        <span class="text-xs font-black text-purple-700 font-mono bg-purple-50 px-3 py-1.5 rounded-xl border border-purple-200/60">
          <?php 
            $filteredDue = 0;
            foreach ($unpaidInvoices as $u) { $filteredDue += (int)$u['balance_due']; }
            echo Helper::formatRupiah($filteredDue);
          ?>
        </span>
      </div>
    </div>

    <!-- Sleek Filter Bar + Per-Page Limit Selector -->
    <div class="space-y-2">
      <form method="GET" action="<?php echo Helper::url('receivables'); ?>" class="flex flex-wrap sm:flex-nowrap items-center gap-2">
        <input type="hidden" name="page" value="receivables">
        <?php if (!empty($_GET['aging_bucket'])): ?><input type="hidden" name="aging_bucket" value="<?php echo Helper::e($_GET['aging_bucket']); ?>"><?php endif; ?>
        <?php if (!empty($_GET['period'])): ?><input type="hidden" name="period" value="<?php echo Helper::e($_GET['period']); ?>"><?php endif; ?>
        <?php if (!empty($_GET['package_id'])): ?><input type="hidden" name="package_id" value="<?php echo Helper::e($_GET['package_id']); ?>"><?php endif; ?>
        <?php if (!empty($_GET['location_id'])): ?><input type="hidden" name="location_id" value="<?php echo Helper::e($_GET['location_id']); ?>"><?php endif; ?>
        <?php if (!empty($_GET['pic_id'])): ?><input type="hidden" name="pic_id" value="<?php echo Helper::e($_GET['pic_id']); ?>"><?php endif; ?>
        <input type="hidden" name="limit" value="<?php echo Helper::e($limitValue); ?>">

        <!-- Search Input -->
        <div class="relative flex-1 min-w-[180px]">
          <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
            <i class="fa-solid fa-magnifying-glass text-xs"></i>
          </span>
          <input type="text" name="search" value="<?php echo Helper::e($_GET['search'] ?? ''); ?>" placeholder="Cari no invoice, nama pelanggan, kontak, area..." class="w-full text-xs pl-8 pr-3 py-2 sm:py-2.5 rounded-2xl bg-slate-50 border border-slate-200/80 focus:bg-white focus:outline-none focus:border-purple-500 shadow-soft-xs placeholder:text-slate-400 font-medium">
        </div>

        <!-- Limit / Per-Page Selector (10, 25, 50, 100, semua - default 10) -->
        <div class="relative">
          <select onchange="changeLimit(this.value)" class="text-xs font-bold text-slate-700 bg-white border border-slate-200 rounded-2xl px-3 py-2 sm:py-2.5 shadow-soft-xs focus:outline-none focus:border-purple-500 cursor-pointer">
            <option value="10" <?php echo ($limitValue === '10') ? 'selected' : ''; ?>>10 / hal</option>
            <option value="25" <?php echo ($limitValue === '25') ? 'selected' : ''; ?>>25 / hal</option>
            <option value="50" <?php echo ($limitValue === '50') ? 'selected' : ''; ?>>50 / hal</option>
            <option value="100" <?php echo ($limitValue === '100') ? 'selected' : ''; ?>>100 / hal</option>
            <option value="all" <?php echo ($limitValue === 'all') ? 'selected' : ''; ?>>Semua</option>
          </select>
        </div>

        <!-- Filter Modal Trigger Button -->
        <button type="button" onclick="openReceivablesFilterModal()" class="px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-bold rounded-2xl border transition-all flex items-center gap-1.5 shadow-soft-xs whitespace-nowrap <?php echo ($activeFilterCount > 0) ? 'bg-purple-50 text-purple-700 border-purple-300 ring-2 ring-purple-500/20' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-200'; ?>">
          <i class="fa-solid fa-sliders text-purple-600 text-xs"></i>
          <span class="hidden sm:inline">Filter</span>
          <?php if ($activeFilterCount > 0): ?>
            <span class="w-4 h-4 rounded-full bg-gradient-to-tl from-purple-700 to-pink-500 text-white text-3xs font-extrabold flex items-center justify-center"><?php echo $activeFilterCount; ?></span>
          <?php endif; ?>
        </button>

        <!-- Submit Search Button -->
        <button type="submit" class="px-3.5 sm:px-4 py-2 sm:py-2.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-2xl shadow-soft-xs transition-all flex items-center justify-center">
          <i class="fa-solid fa-arrow-right text-xs sm:hidden"></i>
          <span class="hidden sm:inline">Cari</span>
        </button>

        <!-- Reset Button -->
        <?php if ($hasActiveFilters): ?>
          <a href="<?php echo Helper::url('receivables', ['limit' => $limitValue]); ?>" class="p-2 sm:px-3 sm:py-2.5 text-xs font-bold text-slate-500 hover:text-red-600 bg-white hover:bg-red-50 border border-slate-200 rounded-2xl transition-all flex items-center justify-center" title="Reset Semua Filter">
            <i class="fa-solid fa-rotate-left text-xs"></i>
            <span class="hidden sm:inline ml-1">Reset</span>
          </a>
        <?php endif; ?>
      </form>

      <!-- Active Filter Chips / Badges -->
      <?php if ($activeFilterCount > 0): ?>
        <div class="flex flex-wrap items-center gap-1.5 pt-1">
          <span class="text-3xs text-slate-400 font-bold uppercase tracking-wider mr-1">Filter Aktif:</span>
          
          <?php if (!empty($_GET['aging_bucket'])): 
            $bucketLabels = [
              '1_7' => '1 - 7 Hari (Peringatan)',
              '8_30' => '8 - 30 Hari (Isolir)',
              '31_60' => '31 - 60 Hari',
              'over_60' => '> 60 Hari',
              'not_due' => 'Belum Jatuh Tempo'
            ];
          ?>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-orange-50 border border-orange-200 text-orange-700 text-3xs font-bold">
              <i class="fa-solid fa-hourglass-half text-3xs"></i>
              <span>Umur: <?php echo $bucketLabels[$_GET['aging_bucket']] ?? Helper::e($_GET['aging_bucket']); ?></span>
            </span>
          <?php endif; ?>

          <?php if (!empty($_GET['period'])): ?>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-purple-50 border border-purple-200 text-purple-700 text-3xs font-bold">
              <i class="fa-regular fa-calendar text-3xs"></i>
              <span>Bulan: <?php echo Helper::e($_GET['period']); ?></span>
            </span>
          <?php endif; ?>

          <?php if (!empty($_GET['package_id'])): 
            $pkgName = '';
            foreach ($packages as $p) { if ((int)$p['id'] === (int)$_GET['package_id']) { $pkgName = $p['name']; break; } }
          ?>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-pink-50 border border-pink-200 text-pink-700 text-3xs font-bold">
              <i class="fa-solid fa-wifi text-3xs"></i>
              <span>Paket: <?php echo Helper::e($pkgName ?: 'ID ' . $_GET['package_id']); ?></span>
            </span>
          <?php endif; ?>

          <?php if (!empty($_GET['location_id'])): 
            $locName = '';
            foreach ($locations as $l) { if ((int)$l['id'] === (int)$_GET['location_id']) { $locName = $l['area_name']; break; } }
          ?>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-3xs font-bold">
              <i class="fa-solid fa-map-location-dot text-3xs"></i>
              <span>Area: <?php echo Helper::e($locName ?: 'ID ' . $_GET['location_id']); ?></span>
            </span>
          <?php endif; ?>

          <?php if (!empty($_GET['pic_id'])): 
            $picLabel = '';
            foreach ($pics as $pc) { if ((int)$pc['id'] === (int)$_GET['pic_id']) { $picLabel = $pc['name']; break; } }
          ?>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 text-3xs font-bold">
              <i class="fa-solid fa-user-tie text-3xs"></i>
              <span>PIC: <?php echo Helper::e($picLabel ?: 'ID ' . $_GET['pic_id']); ?></span>
            </span>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Table Body -->
    <div class="overflow-x-auto">
      <table class="w-full text-xs text-left">
        <thead>
          <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
            <th class="py-3 px-4 font-bold text-center w-10">No</th>
            <th class="py-3 px-4 font-bold">No. Invoice & Periode</th>
            <th class="py-3 px-4 font-bold">Pelanggan & Lokasi</th>
            <th class="py-3 px-4 font-bold text-center">Jatuh Tempo</th>
            <th class="py-3 px-4 font-bold text-center">Keterlambatan</th>
            <th class="py-3 px-4 font-bold text-right">Sisa Piutang</th>
            <th class="py-3 px-4 font-bold text-center">Aksi Penagihan</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($unpaidInvoices)): ?>
          <tr>
            <td colspan="7" class="py-12 text-center text-slate-400">
              <i class="fa-solid fa-circle-check text-3xl mb-2 text-emerald-400 block"></i>
              <span class="text-xs font-semibold text-slate-600">Tidak ada data piutang / tagihan overdue yang sesuai filter.</span>
            </td>
          </tr>
          <?php else: ?>
            <?php $no = ($offset ?? 0) + 1; foreach ($unpaidInvoices as $inv): 
              $days = (int)$inv['days_overdue'];
            ?>
            <tr class="hover:bg-slate-50/80 transition-colors">
              <!-- No -->
              <td class="py-3.5 px-4 text-center whitespace-nowrap font-bold text-slate-500 font-mono text-xs">
                <?php echo $no++; ?>
              </td>

              <!-- No. Invoice & Periode -->
              <td class="py-3.5 px-4 whitespace-nowrap">
                <a href="<?php echo Helper::url('show_invoice', ['id' => $inv['id']]); ?>" class="font-bold text-xs text-purple-700 hover:underline block font-mono">
                  <?php echo Helper::e($inv['invoice_no']); ?>
                </a>
                <span class="text-3xs text-slate-400 font-mono">Periode: <?php echo Helper::e($inv['billing_period']); ?></span>
              </td>

              <!-- Pelanggan & Area -->
              <td class="py-3.5 px-4">
                <span class="font-black text-slate-900 text-xs block leading-snug"><?php echo Helper::e($inv['customer_name']); ?></span>
                <div class="flex items-center gap-1.5 text-3xs text-slate-400 mt-0.5">
                  <span class="font-mono text-purple-600 font-semibold"><?php echo Helper::e($inv['customer_no']); ?></span>
                  <?php if (!empty($inv['area_name'])): ?>
                    <span>&bull; <?php echo Helper::e($inv['area_name']); ?></span>
                  <?php endif; ?>
                  <?php if (!empty($inv['pic_name'])): ?>
                    <span>&bull; PIC: <strong><?php echo Helper::e($inv['pic_name']); ?></strong></span>
                  <?php endif; ?>
                </div>
              </td>

              <!-- Jatuh Tempo -->
              <td class="py-3.5 px-4 text-center whitespace-nowrap text-xs font-mono text-slate-600">
                <?php echo Helper::formatDate($inv['due_date']); ?>
              </td>

              <!-- Keterlambatan Badge -->
              <td class="py-3.5 px-4 text-center whitespace-nowrap">
                <?php if ($days > 30): ?>
                  <span class="px-2.5 py-1 rounded-full bg-rose-100 text-rose-700 font-extrabold text-3xs inline-flex items-center gap-1">
                    <i class="fa-solid fa-circle-exclamation text-3xs"></i>
                    <span>Telat <?php echo $days; ?> Hari</span>
                  </span>
                <?php elseif ($days >= 8): ?>
                  <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700 font-bold text-3xs inline-flex items-center gap-1">
                    <i class="fa-solid fa-triangle-exclamation text-3xs"></i>
                    <span>Telat <?php echo $days; ?> Hari</span>
                  </span>
                <?php elseif ($days >= 1): ?>
                  <span class="px-2.5 py-1 rounded-full bg-orange-100 text-orange-700 font-bold text-3xs inline-flex items-center gap-1">
                    <i class="fa-solid fa-clock text-3xs"></i>
                    <span>Telat <?php echo $days; ?> Hari</span>
                  </span>
                <?php else: ?>
                  <span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-semibold text-3xs inline-flex items-center gap-1">
                    <i class="fa-regular fa-clock text-3xs"></i>
                    <span>Belum Jatuh Tempo</span>
                  </span>
                <?php endif; ?>
              </td>

              <!-- Sisa Piutang -->
              <td class="py-3.5 px-4 text-right whitespace-nowrap font-mono">
                <span class="text-xs font-black text-rose-600 block"><?php echo Helper::formatRupiah($inv['balance_due']); ?></span>
                <?php if ($inv['paid_amount'] > 0): ?>
                  <span class="text-3xs text-slate-400">Terbayar: <?php echo Helper::formatRupiah($inv['paid_amount']); ?></span>
                <?php endif; ?>
              </td>

              <!-- Aksi Penagihan -->
              <td class="py-3.5 px-4 text-center whitespace-nowrap">
                <div class="flex items-center justify-center gap-1.5">
                  <!-- WhatsApp Reminder -->
                  <?php 
                    $waPhone = preg_replace('/[^0-9]/', '', $inv['whatsapp'] ?: $inv['phone']);
                    if (!empty($waPhone) && $waPhone !== '-'):
                      $waMsg = urlencode("Halo Bapak/Ibu " . $inv['customer_name'] . ", kami dari bagian penagihan ingin menginformasikan tagihan internet " . $inv['invoice_no'] . " sebesar " . Helper::formatRupiah($inv['balance_due']) . " jatuh tempo pada " . Helper::formatDate($inv['due_date']) . ". Mohon segera melakukan pembayaran agar layanan tetap aktif. Terima kasih.");
                  ?>
                    <a href="https://wa.me/<?php echo $waPhone; ?>?text=<?php echo $waMsg; ?>" target="_blank" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-2xs rounded-xl transition-colors inline-flex items-center gap-1" title="Kirim Pengingat WhatsApp">
                      <i class="fa-brands fa-whatsapp text-xs"></i>
                      <span>Ingatkan</span>
                    </a>
                  <?php endif; ?>

                  <!-- Rincian Invoice -->
                  <a href="<?php echo Helper::url('show_invoice', ['id' => $inv['id']]); ?>" class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-2xs rounded-xl transition-colors inline-flex items-center gap-1" title="Lihat Rincian & Catat Bayar">
                    <i class="fa-solid fa-eye text-3xs"></i>
                    <span>Rincian</span>
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination & Count Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-slate-100 text-xs">
      <div class="text-slate-400 text-3xs sm:text-2xs font-medium">
        <?php 
          $startItem = ($totalUnpaidCount > 0) ? ($offset + 1) : 0;
          $endItem = ($perPage > 0) ? min($totalUnpaidCount, $offset + $perPage) : $totalUnpaidCount;
        ?>
        Menampilkan <strong class="text-slate-700 font-bold font-mono"><?php echo $startItem; ?> - <?php echo $endItem; ?></strong> dari <strong class="text-slate-700 font-bold font-mono"><?php echo $totalUnpaidCount; ?></strong> data piutang
      </div>

      <?php if ($totalPages > 1): ?>
        <div class="flex items-center gap-1">
          <?php if ($currentPage > 1): 
            $prevUrl = Helper::url('receivables', array_merge($_GET, ['p' => $currentPage - 1]));
          ?>
            <a href="<?php echo $prevUrl; ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-3xs font-bold transition-all">
              <i class="fa-solid fa-chevron-left mr-1"></i> Prev
            </a>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $totalPages; $i++): 
            if ($i == 1 || $i == $totalPages || ($i >= $currentPage - 1 && $i <= $currentPage + 1)):
              $pUrl = Helper::url('receivables', array_merge($_GET, ['p' => $i]));
              $isActive = ($i === $currentPage);
          ?>
            <a href="<?php echo $pUrl; ?>" class="w-7 h-7 flex items-center justify-center rounded-xl text-3xs font-bold transition-all <?php echo $isActive ? 'bg-gradient-to-tl from-purple-700 to-pink-500 text-white shadow-soft-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'; ?>">
              <?php echo $i; ?>
            </a>
          <?php elseif ($i == $currentPage - 2 || $i == $currentPage + 2): ?>
            <span class="px-1 text-slate-400 font-bold text-3xs">...</span>
          <?php endif; endfor; ?>

          <?php if ($currentPage < $totalPages): 
            $nextUrl = Helper::url('receivables', array_merge($_GET, ['p' => $currentPage + 1]));
          ?>
            <a href="<?php echo $nextUrl; ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-3xs font-bold transition-all">
              Next <i class="fa-solid fa-chevron-right ml-1"></i>
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

  </div>

</div>

<!-- Modal Pop-Up: Filter Data Piutang & Aging -->
<div id="receivablesFilterModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-7 transform transition-all duration-300 scale-95 opacity-0 max-h-[90vh] overflow-y-auto" id="receivablesFilterModalContent">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 mb-4">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-sm shadow-soft-md shrink-0">
          <i class="fa-solid fa-sliders"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Filter Piutang & Umur Tagihan</h4>
          <span class="text-3xs text-slate-400">Saring piutang berdasarkan keterlambatan, periode, atau area</span>
        </div>
      </div>
      <button type="button" onclick="closeReceivablesFilterModal()" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="<?php echo Helper::url('receivables'); ?>" class="space-y-3.5 text-xs">
      <input type="hidden" name="page" value="receivables">
      <input type="hidden" name="limit" value="<?php echo Helper::e($limitValue); ?>">
      <?php if (!empty($_GET['search'])): ?>
        <input type="hidden" name="search" value="<?php echo Helper::e($_GET['search']); ?>">
      <?php endif; ?>

      <!-- 1. Kategori Umur Piutang (Aging Bucket) -->
      <div>
        <label class="font-bold text-slate-700 block mb-1">Kategori Keterlambatan (Aging)</label>
        <select name="aging_bucket" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
          <option value="">-- Semua Keterlambatan --</option>
          <option value="1_7" <?php echo (($_GET['aging_bucket'] ?? '') === '1_7') ? 'selected' : ''; ?>>Telat 1 - 7 Hari (Peringatan)</option>
          <option value="8_30" <?php echo (($_GET['aging_bucket'] ?? '') === '8_30') ? 'selected' : ''; ?>>Telat 8 - 30 Hari (Isolir Layanan)</option>
          <option value="31_60" <?php echo (($_GET['aging_bucket'] ?? '') === '31_60') ? 'selected' : ''; ?>>Telat 31 - 60 Hari (Kunjungan)</option>
          <option value="over_60" <?php echo (($_GET['aging_bucket'] ?? '') === 'over_60') ? 'selected' : ''; ?>>Telat &gt; 60 Hari</option>
          <option value="not_due" <?php echo (($_GET['aging_bucket'] ?? '') === 'not_due') ? 'selected' : ''; ?>>Belum Jatuh Tempo</option>
        </select>
      </div>

      <!-- 2. Target Periode Bulan -->
      <div>
        <label class="font-bold text-slate-700 block mb-1">Target Periode Tagihan</label>
        <input type="month" name="period" value="<?php echo Helper::e($_GET['period'] ?? ''); ?>" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
      </div>

      <!-- 3. Paket Layanan -->
      <div>
        <label class="font-bold text-slate-700 block mb-1">Paket Layanan Internet</label>
        <select name="package_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
          <option value="">-- Semua Paket Layanan --</option>
          <?php foreach ($packages as $pkg): ?>
            <option value="<?php echo $pkg['id']; ?>" <?php echo (((int)($_GET['package_id'] ?? 0)) === (int)$pkg['id']) ? 'selected' : ''; ?>>
              <?php echo Helper::e($pkg['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- 4. Area Coverage -->
      <div>
        <label class="font-bold text-slate-700 block mb-1">Area Coverage / Lokasi</label>
        <select name="location_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
          <option value="">-- Semua Area Coverage --</option>
          <?php foreach ($locations as $loc): ?>
            <option value="<?php echo $loc['id']; ?>" <?php echo (((int)($_GET['location_id'] ?? 0)) === (int)$loc['id']) ? 'selected' : ''; ?>>
              <?php echo Helper::e($loc['area_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- 5. PIC / Mitra -->
      <div>
        <label class="font-bold text-slate-700 block mb-1">PIC / Mitra Wilayah</label>
        <select name="pic_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
          <option value="">-- Semua PIC / Mitra --</option>
          <?php foreach ($pics as $pic): ?>
            <option value="<?php echo $pic['id']; ?>" <?php echo (((int)($_GET['pic_id'] ?? 0)) === (int)$pic['id']) ? 'selected' : ''; ?>>
              <?php echo Helper::e($pic['name']); ?> <?php echo !empty($pic['position']) ? '(' . Helper::e($pic['position']) . ')' : ''; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Modal Footer Buttons -->
      <div class="flex items-center justify-between pt-3 border-t border-slate-100">
        <a href="<?php echo Helper::url('receivables', ['limit' => $limitValue]); ?>" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-red-600 transition-colors">
          Reset Filter
        </a>
        <div class="flex items-center gap-2">
          <button type="button" onclick="closeReceivablesFilterModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
            Batal
          </button>
          <button type="submit" class="px-5 py-2 bg-gradient-to-tl from-purple-700 to-pink-500 hover:opacity-90 text-white font-bold text-xs rounded-xl shadow-soft-md transition-all flex items-center gap-1.5">
            <i class="fa-solid fa-filter text-2xs"></i>
            <span>Terapkan Filter</span>
          </button>
        </div>
      </div>

    </form>

  </div>
</div>

<script>
// Per-page Limit Switcher
function changeLimit(val) {
  const url = new URL(window.location.href);
  url.searchParams.set('limit', val);
  url.searchParams.set('p', '1');
  window.location.href = url.toString();
}

// Filter Modal Handlers
function openReceivablesFilterModal() {
  const modal = document.getElementById("receivablesFilterModal");
  const content = document.getElementById("receivablesFilterModalContent");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeReceivablesFilterModal() {
  const modal = document.getElementById("receivablesFilterModal");
  const content = document.getElementById("receivablesFilterModalContent");
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

document.addEventListener("DOMContentLoaded", function() {
  const modal = document.getElementById("receivablesFilterModal");
  if (modal) {
    modal.addEventListener("click", function(e) {
      if (e.target === modal) closeReceivablesFilterModal();
    });
  }

  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && modal && !modal.classList.contains("hidden")) {
      closeReceivablesFilterModal();
    }
  });
});
</script>
