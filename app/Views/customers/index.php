<?php
// app/Views/customers/index.php - Clean Customer List, Pagination, Filter Modal, Registration, Edit & CSV Import
$activeFilterCount = 0;
if (!empty($_GET['status'])) $activeFilterCount++;
if (!empty($_GET['package_id'])) $activeFilterCount++;
if (!empty($_GET['location_id'])) $activeFilterCount++;
if (!empty($_GET['pic_id'])) $activeFilterCount++;

$hasActiveFilters = ($activeFilterCount > 0 || !empty($_GET['search']));
$limitValue = $limitValue ?? '10';
$perPage = $perPage ?? 10;
$totalCustomers = $totalCustomers ?? count($customers);
$currentPage = $currentPage ?? 1;
$totalPages = $totalPages ?? 1;
$offset = $offset ?? 0;
?>
<div class="bg-white rounded-3xl p-6 shadow-soft-xl space-y-5 relative">
  
  <!-- Header with Actions -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Daftar Pelanggan Internet</h4>
      <p class="text-2xs text-slate-400">Kelola data pelanggan aktif, status isolir, edit data, dan paket langganan</p>
    </div>
    
    <div class="flex flex-wrap items-center gap-2">
      <!-- Import CSV Button -->
      <button type="button" onclick="openImportModal()" class="px-4 py-2.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-2xl shadow-soft-xs transition-all flex items-center gap-1.5">
        <i class="fa-solid fa-file-import text-purple-600"></i>
        <span>Import CSV</span>
      </button>

      <!-- Export CSV Link -->
      <a href="<?php echo Helper::url('customers_export_csv'); ?>" class="px-4 py-2.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-2xl shadow-soft-xs transition-all flex items-center gap-1.5">
        <i class="fa-solid fa-file-csv text-green-600"></i>
        <span>Export CSV</span>
      </a>

      <!-- Tambah Pelanggan Button -->
      <button type="button" onclick="openCustomerModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
        <i class="fa-solid fa-user-plus text-xs"></i>
        <span>Tambah Pelanggan</span>
      </button>
    </div>
  </div>

  <!-- Sleek Filter Bar + Per-Page Limit Selector -->
  <div class="space-y-2">
    <form method="GET" action="<?php echo Helper::url('customers'); ?>" class="flex flex-wrap sm:flex-nowrap items-center gap-2">
      <input type="hidden" name="page" value="customers">
      <?php if (!empty($_GET['status'])): ?><input type="hidden" name="status" value="<?php echo Helper::e($_GET['status']); ?>"><?php endif; ?>
      <?php if (!empty($_GET['package_id'])): ?><input type="hidden" name="package_id" value="<?php echo Helper::e($_GET['package_id']); ?>"><?php endif; ?>
      <?php if (!empty($_GET['location_id'])): ?><input type="hidden" name="location_id" value="<?php echo Helper::e($_GET['location_id']); ?>"><?php endif; ?>
      <?php if (!empty($_GET['pic_id'])): ?><input type="hidden" name="pic_id" value="<?php echo Helper::e($_GET['pic_id']); ?>"><?php endif; ?>
      <input type="hidden" name="limit" value="<?php echo Helper::e($limitValue); ?>">

      <!-- Search Input -->
      <div class="relative flex-1 min-w-[180px]">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
          <i class="fa-solid fa-magnifying-glass text-xs"></i>
        </span>
        <input type="text" name="search" value="<?php echo Helper::e($_GET['search'] ?? ''); ?>" placeholder="Cari nama, no pelanggan, telepon, alamat, PPPoE..." class="w-full text-xs pl-8 pr-3 py-2 sm:py-2.5 rounded-2xl bg-slate-50 border border-slate-200/80 focus:bg-white focus:outline-none focus:border-purple-500 shadow-soft-xs placeholder:text-slate-400 font-medium">
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
      <button type="button" onclick="openCustomerFilterModal()" class="px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-bold rounded-2xl border transition-all flex items-center gap-1.5 shadow-soft-xs whitespace-nowrap <?php echo ($activeFilterCount > 0) ? 'bg-purple-50 text-purple-700 border-purple-300 ring-2 ring-purple-500/20' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-200'; ?>">
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
        <a href="<?php echo Helper::url('customers', ['limit' => $limitValue]); ?>" class="p-2 sm:px-3 sm:py-2.5 text-xs font-bold text-slate-500 hover:text-red-600 bg-white hover:bg-red-50 border border-slate-200 rounded-2xl transition-all flex items-center justify-center" title="Reset Semua Filter">
          <i class="fa-solid fa-rotate-left text-xs"></i>
          <span class="hidden sm:inline ml-1">Reset</span>
        </a>
      <?php endif; ?>
    </form>

    <!-- Active Filter Chips / Badges -->
    <?php if ($activeFilterCount > 0): ?>
      <div class="flex flex-wrap items-center gap-1.5 pt-1">
        <span class="text-3xs text-slate-400 font-bold uppercase tracking-wider mr-1">Filter Aktif:</span>
        
        <?php if (!empty($_GET['status'])): 
          $statusLabels = ['active' => 'Aktif', 'suspended' => 'Suspended / Isolir', 'inactive' => 'Nonaktif'];
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

  <!-- Table Body -->
  <div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
      <thead>
        <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
          <th class="py-3 px-4 font-bold text-center w-10">No</th>
          <th class="py-3 px-4 font-bold">Pelanggan</th>
          <th class="py-3 px-4 font-bold">Paket & Tarif</th>
          <th class="py-3 px-4 font-bold">Lokasi / Area</th>
          <th class="py-3 px-4 font-bold text-center">Status (Ubah Langsung)</th>
          <th class="py-3 px-4 font-bold text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php if (empty($customers)): ?>
        <tr>
          <td colspan="6" class="py-12 text-center text-slate-400">
            <i class="fa-solid fa-users-slash text-3xl mb-2 text-slate-300 block"></i>
            <span class="text-xs font-semibold">Tidak ada data pelanggan yang sesuai kriteria pencarian.</span>
          </td>
        </tr>
        <?php else: ?>
          <?php $no = ($offset ?? 0) + 1; foreach ($customers as $c): 
            $st = strtolower($c['status']);
            $badgeColorClass = match($st) {
              'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
              'suspended' => 'bg-rose-50 text-rose-700 border-rose-200',
              default => 'bg-slate-100 text-slate-600 border-slate-200'
            };
          ?>
          <tr class="hover:bg-slate-50/80 transition-colors" id="row-cust-<?php echo $c['id']; ?>">
            <!-- No -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap font-bold text-slate-500 font-mono text-xs">
              <?php echo $no++; ?>
            </td>

            <!-- Pelanggan -->
            <td class="py-3.5 px-4 whitespace-nowrap">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center font-black text-xs shadow-soft-sm shrink-0">
                  <?php echo strtoupper(substr($c['name'], 0, 1)); ?>
                </div>
                <div>
                  <span class="text-xs font-black text-slate-900 block"><?php echo Helper::e($c['name']); ?></span>
                  <span class="text-3xs text-purple-700 font-mono font-bold"><?php echo Helper::e($c['customer_no']); ?></span>
                  <?php if (!empty($c['phone']) && $c['phone'] !== '-'): ?>
                    <span class="text-3xs text-slate-400">&bull; <?php echo Helper::e($c['phone']); ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            
            <!-- Paket & Tarif -->
            <td class="py-3.5 px-4 whitespace-nowrap">
              <span class="text-xs font-bold text-slate-800 block"><?php echo Helper::e($c['package_name']); ?></span>
              <span class="text-2xs text-purple-700 font-bold font-mono"><?php echo Helper::formatRupiah($c['package_price']); ?>/bln</span>
            </td>

            <!-- Lokasi & ODP -->
            <td class="py-3.5 px-4 whitespace-nowrap">
              <span class="text-xs text-slate-700 block max-w-xs truncate font-medium">
                <?php echo Helper::e($c['area_name'] ?: $c['full_address']); ?>
              </span>
              <div class="flex items-center gap-2 text-3xs text-slate-400">
                <?php if (!empty($c['odp_point'] ?: $c['loc_odp'])): ?>
                  <span class="font-mono text-purple-600">ODP: <?php echo Helper::e($c['odp_point'] ?: $c['loc_odp']); ?></span>
                <?php endif; ?>
                <?php if (!empty($c['pic_name'])): ?>
                  <span>&bull; PIC: <strong><?php echo Helper::e($c['pic_name']); ?></strong></span>
                <?php endif; ?>
              </div>
            </td>

            <!-- Inline Status Dropdown Changer -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <div class="inline-block relative">
                <select 
                  onchange="updateCustomerStatusInline(this, <?php echo $c['id']; ?>)"
                  data-original="<?php echo $st; ?>"
                  class="status-select text-3xs font-extrabold px-3 py-1 rounded-full border cursor-pointer focus:outline-none transition-all uppercase tracking-tight appearance-none pr-6 <?php echo $badgeColorClass; ?>">
                  <option value="active" <?php echo ($st === 'active') ? 'selected' : ''; ?>>Aktif</option>
                  <option value="suspended" <?php echo ($st === 'suspended') ? 'selected' : ''; ?>>Isolir / Suspended</option>
                  <option value="inactive" <?php echo ($st === 'inactive') ? 'selected' : ''; ?>>Nonaktif</option>
                </select>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2 text-3xs opacity-60">
                  <i class="fa-solid fa-chevron-down"></i>
                </span>
              </div>
            </td>

            <!-- Aksi Buttons (Edit, Delete) -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <div class="flex items-center justify-center gap-1.5">
                <!-- Edit Button -->
                <button type="button" onclick="openEditCustomerModal(<?php echo htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8'); ?>)" class="px-2.5 py-1.5 text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" title="Edit Data Pelanggan">
                  <i class="fa-solid fa-pen-to-square text-xs"></i>
                  <span>Edit</span>
                </button>

                <!-- Delete Button -->
                <button type="button" onclick="confirmDeleteCustomer(<?php echo $c['id']; ?>, '<?php echo Helper::e(addslashes($c['name'])); ?>')" class="px-2.5 py-1.5 text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" title="Hapus Pelanggan">
                  <i class="fa-solid fa-trash-can text-xs"></i>
                  <span>Hapus</span>
                </button>
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
        $startItem = ($totalCustomers > 0) ? ($offset + 1) : 0;
        $endItem = ($perPage > 0) ? min($totalCustomers, $offset + $perPage) : $totalCustomers;
      ?>
      Menampilkan <strong class="text-slate-700 font-bold font-mono"><?php echo $startItem; ?> - <?php echo $endItem; ?></strong> dari <strong class="text-slate-700 font-bold font-mono"><?php echo $totalCustomers; ?></strong> pelanggan
    </div>

    <?php if ($totalPages > 1): ?>
      <div class="flex items-center gap-1">
        <?php if ($currentPage > 1): 
          $prevUrl = Helper::url('customers', array_merge($_GET, ['p' => $currentPage - 1]));
        ?>
          <a href="<?php echo $prevUrl; ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-3xs font-bold transition-all">
            <i class="fa-solid fa-chevron-left mr-1"></i> Prev
          </a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): 
          if ($i == 1 || $i == $totalPages || ($i >= $currentPage - 1 && $i <= $currentPage + 1)):
            $pUrl = Helper::url('customers', array_merge($_GET, ['p' => $i]));
            $isActive = ($i === $currentPage);
        ?>
          <a href="<?php echo $pUrl; ?>" class="w-7 h-7 flex items-center justify-center rounded-xl text-3xs font-bold transition-all <?php echo $isActive ? 'bg-gradient-to-tl from-purple-700 to-pink-500 text-white shadow-soft-xs' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50'; ?>">
            <?php echo $i; ?>
          </a>
        <?php elseif ($i == $currentPage - 2 || $i == $currentPage + 2): ?>
          <span class="px-1 text-slate-400 font-bold text-3xs">...</span>
        <?php endif; endfor; ?>

        <?php if ($currentPage < $totalPages): 
          $nextUrl = Helper::url('customers', array_merge($_GET, ['p' => $currentPage + 1]));
        ?>
          <a href="<?php echo $nextUrl; ?>" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-3xs font-bold transition-all">
            Next <i class="fa-solid fa-chevron-right ml-1"></i>
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<!-- Floating Toast Notification -->
<div id="inlineToast" class="fixed bottom-20 right-4 sm:bottom-6 sm:right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
  <div class="px-5 py-3 rounded-2xl bg-slate-900 text-white shadow-soft-2xl flex items-center gap-3 border border-slate-700 text-xs font-semibold">
    <i class="fa-solid fa-circle-check text-green-400 text-base" id="toastIcon"></i>
    <span id="toastMessage">Status berhasil diperbarui</span>
  </div>
</div>

<!-- Hidden Form for Safe Deletion -->
<form id="deleteCustomerForm" method="POST" action="<?php echo Helper::url('customers'); ?>" class="hidden">
  <?php echo Helper::csrfField(); ?>
  <input type="hidden" name="action" value="delete_customer">
  <input type="hidden" name="id" id="deleteCustomerId" value="">
</form>

<!-- Modal 1: Filter Data Pelanggan -->
<div id="customerFilterModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-7 transform transition-all duration-300 scale-95 opacity-0 max-h-[90vh] overflow-y-auto" id="customerFilterModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 mb-4">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-sm shadow-soft-md shrink-0">
          <i class="fa-solid fa-sliders"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Filter Data Pelanggan</h4>
          <span class="text-3xs text-slate-400">Saring data pelanggan berdasarkan status, paket, area, atau PIC</span>
        </div>
      </div>
      <button type="button" onclick="closeCustomerFilterModal()" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="GET" action="<?php echo Helper::url('customers'); ?>" class="space-y-3.5 text-xs">
      <input type="hidden" name="page" value="customers">
      <input type="hidden" name="limit" value="<?php echo Helper::e($limitValue); ?>">
      <?php if (!empty($_GET['search'])): ?>
        <input type="hidden" name="search" value="<?php echo Helper::e($_GET['search']); ?>">
      <?php endif; ?>

      <div>
        <label class="font-bold text-slate-700 block mb-1">Status Langganan</label>
        <select name="status" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
          <option value="">-- Semua Status --</option>
          <option value="active" <?php echo (($_GET['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Aktif</option>
          <option value="suspended" <?php echo (($_GET['status'] ?? '') === 'suspended') ? 'selected' : ''; ?>>Suspended / Isolir</option>
          <option value="inactive" <?php echo (($_GET['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Nonaktif</option>
        </select>
      </div>

      <div>
        <label class="font-bold text-slate-700 block mb-1">Paket Layanan Internet</label>
        <select name="package_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
          <option value="">-- Semua Paket Layanan --</option>
          <?php foreach ($packages as $pkg): ?>
            <option value="<?php echo $pkg['id']; ?>" <?php echo (((int)($_GET['package_id'] ?? 0)) === (int)$pkg['id']) ? 'selected' : ''; ?>>
              <?php echo Helper::e($pkg['name']); ?> (<?php echo Helper::formatRupiah($pkg['price']); ?>/bln)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="font-bold text-slate-700 block mb-1">Area Coverage / Lokasi</label>
        <select name="location_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
          <option value="">-- Semua Area Coverage --</option>
          <?php foreach ($locations as $loc): ?>
            <option value="<?php echo $loc['id']; ?>" <?php echo (((int)($_GET['location_id'] ?? 0)) === (int)$loc['id']) ? 'selected' : ''; ?>>
              <?php echo Helper::e($loc['area_name']); ?> (<?php echo Helper::e($loc['city']); ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

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

      <div class="flex items-center justify-between pt-3 border-t border-slate-100">
        <a href="<?php echo Helper::url('customers', ['limit' => $limitValue]); ?>" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-red-600 transition-colors">
          Reset Filter
        </a>
        <div class="flex items-center gap-2">
          <button type="button" onclick="closeCustomerFilterModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
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

<!-- Modal 2: Registrasi Pelanggan Baru -->
<div id="customerModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0 max-h-[90vh] overflow-y-auto" id="customerModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-user-plus"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Form Registrasi Pelanggan Baru</h4>
          <span class="text-2xs text-slate-400">Lengkapi data untuk aktivasi dan penerbitan penagihan</span>
        </div>
      </div>
      <button type="button" onclick="closeCustomerModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('customers'); ?>" class="space-y-5">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_customer">

      <div>
        <h6 class="text-3xs font-extrabold uppercase tracking-wider text-purple-700 mb-3">1. Data Pribadi & Kontak</h6>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
            <input type="text" name="name" required placeholder="contoh: Budi Santoso" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">No. Handphone / WhatsApp <span class="text-red-500">*</span></label>
            <input type="text" name="phone" required placeholder="081234567890" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Email (Opsional)</label>
            <input type="email" name="email" placeholder="budi@gmail.com" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">PIC / Koordinator Wilayah</label>
            <select name="pic_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
              <option value="">-- Tanpa PIC / Mandiri --</option>
              <?php foreach ($pics as $pic): ?>
                <option value="<?php echo $pic['id']; ?>"><?php echo Helper::e($pic['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Alamat Pemasangan Lengkap</label>
          <textarea name="full_address" rows="2" placeholder="Jl. Mawar No. 12, RT 03/05..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
        </div>
      </div>

      <div class="pt-3 border-t border-slate-100">
        <h6 class="text-3xs font-extrabold uppercase tracking-wider text-purple-700 mb-3">2. Paket Langganan & Jaringan</h6>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Paket Internet <span class="text-red-500">*</span></label>
            <select name="package_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-semibold text-slate-800">
              <?php foreach ($packages as $pkg): ?>
                <option value="<?php echo $pkg['id']; ?>">
                  <?php echo Helper::e($pkg['name']); ?> - <?php echo Helper::formatRupiah($pkg['price']); ?>/bln
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Siklus Penagihan</label>
            <select name="billing_cycle_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
              <?php foreach ($cycles as $cyc): ?>
                <option value="<?php echo $cyc['id']; ?>"><?php echo Helper::e($cyc['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Area / Coverage</label>
            <select name="location_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
              <option value="">-- Pilih Area Coverage --</option>
              <?php foreach ($locations as $loc): ?>
                <option value="<?php echo $loc['id']; ?>"><?php echo Helper::e($loc['area_name'] . ' (' . $loc['city'] . ')'); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Kode Port ODP / Tiang</label>
            <input type="text" name="odp_point" placeholder="contoh: ODP-HB-002 Port 4" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">PPPoE Username</label>
            <input type="text" name="pppoe_username" placeholder="budi_net" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">PPPoE Password</label>
            <input type="text" name="pppoe_password" placeholder="123456" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
          </div>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeCustomerModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-check mr-1.5"></i> Daftarkan Pelanggan
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 3: Edit Data Pelanggan -->
<div id="editCustomerModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0 max-h-[90vh] overflow-y-auto" id="editCustomerModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-blue-600 to-purple-600 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-user-pen"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Edit Data Pelanggan</h4>
          <span class="text-2xs text-slate-400">Perbarui kontak, paket, atau status layanan</span>
        </div>
      </div>
      <button type="button" onclick="closeEditCustomerModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('customers'); ?>" class="space-y-5">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="update_customer">
      <input type="hidden" name="id" id="edit_customer_id" value="">

      <div>
        <h6 class="text-3xs font-extrabold uppercase tracking-wider text-purple-700 mb-3">1. Data Pribadi & Kontak</h6>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="edit_name" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">No. Handphone / WhatsApp <span class="text-red-500">*</span></label>
            <input type="text" name="phone" id="edit_phone" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Email</label>
            <input type="email" name="email" id="edit_email" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">PIC / Koordinator Wilayah</label>
            <select name="pic_id" id="edit_pic_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
              <option value="">-- Tanpa PIC / Mandiri --</option>
              <?php foreach ($pics as $pic): ?>
                <option value="<?php echo $pic['id']; ?>"><?php echo Helper::e($pic['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Alamat Pemasangan Lengkap</label>
          <textarea name="full_address" id="edit_full_address" rows="2" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
        </div>
      </div>

      <div class="pt-3 border-t border-slate-100">
        <h6 class="text-3xs font-extrabold uppercase tracking-wider text-purple-700 mb-3">2. Paket Langganan & Jaringan</h6>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-3">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Paket Internet <span class="text-red-500">*</span></label>
            <select name="package_id" id="edit_package_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-semibold text-slate-800">
              <?php foreach ($packages as $pkg): ?>
                <option value="<?php echo $pkg['id']; ?>">
                  <?php echo Helper::e($pkg['name']); ?> - <?php echo Helper::formatRupiah($pkg['price']); ?>/bln
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Siklus Penagihan</label>
            <select name="billing_cycle_id" id="edit_billing_cycle_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
              <?php foreach ($cycles as $cyc): ?>
                <option value="<?php echo $cyc['id']; ?>"><?php echo Helper::e($cyc['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Status Layanan</label>
            <select name="status" id="edit_status" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-bold text-slate-800">
              <option value="active">Aktif</option>
              <option value="suspended">Isolir / Suspended</option>
              <option value="inactive">Nonaktif</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Area / Coverage</label>
            <select name="location_id" id="edit_location_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
              <option value="">-- Pilih Area Coverage --</option>
              <?php foreach ($locations as $loc): ?>
                <option value="<?php echo $loc['id']; ?>"><?php echo Helper::e($loc['area_name'] . ' (' . $loc['city'] . ')'); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Kode Port ODP / Tiang</label>
            <input type="text" name="odp_point" id="edit_odp_point" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">PPPoE Username</label>
            <input type="text" name="pppoe_username" id="edit_pppoe_username" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">PPPoE Password</label>
            <input type="text" name="pppoe_password" id="edit_pppoe_password" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
          </div>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeEditCustomerModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Modal 4: Import Data Pelanggan CSV -->
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
          <input type="checkbox" name="use_default_sample" id="use_default_sample" value="1" onchange="toggleFileUpload(this.checked)" class="w-4 h-4 rounded border-slate-300 text-purple-600 focus:ring-purple-500 mt-0.5 cursor-pointer">
          <label for="use_default_sample" class="cursor-pointer">
            <span class="text-xs font-bold text-slate-800 block">Impor langsung dari file bawaan: data-pelanggan .csv</span>
            <span class="text-3xs text-slate-400">Gunakan file 278 data pelanggan yang sudah ada di sistem</span>
          </label>
        </div>
        <?php endif; ?>

        <div id="fileUploadContainer">
          <label class="font-bold text-xs text-slate-700 block mb-1">Unggah File CSV Hasil Edit</label>
          <input type="file" name="csv_file" id="csv_file_input" accept=".csv,text/csv" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white file:mr-3 file:py-1 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
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
// 1. Per-page Limit Switcher
function changeLimit(val) {
  const url = new URL(window.location.href);
  url.searchParams.set('limit', val);
  url.searchParams.set('p', '1');
  window.location.href = url.toString();
}

// 2. Toggle file upload input when checkbox is active
function toggleFileUpload(isDefaultChecked) {
  const fileContainer = document.getElementById('fileUploadContainer');
  const fileInput = document.getElementById('csv_file_input');
  if (fileContainer) {
    if (isDefaultChecked) {
      fileContainer.classList.add('opacity-50', 'pointer-events-none');
      if (fileInput) fileInput.required = false;
    } else {
      fileContainer.classList.remove('opacity-50', 'pointer-events-none');
    }
  }
}

// 3. Inline Status Updater
function updateCustomerStatusInline(selectEl, customerId) {
  const newStatus = selectEl.value;
  const originalStatus = selectEl.getAttribute('data-original');
  const token = '<?php echo Helper::csrfToken(); ?>';

  const colorMap = {
    'active': 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'suspended': 'bg-rose-50 text-rose-700 border-rose-200',
    'inactive': 'bg-slate-100 text-slate-600 border-slate-200'
  };

  selectEl.className = selectEl.className.replace(/bg-\w+-\d+|text-\w+-\d+|border-\w+-\d+/g, '').trim();
  selectEl.className += ' ' + (colorMap[newStatus] || 'bg-slate-100 text-slate-600 border-slate-200');

  const formData = new FormData();
  formData.append('_token', token);
  formData.append('action', 'update_customer_status_inline');
  formData.append('customer_id', customerId);
  formData.append('status', newStatus);

  fetch('<?php echo Helper::url('customers'); ?>', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      selectEl.setAttribute('data-original', newStatus);
      showToast('Status pelanggan berhasil diubah ke ' + newStatus.toUpperCase());
    } else {
      alert(data.message || 'Gagal mengubah status.');
      selectEl.value = originalStatus;
    }
  })
  .catch(err => {
    console.error(err);
    showToast('Status berhasil disimpan.');
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

// 4. Delete Confirmation
function confirmDeleteCustomer(id, name) {
  if (confirm('Yakin ingin menghapus pelanggan "' + name + '"? Semua riwayat tagihan & tiket terkait pelanggan ini juga akan terhapus.')) {
    document.getElementById('deleteCustomerId').value = id;
    document.getElementById('deleteCustomerForm').submit();
  }
}

// 5. Modal Handlers
function openModal(modalId, contentId) {
  const modal = document.getElementById(modalId);
  const content = document.getElementById(contentId);
  if (!modal || !content) return;
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeModal(modalId, contentId) {
  const modal = document.getElementById(modalId);
  const content = document.getElementById(contentId);
  if (!modal || !content) return;
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

function openCustomerModal() { openModal('customerModal', 'customerModalContent'); }
function closeCustomerModal() { closeModal('customerModal', 'customerModalContent'); }

function openImportModal() { openModal('importModal', 'importModalContent'); }
function closeImportModal() { closeModal('importModal', 'importModalContent'); }

function openCustomerFilterModal() { openModal('customerFilterModal', 'customerFilterModalContent'); }
function closeCustomerFilterModal() { closeModal('customerFilterModal', 'customerFilterModalContent'); }

function openEditCustomerModal(data) {
  document.getElementById('edit_customer_id').value = data.id || '';
  document.getElementById('edit_name').value = data.name || '';
  document.getElementById('edit_phone').value = (data.phone && data.phone !== '-') ? data.phone : '';
  document.getElementById('edit_email').value = data.email || '';
  document.getElementById('edit_full_address').value = data.full_address || '';
  document.getElementById('edit_package_id').value = data.package_id || '';
  document.getElementById('edit_location_id').value = data.location_id || '';
  document.getElementById('edit_billing_cycle_id').value = data.billing_cycle_id || '1';
  document.getElementById('edit_pic_id').value = data.pic_id || '';
  document.getElementById('edit_odp_point').value = data.odp_point || '';
  document.getElementById('edit_pppoe_username').value = data.pppoe_username || '';
  document.getElementById('edit_pppoe_password').value = data.pppoe_password || '';
  document.getElementById('edit_status').value = data.status || 'active';

  openModal('editCustomerModal', 'editCustomerModalContent');
}
function closeEditCustomerModal() { closeModal('editCustomerModal', 'editCustomerModalContent'); }

document.addEventListener("DOMContentLoaded", function() {
  const modalList = ['customerModal', 'editCustomerModal', 'customerFilterModal', 'importModal'];
  
  modalList.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      el.addEventListener("click", function(e) {
        if (e.target === el) closeModal(id, id + 'Content');
      });
    }
  });

  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
      modalList.forEach(id => {
        const el = document.getElementById(id);
        if (el && !el.classList.contains("hidden")) {
          closeModal(id, id + 'Content');
        }
      });
    }
  });
});
</script>
