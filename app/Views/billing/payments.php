<?php
// app/Views/billing/payments.php - Payments History with Sleek Filter Modal
$activeFilterCount = 0;
if (!empty($_GET['method'])) $activeFilterCount++;
if (!empty($_GET['account_id'])) $activeFilterCount++;
if (!empty($_GET['location_id'])) $activeFilterCount++;
if (!empty($_GET['pic_id'])) $activeFilterCount++;
if (!empty($_GET['period'])) $activeFilterCount++;
if (!empty($_GET['start_date']) || !empty($_GET['end_date'])) $activeFilterCount++;

$hasActiveFilters = ($activeFilterCount > 0 || !empty($_GET['search']));
?>
<div class="bg-white rounded-3xl p-6 shadow-soft-xl space-y-5 relative">
  
  <!-- Header Section -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Riwayat Pembayaran Pelanggan</h4>
      <p class="text-2xs text-slate-400">Daftar transaksi penerimaan pembayaran tagihan dan iuran langganan</p>
    </div>

    <!-- Summary Widget -->
    <div class="flex items-center gap-3">
      <div class="px-4 py-2 bg-purple-50 rounded-2xl flex items-center gap-2.5">
        <div class="w-7 h-7 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-xs shadow-soft-xs">
          <i class="fa-solid fa-receipt"></i>
        </div>
        <div>
          <span class="text-3xs text-purple-700 font-extrabold uppercase tracking-wider block">Total Diterima</span>
          <span class="text-xs font-black text-slate-900 font-mono"><?php echo Helper::formatRupiah($totalCollected ?? 0); ?></span>
        </div>
      </div>
      
      <div class="hidden md:flex px-3.5 py-2 bg-slate-50 rounded-2xl items-center gap-2">
        <span class="text-3xs text-slate-400 font-bold uppercase">Data:</span>
        <span class="text-xs font-bold text-slate-800 font-mono"><?php echo count($payments); ?> Transaksi</span>
      </div>
    </div>
  </div>

  <!-- Sleek, Minimal Filter Bar -->
  <div class="space-y-2">
    <form method="GET" action="<?php echo Helper::url('payments'); ?>" class="flex items-center gap-2">
      <input type="hidden" name="page" value="payments">
      <?php if (!empty($_GET['method'])): ?><input type="hidden" name="method" value="<?php echo Helper::e($_GET['method']); ?>"><?php endif; ?>
      <?php if (!empty($_GET['account_id'])): ?><input type="hidden" name="account_id" value="<?php echo Helper::e($_GET['account_id']); ?>"><?php endif; ?>
      <?php if (!empty($_GET['location_id'])): ?><input type="hidden" name="location_id" value="<?php echo Helper::e($_GET['location_id']); ?>"><?php endif; ?>
      <?php if (!empty($_GET['pic_id'])): ?><input type="hidden" name="pic_id" value="<?php echo Helper::e($_GET['pic_id']); ?>"><?php endif; ?>
      <?php if (!empty($_GET['period'])): ?><input type="hidden" name="period" value="<?php echo Helper::e($_GET['period']); ?>"><?php endif; ?>
      <?php if (!empty($_GET['start_date'])): ?><input type="hidden" name="start_date" value="<?php echo Helper::e($_GET['start_date']); ?>"><?php endif; ?>
      <?php if (!empty($_GET['end_date'])): ?><input type="hidden" name="end_date" value="<?php echo Helper::e($_GET['end_date']); ?>"><?php endif; ?>
      
      <!-- Search Input -->
      <div class="relative flex-1">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
          <i class="fa-solid fa-magnifying-glass text-xs"></i>
        </span>
        <input type="text" name="search" value="<?php echo Helper::e($_GET['search'] ?? ''); ?>" placeholder="Cari no bayar, invoice, nama pelanggan, ref..." class="w-full text-xs pl-8 pr-3 py-2 sm:py-2.5 rounded-2xl bg-slate-50 border border-slate-200/80 focus:bg-white focus:outline-none focus:border-purple-500 shadow-soft-xs placeholder:text-slate-400 font-medium">
      </div>

      <!-- Filter Modal Trigger Button -->
      <button type="button" onclick="openPaymentFilterModal()" class="px-3 sm:px-4 py-2 sm:py-2.5 text-xs font-bold rounded-2xl border transition-all flex items-center gap-1.5 shadow-soft-xs whitespace-nowrap <?php echo ($activeFilterCount > 0) ? 'bg-purple-50 text-purple-700 border-purple-300 ring-2 ring-purple-500/20' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-200'; ?>">
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
        <a href="<?php echo Helper::url('payments'); ?>" class="p-2 sm:px-3 sm:py-2.5 text-xs font-bold text-slate-500 hover:text-red-600 bg-white hover:bg-red-50 border border-slate-200 rounded-2xl transition-all flex items-center justify-center" title="Reset Semua Filter">
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

        <?php if (!empty($_GET['start_date']) || !empty($_GET['end_date'])): ?>
          <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-purple-50 border border-purple-200 text-purple-700 text-3xs font-bold">
            <i class="fa-solid fa-calendar-days text-3xs"></i>
            <span>Tgl: <?php echo Helper::e($_GET['start_date'] ?? 'Awal'); ?> s/d <?php echo Helper::e($_GET['end_date'] ?? 'Kini'); ?></span>
          </span>
        <?php endif; ?>

        <?php if (!empty($_GET['method'])): ?>
          <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-teal-50 border border-teal-200 text-teal-700 text-3xs font-bold">
            <i class="fa-solid fa-credit-card text-3xs"></i>
            <span>Metode: <?php echo Helper::e($_GET['method']); ?></span>
          </span>
        <?php endif; ?>

        <?php if (!empty($_GET['account_id'])): 
          $accName = '';
          foreach ($accounts as $a) { if ((int)$a['id'] === (int)$_GET['account_id']) { $accName = $a['account_name']; break; } }
        ?>
          <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl bg-cyan-50 border border-cyan-200 text-cyan-700 text-3xs font-bold">
            <i class="fa-solid fa-wallet text-3xs"></i>
            <span>Kas: <?php echo Helper::e($accName ?: 'ID ' . $_GET['account_id']); ?></span>
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
          <th class="py-3 px-4 font-bold">No. Pembayaran & Ref</th>
          <th class="py-3 px-4 font-bold">Pelanggan & Area</th>
          <th class="py-3 px-4 font-bold">No. Invoice & Periode</th>
          <th class="py-3 px-4 font-bold">Metode & Akun Kas</th>
          <th class="py-3 px-4 font-bold text-right">Nominal</th>
          <th class="py-3 px-4 font-bold text-center">Tanggal Bayar</th>
          <th class="py-3 px-4 font-bold text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php if (empty($payments)): ?>
        <tr>
          <td colspan="8" class="py-12 text-center text-slate-400">
            <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300 block"></i>
            <span class="text-xs font-semibold">Tidak ada riwayat pembayaran yang sesuai dengan filter.</span>
          </td>
        </tr>
        <?php else: ?>
          <?php $no = 1; foreach ($payments as $p): 
            $coverage = $p['area_name'] ?? '';
          ?>
          <tr class="hover:bg-slate-50/80 transition-colors">
            <!-- No -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap font-bold text-slate-500 font-mono text-xs">
              <?php echo $no++; ?>
            </td>

            <!-- No. Pembayaran & Ref -->
            <td class="py-3.5 px-4 whitespace-nowrap">
              <span class="font-mono font-bold text-purple-700 text-xs block"><?php echo Helper::e($p['payment_no']); ?></span>
              <span class="text-3xs text-slate-400">Ref: <?php echo Helper::e($p['reference_no'] ?: '-'); ?></span>
            </td>

            <!-- Pelanggan & Area -->
            <td class="py-3.5 px-4">
              <span class="font-black text-slate-900 text-xs block leading-snug"><?php echo Helper::e($p['customer_name']); ?></span>
              <?php if (!empty($coverage)): ?>
                <span class="inline-flex items-center gap-1 text-3xs text-slate-500 mt-0.5">
                  <i class="fa-solid fa-map-location-dot text-purple-500"></i>
                  <span><?php echo Helper::e($coverage); ?></span>
                </span>
              <?php endif; ?>
            </td>

            <!-- No. Invoice & Periode -->
            <td class="py-3.5 px-4 whitespace-nowrap">
              <a href="<?php echo Helper::url('show_invoice', ['id' => $p['invoice_id']]); ?>" class="text-xs font-bold text-purple-700 hover:underline block font-mono">
                <?php echo Helper::e($p['invoice_no']); ?>
              </a>
              <span class="text-3xs text-slate-400 font-mono"><?php echo Helper::e($p['billing_period']); ?></span>
            </td>

            <!-- Metode & Akun Kas -->
            <td class="py-3.5 px-4 whitespace-nowrap">
              <span class="font-bold text-slate-800 text-xs block"><?php echo Helper::e($p['payment_method']); ?></span>
              <span class="text-3xs text-purple-600 font-medium"><?php echo Helper::e($p['account_name']); ?></span>
            </td>

            <!-- Nominal Diterima -->
            <td class="py-3.5 px-4 text-right whitespace-nowrap font-mono">
              <span class="text-xs font-black text-emerald-600 block"><?php echo Helper::formatRupiah($p['amount']); ?></span>
            </td>

            <!-- Tanggal Bayar -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap text-xs font-mono text-slate-600">
              <?php echo Helper::formatDate($p['payment_date']); ?>
            </td>

            <!-- Aksi -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <a href="<?php echo Helper::url('show_invoice', ['id' => $p['invoice_id']]); ?>" class="px-3 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-2xs rounded-xl transition-colors inline-flex items-center gap-1">
                <i class="fa-solid fa-file-invoice text-3xs"></i>
                <span>Invoice</span>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Modal Pop-Up: Filter Riwayat Pembayaran -->
<div id="paymentFilterModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-7 transform transition-all duration-300 scale-95 opacity-0 max-h-[90vh] overflow-y-auto" id="paymentFilterModalContent">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3.5 mb-4">
      <div class="flex items-center gap-2.5">
        <div class="w-9 h-9 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-sm shadow-soft-md shrink-0">
          <i class="fa-solid fa-sliders"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Filter Riwayat Pembayaran</h4>
          <span class="text-3xs text-slate-400">Saring transaksi berdasarkan metode, akun, tanggal, atau area</span>
        </div>
      </div>
      <button type="button" onclick="closePaymentFilterModal()" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="<?php echo Helper::url('payments'); ?>" class="space-y-3.5 text-xs">
      <input type="hidden" name="page" value="payments">
      <?php if (!empty($_GET['search'])): ?>
        <input type="hidden" name="search" value="<?php echo Helper::e($_GET['search']); ?>">
      <?php endif; ?>

      <!-- 1. Periode Bulan -->
      <div>
        <label class="font-bold text-slate-700 block mb-1">Target Periode / Bulan</label>
        <input type="month" name="period" value="<?php echo Helper::e($_GET['period'] ?? ''); ?>" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
      </div>

      <!-- 2. Rentang Tanggal Bayar -->
      <div class="grid grid-cols-2 gap-2.5">
        <div>
          <label class="font-bold text-slate-700 block mb-1">Mulai Tanggal</label>
          <input type="date" name="start_date" value="<?php echo Helper::e($_GET['start_date'] ?? ''); ?>" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
        </div>
        <div>
          <label class="font-bold text-slate-700 block mb-1">Sampai Tanggal</label>
          <input type="date" name="end_date" value="<?php echo Helper::e($_GET['end_date'] ?? ''); ?>" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
        </div>
      </div>

      <!-- 3. Metode Pembayaran -->
      <div>
        <label class="font-bold text-slate-700 block mb-1">Metode Pembayaran</label>
        <select name="method" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
          <option value="">-- Semua Metode Pembayaran --</option>
          <?php foreach ($methods as $m): ?>
            <option value="<?php echo Helper::e($m['payment_method']); ?>" <?php echo (($_GET['method'] ?? '') === $m['payment_method']) ? 'selected' : ''; ?>>
              <?php echo Helper::e($m['payment_method']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- 4. Akun Kas & Bank -->
      <div>
        <label class="font-bold text-slate-700 block mb-1">Akun Penerimaan (Kas & Bank)</label>
        <select name="account_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium text-slate-800">
          <option value="">-- Semua Akun Kas & Bank --</option>
          <?php foreach ($accounts as $acc): ?>
            <option value="<?php echo $acc['id']; ?>" <?php echo (((int)($_GET['account_id'] ?? 0)) === (int)$acc['id']) ? 'selected' : ''; ?>>
              <?php echo Helper::e($acc['account_name']); ?> <?php echo !empty($acc['account_number']) ? '(' . Helper::e($acc['account_number']) . ')' : ''; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- 5. Area Coverage -->
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

      <!-- 6. PIC / Mitra -->
      <div>
        <label class="font-bold text-slate-700 block mb-1">PIC / Penanggung Jawab Wilayah</label>
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
        <a href="<?php echo Helper::url('payments'); ?>" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-red-600 transition-colors">
          Reset Filter
        </a>
        <div class="flex items-center gap-2">
          <button type="button" onclick="closePaymentFilterModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
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
function openPaymentFilterModal() {
  const modal = document.getElementById("paymentFilterModal");
  const content = document.getElementById("paymentFilterModalContent");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closePaymentFilterModal() {
  const modal = document.getElementById("paymentFilterModal");
  const content = document.getElementById("paymentFilterModalContent");
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

document.addEventListener("DOMContentLoaded", function() {
  const modal = document.getElementById("paymentFilterModal");
  if (modal) {
    modal.addEventListener("click", function(e) {
      if (e.target === modal) closePaymentFilterModal();
    });
  }

  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && modal && !modal.classList.contains("hidden")) {
      closePaymentFilterModal();
    }
  });
});
</script>
