<?php
// app/Views/billing/invoices.php - Invoices List, Pagination & Per-Page Limit, Modal Filter, Batch Actions & Auto-Billing
$activeFilterCount = 0;
if (!empty($_GET['status'])) $activeFilterCount++;
if (!empty($_GET['period'])) $activeFilterCount++;
if (!empty($_GET['package_id'])) $activeFilterCount++;
if (!empty($_GET['location_id'])) $activeFilterCount++;
if (!empty($_GET['pic_id'])) $activeFilterCount++;

$hasActiveFilters = ($activeFilterCount > 0 || !empty($_GET['search']));
$limitValue = $limitValue ?? '10';
$perPage = $perPage ?? 10;
$totalInvoices = $totalInvoices ?? count($invoices);
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$offset = $offset ?? 0;
?>
<div class="bg-white rounded-3xl p-6 shadow-soft-xl space-y-5 relative">
  
  <!-- Card Header -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Daftar Tagihan & Invoice</h4>
      <p class="text-2xs text-slate-400">Kelola penagihan langganan, filter multi-kriteria, aksi massal, dan auto-billing</p>
    </div>
    
    <div class="flex flex-wrap items-center gap-2">
      <!-- Import CSV Button -->
      <button type="button" onclick="openImportModal()" class="px-4 py-2.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-2xl shadow-soft-xs transition-all flex items-center gap-1.5">
        <i class="fa-solid fa-file-import text-purple-600"></i>
        <span>Import CSV</span>
      </button>

      <!-- Auto Billing Settings Modal Button -->
      <button type="button" onclick="openBillingSettingsModal()" class="px-4 py-2.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-2xl shadow-soft-xs transition-all flex items-center gap-2">
        <i class="fa-solid fa-wand-magic-sparkles text-purple-600"></i>
        <span>Periode & Auto Billing</span>
      </button>

      <!-- Manual Create Invoice Button -->
      <a href="<?php echo Helper::url('create_invoice'); ?>" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Terbitkan Manual</span>
      </a>
    </div>
  </div>

  <!-- Batch Action Floating Bar (Shown when items are selected) -->
  <div id="batchActionBar" class="hidden p-4 rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 text-white shadow-soft-xl items-center justify-between gap-4 transition-all duration-300">
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-xs font-black text-pink-400 border border-white/10">
        <i class="fa-solid fa-check-double"></i>
      </div>
      <div>
        <span class="font-extrabold text-sm block leading-tight">
          <span id="selectedCount" class="text-pink-400 font-mono">0</span> Tagihan Terpilih
        </span>
        <span class="text-3xs text-slate-400">Pilih aksi massal yang ingin diterapkan</span>
      </div>
    </div>

    <!-- Batch Action Buttons -->
    <div class="flex flex-wrap items-center gap-2">
      <button type="button" onclick="executeBatchAction('mark_paid')" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-2xs transition-all flex items-center gap-1.5 shadow-soft-xs">
        <i class="fa-solid fa-circle-check"></i>
        <span>Tandai Lunas</span>
      </button>

      <button type="button" onclick="executeBatchAction('mark_unpaid')" class="px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-2xs transition-all flex items-center gap-1.5 shadow-soft-xs">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>Tandai Belum Bayar</span>
      </button>

      <button type="button" onclick="executeBatchAction('mark_cancelled')" class="px-3.5 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-white font-bold text-2xs transition-all flex items-center gap-1.5 shadow-soft-xs">
        <i class="fa-solid fa-ban"></i>
        <span>Batalkan</span>
      </button>

      <button type="button" onclick="executeBatchAction('delete')" class="px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-2xs transition-all flex items-center gap-1.5 shadow-soft-xs">
        <i class="fa-solid fa-trash-can"></i>
        <span>Hapus</span>
      </button>

      <button type="button" onclick="deselectAllInvoices()" class="px-3 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-slate-300 hover:text-white font-bold text-2xs transition-all">
        Batal
      </button>
    </div>
  </div>

  <!-- Sleek Filter Bar + Per-Page Limit Selector -->
  <div class="space-y-2">
    <form method="GET" action="<?php echo Helper::url('invoices'); ?>" class="flex flex-wrap sm:flex-nowrap items-center gap-2">
      <input type="hidden" name="page" value="invoices">
      <?php if (!empty($_GET['status'])): ?><input type="hidden" name="status" value="<?php echo Helper::e($_GET['status']); ?>"><?php endif; ?>
      <?php if (!empty($_GET['period'])): ?><input type="hidden" name="period" value="<?php echo Helper::e($_GET['period']); ?>"><?php endif; ?>
      <?php if (!empty($_GET['package_id'])): ?><input type="hidden" name="package_id" value="<?php echo Helper::e($_GET['package_id']); ?>"><?php endif; ?>
      <input type="hidden" name="limit" value="<?php echo Helper::e($limitValue); ?>">

      <!-- Area / Location Filter -->
      <div class="relative hidden md:block">
        <select name="location_id" onchange="this.form.submit()" class="text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-2xl px-3 py-2 sm:py-2.5 shadow-soft-xs focus:outline-none focus:border-purple-500 cursor-pointer">
          <option value="">-- Semua Area --</option>
          <?php foreach ($locations as $loc): ?>
            <option value="<?php echo $loc['id']; ?>" <?php echo (((int)($_GET['location_id'] ?? 0)) === (int)$loc['id']) ? 'selected' : ''; ?>>
              Area: <?php echo Helper::e($loc['area_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- PIC Filter -->
      <div class="relative hidden lg:block">
        <select name="pic_id" onchange="this.form.submit()" class="text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-2xl px-3 py-2 sm:py-2.5 shadow-soft-xs focus:outline-none focus:border-purple-500 cursor-pointer">
          <option value="">-- Semua PIC --</option>
          <?php foreach ($pics as $pic): ?>
            <option value="<?php echo $pic['id']; ?>" <?php echo (((int)($_GET['pic_id'] ?? 0)) === (int)$pic['id']) ? 'selected' : ''; ?>>
              PIC: <?php echo Helper::e($pic['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Search Input -->
      <div class="relative flex-1 min-w-[180px]">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
          <i class="fa-solid fa-magnifying-glass text-xs"></i>
        </span>
        <input type="text" name="search" id="invoiceSearchInput" oninput="debounceSearch(this)" value="<?php echo Helper::e($_GET['search'] ?? ''); ?>" placeholder="Cari nama, area, PIC..." class="w-full text-xs pl-8 pr-3 py-2 sm:py-2.5 rounded-2xl bg-slate-50 border border-slate-200/80 focus:bg-white focus:outline-none focus:border-purple-500 shadow-soft-xs placeholder:text-slate-400 font-medium">
      </div>

      <!-- Limit / Per-Page Selector (10, 25, 50, 100, semua) -->
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
      <button type="button" onclick="openFilterModal()" class="px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-bold rounded-2xl border transition-all flex items-center gap-1.5 shadow-soft-xs whitespace-nowrap <?php echo ($activeFilterCount > 0) ? 'bg-purple-50 text-purple-700 border-purple-300 ring-2 ring-purple-500/20' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-200'; ?>">
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
        <a href="<?php echo Helper::url('invoices', ['limit' => $limitValue]); ?>" class="p-2 sm:px-3 sm:py-2.5 text-xs font-bold text-slate-500 hover:text-red-600 bg-white hover:bg-red-50 border border-slate-200 rounded-2xl transition-all flex items-center justify-center" title="Reset Semua Filter">
          <i class="fa-solid fa-rotate-left text-xs"></i>
          <span class="hidden sm:inline ml-1">Reset</span>
        </a>
      <?php endif; ?>
    </form>

    <!-- Active Filter Chips / Badges -->
    <?php if ($activeFilterCount > 0): ?>
      <div class="flex flex-wrap items-center gap-1.5 pt-1">
        <span class="text-3xs text-slate-400 font-bold uppercase tracking-wider mr-1">Filter Aktif:</span>
        
        <?php if (!empty($_GET['period'])): ?>
          <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-purple-50 border border-purple-200 text-purple-700 text-3xs font-bold">
            <i class="fa-regular fa-calendar text-3xs"></i>
            <span>Bulan: <?php echo Helper::e($_GET['period']); ?></span>
          </span>
        <?php endif; ?>

        <?php if (!empty($_GET['status'])): 
          $statusLabels = ['paid' => 'Lunas', 'unpaid' => 'Belum Bayar', 'partially_paid' => 'Sebagian', 'overdue' => 'Jatuh Tempo', 'cancelled' => 'Dibatalkan'];
        ?>
          <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-3xs font-bold">
            <i class="fa-solid fa-circle-check text-3xs"></i>
            <span>Status: <?php echo $statusLabels[$_GET['status']] ?? Helper::e($_GET['status']); ?></span>
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

  <!-- Table Body with Selection Checkboxes -->
  <div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
      <thead>
        <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
          <!-- Master Selection Checkbox -->
          <th class="py-3 px-4 font-bold text-center w-10">
            <input type="checkbox" id="masterCheckbox" onclick="toggleSelectAllInvoices(this)" class="w-4 h-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500 cursor-pointer">
          </th>
          <th class="py-3 px-4 font-bold text-center w-10">No</th>
          <th class="py-3 px-4 font-bold">Pelanggan, Layanan & Coverage</th>
          <th class="py-3 px-4 font-bold text-center">Periode</th>
          <th class="py-3 px-4 font-bold text-right">Total Tagihan</th>
          <th class="py-3 px-4 font-bold text-center">Status (Ubah Langsung)</th>
          <th class="py-3 px-4 font-bold text-center">Jatuh Tempo</th>
          <th class="py-3 px-4 font-bold text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php if (empty($invoices)): ?>
        <tr>
          <td colspan="8" class="py-12 text-center text-slate-400">
            <i class="fa-solid fa-file-invoice text-3xl mb-2 text-slate-300 block"></i>
            <span class="text-xs font-semibold">Tidak ada data tagihan yang sesuai dengan filter.</span>
          </td>
        </tr>
        <?php else: ?>
          <?php $no = ($offset ?? 0) + 1; foreach ($invoices as $inv): 
            $st = strtolower($inv['payment_status']);
            $badgeColorClass = match($st) {
              'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
              'overdue', 'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
              default => 'bg-amber-50 text-amber-700 border-amber-200'
            };

            $coverageArea = $inv['area_name'] ?? '';
            if (empty($coverageArea) && !empty($inv['city'])) {
                $coverageArea = $inv['city'];
            }
          ?>
          <tr class="hover:bg-slate-50/80 transition-colors invoice-row" id="row-inv-<?php echo $inv['id']; ?>">
            <!-- Row Checkbox -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <input type="checkbox" value="<?php echo $inv['id']; ?>" onchange="updateBatchBar()" class="invoice-checkbox w-4 h-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500 cursor-pointer">
            </td>

            <!-- No -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap font-bold text-slate-500 font-mono text-xs">
              <?php echo $no++; ?>
            </td>

            <!-- Pelanggan, Paket, Area Coverage, dan PIC -->
            <td class="py-3.5 px-4">
              <span class="font-black text-slate-900 text-xs block leading-snug"><?php echo Helper::e($inv['customer_name']); ?></span>
              <span class="text-3xs text-purple-700 font-bold block mt-0.5"><?php echo Helper::e($inv['package_name_snapshot']); ?></span>
              
              <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-3xs text-slate-500 mt-1">
                <?php if (!empty($coverageArea)): ?>
                  <span class="inline-flex items-center gap-1 font-medium text-slate-600">
                    <i class="fa-solid fa-map-location-dot text-purple-500"></i>
                    <span><?php echo Helper::e($coverageArea); ?></span>
                  </span>
                <?php endif; ?>

                <?php if (!empty($inv['pic_name'])): ?>
                  <?php if (!empty($coverageArea)): ?><span class="text-slate-300">&bull;</span><?php endif; ?>
                  <span class="inline-flex items-center gap-1 text-slate-600">
                    <i class="fa-solid fa-user-tie text-slate-400"></i>
                    <span>PIC: <strong><?php echo Helper::e($inv['pic_name']); ?></strong></span>
                  </span>
                <?php endif; ?>
              </div>
            </td>

            <!-- Periode Tagihan -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap text-xs font-mono font-medium text-slate-700">
              <?php echo Helper::e($inv['billing_period']); ?>
            </td>

            <!-- Total Nominal -->
            <td class="py-3.5 px-4 text-right whitespace-nowrap font-mono">
              <span class="text-xs font-bold text-slate-900 block"><?php echo Helper::formatRupiah($inv['grand_total']); ?></span>
              <span id="balance-due-<?php echo $inv['id']; ?>" class="text-3xs text-red-500 font-semibold <?php echo ($inv['balance_due'] > 0) ? '' : 'hidden'; ?>">
                Sisa: <?php echo Helper::formatRupiah($inv['balance_due']); ?>
              </span>
            </td>

            <!-- Inline Status Dropdown -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <div class="inline-block relative">
                <select 
                  onchange="updateInvoiceStatusInline(this, <?php echo $inv['id']; ?>)"
                  data-original="<?php echo $st; ?>"
                  class="status-select text-3xs font-extrabold px-3 py-1 rounded-full border cursor-pointer focus:outline-none transition-all uppercase tracking-tight appearance-none pr-6 <?php echo $badgeColorClass; ?>">
                  <option value="paid" <?php echo ($st === 'paid') ? 'selected' : ''; ?>>Lunas</option>
                  <option value="unpaid" <?php echo ($st === 'unpaid') ? 'selected' : ''; ?>>Belum Bayar</option>
                  <option value="partially_paid" <?php echo ($st === 'partially_paid') ? 'selected' : ''; ?>>Bayar Sebagian</option>
                  <option value="overdue" <?php echo ($st === 'overdue') ? 'selected' : ''; ?>>Jatuh Tempo</option>
                  <option value="cancelled" <?php echo ($st === 'cancelled') ? 'selected' : ''; ?>>Dibatalkan</option>
                </select>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2 text-3xs opacity-60">
                  <i class="fa-solid fa-chevron-down"></i>
                </span>
              </div>
            </td>

            <!-- Jatuh Tempo -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap text-xs font-mono text-slate-600">
              <?php echo Helper::formatDate($inv['due_date']); ?>
            </td>

            <!-- Aksi -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <a href="<?php echo Helper::url('show_invoice', ['id' => $inv['id']]); ?>" class="px-3.5 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-2xs rounded-xl transition-colors inline-flex items-center gap-1">
                <i class="fa-solid fa-eye"></i>
                <span>Rincian</span>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination & Item Count Bar -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-3 border-t border-slate-100 text-xs">
    <div class="text-slate-400 text-3xs sm:text-2xs font-medium">
      <?php 
        $startItem = ($totalInvoices > 0) ? ($offset + 1) : 0;
        $endItem = ($perPage > 0) ? min($totalInvoices, $offset + $perPage) : $totalInvoices;
      ?>
      Menampilkan <strong class="text-slate-700 font-bold font-mono"><?php echo $startItem; ?> - <?php echo $endItem; ?></strong> dari <strong class="text-slate-700 font-bold font-mono"><?php echo $totalInvoices; ?></strong> tagihan
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="flex items-center gap-1">
        <?php if ($currentPage > 1): 
          $prevUrl = Helper::url('invoices', array_merge($_GET, ['p' => $currentPage - 1]));
        ?>
          <a href="<?php echo $prevUrl; ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-3xs font-bold transition-all">
            <i class="fa-solid fa-chevron-left mr-1"></i> Prev
          </a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): 
          if ($i == 1 || $i == $totalPages || ($i >= $currentPage - 1 && $i <= $currentPage + 1)):
            $pUrl = Helper::url('invoices', array_merge($_GET, ['p' => $i]));
            $isActive = ($i === $currentPage);
        ?>
          <a href="<?php echo $pUrl; ?>" class="w-7 h-7 flex items-center justify-center rounded-xl text-3xs font-bold transition-all <?php echo $isActive ? 'bg-gradient-to-tl from-purple-700 to-pink-500 text-white shadow-soft-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'; ?>">
            <?php echo $i; ?>
          </a>
        <?php elseif ($i == $currentPage - 2 || $i == $currentPage + 2): ?>
          <span class="px-1 text-slate-400 font-bold text-3xs">...</span>
        <?php endif; endfor; ?>

        <?php if ($currentPage < $totalPages): 
          $nextUrl = Helper::url('invoices', array_merge($_GET, ['p' => $currentPage + 1]));
        ?>
          <a href="<?php echo $nextUrl; ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-3xs font-bold transition-all">
            Next <i class="fa-solid fa-chevron-right ml-1"></i>
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- Modal Pop-Up: Filter Multi-Kriteria (PIC, Paket, Area, Status, Periode) -->
<div id="filterModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-7 transform transition-all duration-300 scale-95 opacity-0 max-h-[90vh] overflow-y-auto" id="filterModalContent">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 mb-4">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-sm shadow-soft-md shrink-0">
          <i class="fa-solid fa-sliders"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Filter Data Tagihan</h4>
          <span class="text-3xs text-slate-400">Saring tagihan berdasarkan beberapa kriteria sekaligus</span>
        </div>
      </div>
      <button type="button" onclick="closeFilterModal()" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <!-- Filter Form Inside Modal -->
    <form method="GET" action="<?php echo Helper::url('invoices'); ?>" class="space-y-3.5 text-xs">
      <input type="hidden" name="page" value="invoices">
      <input type="hidden" name="limit" value="<?php echo Helper::e($limitValue); ?>">
      <?php if (!empty($_GET['search'])): ?>
        <input type="hidden" name="search" value="<?php echo Helper::e($_GET['search']); ?>">
      <?php endif; ?>

      <!-- 1. Periode Tagihan Bulan -->
      <div>
        <label class="font-bold text-slate-700 block mb-1">Target Periode / Bulan</label>
        <input type="month" name="period" value="<?php echo Helper::e($_GET['period'] ?? ''); ?>" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
      </div>

      <!-- 2. Status Pembayaran -->
      <div>
        <label class="font-bold text-slate-700 block mb-1">Status Pembayaran</label>
        <select name="status" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
          <option value="">-- Semua Status Pembayaran --</option>
          <option value="paid" <?php echo (($_GET['status'] ?? '') === 'paid') ? 'selected' : ''; ?>>Lunas (Paid)</option>
          <option value="unpaid" <?php echo (($_GET['status'] ?? '') === 'unpaid') ? 'selected' : ''; ?>>Belum Bayar (Unpaid)</option>
          <option value="partially_paid" <?php echo (($_GET['status'] ?? '') === 'partially_paid') ? 'selected' : ''; ?>>Bayar Sebagian</option>
          <option value="overdue" <?php echo (($_GET['status'] ?? '') === 'overdue') ? 'selected' : ''; ?>>Jatuh Tempo (Overdue)</option>
          <option value="cancelled" <?php echo (($_GET['status'] ?? '') === 'cancelled') ? 'selected' : ''; ?>>Dibatalkan (Cancelled)</option>
        </select>
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
        <label class="font-bold text-slate-700 block mb-1">Area Coverage / Wilayah</label>
        <select name="location_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
          <option value="">-- Semua Area Coverage --</option>
          <?php foreach ($locations as $loc): ?>
            <option value="<?php echo $loc['id']; ?>" <?php echo (((int)($_GET['location_id'] ?? 0)) === (int)$loc['id']) ? 'selected' : ''; ?>>
              <?php echo Helper::e($loc['area_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- 5. PIC / Mitra RT-RW -->
      <div>
        <label class="font-bold text-slate-700 block mb-1">PIC / Penanggung Jawab Wilayah</label>
        <select name="pic_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
          <option value="">-- Semua PIC / Mitra --</option>
          <?php foreach ($pics as $pic): ?>
            <option value="<?php echo $pic['id']; ?>" <?php echo (((int)($_GET['pic_id'] ?? 0)) === (int)$pic['id']) ? 'selected' : ''; ?>>
              <?php echo Helper::e($pic['name']); ?> <?php echo !empty($pic['position']) ? '(' . Helper::e($pic['position']) . ')' : (!empty($pic['company']) ? '(' . Helper::e($pic['company']) . ')' : ''); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Modal Footer Buttons -->
      <div class="flex items-center justify-between pt-3 border-t border-slate-100">
        <a href="<?php echo Helper::url('invoices', ['limit' => $limitValue]); ?>" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-red-600 transition-colors">
          Reset Filter
        </a>
        <div class="flex items-center gap-2">
          <button type="button" onclick="closeFilterModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
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

<!-- Floating Toast Notification -->
<div id="inlineToast" class="fixed bottom-20 right-4 sm:bottom-6 sm:right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
  <div class="px-5 py-3 rounded-2xl bg-slate-900 text-white shadow-soft-2xl flex items-center gap-3 border border-slate-700 text-xs font-semibold">
    <i class="fa-solid fa-circle-check text-green-400 text-base" id="toastIcon"></i>
    <span id="toastMessage">Status berhasil diperbarui</span>
  </div>
</div>

<!-- Modal Pop-Up: Auto-Generate Tagihan Bulanan -->
<div id="billingSettingsModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0 max-h-[90vh] overflow-y-auto" id="billingSettingsModalContent">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-wand-magic-sparkles"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Auto-Generate Tagihan Bulanan</h4>
          <span class="text-2xs text-slate-400">Pengaturan jadwal penagihan otomatis per pelanggan</span>
        </div>
      </div>
      <button type="button" onclick="closeBillingSettingsModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <!-- Explanation & Rules Box -->
    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 mb-5 space-y-2 text-xs leading-relaxed text-slate-600">
      <p>
        Jika status <strong>Aktif</strong>, server otomatis membuat invoice untuk setiap pelanggan sesuai tanggal penagihan masing-masing — dihitung mundur beberapa hari sebelum jatuh tempo, bukan satu tanggal terbit yang sama untuk semua. Tanpa perlu ada yang membuka aplikasi.
      </p>
      <div class="pt-2 border-t border-slate-200/60 space-y-1 text-2xs">
        <p class="font-bold text-slate-700 uppercase tracking-wider">Aturan:</p>
        <p class="flex items-start gap-1.5">
          <i class="fa-solid fa-check text-green-600 mt-0.5 text-3xs"></i>
          <span><strong>Pelanggan Aktif:</strong> Otomatis ditagihkan setiap bulannya, mengikuti Tanggal Penagihan masing-masing.</span>
        </p>
        <p class="flex items-start gap-1.5">
          <i class="fa-solid fa-xmark text-red-500 mt-0.5 text-3xs"></i>
          <span><strong>Pelanggan Nonaktif:</strong> Tidak akan dibuatkan tagihan di menu Billing.</span>
        </p>
      </div>
    </div>

    <!-- Auto-Billing Config Form -->
    <form method="POST" action="<?php echo Helper::url('invoices'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_auto_billing_config">

      <!-- Field 1: Status Auto-Generate -->
      <div>
        <label class="font-bold text-xs text-slate-800 block mb-1">Status Auto-Generate</label>
        <select name="status" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-bold text-slate-800">
          <option value="inactive" <?php echo (($autoBilling['status'] ?? 'inactive') === 'inactive') ? 'selected' : ''; ?>>Nonaktif</option>
          <option value="active" <?php echo (($autoBilling['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Aktif</option>
        </select>
        <p class="text-3xs text-slate-400 mt-1">
          <?php echo (($autoBilling['status'] ?? 'inactive') === 'inactive') 
            ? 'Siklus otomatis nonaktif. Tagihan hanya dibuat saat Anda menekan tombol di bawah.' 
            : 'Siklus otomatis aktif. Sistem akan mengeksekusi pembuatan invoice sesuai tanggal penagihan masing-masing pelanggan.'; ?>
        </p>
      </div>

      <!-- Field 2: Generate Berapa Hari Sebelum Jatuh Tempo -->
      <div>
        <label class="font-bold text-xs text-slate-800 block mb-1">Generate Berapa Hari Sebelum Jatuh Tempo</label>
        <div class="relative">
          <input type="number" name="days_before_due" min="1" max="30" value="<?php echo Helper::e($autoBilling['days_before_due'] ?? 7); ?>" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-bold text-slate-800">
          <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-xs text-slate-400 pointer-events-none font-bold">Hari</span>
        </div>
        <p class="text-3xs text-slate-400 mt-1">
          Contoh: isi 7 dan pelanggan punya Tanggal Penagihan tanggal 15 &rarr; invoice otomatis dibuat & bisa dibayar mulai tanggal 8, jatuh tempo tetap tanggal 15.
        </p>
      </div>

      <!-- Field 3: Tanggal Jatuh Tempo Default -->
      <div>
        <label class="font-bold text-xs text-slate-800 block mb-1">Tanggal Jatuh Tempo Default (Setiap Bulan)</label>
        <select name="default_due_day" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-bold text-slate-800">
          <option value="1" <?php echo (((int)($autoBilling['default_due_day'] ?? 1)) === 1) ? 'selected' : ''; ?>>Tanggal 1 (Awal Bulan)</option>
          <option value="5" <?php echo (((int)($autoBilling['default_due_day'] ?? 1)) === 5) ? 'selected' : ''; ?>>Tanggal 5</option>
          <option value="10" <?php echo (((int)($autoBilling['default_due_day'] ?? 1)) === 10) ? 'selected' : ''; ?>>Tanggal 10</option>
          <option value="15" <?php echo (((int)($autoBilling['default_due_day'] ?? 1)) === 15) ? 'selected' : ''; ?>>Tanggal 15 (Tengah Bulan)</option>
          <option value="20" <?php echo (((int)($autoBilling['default_due_day'] ?? 1)) === 20) ? 'selected' : ''; ?>>Tanggal 20</option>
          <option value="25" <?php echo (((int)($autoBilling['default_due_day'] ?? 1)) === 25) ? 'selected' : ''; ?>>Tanggal 25</option>
          <option value="28" <?php echo (((int)($autoBilling['default_due_day'] ?? 1)) === 28) ? 'selected' : ''; ?>>Tanggal 28 (Akhir Bulan)</option>
        </select>
        <p class="text-3xs text-slate-400 mt-1">
          Dipakai sebagai tanggal jatuh tempo hanya untuk pelanggan yang belum diisi "Tanggal Penagihan" di menu Pelanggan.
        </p>
      </div>

      <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
        <button type="button" onclick="closeBillingSettingsModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Tutup
        </button>
        <button type="submit" class="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-xs transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Pengaturan
        </button>
      </div>
    </form>

    <!-- Manual 1-Click Trigger Section -->
    <div class="mt-6 pt-5 border-t border-slate-200/80">
      <div class="flex items-center justify-between gap-4">
        <div>
          <h6 class="text-xs font-bold text-slate-900">Jalankan Generate Sekarang (Manual)</h6>
          <p class="text-3xs text-slate-400">Terbitkan tagihan bulan ini secara langsung untuk semua pelanggan aktif yang belum memiliki invoice</p>
        </div>

        <form method="POST" action="<?php echo Helper::url('invoices'); ?>">
          <?php echo Helper::csrfField(); ?>
          <input type="hidden" name="action" value="manual_trigger_auto_billing">
          <input type="hidden" name="period" value="<?php echo date('Y-m'); ?>">
          
          <button type="submit" onclick="return confirm('Jalankan generate tagihan massal untuk bulan ini sekarang?')" class="px-5 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-1.5 whitespace-nowrap">
            <i class="fa-solid fa-play text-2xs"></i>
            <span>Generate Sekarang</span>
          </button>
        </form>
      </div>
    </div>

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
function openFilterModal() {
  const modal = document.getElementById("filterModal");
  const content = document.getElementById("filterModalContent");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeFilterModal() {
  const modal = document.getElementById("filterModal");
  const content = document.getElementById("filterModalContent");
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

// Selection & Batch Action Logic
function getSelectedInvoiceIds() {
  const checkboxes = document.querySelectorAll('.invoice-checkbox:checked');
  return Array.from(checkboxes).map(cb => parseInt(cb.value));
}

function updateBatchBar() {
  const selected = getSelectedInvoiceIds();
  const bar = document.getElementById('batchActionBar');
  const countEl = document.getElementById('selectedCount');
  const masterCb = document.getElementById('masterCheckbox');
  const allCheckboxes = document.querySelectorAll('.invoice-checkbox');

  if (countEl) countEl.textContent = selected.length;

  if (selected.length > 0) {
    bar.classList.remove('hidden');
    bar.classList.add('flex');
  } else {
    bar.classList.add('hidden');
    bar.classList.remove('flex');
  }

  if (masterCb) {
    masterCb.checked = (selected.length === allCheckboxes.length && allCheckboxes.length > 0);
    masterCb.indeterminate = (selected.length > 0 && selected.length < allCheckboxes.length);
  }
}

function toggleSelectAllInvoices(masterCb) {
  const checkboxes = document.querySelectorAll('.invoice-checkbox');
  checkboxes.forEach(cb => {
    cb.checked = masterCb.checked;
  });
  updateBatchBar();
}

function deselectAllInvoices() {
  const masterCb = document.getElementById('masterCheckbox');
  if (masterCb) masterCb.checked = false;
  const checkboxes = document.querySelectorAll('.invoice-checkbox');
  checkboxes.forEach(cb => cb.checked = false);
  updateBatchBar();
}

function executeBatchAction(actionType) {
  const ids = getSelectedInvoiceIds();
  if (ids.length === 0) {
    alert('Pilih minimal satu tagihan.');
    return;
  }

  const actionLabels = {
    'mark_paid': 'Tandai LUNAS ' + ids.length + ' tagihan terpilih?',
    'mark_unpaid': 'Tandai BELUM BAYAR ' + ids.length + ' tagihan terpilih?',
    'mark_cancelled': 'BATALKAN ' + ids.length + ' tagihan terpilih?',
    'delete': 'HAPUS PERMANEN ' + ids.length + ' tagihan terpilih? (Tindakan ini tidak bisa dibatalkan)'
  };

  if (!confirm(actionLabels[actionType] || 'Jalankan aksi massal?')) {
    return;
  }

  const formData = new FormData();
  formData.append('_token', '<?php echo Helper::csrfToken(); ?>');
  formData.append('action', 'batch_update_invoices');
  formData.append('batch_action', actionType);
  ids.forEach(id => formData.append('invoice_ids[]', id));

  fetch('<?php echo Helper::url('invoices'); ?>', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast(data.message || 'Aksi massal berhasil dijalankan.');
      setTimeout(() => {
        window.location.reload();
      }, 700);
    } else {
      alert(data.message || 'Gagal memproses aksi massal.');
    }
  })
  .catch(err => {
    console.error(err);
    window.location.reload();
  });
}

// Inline Status Updater
function updateInvoiceStatusInline(selectEl, invoiceId) {
  const newStatus = selectEl.value;
  const originalStatus = selectEl.getAttribute('data-original');
  const token = '<?php echo Helper::csrfToken(); ?>';

  const colorMap = {
    'paid': 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'overdue': 'bg-rose-50 text-rose-700 border-rose-200',
    'cancelled': 'bg-rose-50 text-rose-700 border-rose-200',
    'unpaid': 'bg-amber-50 text-amber-700 border-amber-200',
    'partially_paid': 'bg-amber-50 text-amber-700 border-amber-200'
  };

  selectEl.className = selectEl.className.replace(/bg-\w+-\d+|text-\w+-\d+|border-\w+-\d+/g, '').trim();
  selectEl.className += ' ' + (colorMap[newStatus] || 'bg-slate-100 text-slate-700 border-slate-200');

  const balanceEl = document.getElementById('balance-due-' + invoiceId);
  if (balanceEl) {
    if (newStatus === 'paid') {
      balanceEl.classList.add('hidden');
    } else {
      balanceEl.classList.remove('hidden');
    }
  }

  const formData = new FormData();
  formData.append('_token', token);
  formData.append('action', 'update_invoice_status_inline');
  formData.append('invoice_id', invoiceId);
  formData.append('status', newStatus);

  fetch('<?php echo Helper::url('invoices'); ?>', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      selectEl.setAttribute('data-original', newStatus);
      showToast('Status invoice berhasil diubah ke ' + newStatus.toUpperCase());
    } else {
      alert(data.message || 'Gagal mengubah status.');
      selectEl.value = originalStatus;
    }
  })
  .catch(err => {
    console.error(err);
    showToast('Status berhasil tersimpan di server.');
  });
}

function showToast(msg) {
  const toast = document.getElementById('inlineToast');
  const toastMsg = document.getElementById('toastMessage');
  if (toast && toastMsg) {
    toastMsg.textContent = msg;
    toast.classList.remove('translate-y-20', 'opacity-0');
    toast.classList.add('translate-y-0', 'opacity-100');
    setTimeout(() => {
      toast.classList.remove('translate-y-0', 'opacity-100');
      toast.classList.add('translate-y-20', 'opacity-0');
    }, 2500);
  }
}

function openBillingSettingsModal() {
  const modal = document.getElementById("billingSettingsModal");
  const content = document.getElementById("billingSettingsModalContent");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

<!-- Modal: Import Data Pelanggan CSV -->
<div id="importModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-4xl bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0 max-h-[90vh] overflow-y-auto" id="importModalContent">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-file-import"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Import Data Pelanggan CSV</h4>
          <span class="text-2xs text-slate-400">Unduh template CSV lengkap, edit di Excel/Spreadsheet, lalu unggah kembali</span>
        </div>
      </div>
      <button type="button" onclick="closeImportModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <!-- Step 1: Download Template Box with Complete 15 Parameters & 3 Dummy Records -->
    <div class="p-4 rounded-2xl bg-gradient-to-br from-purple-50/80 to-pink-50/50 border border-purple-100/80 space-y-3 mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5">
        <div>
          <span class="text-3xs font-extrabold text-purple-900 uppercase tracking-wider block">Langkah 1: Unduh Format / Template CSV Lengkap</span>
          <p class="text-2xs text-slate-600">Template memuat <strong>15 kolom lengkap</strong> sesuai Form Registrasi Pelanggan Baru beserta <strong>3 data contoh (dummy)</strong>.</p>
        </div>
        <a href="<?php echo Helper::url('customers_download_template'); ?>" class="px-4 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 hover:scale-105 text-white font-bold text-xs rounded-xl shadow-soft-xs transition-all flex items-center justify-center gap-2 shrink-0">
          <i class="fa-solid fa-cloud-arrow-down text-sm"></i>
          <span>Unduh Template CSV Lengkap</span>
        </a>
      </div>

      <!-- Live Preview of All 15 Parameters & 3 Dummy Records -->
      <div class="space-y-1.5 pt-1">
        <div class="flex items-center justify-between text-3xs font-extrabold text-slate-500 uppercase tracking-wider">
          <span>Pratinjau 3 Baris Data Dummy & Seluruh 15 Kolom Template:</span>
          <span class="text-purple-700 font-mono">* Geser ke kanan untuk melihat semua kolom</span>
        </div>
        <div class="overflow-x-auto border border-purple-100 rounded-2xl bg-white shadow-soft-xs">
          <table class="w-full text-3xs text-left whitespace-nowrap">
            <thead class="bg-slate-50 border-b border-purple-100 text-slate-600 font-bold uppercase">
              <tr>
                <th class="py-2.5 px-3">ID Pelanggan</th>
                <th class="py-2.5 px-3">Nama Lengkap</th>
                <th class="py-2.5 px-3">No Handphone</th>
                <th class="py-2.5 px-3">WhatsApp</th>
                <th class="py-2.5 px-3">Email</th>
                <th class="py-2.5 px-3">PIC Wilayah</th>
                <th class="py-2.5 px-3">Alamat Pemasangan</th>
                <th class="py-2.5 px-3">Area Coverage</th>
                <th class="py-2.5 px-3">Paket Internet</th>
                <th class="py-2.5 px-3 text-right">Tarif Bulanan</th>
                <th class="py-2.5 px-3 text-center">Siklus Penagihan</th>
                <th class="py-2.5 px-3">Kode Port ODP</th>
                <th class="py-2.5 px-3">PPPoE Username</th>
                <th class="py-2.5 px-3">PPPoE Password</th>
                <th class="py-2.5 px-3 text-center">Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700 font-medium font-mono">
              <!-- Row 1 -->
              <tr class="hover:bg-slate-50/60">
                <td class="py-2 px-3 font-bold text-purple-700">CUST-001</td>
                <td class="py-2 px-3 font-sans font-bold text-slate-900">VERY PRASETYO</td>
                <td class="py-2 px-3">081234567891</td>
                <td class="py-2 px-3">081234567891</td>
                <td class="py-2 px-3 font-sans text-slate-500">very.prasetyo@gmail.com</td>
                <td class="py-2 px-3 font-sans font-semibold text-indigo-700">Ahmad Fauzi</td>
                <td class="py-2 px-3 font-sans">Jl. Sekembang Raya No. 12 RT 01/RW 02</td>
                <td class="py-2 px-3 font-sans"><span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 font-bold">SEKEMBANG</span></td>
                <td class="py-2 px-3 font-sans"><span class="px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700 font-bold">PAKET HEMAT</span></td>
                <td class="py-2 px-3 text-right font-bold text-purple-700">100000</td>
                <td class="py-2 px-3 text-center font-sans">Tagihan Tgl 10</td>
                <td class="py-2 px-3 text-purple-600">ODP-SKB-001 Port 2</td>
                <td class="py-2 px-3 text-slate-800">very_net</td>
                <td class="py-2 px-3 text-slate-400">pass1234</td>
                <td class="py-2 px-3 text-center"><span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold uppercase text-3xs">Aktif</span></td>
              </tr>
              <!-- Row 2 -->
              <tr class="hover:bg-slate-50/60">
                <td class="py-2 px-3 font-bold text-purple-700">CUST-002</td>
                <td class="py-2 px-3 font-sans font-bold text-slate-900">NURUL HIDAYAH</td>
                <td class="py-2 px-3">085712345678</td>
                <td class="py-2 px-3">085712345678</td>
                <td class="py-2 px-3 font-sans text-slate-500">nurul.hidayah@gmail.com</td>
                <td class="py-2 px-3 font-sans font-semibold text-indigo-700">Rudi Hartono</td>
                <td class="py-2 px-3 font-sans">Dusun Prangkokan RT 03/RW 01</td>
                <td class="py-2 px-3 font-sans"><span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 font-bold">PRANGKOKAN</span></td>
                <td class="py-2 px-3 font-sans"><span class="px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700 font-bold">PAKET HEMAT</span></td>
                <td class="py-2 px-3 text-right font-bold text-purple-700">100000</td>
                <td class="py-2 px-3 text-center font-sans">Tagihan Tgl 10</td>
                <td class="py-2 px-3 text-purple-600">ODP-PRK-002 Port 4</td>
                <td class="py-2 px-3 text-slate-800">nurul_net</td>
                <td class="py-2 px-3 text-slate-400">pass5678</td>
                <td class="py-2 px-3 text-center"><span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold uppercase text-3xs">Aktif</span></td>
              </tr>
              <!-- Row 3 -->
              <tr class="hover:bg-slate-50/60">
                <td class="py-2 px-3 font-bold text-purple-700">CUST-003</td>
                <td class="py-2 px-3 font-sans font-bold text-slate-900">SRIMULYANI</td>
                <td class="py-2 px-3">087811223344</td>
                <td class="py-2 px-3">087811223344</td>
                <td class="py-2 px-3 font-sans text-slate-500">srimulyani@gmail.com</td>
                <td class="py-2 px-3 font-sans font-semibold text-indigo-700">Ahmad Fauzi</td>
                <td class="py-2 px-3 font-sans">Perum Grand Galaxy Blok A5 No. 8</td>
                <td class="py-2 px-3 font-sans"><span class="px-2 py-0.5 rounded-lg bg-blue-50 text-blue-700 font-bold">GRAND GALAXY</span></td>
                <td class="py-2 px-3 font-sans"><span class="px-2 py-0.5 rounded-lg bg-purple-50 text-purple-700 font-bold">HOME FIBER 20M</span></td>
                <td class="py-2 px-3 text-right font-bold text-purple-700">150000</td>
                <td class="py-2 px-3 text-center font-sans">Tagihan Tgl 15</td>
                <td class="py-2 px-3 text-purple-600">ODP-GLX-001 Port 1</td>
                <td class="py-2 px-3 text-slate-800">sri_galaxy</td>
                <td class="py-2 px-3 text-slate-400">pass9012</td>
                <td class="py-2 px-3 text-center"><span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 font-bold uppercase text-3xs">Isolir</span></td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="flex flex-wrap items-center justify-between text-3xs text-slate-400 pt-1">
          <span>* Paket Internet, Area Coverage &amp; PIC Wilayah baru akan otomatis didaftarkan jika belum ada.</span>
          <span>* Kolom yang tidak diisi dapat dikosongkan.</span>
        </div>
      </div>
    </div>

    <!-- Step 2: Upload CSV Form -->
    <div>
      <span class="text-3xs font-extrabold text-slate-400 uppercase tracking-wider block mb-2">Langkah 2: Pilih & Unggah File CSV</span>
      
      <form method="POST" action="<?php echo Helper::url('customers_import_csv'); ?>" enctype="multipart/form-data" class="space-y-4">
        <?php echo Helper::csrfField(); ?>

        <?php 
          $defaultCsvPath = dirname(__DIR__, 2) . '/data-pelanggan .csv';
          if (file_exists($defaultCsvPath)): 
        ?>
        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-start gap-3">
          <input type="checkbox" name="use_default_sample" id="use_default_sample_inv" value="1" onchange="toggleFileUploadInv(this.checked)" class="w-4 h-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500 mt-0.5 cursor-pointer">
          <label for="use_default_sample_inv" class="cursor-pointer">
            <span class="text-xs font-bold text-slate-800 block">Impor langsung dari file bawaan: data-pelanggan .csv</span>
            <span class="text-3xs text-slate-400">Gunakan file 278 data pelanggan yang sudah ada di sistem</span>
          </label>
        </div>
        <?php endif; ?>

        <div id="fileUploadContainerInv">
          <label class="font-bold text-xs text-slate-700 block mb-1">Unggah File CSV Hasil Edit</label>
          <input type="file" name="csv_file" id="csv_file_input_inv" accept=".csv,text/csv" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white file:mr-3 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
        </div>

        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
          <button type="button" onclick="closeImportModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
            Batal
          </button>
          <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-1.5">
            <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
            <span>Mulai Impor Data</span>
          </button>
        </div>
      </form>
    </div>

  </div>
</div>

<script>
function openImportModal() {
  const modal = document.getElementById("importModal");
  const content = document.getElementById("importModalContent");
  if (!modal || !content) return;
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeImportModal() {
  const modal = document.getElementById("importModal");
  const content = document.getElementById("importModalContent");
  if (!modal || !content) return;
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

function toggleFileUploadInv(useDefault) {
  const container = document.getElementById('fileUploadContainerInv');
  const input = document.getElementById('csv_file_input_inv');
  if (useDefault) {
    container.classList.add('hidden');
    input.removeAttribute('required');
  } else {
    container.classList.remove('hidden');
    input.setAttribute('required', 'required');
  }
}

// Real-time Search Logic
let searchTimeout;
function debounceSearch(input) {
  clearTimeout(searchTimeout);
  sessionStorage.setItem('invoiceWasSearching', 'true');
  searchTimeout = setTimeout(() => {
    input.form.submit();
  }, 600);
}

document.addEventListener('DOMContentLoaded', () => {
  if (sessionStorage.getItem('invoiceWasSearching') === 'true') {
    const searchInput = document.getElementById('invoiceSearchInput');
    if (searchInput) {
      searchInput.focus();
      const val = searchInput.value;
      searchInput.value = '';
      searchInput.value = val;
    }
    sessionStorage.removeItem('invoiceWasSearching');
  }
});

function openBillingSettingsModal() {
  const modal = document.getElementById("billingSettingsModal");
  const content = document.getElementById("billingSettingsModalContent");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeBillingSettingsModal() {
  const modal = document.getElementById("billingSettingsModal");
  const content = document.getElementById("billingSettingsModalContent");
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

document.addEventListener("DOMContentLoaded", function() {
  const modalBilling = document.getElementById("billingSettingsModal");
  const modalFilter = document.getElementById("filterModal");
  const modalImport = document.getElementById("importModal");

  if (modalBilling) {
    modalBilling.addEventListener("click", function(e) {
      if (e.target === modalBilling) closeBillingSettingsModal();
    });
  }

  if (modalFilter) {
    modalFilter.addEventListener("click", function(e) {
      if (e.target === modalFilter) closeFilterModal();
    });
  }

  if (modalImport) {
    modalImport.addEventListener("click", function(e) {
      if (e.target === modalImport) closeImportModal();
    });
  }

  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
      if (modalBilling && !modalBilling.classList.contains("hidden")) closeBillingSettingsModal();
      if (modalFilter && !modalFilter.classList.contains("hidden")) closeFilterModal();
      if (modalImport && !modalImport.classList.contains("hidden")) closeImportModal();
    }
  });
});
</script>
