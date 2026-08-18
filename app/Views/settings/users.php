<?php
// app/Views/settings/users.php - User Management and Granular Role Permissions
$activeTab = $_GET['tab'] ?? 'users';
if (!in_array($activeTab, ['users', 'roles'])) {
    $activeTab = 'users';
}
?>

<div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-soft-xl space-y-6">
  
  <!-- Header with Title & Navigation Tabs -->
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-5">
    <div>
      <h4 class="font-black text-slate-900 text-lg tracking-tight">Pengguna Sistem & Manajemen Role Hak Akses</h4>
      <p class="text-2xs text-slate-400">Kelola akun pengguna dan atur batasan hak akses fitur secara rinci</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center p-1.5 bg-slate-100/80 rounded-2xl border border-slate-200/60 self-start lg:self-auto">
      <a href="?page=settings_users&tab=users" class="px-4 py-2 text-xs font-bold rounded-xl transition-all flex items-center gap-2 <?php echo ($activeTab === 'users') ? 'bg-white text-purple-700 shadow-soft-xs font-extrabold' : 'text-slate-500 hover:text-slate-900'; ?>">
        <i class="fa-solid fa-users text-xs"></i>
        <span>Daftar Pengguna</span>
        <span class="px-2 py-0.5 rounded-full text-3xs font-extrabold <?php echo ($activeTab === 'users') ? 'bg-purple-100 text-purple-700' : 'bg-slate-200 text-slate-600'; ?>"><?php echo count($users); ?></span>
      </a>

      <a href="?page=settings_users&tab=roles" class="px-4 py-2 text-xs font-bold rounded-xl transition-all flex items-center gap-2 <?php echo ($activeTab === 'roles') ? 'bg-white text-purple-700 shadow-soft-xs font-extrabold' : 'text-slate-500 hover:text-slate-900'; ?>">
        <i class="fa-solid fa-user-shield text-xs"></i>
        <span>Role & Hak Akses</span>
        <span class="px-2 py-0.5 rounded-full text-3xs font-extrabold <?php echo ($activeTab === 'roles') ? 'bg-purple-100 text-purple-700' : 'bg-slate-200 text-slate-600'; ?>"><?php echo count($roles); ?></span>
      </a>
    </div>
  </div>

  <?php if ($activeTab === 'users'): ?>
  <!-- ================================================================= -->
  <!-- TAB 1: DAFTAR PENGGUNA -->
  <!-- ================================================================= -->
  <div class="space-y-4">
    
    <div class="flex items-center justify-between">
      <div>
        <h5 class="font-bold text-slate-800 text-sm">Akun Pengguna Terdaftar</h5>
        <span class="text-3xs text-slate-400">Total <?php echo count($users); ?> pengguna aktif di sistem</span>
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
            <th class="py-3 px-4 font-bold text-center">Aksi</th>
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
              <span class="px-3 py-1 rounded-xl bg-purple-50 text-purple-800 font-extrabold text-3xs uppercase tracking-wider border border-purple-200/60 inline-flex items-center gap-1.5">
                <i class="fa-solid fa-shield-halved text-2xs"></i>
                <span><?php echo Helper::e($u['role_display']); ?></span>
              </span>
            </td>

            <td class="py-3.5 px-4 whitespace-nowrap text-xs">
              <span class="text-slate-900 font-semibold block"><?php echo Helper::e($u['email']); ?></span>
              <span class="text-3xs text-slate-400 font-mono"><?php echo Helper::e($u['phone'] ?: '-'); ?></span>
            </td>

            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <?php if (($u['status'] ?? 'active') === 'active'): ?>
                <span class="px-2.5 py-0.5 text-3xs font-extrabold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">Aktif</span>
              <?php else: ?>
                <span class="px-2.5 py-0.5 text-3xs font-extrabold rounded-full bg-rose-50 text-rose-700 border border-rose-200 uppercase">Nonaktif</span>
              <?php endif; ?>
            </td>

            <td class="py-3.5 px-4 text-center whitespace-nowrap">
              <div class="flex items-center justify-center gap-1.5">
                <button type="button" 
                        onclick='openEditUserModal(<?php echo htmlspecialchars(json_encode($u), ENT_QUOTES, "UTF-8"); ?>)' 
                        class="px-2.5 py-1.5 text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1">
                  <i class="fa-solid fa-pen-to-square text-xs"></i>
                  <span>Edit</span>
                </button>

                <?php if ($u['id'] != 1 && $u['username'] !== 'admin'): ?>
                <button type="button" 
                        onclick="confirmDeleteUser(<?php echo $u['id']; ?>, '<?php echo Helper::e(addslashes($u['name'])); ?>')" 
                        class="px-2.5 py-1.5 text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl font-bold text-2xs transition-colors inline-flex items-center gap-1">
                  <i class="fa-solid fa-trash-can text-xs"></i>
                  <span>Hapus</span>
                </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>

  <?php else: ?>
  <!-- ================================================================= -->
  <!-- TAB 2: ROLE & HAK AKSES -->
  <!-- ================================================================= -->
  <div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-purple-50/60 p-4 rounded-2xl border border-purple-100">
      <div>
        <h5 class="font-black text-purple-950 text-sm flex items-center gap-2">
          <i class="fa-solid fa-user-shield text-purple-600"></i>
          <span>Kelola Role & Permision Hak Akses</span>
        </h5>
        <p class="text-3xs text-purple-700">Buat role baru atau sesuaikan centang permission untuk membatasi fitur pengguna</p>
      </div>

      <button type="button" onclick="openRoleModal()" class="px-4 py-2.5 text-xs font-bold text-white bg-gradient-to-tl from-purple-700 to-pink-500 rounded-2xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2 shrink-0">
        <i class="fa-solid fa-plus-circle text-xs"></i>
        <span>Tambah Role Baru</span>
      </button>
    </div>

    <!-- Role Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($roles as $r): ?>
      <?php 
        $assignedPerms = $rolePermissionsMap[$r['id']] ?? [];
        $isSuper = ($r['name'] === 'super_admin');
      ?>
      <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-soft-sm hover:shadow-soft-md transition-all flex flex-col justify-between space-y-4">
        
        <div class="space-y-2">
          <div class="flex items-start justify-between gap-2">
            <div class="flex items-center gap-2.5">
              <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl <?php echo $isSuper ? 'from-purple-700 to-indigo-600' : 'from-slate-800 to-slate-600'; ?> text-white flex items-center justify-center text-sm font-black shadow-soft-xs shrink-0">
                <i class="fa-solid <?php echo $isSuper ? 'fa-crown text-amber-300' : 'fa-user-gear'; ?>"></i>
              </div>
              <div>
                <h5 class="font-black text-slate-900 text-sm leading-tight"><?php echo Helper::e($r['display_name']); ?></h5>
                <span class="text-3xs text-slate-400 font-mono">slug: <?php echo Helper::e($r['name']); ?></span>
              </div>
            </div>

            <?php if ($isSuper): ?>
              <span class="px-2.5 py-0.5 text-3xs font-extrabold rounded-full bg-amber-50 text-amber-700 border border-amber-200 uppercase shrink-0">Bawaan</span>
            <?php endif; ?>
          </div>

          <p class="text-2xs text-slate-600 line-clamp-2 min-h-[2.25rem]">
            <?php echo Helper::e($r['description'] ?: 'Tidak ada deskripsi role.'); ?>
          </p>
        </div>

        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
          <div class="flex items-center gap-2 text-3xs text-slate-500 font-bold">
            <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 flex items-center gap-1">
              <i class="fa-solid fa-users text-slate-400"></i>
              <?php echo $r['user_count']; ?> User
            </span>
            <span class="px-2.5 py-1 rounded-xl bg-purple-50 text-purple-700 flex items-center gap-1">
              <i class="fa-solid fa-key text-purple-400"></i>
              <?php echo $isSuper ? 'Semua Hak Akses' : count($assignedPerms) . ' Permission'; ?>
            </span>
          </div>

          <div class="flex items-center gap-1.5">
            <button type="button" 
                    onclick='openEditRoleModal(<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, "UTF-8"); ?>, <?php echo json_encode($assignedPerms); ?>)' 
                    class="p-2 text-purple-600 hover:text-purple-700 bg-purple-50 hover:bg-purple-100 rounded-xl font-bold text-2xs transition-colors"
                    title="Edit Role & Permission">
              <i class="fa-solid fa-pen-to-square"></i>
            </button>

            <?php if (!$isSuper): ?>
            <button type="button" 
                    onclick="confirmDeleteRole(<?php echo $r['id']; ?>, '<?php echo Helper::e(addslashes($r['display_name'])); ?>')" 
                    class="p-2 text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-xl font-bold text-2xs transition-colors"
                    title="Hapus Role">
              <i class="fa-solid fa-trash-can"></i>
            </button>
            <?php endif; ?>
          </div>
        </div>

      </div>
      <?php endforeach; ?>
    </div>

  </div>
  <?php endif; ?>

</div>

<!-- ================================================================= -->
<!-- MODAL 1: TAMBAH / EDIT USER -->
<!-- ================================================================= -->
<div id="userModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0" id="userModalContent">
    
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-5">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-user-shield"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight" id="userModalTitle">Tambah Pengguna Baru</h4>
          <span class="text-2xs text-slate-400">Buat atau perbarui akun pengguna sistem</span>
        </div>
      </div>
      <button type="button" onclick="closeUserModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <form method="POST" action="<?php echo Helper::url('settings_users'); ?>" class="space-y-4">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" id="user_action" value="save_user">
      <input type="hidden" name="id" id="user_id">

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
        <input type="text" name="name" id="user_name" required placeholder="contoh: Rahmat Hidayat" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Username <span class="text-red-500">*</span></label>
          <input type="text" name="username" id="user_username" required placeholder="rahmat" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Role Akses <span class="text-red-500">*</span></label>
          <select name="role_id" id="user_role_id" required class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
            <?php foreach ($roles as $r): ?>
              <option value="<?php echo $r['id']; ?>"><?php echo Helper::e($r['display_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Email <span class="text-red-500">*</span></label>
        <input type="email" name="email" id="user_email" required placeholder="rahmat@nusantaranet.id" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">No. Handphone</label>
          <input type="text" name="phone" id="user_phone" placeholder="081299887711" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
        </div>
        <div id="user_status_container" class="hidden">
          <label class="font-bold text-xs text-slate-700 block mb-1">Status Akun</label>
          <select name="status" id="user_status" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
          </select>
        </div>
      </div>

      <div>
        <label class="font-bold text-xs text-slate-700 block mb-1">Password <span id="user_password_required" class="text-red-500">*</span></label>
        <input type="password" name="password" id="user_password" placeholder="Kosongkan jika tidak mengubah password saat edit" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500">
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" onclick="closeUserModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan User
        </button>
      </div>
    </form>

  </div>
</div>

<!-- Hidden Delete User Form -->
<form id="deleteUserForm" method="POST" action="<?php echo Helper::url('settings_users'); ?>" class="hidden">
  <?php echo Helper::csrfField(); ?>
  <input type="hidden" name="action" value="delete_user">
  <input type="hidden" name="id" id="deleteUserId">
</form>


<!-- ================================================================= -->
<!-- MODAL 2: TAMBAH / EDIT ROLE & HAK AKSES (DETAILED PERMISSIONS) -->
<!-- ================================================================= -->
<div id="roleModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm transition-opacity duration-300">
  <div class="relative w-full max-w-4xl max-h-[90vh] bg-white rounded-3xl shadow-soft-2xl border border-slate-100 p-6 md:p-8 transform transition-all duration-300 scale-95 opacity-0 flex flex-col" id="roleModalContent">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-4 shrink-0">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tl from-purple-700 to-indigo-600 text-white flex items-center justify-center text-base shadow-soft-md shrink-0">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div>
          <h4 class="font-black text-slate-900 text-base leading-tight" id="roleModalTitle">Atur Role & Hak Akses Detail</h4>
          <span class="text-2xs text-slate-400">Centang permission yang diizinkan untuk role ini</span>
        </div>
      </div>
      <button type="button" onclick="closeRoleModal()" class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-base"></i>
      </button>
    </div>

    <!-- Modal Form Scrollable Body -->
    <form method="POST" action="<?php echo Helper::url('settings_users', ['tab' => 'roles']); ?>" class="flex-1 overflow-y-auto space-y-5 pr-1">
      <?php echo Helper::csrfField(); ?>
      <input type="hidden" name="action" id="role_action" value="save_role">
      <input type="hidden" name="id" id="role_id">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Nama Role <span class="text-red-500">*</span></label>
          <input type="text" name="display_name" id="role_display_name" required placeholder="contoh: Koordinator Lapangan" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
        </div>
        <div>
          <label class="font-bold text-xs text-slate-700 block mb-1">Deskripsi Role</label>
          <input type="text" name="description" id="role_description" placeholder="contoh: Menangani operasional & perbaikan jaringan pelanggan" class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-purple-500 bg-white">
        </div>
      </div>

      <!-- Permission Controls Toolbar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-purple-50/70 p-3.5 rounded-2xl border border-purple-100">
        <div class="relative flex-1">
          <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-purple-400">
            <i class="fa-solid fa-magnifying-glass text-xs"></i>
          </span>
          <input type="text" id="permission_search" onkeyup="filterPermissions(this.value)" placeholder="Cari hak akses... (misal: tambah pelanggan, hapus, barang keluar)" class="w-full text-xs pl-8 pr-3 py-2 rounded-xl border border-purple-200 focus:outline-none focus:border-purple-600 bg-white">
        </div>

        <div class="flex items-center gap-2 shrink-0">
          <button type="button" onclick="toggleAllPermissions(true)" class="px-3 py-1.5 text-3xs font-extrabold text-purple-700 bg-white hover:bg-purple-100 border border-purple-200 rounded-xl transition-all shadow-soft-xs">
            <i class="fa-solid fa-check-double mr-1"></i> Pilih Semua
          </button>
          <button type="button" onclick="toggleAllPermissions(false)" class="px-3 py-1.5 text-3xs font-extrabold text-slate-600 bg-white hover:bg-slate-100 border border-slate-200 rounded-xl transition-all shadow-soft-xs">
            <i class="fa-solid fa-square-xmark mr-1"></i> Kosongkan
          </button>
        </div>
      </div>

      <!-- Granular Permission Categories Grid -->
      <div class="space-y-4" id="permissions_container">
        <?php foreach ($groupedPermissions as $catName => $perms): ?>
        <div class="permission-category-box bg-white rounded-2xl p-4 border border-slate-200/80 shadow-soft-xs space-y-3">
          
          <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center text-xs font-black">
                <i class="fa-solid fa-folder-tree"></i>
              </div>
              <h6 class="font-extrabold text-slate-900 text-xs tracking-tight uppercase"><?php echo Helper::e($catName); ?></h6>
              <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 font-bold text-3xs"><?php echo count($perms); ?> Fitur</span>
            </div>

            <div class="flex items-center gap-1.5">
              <button type="button" onclick="toggleCategoryPermissions('<?php echo Helper::e(addslashes($catName)); ?>', true)" class="px-2 py-1 text-3xs font-bold text-purple-600 hover:bg-purple-50 rounded-lg transition-colors">
                Centang Semua
              </button>
              <span class="text-slate-300">|</span>
              <button type="button" onclick="toggleCategoryPermissions('<?php echo Helper::e(addslashes($catName)); ?>', false)" class="px-2 py-1 text-3xs font-bold text-slate-400 hover:bg-slate-100 rounded-lg transition-colors">
                Hapus Centang
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5">
            <?php foreach ($perms as $p): ?>
            <label class="permission-item flex items-start gap-2.5 p-2.5 rounded-xl border border-slate-100 hover:border-purple-200 hover:bg-purple-50/40 transition-all cursor-pointer group" data-category="<?php echo Helper::e($catName); ?>" data-text="<?php echo strtolower(Helper::e($p['name'] . ' ' . $p['description'])); ?>">
              <input type="checkbox" 
                     name="permissions[]" 
                     value="<?php echo $p['id']; ?>" 
                     class="perm-checkbox rounded text-purple-600 focus:ring-purple-500 border-slate-300 mt-0.5 shrink-0 cursor-pointer">
              <div>
                <span class="text-xs font-bold text-slate-800 group-hover:text-purple-900 block leading-tight">
                  <?php echo Helper::e($p['description']); ?>
                </span>
                <span class="text-3xs text-slate-400 font-mono font-semibold"><?php echo Helper::e($p['name']); ?></span>
              </div>
            </label>
            <?php endforeach; ?>
          </div>

        </div>
        <?php endforeach; ?>
      </div>

      <!-- Footer Action Buttons -->
      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 shrink-0">
        <button type="button" onclick="closeRoleModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
          Batal
        </button>
        <button type="submit" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-indigo-600 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-soft-md hover:scale-105 transition-all">
          <i class="fa-solid fa-save mr-1.5"></i> Simpan Role & Hak Akses
        </button>
      </div>
    </form>

  </div>
</div>

<!-- Hidden Delete Role Form -->
<form id="deleteRoleForm" method="POST" action="<?php echo Helper::url('settings_users', ['tab' => 'roles']); ?>" class="hidden">
  <?php echo Helper::csrfField(); ?>
  <input type="hidden" name="action" value="delete_role">
  <input type="hidden" name="id" id="deleteRoleId">
</form>


<!-- ================================================================= -->
<!-- JAVASCRIPT CONTROL SCRIPTS -->
<!-- ================================================================= -->
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

/* User Modal Controls */
function openUserModal() {
  document.getElementById('user_action').value = 'save_user';
  document.getElementById('user_id').value = '';
  document.getElementById('user_name').value = '';
  document.getElementById('user_username').value = '';
  document.getElementById('user_email').value = '';
  document.getElementById('user_phone').value = '';
  document.getElementById('user_password').value = 'admin123';
  document.getElementById('user_password_required').style.display = 'inline';
  document.getElementById('user_status_container').classList.add('hidden');
  document.getElementById('userModalTitle').innerText = 'Tambah Pengguna Baru';
  openModal('userModal', 'userModalContent');
}

function openEditUserModal(u) {
  if (!u) return;
  document.getElementById('user_action').value = 'update_user';
  document.getElementById('user_id').value = u.id || '';
  document.getElementById('user_name').value = u.name || '';
  document.getElementById('user_username').value = u.username || '';
  document.getElementById('user_email').value = u.email || '';
  document.getElementById('user_phone').value = u.phone || '';
  document.getElementById('user_role_id').value = u.role_id || 3;
  document.getElementById('user_status').value = u.status || 'active';
  document.getElementById('user_password').value = '';
  document.getElementById('user_password_required').style.display = 'none';
  document.getElementById('user_status_container').classList.remove('hidden');
  document.getElementById('userModalTitle').innerText = 'Edit Data Pengguna';
  openModal('userModal', 'userModalContent');
}

function closeUserModal() {
  closeModal('userModal', 'userModalContent');
}

function confirmDeleteUser(id, name) {
  if (confirm('Apakah Anda yakin ingin menghapus akun pengguna "' + name + '"?')) {
    document.getElementById('deleteUserId').value = id;
    document.getElementById('deleteUserForm').submit();
  }
}

/* Role Modal Controls */
function openRoleModal() {
  document.getElementById('role_action').value = 'save_role';
  document.getElementById('role_id').value = '';
  document.getElementById('role_display_name').value = '';
  document.getElementById('role_description').value = '';
  document.getElementById('roleModalTitle').innerText = 'Tambah Role Baru';

  toggleAllPermissions(false);
  openModal('roleModal', 'roleModalContent');
}

function openEditRoleModal(role, assignedPermIds) {
  if (!role) return;
  document.getElementById('role_action').value = 'update_role';
  document.getElementById('role_id').value = role.id || '';
  document.getElementById('role_display_name').value = role.display_name || '';
  document.getElementById('role_description').value = role.description || '';
  document.getElementById('roleModalTitle').innerText = 'Edit Hak Akses Role: ' + role.display_name;

  toggleAllPermissions(false);

  if (Array.isArray(assignedPermIds)) {
    const checkboxes = document.querySelectorAll('.perm-checkbox');
    checkboxes.forEach(cb => {
      if (assignedPermIds.includes(parseInt(cb.value))) {
        cb.checked = true;
      }
    });
  }

  openModal('roleModal', 'roleModalContent');
}

function closeRoleModal() {
  closeModal('roleModal', 'roleModalContent');
}

function confirmDeleteRole(id, name) {
  if (confirm('Apakah Anda yakin ingin menghapus role "' + name + '"?\n\nPengguna yang masih menggunakan role ini harus dipindahkan ke role lain terlebih dahulu.')) {
    document.getElementById('deleteRoleId').value = id;
    document.getElementById('deleteRoleForm').submit();
  }
}

/* Permission Checkbox Controls */
function toggleCategoryPermissions(catName, selectAll) {
  const items = document.querySelectorAll(`.permission-item[data-category="${catName}"] .perm-checkbox`);
  items.forEach(cb => {
    cb.checked = selectAll;
  });
}

function toggleAllPermissions(selectAll) {
  const checkboxes = document.querySelectorAll('.perm-checkbox');
  checkboxes.forEach(cb => {
    cb.checked = selectAll;
  });
}

function filterPermissions(query) {
  query = query.toLowerCase().trim();
  const items = document.querySelectorAll('.permission-item');
  const boxes = document.querySelectorAll('.permission-category-box');

  items.forEach(item => {
    const text = item.getAttribute('data-text') || '';
    if (query === '' || text.includes(query)) {
      item.classList.remove('hidden');
      item.classList.add('flex');
    } else {
      item.classList.add('hidden');
      item.classList.remove('flex');
    }
  });

  boxes.forEach(box => {
    const visibleItems = box.querySelectorAll('.permission-item.flex');
    if (query !== '' && visibleItems.length === 0) {
      box.classList.add('hidden');
    } else {
      box.classList.remove('hidden');
    }
  });
}

/* Outside click and ESC listener */
document.addEventListener("DOMContentLoaded", function() {
  const modals = [
    { id: 'userModal', content: 'userModalContent' },
    { id: 'roleModal', content: 'roleModalContent' }
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
