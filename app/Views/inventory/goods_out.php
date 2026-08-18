<?php
// app/Views/inventory/goods_out.php - Goods Out Recording
?>
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl space-y-6">
  
  <!-- Header with Title & Add Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Riwayat Pengeluaran Barang</h4>
      <p class="text-2xs text-slate-400">Log mutasi pengurangan stok material, perangkat instalasi pelanggan, dan kebutuhan proyek</p>
    </div>

    <button type="button" onclick="openGoodsOutModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-orange-500 to-amber-400 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
      <i class="fa-solid fa-minus-circle text-xs"></i>
      <span>Catat Barang Keluar</span>
    </button>
  </div>

  <!-- Filter Menu -->
  <form method="GET" action="" class="flex flex-col sm:flex-row gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
    <input type="hidden" name="page" value="goods_out">
    <div class="flex-1">
      <label class="text-3xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">Bulan Transaksi</label>
      <input type="month" name="month" value="<?php echo htmlspecialchars($_GET['month'] ?? date('Y-m')); ?>" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-orange-500">
    </div>
    <div class="flex-[2]">
      <label class="text-3xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">Item Barang</label>
      <select name="item_id" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-orange-500 bg-white">
        <option value="">Semua Barang</option>
        <?php foreach ($items as $it): ?>
          <option value="<?php echo $it['id']; ?>" <?php echo (isset($_GET['item_id']) && $_GET['item_id'] == $it['id']) ? 'selected' : ''; ?>>
            <?php echo Helper::e($it['name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="flex items-end gap-2">
      <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-slate-800 rounded-xl shadow-soft-sm hover:bg-slate-900 transition-colors">
        Terapkan Filter
      </button>
      <a href="?page=goods_out" class="px-5 py-2.5 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl shadow-soft-sm hover:bg-slate-50 transition-colors">
        Reset
      </a>
    </div>
  </form>

  <!-- Goods Out History Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
      <thead>
        <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
          <th class="py-3 px-4 font-bold">No. Bukti & Nama Item</th>
          <th class="py-3 px-4 font-bold text-center">Jumlah Keluar</th>
          <th class="py-3 px-4 font-bold">Tujuan Distribusi</th>
          <th class="py-3 px-4 font-bold">Catatan Penggunaan</th>
          <th class="py-3 px-4 font-bold text-center">Tanggal Transaksi</th>
          <th class="py-3 px-4 font-bold text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php if (empty($history)): ?>
        <tr>
          <td colspan="6" class="py-12 text-center text-slate-400">
            <i class="fa-solid fa-truck-ramp-box text-3xl mb-2 block text-slate-300"></i>
            Belum ada data barang keluar yang tercatat
          </td>
        </tr>
        <?php else: ?>
          <?php foreach ($history as $h): ?>
          <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3.5 px-4 whitespace-nowrap">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center text-sm font-black shrink-0">
                  <i class="fa-solid fa-arrow-up-right text-xs"></i>
                </div>
                <div>
                  <span class="text-xs font-bold text-slate-900 block"><?php echo Helper::e($h['item_name']); ?></span>
                  <span class="text-3xs text-orange-700 font-mono font-bold"><?php echo Helper::e($h['transaction_no']); ?></span>
                </div>
              </div>
            </td>

            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <span class="px-3 py-1 rounded-full bg-orange-50 text-orange-700 font-bold font-mono text-2xs">
                -<?php echo $h['quantity']; ?> <?php echo Helper::e($h['unit']); ?>
              </span>
            </td>

            <td class="py-3.5 px-4 whitespace-nowrap text-xs">
              <?php 
                $dest = strtolower($h['destination_type'] ?? '');
                $badgeClasses = [
                  'customer' => 'bg-purple-50 text-purple-700 border-purple-200',
                  'technician' => 'bg-blue-50 text-blue-700 border-blue-200',
                  'project' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                  'damaged' => 'bg-rose-50 text-rose-700 border-rose-200',
                ];
                $destLabels = [
                  'customer' => 'Instalasi Pelanggan',
                  'technician' => 'Operasional Teknisi',
                  'project' => 'Proyek RAB / Backbone',
                  'damaged' => 'Rusak / Afkir',
                ];
                $cls = $badgeClasses[$dest] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                $lbl = $destLabels[$dest] ?? ucfirst($dest ?: 'Umum');
              ?>
              <span class="px-2.5 py-0.5 rounded-full text-3xs font-extrabold border <?php echo $cls; ?> uppercase">
                <?php echo $lbl; ?>
              </span>
            </td>

            <td class="py-3.5 px-4 text-xs text-slate-600 max-w-xs truncate">
              <?php echo Helper::e($h['notes'] ?: '-'); ?>
            </td>

            <td class="py-3.5 px-4 text-center whitespace-nowrap text-xs font-mono text-slate-600">
              <?php echo Helper::formatDate($h['transaction_date']); ?>
            </td>

            <!-- Aksi: Edit & Hapus -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <div class="flex items-center justify-center gap-1.5">
                <button type="button" 
                        onclick='openEditGoodsOutModal(<?php echo htmlspecialchars(json_encode($h), ENT_QUOTES, "UTF-8"); ?>)' 
                        class="px-2.5 py-1.5 text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" 
                        title="Edit Transaksi Keluar">
                  <i class="fa-solid fa-pen-to-square text-xs"></i>
                  <span>Edit</span>
                </button>

                <button type="button" 
                        onclick="confirmDeleteGoodsOut(<?php echo $h['id']; ?>, '<?php echo Helper::e(addslashes($h['transaction_no'])); ?>', <?php echo (int)$h['quantity']; ?>, '<?php echo Helper::e(addslashes($h['item_name'])); ?>')" 
                        class="px-2.5 py-1.5 text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" 
                        title="Hapus Transaksi">
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

</div>

<!-- Modal Pop-Up: Catat Barang Keluar -->
<div id="goodsOutModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="goodsOutModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-orange-500 to-amber-400 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-truck-ramp-box"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Catat Barang Keluar</h4>
          <span class="text-2xs text-slate-400">Pengurangan stok untuk instalasi, operasional, atau proyek</span>
        </div>
      </div>
      <button type="button" onclick="closeGoodsOutModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('goods_out'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_goods_out">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Pilih Item Barang <span class="text-red-500">*</span></label>
        <select name="item_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
          <option value="">-- Pilih Barang --</option>
          <?php foreach ($items as $it): ?>
            <option value="<?php echo $it['id']; ?>">
              <?php echo Helper::e($it['name'] . ' (Tersedia: ' . $it['current_stock'] . ' ' . $it['unit'] . ')'); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Jumlah Keluar <span class="text-red-500">*</span></label>
          <input type="number" name="quantity" min="1" value="1" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 font-bold focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Tujuan Distribusi <span class="text-red-500">*</span></label>
          <select name="destination_type" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
            <option value="customer">Instalasi Pelanggan</option>
            <option value="technician">Operasional Teknisi</option>
            <option value="project">Proyek RAB / Backbone</option>
            <option value="damaged">Rusak / Afkir</option>
          </select>
        </div>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Catatan Penggunaan</label>
        <textarea name="notes" rows="2" placeholder="contoh: Dipasang di rumah pelanggan Bpk. Anugrah / penarikan kabel ODP..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeGoodsOutModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-orange-500 to-amber-400 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-minus-circle mr-1.5"></i> Simpan Barang Keluar
        </button>
      </div>
    </form>

  </div>
</div>

<!-- Modal Pop-Up: Edit Barang Keluar -->
<div id="editGoodsOutModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="editGoodsOutModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-blue-600 to-cyan-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-pen-to-square"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Edit Transaksi Barang Keluar</h4>
          <span class="text-2xs text-slate-400">Penyesuaian jumlah pengeluaran, tujuan, atau catatan penggunaan</span>
        </div>
      </div>
      <button type="button" onclick="closeEditGoodsOutModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('goods_out'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="update_goods_out">
      <input type="hidden" name="id" id="edit_gout_id">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Item Barang <span class="text-red-500">*</span></label>
        <select name="item_id" id="edit_gout_item_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
          <?php foreach ($items as $it): ?>
            <option value="<?php echo $it['id']; ?>">
              <?php echo Helper::e($it['name'] . ' (Tersedia: ' . $it['current_stock'] . ' ' . $it['unit'] . ')'); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Jumlah Keluar <span class="text-red-500">*</span></label>
          <input type="number" name="quantity" id="edit_gout_quantity" min="1" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 font-bold focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Tujuan Distribusi <span class="text-red-500">*</span></label>
          <select name="destination_type" id="edit_gout_destination_type" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
            <option value="customer">Instalasi Pelanggan</option>
            <option value="technician">Operasional Teknisi</option>
            <option value="project">Proyek RAB / Backbone</option>
            <option value="damaged">Rusak / Afkir</option>
          </select>
        </div>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Catatan Penggunaan</label>
        <textarea name="notes" id="edit_gout_notes" rows="2" placeholder="contoh: Dipasang di rumah pelanggan Bpk. Anugrah / penarikan kabel ODP..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeEditGoodsOutModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-blue-600 to-cyan-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Perubahan
        </button>
      </div>
    </form>

  </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteGoodsOutForm" method="POST" action="<?php echo Helper::url('goods_out'); ?>" class="hidden">
  <?php echo Helper::csrfField(); ?>
  <input type="hidden" name="action" value="delete_goods_out">
  <input type="hidden" name="id" id="deleteGoodsOutId">
</form>

<!-- Modal Control Script -->
<script>
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

function openGoodsOutModal() {
  openModal('goodsOutModal', 'goodsOutModalContent');
}

function closeGoodsOutModal() {
  closeModal('goodsOutModal', 'goodsOutModalContent');
}

function openEditGoodsOutModal(trx) {
  if (!trx) return;

  document.getElementById('edit_gout_id').value = trx.id || '';
  document.getElementById('edit_gout_item_id').value = trx.item_id || '';
  document.getElementById('edit_gout_quantity').value = trx.quantity || 1;
  document.getElementById('edit_gout_destination_type').value = trx.destination_type || 'customer';
  document.getElementById('edit_gout_notes').value = trx.notes || '';

  openModal('editGoodsOutModal', 'editGoodsOutModalContent');
}

function closeEditGoodsOutModal() {
  closeModal('editGoodsOutModal', 'editGoodsOutModalContent');
}

function confirmDeleteGoodsOut(id, trxNo, qty, itemName) {
  if (confirm('Apakah Anda yakin ingin menghapus transaksi barang keluar "' + trxNo + '" (' + itemName + ' -' + qty + ')?\n\nStok barang di gudang akan otomatis dikembalikan (bertambah).')) {
    document.getElementById('deleteGoodsOutId').value = id;
    document.getElementById('deleteGoodsOutForm').submit();
  }
}

// Close on outside click or ESC key
document.addEventListener("DOMContentLoaded", function() {
  const modals = [
    { id: 'goodsOutModal', content: 'goodsOutModalContent' },
    { id: 'editGoodsOutModal', content: 'editGoodsOutModalContent' }
  ];

  modals.forEach(m => {
    const modalEl = document.getElementById(m.id);
    if (modalEl) {
      modalEl.addEventListener("click", function(e) {
        if (e.target === modalEl) {
          closeModal(m.id, m.content);
        }
      });
    }
  });

  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") {
      modals.forEach(m => {
        const modalEl = document.getElementById(m.id);
        if (modalEl && !modalEl.classList.contains("hidden")) {
          closeModal(m.id, m.content);
        }
      });
    }
  });
});
</script>
