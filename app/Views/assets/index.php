<?php
// app/Views/assets/index.php - Company Fixed Assets & CPE Tracking
?>
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl space-y-6">
  
  <!-- Header with Title & Action Buttons -->
  <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Daftar Aset & Peralatan Kerja</h4>
      <p class="text-2xs text-slate-400">Pengawasan serial number, kondisi perangkat, dan penanggung jawab alat kerja ISP</p>
    </div>

    <!-- Action Toolbar (Import, Export CSV, Export PDF, Registrasi) -->
    <div class="flex flex-wrap items-center gap-2">
      <!-- Import CSV Button -->
      <button type="button" onclick="openImportAssetModal()" class="px-3.5 py-2.5 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs rounded-2xl transition-colors flex items-center gap-1.5 shadow-soft-xs">
        <i class="fa-solid fa-file-import text-xs"></i>
        <span>Import CSV</span>
      </button>

      <!-- Export CSV Link -->
      <a href="<?php echo Helper::url('assets_export_csv'); ?>" class="px-3.5 py-2.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-bold text-xs rounded-2xl transition-colors flex items-center gap-1.5 shadow-soft-xs">
        <i class="fa-solid fa-file-excel text-xs"></i>
        <span>Export CSV</span>
      </a>

      <!-- Export PDF Link -->
      <a href="<?php echo Helper::url('assets_export_pdf'); ?>" target="_blank" class="px-3.5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-2xl transition-colors flex items-center gap-1.5 shadow-soft-xs">
        <i class="fa-solid fa-file-pdf text-xs"></i>
        <span>Cetak / PDF</span>
      </a>

      <!-- Add New Asset Button -->
      <button type="button" onclick="openAssetModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Registrasi Aset Baru</span>
      </button>
    </div>
  </div>

  <!-- Assets Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
      <thead>
        <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
          <th class="py-3 px-4 font-bold">Nama Aset & SN</th>
          <th class="py-3 px-4 font-bold">Lokasi & PIC Penanggung Jawab</th>
          <th class="py-3 px-4 font-bold text-right">Nilai Buku / Estimasi</th>
          <th class="py-3 px-4 font-bold text-center">Status Aset</th>
          <th class="py-3 px-4 font-bold text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php if (empty($assets)): ?>
        <tr>
          <td colspan="5" class="py-12 text-center text-slate-400">
            <i class="fa-solid fa-laptop-code text-3xl mb-2 block text-slate-300"></i>
            Belum ada aset atau peralatan yang terdaftar
          </td>
        </tr>
        <?php else: ?>
          <?php foreach ($assets as $ast): ?>
          <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3.5 px-4 whitespace-nowrap">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-black shrink-0">
                  <i class="fa-solid fa-laptop-code"></i>
                </div>
                <div>
                  <span class="text-xs font-bold text-slate-900 block"><?php echo Helper::e($ast['name']); ?></span>
                  <span class="text-3xs text-purple-700 font-mono font-bold"><?php echo Helper::e($ast['asset_no']); ?></span>
                  <?php if (!empty($ast['serial_number'])): ?>
                    <span class="text-3xs text-slate-400 font-mono">&bull; SN: <?php echo Helper::e($ast['serial_number']); ?></span>
                  <?php endif; ?>
                  <?php if (!empty($ast['mac_address'])): ?>
                    <span class="text-3xs text-slate-400 font-mono">&bull; MAC: <?php echo Helper::e($ast['mac_address']); ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </td>

            <td class="py-3.5 px-4 whitespace-nowrap text-xs">
              <span class="text-slate-900 font-bold block"><?php echo Helper::e($ast['location'] ?: 'Kantor / Gudang'); ?></span>
              <span class="text-3xs text-slate-400">PIC: <?php echo Helper::e($ast['pic_name'] ?: '-'); ?></span>
            </td>

            <td class="py-3.5 px-4 text-right whitespace-nowrap text-xs font-bold text-slate-900 font-mono">
              <?php echo Helper::formatRupiah($ast['current_value'] ?: $ast['purchase_price']); ?>
            </td>

            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <?php echo Helper::statusBadge($ast['status']); ?>
            </td>

            <!-- Aksi: Edit & Hapus -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <div class="flex items-center justify-center gap-1.5">
                <button type="button" 
                        onclick='openEditAssetModal(<?php echo htmlspecialchars(json_encode($ast), ENT_QUOTES, "UTF-8"); ?>)' 
                        class="px-2.5 py-1.5 text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" 
                        title="Edit Data Aset">
                  <i class="fa-solid fa-pen-to-square text-xs"></i>
                  <span>Edit</span>
                </button>

                <button type="button" 
                        onclick="confirmDeleteAsset(<?php echo $ast['id']; ?>, '<?php echo Helper::e(addslashes($ast['name'])); ?>', '<?php echo Helper::e(addslashes($ast['asset_no'])); ?>')" 
                        class="px-2.5 py-1.5 text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" 
                        title="Hapus Aset">
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

<!-- Modal Pop-Up: Registrasi Aset Baru -->
<div id="assetModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="assetModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-laptop-code"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Registrasi Aset Perusahaan</h4>
          <span class="text-2xs text-slate-400">Catat inventaris alat kerja (Splicer, OTDR) atau modem CPE</span>
        </div>
      </div>
      <button type="button" onclick="closeAssetModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('assets'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_asset">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Aset / Alat <span class="text-red-500">*</span></label>
        <input type="text" name="name" required placeholder="contoh: Splicer Fujikura 88S / Mikrotik CCR1036" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Serial Number (SN)</label>
          <input type="text" name="serial_number" placeholder="SN-99881100" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">MAC Address</label>
          <input type="text" name="mac_address" placeholder="48:8F:5A:11:22:33" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Harga Beli (Rp)</label>
          <input type="number" name="purchase_price" placeholder="45000000" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Status Aset</label>
          <select name="status" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
            <option value="available">Tersedia di Gudang</option>
            <option value="in_use">Sedang Digunakan</option>
            <option value="assigned_customer">Dipinjamkan Pelanggan</option>
            <option value="maintenance">Perbaikan / Servis</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Lokasi Aset</label>
          <input type="text" name="location" placeholder="Gudang HQ / POP-01" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">PIC Penanggung Jawab</label>
          <input type="text" name="pic_name" placeholder="Ahmad Fauzi (Teknisi)" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeAssetModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Aset
        </button>
      </div>
    </form>

  </div>
</div>

<!-- Modal Pop-Up: Edit Data Aset -->
<div id="editAssetModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="editAssetModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-blue-600 to-cyan-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-pen-to-square"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Edit Data Aset</h4>
          <span class="text-2xs text-slate-400">Perbarui kondisi, lokasi, PIC, atau status alat</span>
        </div>
      </div>
      <button type="button" onclick="closeEditAssetModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('assets'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="update_asset">
      <input type="hidden" name="id" id="edit_asset_id">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Aset / Alat <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="edit_asset_name" required placeholder="contoh: Splicer Fujikura 88S" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Serial Number (SN)</label>
          <input type="text" name="serial_number" id="edit_asset_serial_number" placeholder="SN-99881100" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">MAC Address</label>
          <input type="text" name="mac_address" id="edit_asset_mac_address" placeholder="48:8F:5A:11:22:33" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Nilai Buku / Estimasi (Rp)</label>
          <input type="number" name="current_value" id="edit_asset_current_value" placeholder="45000000" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-mono">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Status Aset</label>
          <select name="status" id="edit_asset_status" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
            <option value="available">Tersedia di Gudang</option>
            <option value="in_use">Sedang Digunakan</option>
            <option value="assigned_customer">Dipinjamkan Pelanggan</option>
            <option value="maintenance">Perbaikan / Servis</option>
            <option value="damaged">Rusak / Rusak Fisik</option>
            <option value="lost">Hilang</option>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Lokasi Aset</label>
          <input type="text" name="location" id="edit_asset_location" placeholder="Gudang HQ / POP-01" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">PIC Penanggung Jawab</label>
          <input type="text" name="pic_name" id="edit_asset_pic_name" placeholder="Ahmad Fauzi (Teknisi)" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeEditAssetModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-blue-600 to-cyan-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Perubahan
        </button>
      </div>
    </form>

  </div>
</div>

<!-- Modal Pop-Up: Import Data Aset CSV -->
<div id="importAssetModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-md bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="importAssetModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-file-import"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Import Data Aset</h4>
          <span class="text-2xs text-slate-400">Unggah file spreadsheet CSV inventaris alat</span>
        </div>
      </div>
      <button type="button" onclick="closeImportAssetModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('assets_import_csv'); ?>" enctype="multipart/form-data" class="space-y-4">
      <?php echo Helper::csrfField(); ?>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Pilih File CSV <span class="text-red-500">*</span></label>
        <input type="file" name="csv_file" accept=".csv" required class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 cursor-pointer">
      </div>

      <!-- Download Template Link -->
      <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-2xl flex items-center justify-between">
        <div>
          <span class="text-xs font-bold text-slate-800 block">Belum punya template CSV?</span>
          <span class="text-3xs text-slate-400">Unduh contoh susunan kolom yang valid</span>
        </div>
        <a href="<?php echo Helper::url('assets_download_template'); ?>" class="px-3 py-1.5 bg-white border border-slate-200 hover:border-purple-300 text-purple-700 font-bold text-2xs rounded-xl shadow-soft-xs transition-all flex items-center gap-1">
          <i class="fa-solid fa-download text-3xs"></i>
          <span>Template CSV</span>
        </a>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeImportAssetModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-cloud-arrow-up mr-1.5"></i> Proses Import
        </button>
      </div>
    </form>

  </div>
</div>

<!-- Hidden Delete Form -->
<form id="deleteAssetForm" method="POST" action="<?php echo Helper::url('assets'); ?>" class="hidden">
  <?php echo Helper::csrfField(); ?>
  <input type="hidden" name="action" value="delete_asset">
  <input type="hidden" name="id" id="deleteAssetId">
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

function openAssetModal() {
  openModal('assetModal', 'assetModalContent');
}

function closeAssetModal() {
  closeModal('assetModal', 'assetModalContent');
}

function openEditAssetModal(ast) {
  if (!ast) return;

  document.getElementById('edit_asset_id').value = ast.id || '';
  document.getElementById('edit_asset_name').value = ast.name || '';
  document.getElementById('edit_asset_serial_number').value = ast.serial_number || '';
  document.getElementById('edit_asset_mac_address').value = ast.mac_address || '';
  document.getElementById('edit_asset_current_value').value = ast.current_value || ast.purchase_price || 0;
  document.getElementById('edit_asset_status').value = ast.status || 'available';
  document.getElementById('edit_asset_location').value = ast.location || '';
  document.getElementById('edit_asset_pic_name').value = ast.pic_name || '';

  openModal('editAssetModal', 'editAssetModalContent');
}

function closeEditAssetModal() {
  closeModal('editAssetModal', 'editAssetModalContent');
}

function openImportAssetModal() {
  openModal('importAssetModal', 'importAssetModalContent');
}

function closeImportAssetModal() {
  closeModal('importAssetModal', 'importAssetModalContent');
}

function confirmDeleteAsset(id, name, assetNo) {
  if (confirm('Apakah Anda yakin ingin menghapus data aset "' + name + '" (' + assetNo + ')?')) {
    document.getElementById('deleteAssetId').value = id;
    document.getElementById('deleteAssetForm').submit();
  }
}

// Close on outside click or ESC key
document.addEventListener("DOMContentLoaded", function() {
  const modals = [
    { id: 'assetModal', content: 'assetModalContent' },
    { id: 'editAssetModal', content: 'editAssetModalContent' },
    { id: 'importAssetModal', content: 'importAssetModalContent' }
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
