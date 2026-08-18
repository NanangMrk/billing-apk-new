<?php
// app/Views/inventory/goods_in.php - Goods In Recording
?>
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl space-y-6">
  
  <!-- Header with Title & Add Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Riwayat Penerimaan Barang Masuk</h4>
      <p class="text-2xs text-slate-400">Log mutasi penambahan stok logistik dari pengadaan supplier dan vendor</p>
    </div>

    <button type="button" onclick="openGoodsInModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-emerald-600 to-teal-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
      <i class="fa-solid fa-plus-circle text-xs"></i>
      <span>Catat Barang Masuk</span>
    </button>
  </div>

  <!-- Filter Menu -->
  <form method="GET" action="" class="flex flex-col sm:flex-row gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
    <input type="hidden" name="page" value="goods_in">
    <div class="flex-1">
      <label class="text-3xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">Bulan Transaksi</label>
      <input type="month" name="month" value="<?php echo htmlspecialchars($_GET['month'] ?? date('Y-m')); ?>" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500">
    </div>
    <div class="flex-[2]">
      <label class="text-3xs font-bold text-slate-500 uppercase tracking-wider mb-1 block">Item Barang</label>
      <select name="item_id" class="w-full text-xs px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-emerald-500 bg-white">
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
      <a href="?page=goods_in" class="px-5 py-2.5 text-xs font-bold text-slate-600 bg-white border border-slate-200 rounded-xl shadow-soft-sm hover:bg-slate-50 transition-colors">
        Reset
      </a>
    </div>
  </form>

  <!-- Goods In History Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
      <thead>
        <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
          <th class="py-3 px-4 font-bold">No. Bukti & Nama Item</th>
          <th class="py-3 px-4 font-bold text-center">Jumlah Masuk</th>
          <th class="py-3 px-4 font-bold text-right">Total Nilai Pembelian</th>
          <th class="py-3 px-4 font-bold">No. Referensi / PO</th>
          <th class="py-3 px-4 font-bold text-center">Tanggal Transaksi</th>
          <th class="py-3 px-4 font-bold text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php if (empty($history)): ?>
        <tr>
          <td colspan="6" class="py-12 text-center text-slate-400">
            <i class="fa-solid fa-dolly text-3xl mb-2 block text-slate-300"></i>
            Belum ada data barang masuk yang tercatat
          </td>
        </tr>
        <?php else: ?>
          <?php foreach ($history as $h): ?>
          <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3.5 px-4 whitespace-nowrap">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-black shrink-0">
                  <i class="fa-solid fa-arrow-down-left text-xs"></i>
                </div>
                <div>
                  <span class="text-xs font-bold text-slate-900 block"><?php echo Helper::e($h['item_name']); ?></span>
                  <span class="text-3xs text-emerald-700 font-mono font-bold"><?php echo Helper::e($h['transaction_no']); ?></span>
                </div>
              </div>
            </td>

            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold font-mono text-2xs">
                +<?php echo $h['quantity']; ?> <?php echo Helper::e($h['unit']); ?>
              </span>
            </td>

            <td class="py-3.5 px-4 text-right whitespace-nowrap">
              <span class="text-sm font-black text-slate-900 font-mono block"><?php echo Helper::formatRupiah($h['total_amount']); ?></span>
              <?php if ($h['quantity'] > 0): ?>
                <span class="text-3xs text-slate-400">@<?php echo Helper::formatRupiah($h['unit_price'] ?: ($h['total_amount'] / $h['quantity'])); ?></span>
              <?php endif; ?>
            </td>

            <td class="py-3.5 px-4 whitespace-nowrap text-xs">
              <span class="font-semibold text-slate-800 block"><?php echo Helper::e($h['reference_no'] ?: '-'); ?></span>
              <span class="text-3xs text-slate-400 max-w-xs truncate block"><?php echo Helper::e($h['notes'] ?: 'Penerimaan stok'); ?></span>
            </td>

            <td class="py-3.5 px-4 text-center whitespace-nowrap text-xs font-mono text-slate-600">
              <?php echo Helper::formatDate($h['transaction_date']); ?>
            </td>

            <!-- Aksi: Edit & Hapus -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <div class="flex items-center justify-center gap-1.5">
                <button type="button" 
                        onclick='openEditGoodsInModal(<?php echo htmlspecialchars(json_encode($h), ENT_QUOTES, "UTF-8"); ?>)' 
                        class="px-2.5 py-1.5 text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" 
                        title="Edit Transaksi Masuk">
                  <i class="fa-solid fa-pen-to-square text-xs"></i>
                  <span>Edit</span>
                </button>

                <button type="button" 
                        onclick="confirmDeleteGoodsIn(<?php echo $h['id']; ?>, '<?php echo Helper::e(addslashes($h['transaction_no'])); ?>', <?php echo (int)$h['quantity']; ?>, '<?php echo Helper::e(addslashes($h['item_name'])); ?>')" 
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

<!-- Modal Pop-Up: Catat Barang Masuk -->
<div id="goodsInModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="goodsInModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-emerald-600 to-teal-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-dolly"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Catat Barang Masuk</h4>
          <span class="text-2xs text-slate-400">Input stok bertambah dari pembelian supplier / vendor</span>
        </div>
      </div>
      <button type="button" onclick="closeGoodsInModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('goods_in'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_goods_in">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Pilih Item Barang <span class="text-red-500">*</span></label>
        <select name="item_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
          <option value="">-- Pilih Barang --</option>
          <?php foreach ($items as $it): ?>
            <option value="<?php echo $it['id']; ?>">
              <?php echo Helper::e($it['name'] . ' (Sisa Stok: ' . $it['current_stock'] . ' ' . $it['unit'] . ')'); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Jumlah Masuk <span class="text-red-500">*</span></label>
          <input type="number" name="quantity" min="1" value="1" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 font-bold focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Harga Beli Satuan (Rp)</label>
          <input type="number" name="unit_price" placeholder="0" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
        </div>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">No. Surat Jalan / Invoice PO</label>
        <input type="text" name="reference_no" placeholder="contoh: PO-202608-01 / SJ-FIBER-88" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Catatan Pengadaan</label>
        <textarea name="notes" rows="2" placeholder="Catatan supplier, penerima barang, atau kondisi material..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeGoodsInModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-emerald-600 to-teal-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-plus-circle mr-1.5"></i> Simpan Barang Masuk
        </button>
      </div>
    </form>

  </div>
</div>

<!-- Modal Pop-Up: Edit Barang Masuk -->
<div id="editGoodsInModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="editGoodsInModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-blue-600 to-cyan-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-pen-to-square"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Edit Transaksi Barang Masuk</h4>
          <span class="text-2xs text-slate-400">Penyesuaian jumlah masuk, harga, atau nomor referensi</span>
        </div>
      </div>
      <button type="button" onclick="closeEditGoodsInModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('goods_in'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="update_goods_in">
      <input type="hidden" name="id" id="edit_gin_id">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Item Barang <span class="text-red-500">*</span></label>
        <select name="item_id" id="edit_gin_item_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
          <?php foreach ($items as $it): ?>
            <option value="<?php echo $it['id']; ?>">
              <?php echo Helper::e($it['name'] . ' (Sisa Stok: ' . $it['current_stock'] . ' ' . $it['unit'] . ')'); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Jumlah Masuk <span class="text-red-500">*</span></label>
          <input type="number" name="quantity" id="edit_gin_quantity" min="1" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 font-bold focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Harga Beli Satuan (Rp)</label>
          <input type="number" name="unit_price" id="edit_gin_unit_price" placeholder="0" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
        </div>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">No. Surat Jalan / Invoice PO</label>
        <input type="text" name="reference_no" id="edit_gin_reference_no" placeholder="contoh: PO-202608-01 / SJ-FIBER-88" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Catatan Pengadaan</label>
        <textarea name="notes" id="edit_gin_notes" rows="2" placeholder="Catatan supplier, penerima barang, atau kondisi material..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeEditGoodsInModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
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
<form id="deleteGoodsInForm" method="POST" action="<?php echo Helper::url('goods_in'); ?>" class="hidden">
  <?php echo Helper::csrfField(); ?>
  <input type="hidden" name="action" value="delete_goods_in">
  <input type="hidden" name="id" id="deleteGoodsInId">
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

function openGoodsInModal() {
  openModal('goodsInModal', 'goodsInModalContent');
}

function closeGoodsInModal() {
  closeModal('goodsInModal', 'goodsInModalContent');
}

function openEditGoodsInModal(trx) {
  if (!trx) return;

  document.getElementById('edit_gin_id').value = trx.id || '';
  document.getElementById('edit_gin_item_id').value = trx.item_id || '';
  document.getElementById('edit_gin_quantity').value = trx.quantity || 1;
  document.getElementById('edit_gin_unit_price').value = trx.unit_price || (trx.quantity > 0 ? (trx.total_amount / trx.quantity) : 0);
  document.getElementById('edit_gin_reference_no').value = trx.reference_no || '';
  document.getElementById('edit_gin_notes').value = trx.notes || '';

  openModal('editGoodsInModal', 'editGoodsInModalContent');
}

function closeEditGoodsInModal() {
  closeModal('editGoodsInModal', 'editGoodsInModalContent');
}

function confirmDeleteGoodsIn(id, trxNo, qty, itemName) {
  if (confirm('Apakah Anda yakin ingin menghapus transaksi barang masuk "' + trxNo + '" (' + itemName + ' +' + qty + ')?\n\nStok barang di gudang akan otomatis dikurangi kembali.')) {
    document.getElementById('deleteGoodsInId').value = id;
    document.getElementById('deleteGoodsInForm').submit();
  }
}

// Close on outside click or ESC key
document.addEventListener("DOMContentLoaded", function() {
  const modals = [
    { id: 'goodsInModal', content: 'goodsInModalContent' },
    { id: 'editGoodsInModal', content: 'editGoodsInModalContent' }
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
