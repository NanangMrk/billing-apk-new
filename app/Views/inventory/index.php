<?php
// app/Views/inventory/index.php - Inventory Catalog
?>
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl space-y-6">
  
  <!-- Header with Title & Action Buttons -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Katalog Stok Persediaan Gudang</h4>
      <p class="text-2xs text-slate-400">Monitoring persediaan kabel fiber optik, perangkat modem ONT, dan aksesoris</p>
    </div>

    <div class="flex flex-wrap items-center gap-2">
      <a href="<?php echo Helper::url('goods_in'); ?>" class="px-3.5 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-2xl transition-colors flex items-center gap-1.5">
        <i class="fa-solid fa-dolly"></i>
        <span>Barang Masuk</span>
      </a>
      <a href="<?php echo Helper::url('goods_out'); ?>" class="px-3.5 py-2.5 bg-orange-50 hover:bg-orange-100 text-orange-700 font-bold text-xs rounded-2xl transition-colors flex items-center gap-1.5">
        <i class="fa-solid fa-truck-ramp-box"></i>
        <span>Barang Keluar</span>
      </a>
      <button type="button" onclick="openItemModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Tambah Barang</span>
      </button>
    </div>
  </div>

  <!-- Items Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
      <thead>
        <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
          <th class="py-3 px-4 font-bold">Nama Barang & SKU</th>
          <th class="py-3 px-4 font-bold">Kategori</th>
          <th class="py-3 px-4 font-bold text-right">Harga Beli</th>
          <th class="py-3 px-4 font-bold text-center">Sisa Stok</th>
          <th class="py-3 px-4 font-bold text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php if (empty($items)): ?>
        <tr>
          <td colspan="5" class="py-12 text-center text-slate-400">
            <i class="fa-solid fa-boxes-stacked text-3xl mb-2 block text-slate-300"></i>
            Belum ada data barang di inventaris gudang
          </td>
        </tr>
        <?php else: ?>
          <?php foreach ($items as $item): 
            $isLow = ($item['current_stock'] <= $item['min_stock']);
          ?>
          <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3.5 px-4 whitespace-nowrap">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-black shrink-0">
                  <i class="fa-solid fa-box"></i>
                </div>
                <div>
                  <span class="text-xs font-bold text-slate-900 block"><?php echo Helper::e($item['name']); ?></span>
                  <span class="text-3xs text-purple-700 font-mono font-bold"><?php echo Helper::e($item['sku']); ?> &bull; <?php echo Helper::e($item['brand'] ?: 'Generic'); ?></span>
                </div>
              </div>
            </td>

            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-600">
              <span class="px-2.5 py-0.5 rounded-xl bg-purple-50 text-purple-700 font-bold text-3xs inline-block">
                <?php echo Helper::e($item['category_name'] ?: 'Umum'); ?>
              </span>
            </td>

            <td class="py-3.5 px-4 text-right whitespace-nowrap text-xs font-bold text-slate-800 font-mono">
              <?php echo Helper::formatRupiah($item['purchase_price']); ?>
            </td>

            <td class="py-3.5 px-4 text-center whitespace-nowrap text-xs font-mono">
              <span class="font-bold <?php echo $isLow ? 'text-red-600' : 'text-slate-900'; ?>">
                <?php echo $item['current_stock']; ?> <?php echo Helper::e($item['unit']); ?>
              </span>
              <span class="text-3xs text-slate-400 block font-normal">(Min: <?php echo $item['min_stock']; ?>)</span>
            </td>

            <!-- Aksi: Edit & Hapus -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <div class="flex items-center justify-center gap-1.5">
                <button type="button" 
                        onclick='openEditItemModal(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, "UTF-8"); ?>)' 
                        class="px-2.5 py-1.5 text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" 
                        title="Edit Data Barang">
                  <i class="fa-solid fa-pen-to-square text-xs"></i>
                  <span>Edit</span>
                </button>

                <button type="button" 
                        onclick="confirmDeleteItem(<?php echo $item['id']; ?>, '<?php echo Helper::e(addslashes($item['name'])); ?>', <?php echo (int)$item['current_stock']; ?>)" 
                        class="px-2.5 py-1.5 text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" 
                        title="Hapus Barang">
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

<!-- Modal Pop-Up: Tambah Barang -->
<div id="itemModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="itemModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-boxes-stacked"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Tambah Master Barang</h4>
          <span class="text-2xs text-slate-400">Input katalog material fiber optik atau perangkat</span>
        </div>
      </div>
      <button type="button" onclick="closeItemModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('inventory'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_item">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Barang <span class="text-red-500">*</span></label>
        <input type="text" name="name" required placeholder="contoh: ONT Huawei HG8245H" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Kategori</label>
          <select name="category_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['id']; ?>"><?php echo Helper::e($cat['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Brand / Merek</label>
          <input type="text" name="brand" placeholder="Huawei / ZTE" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Satuan</label>
          <input type="text" name="unit" value="unit" placeholder="unit / pcs / roll" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Harga Beli Estimasi (Rp)</label>
          <input type="number" name="purchase_price" placeholder="175000" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-bold">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Min. Stok Warning</label>
          <input type="number" name="min_stock" value="5" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Stok Awal</label>
          <input type="number" name="current_stock" value="0" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeItemModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Barang
        </button>
      </div>
    </form>

  </div>
</div>

<!-- Modal Pop-Up: Edit Barang -->
<div id="editItemModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="editItemModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-blue-600 to-cyan-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-box-archive"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Edit Data Barang</h4>
          <span class="text-2xs text-slate-400">Perbarui informasi katalog, harga beli, atau stok gudang</span>
        </div>
      </div>
      <button type="button" onclick="closeEditItemModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('inventory'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="update_item">
      <input type="hidden" name="id" id="edit_item_id">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Barang <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="edit_item_name" required placeholder="contoh: ONT Huawei HG8245H" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Kategori</label>
          <select name="category_id" id="edit_item_category_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
            <?php foreach ($categories as $cat): ?>
              <option value="<?php echo $cat['id']; ?>"><?php echo Helper::e($cat['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Brand / Merek</label>
          <input type="text" name="brand" id="edit_item_brand" placeholder="Huawei / ZTE" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Satuan</label>
          <input type="text" name="unit" id="edit_item_unit" placeholder="unit / pcs / roll" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Harga Beli Estimasi (Rp)</label>
          <input type="number" name="purchase_price" id="edit_item_purchase_price" placeholder="175000" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-bold">
        </div>
      </div>

      <div class="grid grid-cols-3 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Min. Stok Warning</label>
          <input type="number" name="min_stock" id="edit_item_min_stock" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Stok Saat Ini</label>
          <input type="number" name="current_stock" id="edit_item_current_stock" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 font-bold text-slate-900 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Status Barang</label>
          <select name="status" id="edit_item_status" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium">
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
          </select>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeEditItemModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
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
<form id="deleteItemForm" method="POST" action="<?php echo Helper::url('inventory'); ?>" class="hidden">
  <?php echo Helper::csrfField(); ?>
  <input type="hidden" name="action" value="delete_item">
  <input type="hidden" name="id" id="deleteItemId">
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

function openItemModal() {
  openModal('itemModal', 'itemModalContent');
}

function closeItemModal() {
  closeModal('itemModal', 'itemModalContent');
}

function openEditItemModal(item) {
  if (!item) return;

  document.getElementById('edit_item_id').value = item.id || '';
  document.getElementById('edit_item_name').value = item.name || '';
  document.getElementById('edit_item_category_id').value = item.category_id || '1';
  document.getElementById('edit_item_brand').value = item.brand || '';
  document.getElementById('edit_item_unit').value = item.unit || 'unit';
  document.getElementById('edit_item_purchase_price').value = item.purchase_price || 0;
  document.getElementById('edit_item_min_stock').value = item.min_stock !== undefined ? item.min_stock : 5;
  document.getElementById('edit_item_current_stock').value = item.current_stock !== undefined ? item.current_stock : 0;
  document.getElementById('edit_item_status').value = item.status || 'active';

  openModal('editItemModal', 'editItemModalContent');
}

function closeEditItemModal() {
  closeModal('editItemModal', 'editItemModalContent');
}

function confirmDeleteItem(id, name, stock) {
  if (stock > 0) {
    if (!confirm('Peringatan: Barang "' + name + '" saat ini masih memiliki sisa stok sebanyak ' + stock + ' unit.\n\nApakah Anda yakin ingin menghapus master barang ini?')) {
      return;
    }
  } else {
    if (!confirm('Apakah Anda yakin ingin menghapus barang "' + name + '" dari katalog inventaris?')) {
      return;
    }
  }

  document.getElementById('deleteItemId').value = id;
  document.getElementById('deleteItemForm').submit();
}

// Close on outside click or ESC key
document.addEventListener("DOMContentLoaded", function() {
  const modals = [
    { id: 'itemModal', content: 'itemModalContent' },
    { id: 'editItemModal', content: 'editItemModalContent' }
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
