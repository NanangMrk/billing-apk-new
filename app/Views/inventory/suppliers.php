<?php
// app/Views/inventory/suppliers.php - Suppliers Master Data
?>
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl space-y-6">
  
  <!-- Header with Title & Add Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Daftar Supplier & Vendor Logistik</h4>
      <p class="text-2xs text-slate-400">Kontak vendor pengadaan hardware, kabel fiber optik, dan perangkat jaringan</p>
    </div>

    <button type="button" onclick="openSupplierModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
      <i class="fa-solid fa-plus text-xs"></i>
      <span>Tambah Supplier Baru</span>
    </button>
  </div>

  <!-- Suppliers Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
      <thead>
        <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
          <th class="py-3 px-4 font-bold">Nama Supplier / PT</th>
          <th class="py-3 px-4 font-bold">Kontak Sales / PIC</th>
          <th class="py-3 px-4 font-bold">Alamat Kantor / Gudang</th>
          <th class="py-3 px-4 font-bold text-center">Status</th>
          <th class="py-3 px-4 font-bold text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php if (empty($suppliers)): ?>
        <tr>
          <td colspan="5" class="py-12 text-center text-slate-400">
            <i class="fa-solid fa-truck-field text-3xl mb-2 block text-slate-300"></i>
            Belum ada supplier atau vendor yang terdaftar
          </td>
        </tr>
        <?php else: ?>
          <?php foreach ($suppliers as $s): ?>
          <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3.5 px-4 whitespace-nowrap">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-black shrink-0">
                  <i class="fa-solid fa-truck-field"></i>
                </div>
                <div>
                  <span class="text-xs font-bold text-slate-900 block"><?php echo Helper::e($s['name']); ?></span>
                  <span class="text-3xs text-purple-700 font-semibold"><?php echo Helper::e($s['company'] ?: 'Vendor Hardware'); ?></span>
                  <?php if (!empty($s['email'])): ?>
                    <span class="text-3xs text-slate-400 block">&bull; <?php echo Helper::e($s['email']); ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </td>

            <td class="py-3.5 px-4 whitespace-nowrap text-xs">
              <span class="font-bold text-slate-800 block"><?php echo Helper::e($s['contact_person'] ?: '-'); ?></span>
              <span class="text-2xs text-slate-500 font-mono"><?php echo Helper::e($s['phone'] ?: '-'); ?></span>
            </td>

            <td class="py-3.5 px-4 text-xs text-slate-600 max-w-xs truncate">
              <?php echo Helper::e($s['address'] ?: '-'); ?>
            </td>

            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <?php if (($s['status'] ?? 'active') === 'inactive'): ?>
                <span class="px-2.5 py-0.5 text-3xs font-extrabold rounded-full bg-slate-100 text-slate-700 border border-slate-200 uppercase">Nonaktif</span>
              <?php else: ?>
                <span class="px-2.5 py-0.5 text-3xs font-extrabold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">Aktif</span>
              <?php endif; ?>
            </td>

            <!-- Aksi: Edit & Hapus -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <div class="flex items-center justify-center gap-1.5">
                <button type="button" 
                        onclick='openEditSupplierModal(<?php echo htmlspecialchars(json_encode($s), ENT_QUOTES, "UTF-8"); ?>)' 
                        class="px-2.5 py-1.5 text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" 
                        title="Edit Data Supplier">
                  <i class="fa-solid fa-pen-to-square text-xs"></i>
                  <span>Edit</span>
                </button>

                <button type="button" 
                        onclick="confirmDeleteSupplier(<?php echo $s['id']; ?>, '<?php echo Helper::e(addslashes($s['name'])); ?>')" 
                        class="px-2.5 py-1.5 text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" 
                        title="Hapus Supplier">
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

<!-- Modal Pop-Up: Tambah Supplier -->
<div id="supplierModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="supplierModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-truck-field"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Tambah Supplier Vendor</h4>
          <span class="text-2xs text-slate-400">Pemasok material fiber optik, OLT, dan kabel</span>
        </div>
      </div>
      <button type="button" onclick="closeSupplierModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('suppliers'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_supplier">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Supplier / PT <span class="text-red-500">*</span></label>
        <input type="text" name="name" required placeholder="contoh: PT Optik Solusi Indonesia" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Kategori Perusahaan / Bidang Vendor</label>
        <input type="text" name="company" placeholder="Distributor Fiber Optic / Supplier Router & ONT" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Contact Person (PIC)</label>
          <input type="text" name="contact_person" placeholder="Bapak Surya (Sales)" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">No. Telepon / WA</label>
          <input type="text" name="phone" placeholder="081299881100" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
        </div>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Email</label>
        <input type="email" name="email" placeholder="sales@optiksolusi.co.id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Alamat Kantor / Gudang</label>
        <textarea name="address" rows="2" placeholder="Alamat supplier..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeSupplierModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Supplier
        </button>
      </div>
    </form>

  </div>
</div>

<!-- Modal Pop-Up: Edit Supplier -->
<div id="editSupplierModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="editSupplierModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-blue-600 to-cyan-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-pen-to-square"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Edit Data Supplier</h4>
          <span class="text-2xs text-slate-400">Perbarui kontak sales, nomor telepon, atau alamat vendor</span>
        </div>
      </div>
      <button type="button" onclick="closeEditSupplierModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('suppliers'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="update_supplier">
      <input type="hidden" name="id" id="edit_supplier_id">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Supplier / PT <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="edit_supplier_name" required placeholder="contoh: PT Optik Solusi Indonesia" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Kategori Perusahaan / Bidang Vendor</label>
        <input type="text" name="company" id="edit_supplier_company" placeholder="Distributor Fiber Optic / Supplier Router & ONT" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Contact Person (PIC)</label>
          <input type="text" name="contact_person" id="edit_supplier_contact_person" placeholder="Bapak Surya (Sales)" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">No. Telepon / WA</label>
          <input type="text" name="phone" id="edit_supplier_phone" placeholder="081299881100" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Email</label>
          <input type="email" name="email" id="edit_supplier_email" placeholder="sales@optiksolusi.co.id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Status Supplier</label>
          <select name="status" id="edit_supplier_status" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white font-medium">
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
          </select>
        </div>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Alamat Kantor / Gudang</label>
        <textarea name="address" id="edit_supplier_address" rows="2" placeholder="Alamat supplier..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeEditSupplierModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
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
<form id="deleteSupplierForm" method="POST" action="<?php echo Helper::url('suppliers'); ?>" class="hidden">
  <?php echo Helper::csrfField(); ?>
  <input type="hidden" name="action" value="delete_supplier">
  <input type="hidden" name="id" id="deleteSupplierId">
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

function openSupplierModal() {
  openModal('supplierModal', 'supplierModalContent');
}

function closeSupplierModal() {
  closeModal('supplierModal', 'supplierModalContent');
}

function openEditSupplierModal(sup) {
  if (!sup) return;

  document.getElementById('edit_supplier_id').value = sup.id || '';
  document.getElementById('edit_supplier_name').value = sup.name || '';
  document.getElementById('edit_supplier_company').value = sup.company || '';
  document.getElementById('edit_supplier_contact_person').value = sup.contact_person || '';
  document.getElementById('edit_supplier_phone').value = sup.phone || '';
  document.getElementById('edit_supplier_email').value = sup.email || '';
  document.getElementById('edit_supplier_address').value = sup.address || '';
  document.getElementById('edit_supplier_status').value = sup.status || 'active';

  openModal('editSupplierModal', 'editSupplierModalContent');
}

function closeEditSupplierModal() {
  closeModal('editSupplierModal', 'editSupplierModalContent');
}

function confirmDeleteSupplier(id, name) {
  if (confirm('Apakah Anda yakin ingin menghapus data supplier "' + name + '"?')) {
    document.getElementById('deleteSupplierId').value = id;
    document.getElementById('deleteSupplierForm').submit();
  }
}

// Close on outside click or ESC key
document.addEventListener("DOMContentLoaded", function() {
  const modals = [
    { id: 'supplierModal', content: 'supplierModalContent' },
    { id: 'editSupplierModal', content: 'editSupplierModalContent' }
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
