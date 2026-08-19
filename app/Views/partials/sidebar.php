<?php
// app/Views/partials/sidebar.php - Clean Soft UI Floating Sidebar (Hidden on mobile)
$currentPage = $_GET['page'] ?? 'dashboard';

function navItem(string $page, string $label, string $iconClass, string $currentPage, ?string $permission = null): string {
    if ($permission !== null && !AuthService::hasPermission($permission)) {
        return '';
    }
    $isActive = ($currentPage === $page);
    $activeBg = $isActive ? 'bg-white shadow-soft-lg font-bold text-slate-900' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100/80 font-medium';
    $iconBg = $isActive ? 'bg-gradient-to-tl from-purple-700 to-pink-500 text-white shadow-soft-md' : 'bg-slate-100 text-slate-600';
    $url = Helper::url($page);

    return <<<HTML
    <li class="w-full">
      <a class="py-2.5 my-0.5 mx-2 flex items-center px-3.5 rounded-2xl transition-all duration-200 text-xs {$activeBg}" href="{$url}">
        <div class="mr-3 flex h-7 w-7 items-center justify-center rounded-xl {$iconBg} text-center shrink-0">
          <i class="fa-solid {$iconClass} text-2xs"></i>
        </div>
        <span class="truncate tracking-tight">{$label}</span>
      </a>
    </li>
HTML;
}

function navHeader(string $title, ?string $permission = null): string {
    if ($permission !== null && !AuthService::hasPermission($permission)) {
        return '';
    }
    return <<<HTML
    <li class="w-full mt-3 mb-1">
      <h6 class="px-5 text-3xs font-extrabold tracking-wider uppercase text-slate-400">{$title}</h6>
    </li>
HTML;
}
?>
<!-- Floating Sidebar (Desktop only, completely hidden on mobile) -->
<aside id="appSidebar" class="hidden xl:flex fixed inset-y-0 left-0 z-50 w-64 my-4 ml-4 h-[calc(100vh-2rem)] overflow-y-auto bg-white/95 backdrop-blur-xl rounded-3xl shadow-soft-xl flex-col justify-between">
  
  <div>
    <!-- Logo Header -->
    <div class="p-5 flex items-center justify-between">
      <a class="flex items-center gap-3" href="<?php echo Helper::url('dashboard'); ?>">
        <div class="flex items-center justify-center w-9 h-9 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 shadow-soft-md text-white font-black text-sm shrink-0">
          <i class="fa-solid fa-tower-broadcast"></i>
        </div>
        <div>
          <span class="font-extrabold text-slate-900 text-base leading-tight block tracking-tight">Nusantara<span class="text-pink-600">Net</span></span>
          <span class="text-3xs text-slate-400 uppercase font-bold tracking-wider block">ISP Management</span>
        </div>
      </a>
    </div>

    <!-- Menu Items -->
    <div class="py-2">
      <ul class="flex flex-col pl-0 mb-0 space-y-0.5">
        
        <!-- Core -->
        <?php echo navItem('dashboard', 'Dashboard Utama', 'fa-gauge-high', $currentPage, 'dashboard.view'); ?>

        <!-- Customer -->
        <?php if (AuthService::hasPermission('customers.view') || AuthService::hasPermission('packages.view') || AuthService::hasPermission('locations.view') || AuthService::hasPermission('pics.view')): ?>
          <?php echo navHeader('Pelanggan'); ?>
          <?php echo navItem('customers', 'Data Pelanggan', 'fa-users', $currentPage, 'customers.view'); ?>
          <?php echo navItem('packages', 'Paket Internet', 'fa-wifi', $currentPage, 'packages.view'); ?>
          <?php echo navItem('locations', 'Area & Coverage', 'fa-map-location-dot', $currentPage, 'locations.view'); ?>
          <?php echo navItem('pics', 'Data PIC / RT-RW', 'fa-address-book', $currentPage, 'pics.view'); ?>
        <?php endif; ?>

        <!-- Billing -->
        <?php if (AuthService::hasPermission('billing.view')): ?>
          <?php echo navHeader('Billing & Kasir'); ?>
          <?php echo navItem('invoices', 'Tagihan & Invoice', 'fa-file-invoice-dollar', $currentPage, 'billing.view'); ?>
          <?php echo navItem('payments', 'Riwayat Bayar', 'fa-receipt', $currentPage, 'billing.view'); ?>
          <?php echo navItem('receivables', 'Aging Piutang', 'fa-clock-rotate-left', $currentPage, 'billing.view'); ?>
        <?php endif; ?>

        <!-- Operations -->
        <?php if (AuthService::hasPermission('rab.view')): ?>
          <?php echo navHeader('Operasional'); ?>
          <?php echo navItem('rab', 'RAB Proyek', 'fa-calculator', $currentPage, 'rab.view'); ?>
        <?php endif; ?>

        <!-- Finance -->
        <?php if (AuthService::hasPermission('finance.view') || AuthService::hasPermission('payroll.view')): ?>
          <?php echo navHeader('Keuangan'); ?>
          <?php echo navItem('transactions', 'Transaksi Kas', 'fa-money-bill-transfer', $currentPage, 'finance.view'); ?>
          <?php echo navItem('cashflow', 'Arus Kas (Cashflow)', 'fa-arrow-trend-up', $currentPage, 'finance.view'); ?>
          <?php echo navItem('payroll', 'Payroll & Gaji', 'fa-money-check-dollar', $currentPage, 'payroll.view'); ?>
        <?php endif; ?>

        <!-- Inventory & Assets -->
        <?php if (AuthService::hasPermission('inventory.view') || AuthService::hasPermission('inventory.goods_in') || AuthService::hasPermission('inventory.goods_out') || AuthService::hasPermission('assets.view') || AuthService::hasPermission('inventory.suppliers')): ?>
          <?php echo navHeader('Logistik & Aset'); ?>
          <?php echo navItem('inventory', 'Katalog Stok', 'fa-boxes-stacked', $currentPage, 'inventory.view'); ?>
          <?php echo navItem('goods_in', 'Barang Masuk', 'fa-dolly', $currentPage, 'inventory.goods_in'); ?>
          <?php echo navItem('goods_out', 'Barang Keluar', 'fa-truck-ramp-box', $currentPage, 'inventory.goods_out'); ?>
          <?php echo navItem('assets', 'Aset Perusahaan', 'fa-laptop-code', $currentPage, 'assets.view'); ?>
          <?php echo navItem('suppliers', 'Data Supplier', 'fa-truck-field', $currentPage, 'inventory.suppliers'); ?>
        <?php endif; ?>

        <!-- Intelligence -->
        <?php if (AuthService::hasPermission('ai.use')): ?>
          <?php echo navHeader('AI & Advisor'); ?>
          <?php echo navItem('ai', 'AI Assistant', 'fa-robot', $currentPage, 'ai.use'); ?>
        <?php endif; ?>

        <!-- System Settings -->
        <?php if (AuthService::hasPermission('settings.company') || AuthService::hasPermission('settings.users') || AuthService::hasPermission('settings.roles') || AuthService::hasPermission('settings.logs')): ?>
          <?php echo navHeader('Sistem'); ?>
          <?php echo navItem('settings_company', 'Profil Perusahaan', 'fa-building', $currentPage, 'settings.company'); ?>
          <?php echo navItem('settings_users', 'Pengguna & Role', 'fa-user-shield', $currentPage, 'settings.users'); ?>
          <?php echo navItem('settings_logs', 'Audit & Activity Log', 'fa-shield-halved', $currentPage, 'settings.logs'); ?>
        <?php endif; ?>
      </ul>
    </div>
  </div>

  <!-- Bottom Logout CTA -->
  <div class="p-4">
    <a href="<?php echo Helper::url('logout'); ?>" class="w-full py-2.5 px-4 rounded-2xl bg-red-50 hover:bg-red-100 text-red-600 font-bold text-xs flex items-center justify-center gap-2 transition-all">
      <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
      <span>Keluar Sistem</span>
    </a>
  </div>
</aside>

<!-- Preserve Sidebar Scroll Position Across Navigation -->
<script>
(function() {
  const STORAGE_KEY = 'nusantaranet_sidebar_scroll_top';
  const sidebar = document.getElementById('appSidebar');
  if (!sidebar) return;

  // Restore scroll position immediately
  function restoreScroll() {
    const savedPos = sessionStorage.getItem(STORAGE_KEY);
    if (savedPos !== null) {
      sidebar.scrollTop = parseInt(savedPos, 10);
    }
  }

  // Restore on execution & on DOMContentLoaded
  restoreScroll();
  document.addEventListener('DOMContentLoaded', restoreScroll);
  window.addEventListener('load', restoreScroll);

  // Save scroll position on scroll (throttled)
  let scrollTimeout = null;
  sidebar.addEventListener('scroll', function() {
    if (scrollTimeout) clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(function() {
      sessionStorage.setItem(STORAGE_KEY, sidebar.scrollTop);
    }, 50);
  }, { passive: true });

  // Save immediately when clicking any navigation link
  sidebar.querySelectorAll('a').forEach(function(link) {
    link.addEventListener('click', function() {
      sessionStorage.setItem(STORAGE_KEY, sidebar.scrollTop);
    });
  });

  // Save before page unloads
  window.addEventListener('beforeunload', function() {
    if (sidebar) {
      sessionStorage.setItem(STORAGE_KEY, sidebar.scrollTop);
    }
  });
})();
</script>
