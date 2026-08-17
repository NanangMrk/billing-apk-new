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
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
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
              <?php echo $pic['total_customers']; ?> Pelanggan
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
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

<script>
function openPicModal() {
  const modal = document.getElementById("picModal");
  const content = document.getElementById("picModalContent");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closePicModal() {
  const modal = document.getElementById("picModal");
  const content = document.getElementById("picModalContent");
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

document.addEventListener("DOMContentLoaded", function() {
  const modal = document.getElementById("picModal");
  modal.addEventListener("click", function(e) {
    if (e.target === modal) closePicModal();
  });
  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && !modal.classList.contains("hidden")) closePicModal();
  });
});
</script>
