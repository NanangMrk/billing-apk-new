<?php
// app/Views/partials/mobile_nav.php - App-like Mobile Bottom Navigation Bar & Feature Sheet Drawer
$currentPage = $_GET['page'] ?? 'dashboard';

function mobileNavItem(string $page, string $label, string $icon, string $currentPage): string {
    $isActive = ($currentPage === $page);
    $url = Helper::url($page);
    
    if ($isActive) {
        return <<<HTML
        <a href="{$url}" class="flex flex-col items-center justify-center flex-1 py-1 text-purple-700 transition-all">
          <div class="w-10 h-7 rounded-full bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center shadow-soft-sm text-xs mb-0.5">
            <i class="fa-solid {$icon}"></i>
          </div>
          <span class="text-3xs font-extrabold tracking-tight">{$label}</span>
        </a>
HTML;
    }

    return <<<HTML
    <a href="{$url}" class="flex flex-col items-center justify-center flex-1 py-1 text-slate-400 hover:text-slate-700 transition-all">
      <div class="w-10 h-7 flex items-center justify-center text-sm mb-0.5">
        <i class="fa-solid {$icon}"></i>
      </div>
      <span class="text-3xs font-semibold tracking-tight">{$label}</span>
    </a>
HTML;
}
?>

<!-- Mobile App Bottom Navigation Bar (Dock) -->
<div class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-2xl shadow-[0_-5px_25px_rgba(0,0,0,0.06)] xl:hidden px-3 py-1.5 flex items-center justify-around pb-[max(0.5rem,env(safe-area-inset-bottom))]">
  <?php echo mobileNavItem('dashboard', 'Beranda', 'fa-house', $currentPage); ?>
  <?php echo mobileNavItem('customers', 'Pelanggan', 'fa-users', $currentPage); ?>
  <?php echo mobileNavItem('invoices', 'Invoice', 'fa-file-invoice-dollar', $currentPage); ?>

  <!-- More Features Button (Opens Bottom Sheet) -->
  <button type="button" onclick="openMobileMenuSheet()" class="flex flex-col items-center justify-center flex-1 py-1 text-slate-400 hover:text-slate-700 transition-all">
    <div class="w-10 h-7 flex items-center justify-center text-sm mb-0.5">
      <i class="fa-solid fa-grid-2 text-slate-500"></i>
    </div>
    <span class="text-3xs font-semibold tracking-tight text-slate-600">Menu</span>
  </button>
</div>

<!-- Mobile Bottom Sheet Drawer (App Features Menu) -->
<div id="mobileMenuSheet" class="fixed inset-0 z-50 hidden items-end justify-center bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300 xl:hidden">
  
  <div class="relative w-full bg-white rounded-t-3xl shadow-soft-2xl p-5 transform transition-transform duration-300 translate-y-full max-h-[85vh] overflow-y-auto" id="mobileMenuSheetContent">
    
    <!-- Drag Bar Handle -->
    <div class="w-12 h-1.5 bg-slate-200 rounded-full mx-auto mb-4 cursor-pointer" onclick="closeMobileMenuSheet()"></div>

    <!-- Sheet Header -->
    <div class="flex items-center justify-between pb-3 mb-4">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-xs shadow-soft-xs">
          <i class="fa-solid fa-tower-broadcast"></i>
        </div>
        <div>
          <h5 class="font-extrabold text-slate-900 text-sm leading-tight">Nusantara<span class="text-pink-600">Net</span></h5>
          <span class="text-3xs text-slate-400 font-semibold uppercase">Semua Fitur Aplikasi</span>
        </div>
      </div>
      <button type="button" onclick="closeMobileMenuSheet()" class="p-1.5 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-100 transition-colors">
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>
    </div>

    <!-- Feature Grid Groups -->
    <div class="space-y-4">
      
      <!-- Group 1: Pelanggan & Paket -->
      <div>
        <h6 class="text-3xs font-extrabold text-slate-400 uppercase tracking-wider mb-2 px-1">Pelanggan & Jaringan</h6>
        <div class="grid grid-cols-4 gap-2">
          <a href="<?php echo Helper::url('customers'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-users"></i>
            </div>
            <span class="text-3xs font-bold text-slate-700 group-hover:text-purple-700">Pelanggan</span>
          </a>

          <a href="<?php echo Helper::url('packages'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-pink-100 text-pink-700 flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-wifi"></i>
            </div>
            <span class="text-3xs font-bold text-slate-700 group-hover:text-pink-700">Paket</span>
          </a>

          <a href="<?php echo Helper::url('locations'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-map-location-dot"></i>
            </div>
            <span class="text-3xs font-bold text-slate-700 group-hover:text-blue-700">Coverage</span>
          </a>

          <a href="<?php echo Helper::url('pics'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-address-book"></i>
            </div>
            <span class="text-3xs font-bold text-slate-700 group-hover:text-indigo-700">Data PIC</span>
          </a>
        </div>
      </div>

      <!-- Group 2: Billing & Kasir -->
      <div>
        <h6 class="text-3xs font-extrabold text-slate-400 uppercase tracking-wider mb-2 px-1">Billing & Kasir</h6>
        <div class="grid grid-cols-4 gap-2">
          <a href="<?php echo Helper::url('invoices'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <span class="text-3xs font-bold text-slate-700 group-hover:text-emerald-700">Invoice</span>
          </a>

          <a href="<?php echo Helper::url('payments'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-receipt"></i>
            </div>
            <span class="text-3xs font-bold text-slate-700 group-hover:text-teal-700">Riwayat</span>
          </a>

          <a href="<?php echo Helper::url('receivables'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <span class="text-3xs font-bold text-slate-700 group-hover:text-rose-700">Piutang</span>
          </a>

          <a href="<?php echo Helper::url('tickets'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-ticket"></i>
            </div>
            <span class="text-3xs font-bold text-slate-700 group-hover:text-amber-700">Tiket</span>
          </a>
        </div>
      </div>

      <!-- Group 3: Keuangan & Laporan -->
      <div>
        <h6 class="text-3xs font-extrabold text-slate-400 uppercase tracking-wider mb-2 px-1">Keuangan & Akuntansi</h6>
        <div class="grid grid-cols-4 gap-2">
          <a href="<?php echo Helper::url('finance'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-wallet"></i>
            </div>
            <span class="text-3xs font-bold text-slate-700 group-hover:text-cyan-700">Kas & Bank</span>
          </a>

          <a href="<?php echo Helper::url('transactions'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-money-bill-transfer"></i>
            </div>
            <span class="text-3xs font-bold text-slate-700 group-hover:text-sky-700">Transaksi</span>
          </a>

          <a href="<?php echo Helper::url('cashflow'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-arrow-trend-up"></i>
            </div>
            <span class="text-3xs font-bold text-slate-700 group-hover:text-emerald-700">Arus Kas</span>
          </a>

          <a href="<?php echo Helper::url('profit_loss'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-violet-100 text-violet-700 flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-chart-line"></i>
            </div>
            <span class="text-3xs font-bold text-slate-700 group-hover:text-violet-700">Laba Rugi</span>
          </a>
        </div>
      </div>

      <!-- Group 4: Logistik, Aset & Operasional -->
      <div>
        <h6 class="text-3xs font-extrabold text-slate-400 uppercase tracking-wider mb-2 px-1">Logistik, Aset & Sistem</h6>
        <div class="grid grid-cols-4 gap-2">
          <a href="<?php echo Helper::url('inventory'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-orange-100 text-orange-700 flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-boxes-stacked"></i>
            </div>
            <span class="text-3xs font-bold text-slate-700 group-hover:text-orange-700">Stok</span>
          </a>

          <a href="<?php echo Helper::url('assets'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-laptop-code"></i>
            </div>
            <span class="text-3xs font-bold text-slate-700 group-hover:text-purple-700">Aset</span>
          </a>

          <a href="<?php echo Helper::url('ai'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform shadow-soft-xs">
              <i class="fa-solid fa-robot"></i>
            </div>
            <span class="text-3xs font-bold text-purple-700">AI Advisor</span>
          </a>

          <a href="<?php echo Helper::url('settings_company'); ?>" class="flex flex-col items-center p-2.5 rounded-2xl bg-slate-50 hover:bg-purple-50 transition-all text-center group">
            <div class="w-9 h-9 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center text-xs mb-1.5 group-hover:scale-110 transition-transform">
              <i class="fa-solid fa-gear"></i>
            </div>
            <span class="text-3xs font-bold text-slate-700 group-hover:text-slate-900">Setting</span>
          </a>
        </div>
      </div>

      <!-- Logout CTA -->
      <div class="pt-2 border-t border-slate-100">
        <a href="<?php echo Helper::url('logout'); ?>" class="w-full py-2.5 px-4 rounded-2xl bg-red-50 hover:bg-red-100 text-red-600 font-bold text-xs flex items-center justify-center gap-2 transition-all">
          <i class="fa-solid fa-arrow-right-from-bracket"></i>
          <span>Keluar dari Aplikasi</span>
        </a>
      </div>

    </div>
  </div>
</div>

<script>
function openMobileMenuSheet() {
  const sheet = document.getElementById("mobileMenuSheet");
  const content = document.getElementById("mobileMenuSheetContent");
  sheet.classList.remove("hidden");
  sheet.classList.add("flex");
  setTimeout(() => {
    content.classList.remove("translate-y-full");
    content.classList.add("translate-y-0");
  }, 10);
}

function closeMobileMenuSheet() {
  const sheet = document.getElementById("mobileMenuSheet");
  const content = document.getElementById("mobileMenuSheetContent");
  content.classList.remove("translate-y-0");
  content.classList.add("translate-y-full");
  setTimeout(() => {
    sheet.classList.add("hidden");
    sheet.classList.remove("flex");
  }, 250);
}

document.addEventListener("DOMContentLoaded", function() {
  const sheet = document.getElementById("mobileMenuSheet");
  if (sheet) {
    sheet.addEventListener("click", function(e) {
      if (e.target === sheet) closeMobileMenuSheet();
    });
  }
});
</script>
