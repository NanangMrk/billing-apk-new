<?php
// app/Views/tickets/index.php - Ticketing View
?>
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl space-y-6">
  
  <!-- Header with Title & Add Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Daftar Tiket Gangguan & Layanan Pelanggan</h4>
      <p class="text-2xs text-slate-400">Monitoring status perbaikan gangguan jaringan, SLA, dan penugasan teknisi lapangan</p>
    </div>

    <button type="button" onclick="openTicketModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
      <i class="fa-solid fa-plus text-xs"></i>
      <span>Buka Tiket Baru</span>
    </button>
  </div>

  <!-- Tickets Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
      <thead>
        <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
          <th class="py-3 px-4 font-bold">No. Tiket & Masalah</th>
          <th class="py-3 px-4 font-bold">Pelanggan</th>
          <th class="py-3 px-4 font-bold">Teknisi Bertugas</th>
          <th class="py-3 px-4 font-bold text-center">Prioritas</th>
          <th class="py-3 px-4 font-bold text-center">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($tickets as $tkt): ?>
        <tr class="hover:bg-slate-50/80 transition-colors">
          <td class="py-3.5 px-4 whitespace-nowrap">
            <span class="text-xs font-bold text-slate-900 block"><?php echo Helper::e($tkt['title']); ?></span>
            <span class="text-3xs text-purple-700 font-mono font-bold"><?php echo Helper::e($tkt['ticket_no']); ?></span>
            <span class="text-3xs text-slate-400">&bull; <?php echo Helper::formatDate($tkt['reported_at'], 'd/m/Y H:i'); ?></span>
          </td>

          <td class="py-3.5 px-4 whitespace-nowrap text-xs">
            <span class="font-bold text-slate-800 block"><?php echo Helper::e($tkt['customer_name']); ?></span>
            <span class="text-3xs text-slate-400"><?php echo Helper::e($tkt['customer_no']); ?> &bull; <?php echo Helper::e($tkt['phone']); ?></span>
          </td>

          <td class="py-3.5 px-4 whitespace-nowrap text-xs">
            <span class="font-semibold text-slate-700 block"><?php echo Helper::e($tkt['tech_name'] ?: 'Belum Ditugaskan'); ?></span>
          </td>

          <td class="py-3.5 px-4 text-center whitespace-nowrap">
            <span class="px-2.5 py-0.5 text-3xs font-extrabold rounded-full <?php echo ($tkt['priority'] === 'high') ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-blue-50 text-blue-700 border border-blue-200'; ?> uppercase">
              <?php echo Helper::e($tkt['priority']); ?>
            </span>
          </td>

          <td class="py-3.5 px-4 text-center whitespace-nowrap">
            <?php echo Helper::statusBadge($tkt['status']); ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Modal Pop-Up: Buka Tiket -->
<div id="ticketModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="ticketModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-ticket"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Buka Tiket Baru</h4>
          <span class="text-2xs text-slate-400">Input keluhan internet los/lambat atau relokasi</span>
        </div>
      </div>
      <button type="button" onclick="closeTicketModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('tickets'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_ticket">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Pilih Pelanggan <span class="text-red-500">*</span></label>
        <select name="customer_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 font-medium">
          <option value="">-- Pilih Pelanggan --</option>
          <?php foreach ($customers as $c): ?>
            <option value="<?php echo $c['id']; ?>"><?php echo Helper::e($c['customer_no'] . ' - ' . $c['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Judul Keluhan / Gejala <span class="text-red-500">*</span></label>
        <input type="text" name="title" required placeholder="contoh: LOS Merah / Redaman Tinggi" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Kategori Masalah</label>
          <select name="category" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            <option value="connection_down">Koneksi Putus / LOS</option>
            <option value="slow_speed">Koneksi Lambat</option>
            <option value="relocation">Relokasi Router / Kabel</option>
            <option value="device_fault">Perangkat Modem Rusak</option>
          </select>
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Prioritas SLA</label>
          <select name="priority" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            <option value="medium">Medium</option>
            <option value="high">Tinggi (Urgent)</option>
            <option value="low">Rendah</option>
          </select>
        </div>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Tugaskan Teknisi</label>
        <select name="technician_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
          <option value="">-- Belum Ditugaskan --</option>
          <?php foreach ($technicians as $tech): ?>
            <option value="<?php echo $tech['id']; ?>"><?php echo Helper::e($tech['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Uraian Masalah</label>
        <textarea name="description" rows="2" placeholder="Detail kendala dari pelanggan..." class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500"></textarea>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeTicketModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-plus mr-1.5"></i> Buka Tiket
        </button>
      </div>
    </form>

  </div>
</div>

<script>
function openTicketModal() {
  const modal = document.getElementById("ticketModal");
  const content = document.getElementById("ticketModalContent");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeTicketModal() {
  const modal = document.getElementById("ticketModal");
  const content = document.getElementById("ticketModalContent");
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

document.addEventListener("DOMContentLoaded", function() {
  const modal = document.getElementById("ticketModal");
  modal.addEventListener("click", function(e) {
    if (e.target === modal) closeTicketModal();
  });
  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && !modal.classList.contains("hidden")) closeTicketModal();
  });
});
</script>
