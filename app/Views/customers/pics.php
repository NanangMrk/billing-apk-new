<?php
// app/Views/customers/pics.php - Customer PICs / RT-RW Coordinators
?>
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl space-y-6">
  
  <!-- Header with Title & Add Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Data PIC & Koordinator RT-RW</h4>
      <p class="text-2xs text-slate-400">Kontak penghubung wilayah, mitra RT-RW Net, dan koordinator perumahan</p>
    </div>

    <button type="button" onclick="openPicModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
      <i class="fa-solid fa-plus text-xs"></i>
      <span>Tambah PIC Baru</span>
    </button>
  </div>

  <!-- PIC List Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
      <thead>
        <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
          <th class="py-3 px-4 font-bold">Nama PIC & Catatan</th>
          <th class="py-3 px-4 font-bold">Jabatan & Instansi / Wilayah</th>
          <th class="py-3 px-4 font-bold">Kontak WhatsApp</th>
          <th class="py-3 px-4 font-bold text-center">Pelanggan Terhubung</th>
          <th class="py-3 px-4 font-bold text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php if (empty($pics)): ?>
        <tr>
          <td colspan="5" class="py-8 text-center text-slate-400">
            <i class="fa-solid fa-address-book text-2xl mb-2 block text-slate-300"></i>
            Belum ada data PIC atau Koordinator yang terdaftar
          </td>
        </tr>
        <?php else: ?>
          <?php foreach ($pics as $pic): ?>
          <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3.5 px-4 whitespace-nowrap">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-xs font-black shrink-0 shadow-soft-sm">
                  <?php echo strtoupper(substr($pic['name'], 0, 1)); ?>
                </div>
                <div>
                  <span class="text-xs font-bold text-slate-900 block"><?php echo Helper::e($pic['name']); ?></span>
                  <span class="text-2xs text-slate-400 block"><?php echo Helper::e($pic['notes'] ?: '-'); ?></span>
                </div>
              </div>
            </td>

            <td class="py-3.5 px-4 whitespace-nowrap text-xs">
              <span class="font-bold text-slate-800 block"><?php echo Helper::e($pic['position'] ?: '-'); ?></span>
              <span class="text-2xs text-purple-700 font-semibold"><?php echo Helper::e($pic['company'] ?: '-'); ?></span>
            </td>

            <td class="py-3.5 px-4 whitespace-nowrap text-xs font-mono">
              <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $pic['phone']); ?>" target="_blank" class="inline-flex items-center gap-1 px-3 py-1 bg-green-50 text-green-700 font-bold rounded-xl hover:bg-green-100 transition-colors">
                <i class="fa-brands fa-whatsapp text-sm"></i>
                <span><?php echo Helper::e($pic['phone']); ?></span>
              </a>
            </td>

            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800 font-bold text-2xs">
                <?php echo (int)$pic['total_customers']; ?> Pelanggan
              </span>
            </td>

            <!-- Aksi: Edit & Hapus -->
            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <div class="flex items-center justify-center gap-1.5">
                <button type="button" 
                        onclick='openEditPicModal(<?php echo htmlspecialchars(json_encode($pic), ENT_QUOTES, "UTF-8"); ?>)' 
                        class="px-2.5 py-1.5 text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" 
                        title="Edit Data PIC">
                  <i class="fa-solid fa-pen-to-square text-xs"></i>
                  <span>Edit</span>
                </button>

                <button type="button" 
                        onclick="confirmDeletePic(<?php echo $pic['id']; ?>, '<?php echo Helper::e(addslashes($pic['name'])); ?>', <?php echo (int)$pic['total_customers']; ?>)" 
                        class="px-2.5 py-1.5 text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1" 
                        title="Hapus PIC">
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

<!-- Modal Pop-Up: Tambah PIC -->
<div id="picModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="picModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-address-book"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Tambah PIC / Koordinator</h4>
          <span class="text-2xs text-slate-400">Pengelola RT/RW atau penanggung jawab gedung</span>
        </div>
      </div>
      <button type="button" onclick="closePicModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('pics'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_pic">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Lengkap PIC <span class="text-red-500">*</span></label>
        <input type="text" name="name" required placeholder="contoh: Hendra Wijaya" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">No. Handphone / WhatsApp <span class="text-red-500">*</span></label>
        <input type="text" name="phone" required placeholder="081399881122" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Jabatan / Peran</label>
          <input type="text" name="position" placeholder="Ketua RT 04 / Koordinator" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Entitas / RW / Instansi</label>
          <input type="text" name="company" placeholder="RT 04 RW 12 Galaxy" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div class="p-3 bg-purple-50 rounded-2xl border border-purple-100 space-y-3">
        <h6 class="font-bold text-xs text-purple-800">Akses Portal PIC (Opsional)</h6>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Username Login</label>
            <input type="text" name="username" placeholder="contoh: hendra_rw04" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Password</label>
            <input type="password" name="password" placeholder="Minimal 6 karakter" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
        </div>
        <p class="text-3xs text-purple-600">Isi username dan password jika PIC ini diizinkan login untuk melihat tagihan warganya.</p>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Catatan</label>
        <textarea name="notes" rows="2" placeholder="Catatan koordinator..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closePicModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan PIC
        </button>
      </div>
    </form>

  </div>
</div>

<!-- Modal Pop-Up: Edit PIC -->
<div id="editPicModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="editPicModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-blue-600 to-cyan-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-user-pen"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Edit PIC / Koordinator</h4>
          <span class="text-2xs text-slate-400">Perbarui kontak, jabatan, atau instansi koordinator</span>
        </div>
      </div>
      <button type="button" onclick="closeEditPicModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('pics'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="update_pic">
      <input type="hidden" name="id" id="edit_pic_id">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Lengkap PIC <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="edit_pic_name" required placeholder="contoh: Hendra Wijaya" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">No. Handphone / WhatsApp <span class="text-red-500">*</span></label>
        <input type="text" name="phone" id="edit_pic_phone" required placeholder="081399881122" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Jabatan / Peran</label>
          <input type="text" name="position" id="edit_pic_position" placeholder="Ketua RT 04 / Koordinator" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Entitas / RW / Instansi</label>
          <input type="text" name="company" id="edit_pic_company" placeholder="RT 04 RW 12 Galaxy" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div class="p-3 bg-blue-50 rounded-2xl border border-blue-100 space-y-3">
        <h6 class="font-bold text-xs text-blue-800">Akses Portal PIC</h6>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Username Login</label>
            <input type="text" name="username" id="edit_pic_username" placeholder="contoh: hendra_rw04" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
          <div>
            <label class="font-bold text-xs text-slate-700 block mb-1">Password Baru</label>
            <input type="password" name="password" placeholder="(Kosongkan jika tidak diubah)" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          </div>
        </div>
        <p class="text-3xs text-blue-600">Kosongkan username jika PIC tidak diizinkan untuk login.</p>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Catatan</label>
        <textarea name="notes" id="edit_pic_notes" rows="2" placeholder="Catatan koordinator..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeEditPicModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
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
<form id="deletePicForm" method="POST" action="<?php echo Helper::url('pics'); ?>" class="hidden">
  <?php echo Helper::csrfField(); ?>
  <input type="hidden" name="action" value="delete_pic">
  <input type="hidden" name="id" id="deletePicId">
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

function openPicModal() {
  openModal('picModal', 'picModalContent');
}

function closePicModal() {
  closeModal('picModal', 'picModalContent');
}

function openEditPicModal(pic) {
  if (!pic) return;
  document.getElementById('edit_pic_id').value = pic.id || '';
  document.getElementById('edit_pic_name').value = pic.name || '';
  document.getElementById('edit_pic_phone').value = pic.phone || '';
  document.getElementById('edit_pic_position').value = pic.position || '';
  document.getElementById('edit_pic_company').value = pic.company || '';
  document.getElementById('edit_pic_notes').value = pic.notes || '';
  document.getElementById('edit_pic_username').value = pic.username || '';

  openModal('editPicModal', 'editPicModalContent');
}

function closeEditPicModal() {
  closeModal('editPicModal', 'editPicModalContent');
}

function confirmDeletePic(id, name, totalCustomers) {
  if (totalCustomers > 0) {
    if (!confirm('Peringatan: PIC "' + name + '" saat ini masih terhubung dengan ' + totalCustomers + ' pelanggan aktif.\n\nApakah Anda yakin ingin tetap mencoba menghapusnya? (Sistem akan menolak jika masih ada pelanggan terhubung)')) {
      return;
    }
  } else {
    if (!confirm('Apakah Anda yakin ingin menghapus data PIC "' + name + '"? Tindakan ini tidak dapat dibatalkan.')) {
      return;
    }
  }

  document.getElementById('deletePicId').value = id;
  document.getElementById('deletePicForm').submit();
}

// Close on outside click or ESC key
document.addEventListener("DOMContentLoaded", function() {
  const modals = [
    { id: 'picModal', content: 'picModalContent' },
    { id: 'editPicModal', content: 'editPicModalContent' }
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
