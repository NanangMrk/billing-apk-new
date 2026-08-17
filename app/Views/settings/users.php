<?php
// app/Views/settings/users.php - User Management and Roles
?>
<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl space-y-6">
  
  <!-- Header with Title & Add Button -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-4">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Daftar Pengguna Sistem & Hak Akses</h4>
      <p class="text-2xs text-slate-400">Kelola akun staf administrasi, tim keuangan billing, dan teknisi lapangan</p>
    </div>

    <button type="button" onclick="openUserModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
      <i class="fa-solid fa-user-plus text-xs"></i>
      <span>Tambah Pengguna</span>
    </button>
  </div>

  <!-- Users Table -->
  <div class="overflow-x-auto">
    <table class="w-full text-xs text-left">
      <thead>
        <tr class="border-b border-slate-200 text-slate-400 uppercase text-3xs tracking-wider">
          <th class="py-3 px-4 font-bold">Pengguna & Username</th>
          <th class="py-3 px-4 font-bold">Role Hak Akses</th>
          <th class="py-3 px-4 font-bold">Email & Kontak</th>
          <th class="py-3 px-4 font-bold text-center">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($users as $u): ?>
        <tr class="hover:bg-slate-50/80 transition-colors">
          <td class="py-3.5 px-4 whitespace-nowrap">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center font-black text-xs shadow-soft-sm shrink-0">
                <?php echo strtoupper(substr($u['name'], 0, 1)); ?>
              </div>
              <div>
                <span class="text-xs font-bold text-slate-900 block"><?php echo Helper::e($u['name']); ?></span>
                <span class="text-3xs text-purple-700 font-mono font-bold">@<?php echo Helper::e($u['username']); ?></span>
              </div>
            </div>
          </td>

          <td class="py-3.5 px-4 whitespace-nowrap text-xs">
            <span class="px-3 py-1 rounded-xl bg-purple-50 text-purple-800 font-extrabold text-3xs uppercase tracking-wider">
              <?php echo Helper::e($u['role_display']); ?>
            </span>
          </td>

          <td class="py-3.5 px-4 whitespace-nowrap text-xs">
            <span class="text-slate-900 font-semibold block"><?php echo Helper::e($u['email']); ?></span>
            <span class="text-3xs text-slate-400 font-mono"><?php echo Helper::e($u['phone'] ?: '-'); ?></span>
          </td>

          <td class="py-3.5 px-4 text-center whitespace-nowrap">
            <span class="px-2.5 py-0.5 text-3xs font-extrabold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">Aktif</span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- Modal Pop-Up: Tambah User -->
<div id="userModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="userModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-user-shield"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight">Tambah Pengguna Baru</h4>
          <span class="text-2xs text-slate-400">Buat akun untuk staf, billing kasir, atau teknisi</span>
        </div>
      </div>
      <button type="button" onclick="closeUserModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('settings_users'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" value="save_user">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
        <input type="text" name="name" required placeholder="contoh: Rahmat Hidayat" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Username <span class="text-red-500">*</span></label>
          <input type="text" name="username" required placeholder="rahmat" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Role Akses</label>
          <select name="role_id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
            <?php foreach ($roles as $r): ?>
              <option value="<?php echo $r['id']; ?>"><?php echo Helper::e($r['display_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Email <span class="text-red-500">*</span></label>
        <input type="email" name="email" required placeholder="rahmat@nusantaranet.id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">No. Handphone</label>
          <input type="text" name="phone" placeholder="081299887711" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Password</label>
          <input type="password" name="password" value="admin123" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeUserModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-user-plus mr-1.5"></i> Daftarkan User
        </button>
      </div>
    </form>

  </div>
</div>

<script>
function openUserModal() {
  const modal = document.getElementById("userModal");
  const content = document.getElementById("userModalContent");
  modal.classList.remove("hidden");
  modal.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("scale-95", "opacity-0");
    content.classList.add("scale-100", "opacity-100");
  }, 10);
}

function closeUserModal() {
  const modal = document.getElementById("userModal");
  const content = document.getElementById("userModalContent");
  content.classList.remove("scale-100", "opacity-100");
  content.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
    modal.classList.remove("flex");
  }, 200);
}

document.addEventListener("DOMContentLoaded", function() {
  const modal = document.getElementById("userModal");
  modal.addEventListener("click", function(e) {
    if (e.target === modal) closeUserModal();
  });
  document.addEventListener("keydown", function(e) {
    if (e.key === "Escape" && !modal.classList.contains("hidden")) closeUserModal();
  });
});
</script>
