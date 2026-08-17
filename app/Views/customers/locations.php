<?php
// app/Views/customers/locations.php - Locations and Coverage Master
?>
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl space-y-6">
  
  <!-- Header with Title & Add Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Area Jangkauan Fiber Optik & Titik ODP</h4>
      <p class="text-2xs text-slate-400">Daftar wilayah coverage, titik distribusi ODP, dan POP hub</p>
    </div>

    <button type="button" onclick="openLocationModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
      <i class="fa-solid fa-plus text-xs"></i>
      <span>Tambah Area Baru</span>
    </button>
  </div>

  <!-- Locations Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
      <thead>
        <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
          <th class="py-3 px-4 font-bold">Area / Kawasan</th>
          <th class="py-3 px-4 font-bold">Kecamatan & Kota</th>
          <th class="py-3 px-4 font-bold">POP Hub & Titik ODP</th>
          <th class="py-3 px-4 font-bold text-center">Pelanggan Aktif</th>
          <th class="py-3 px-4 font-bold text-center">Status Coverage</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($locations as $loc): ?>
        <tr class="hover:bg-slate-50/80 transition-colors">
          <td class="py-3.5 px-4 whitespace-nowrap">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-sm font-black shrink-0">
                <i class="fa-solid fa-location-dot"></i>
              </div>
              <span class="text-xs font-bold text-slate-900"><?php echo Helper::e($loc['area_name']); ?></span>
            </div>
          </td>

          <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-600">
            <?php echo Helper::e($loc['district'] . ', ' . $loc['city']); ?>
          </td>

          <td class="py-3.5 px-4 whitespace-nowrap text-xs font-mono">
            <span class="text-purple-700 font-bold block"><?php echo Helper::e($loc['odp_name'] ?: '-'); ?></span>
            <span class="text-2xs text-slate-400 font-normal">POP: <?php echo Helper::e($loc['pop_name'] ?: '-'); ?></span>
          </td>

          <td class="py-3.5 px-4 text-center whitespace-nowrap">
            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-800 font-bold text-2xs">
              <?php echo $loc['total_customers']; ?> User
            </span>
          </td>

          <td class="py-3.5 px-4 text-center whitespace-nowrap">
            <span class="px-3 py-0.5 text-3xs font-extrabold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">
              Tercover
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Modal Pop-Up: Tambah Area -->
<div id="locationModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="locationModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-map-location-dot"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Tambah Area & ODP</h4>
          <span class="text-2xs text-slate-400">Daftarkan titik distribusi dan jangkauan baru</span>
        </div>
      </div>
      <button type="button" onclick="closeLocationModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('locations'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_location">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Area / Perumahan / RW <span class="text-red-500">*</span></label>
        <input type="text" name="area_name" required placeholder="contoh: Perum Harapan Baru Blok C" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Kecamatan</label>
          <input type="text" name="district" placeholder="Bekasi Utara" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Kota / Kab</label>
          <input type="text" name="city" placeholder="Bekasi" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Nama POP Hub</label>
          <input type="text" name="pop_name" placeholder="POP-Bekasi-03" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Kode Titik ODP</label>
          <input type="text" name="odp_name" placeholder="ODP-HB-005" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeLocationModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Area
        </button>
      </div>
    </form>

  </div>
</div>

<script>
function openLocationModal() {
  const modal = document.getElementById("locationModal");
  const content = document.getElementById("locationModalContent");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeLocationModal() {
  const modal = document.getElementById("locationModal");
  const content = document.getElementById("locationModalContent");
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

document.addEventListener("DOMContentLoaded", function() {
  const modal = document.getElementById("locationModal");
  modal.addEventListener("click", function(e) {
    if (e.target === modal) closeLocationModal();
  });
  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && !modal.classList.contains("hidden")) closeLocationModal();
  });
});
</script>
