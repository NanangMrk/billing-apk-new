<?php
// app/Views/partials/navbar.php - Clean App Navbar with Wrapped Title Pill
$user = AuthService::user();
$pageTitle = $pageTitle ?? 'Dashboard Utama';
?>
<nav class="sticky top-0 z-30 w-full px-4 sm:px-6 py-2.5 sm:py-3.5 mx-auto max-w-[1600px] flex items-center justify-between">
  
  <div class="flex items-center gap-2 sm:gap-3">
    <!-- Mobile Brand / App Icon -->
    <a href="<?php echo Helper::url('dashboard'); ?>" class="xl:hidden flex items-center justify-center w-8 h-8 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white shadow-soft-xs text-xs">
      <i class="fa-solid fa-tower-broadcast"></i>
    </a>

    <!-- Wrapped Menu Title Badge / Pill -->
    <div class="flex items-center gap-2 px-3.5 sm:px-4 py-1.5 sm:py-2 bg-white/90 backdrop-blur-md rounded-2xl shadow-soft-xs">
      <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-3xs shadow-soft-xs shrink-0">
        <i class="fa-solid fa-layer-group"></i>
      </div>
      <span class="text-2xs sm:text-xs font-black text-slate-800 tracking-tight leading-none truncate max-w-[150px] sm:max-w-none"><?php echo Helper::e($pageTitle); ?></span>
    </div>
  </div>

  <div class="flex items-center gap-2 sm:gap-3">
    <!-- Global Quick Search (Desktop) -->
    <div class="relative hidden md:block w-72">
      <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
        <i class="fa-solid fa-magnifying-glass text-xs"></i>
      </span>
      <input type="text" placeholder="Cari pelanggan, SN, ODP..." class="w-full text-xs pl-9 pr-3.5 py-2.5 rounded-2xl bg-white focus:outline-none focus:ring-2 focus:ring-purple-500/20 shadow-soft-xs placeholder:text-slate-400">
    </div>

    <!-- Landing Page Public Portal Link -->
    <a href="<?php echo Helper::url('landing'); ?>" target="_blank" class="px-3 sm:px-4 py-1.5 sm:py-2 text-2xs sm:text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 rounded-2xl shadow-soft-xs transition-colors flex items-center gap-1.5">
      <i class="fa-solid fa-globe text-purple-600"></i>
      <span class="hidden sm:inline">Portal Publik</span>
    </a>

    <!-- User Profile Badge -->
    <div class="flex items-center gap-2 px-2.5 sm:px-3 py-1 sm:py-1.5 bg-white/90 backdrop-blur-md rounded-2xl shadow-soft-xs">
      <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center font-black text-2xs sm:text-xs shadow-soft-sm shrink-0">
        <?php echo strtoupper(substr($user['name'] ?? 'A', 0, 1)); ?>
      </div>
      <div class="hidden sm:block text-left pr-1">
        <span class="text-xs font-bold text-slate-800 block leading-tight"><?php echo Helper::e($user['name'] ?? 'Admin'); ?></span>
        <span class="text-3xs text-purple-700 font-extrabold block uppercase tracking-wider"><?php echo Helper::e($user['role_display'] ?? $user['role_name'] ?? 'Super Admin'); ?></span>
      </div>
    </div>
  </div>

</nav>
