<?php
// app/Views/landing/index.php - Front-facing Landing Page
$packages = $packages ?? [];
$locations = $locations ?? [];
?>

<!-- Hero Section -->
<section class="gradient-hero text-white relative overflow-hidden pt-16 pb-28 md:pt-24 md:pb-36">
  <!-- Background Glow & Shapes -->
  <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-purple-600/30 rounded-full blur-3xl pointer-events-none"></div>
  <div class="absolute bottom-0 right-10 w-80 h-80 bg-pink-600/20 rounded-full blur-3xl pointer-events-none"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
      
      <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
        <!-- Badge -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 border border-white/20 text-xs font-semibold text-pink-300 backdrop-blur-md">
          <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
          <span>100% Jaringan Full Fiber Optic FTTH</span>
        </div>

        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-tight">
          Internet Cepat, Stabil & <span class="bg-gradient-to-r from-pink-400 via-purple-300 to-indigo-300 bg-clip-text text-transparent">Tanpa Batas Kuota (FUP)</span>
        </h1>

        <p class="text-sm sm:text-base text-slate-300 max-w-2xl leading-relaxed">
          Nikmati koneksi broadband ultra-cepat dengan latensi rendah untuk kebutuhan streaming 4K, WFH, gaming kompetitif, dan operasional bisnis Anda.
        </p>

        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
          <a href="#paket" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-sm uppercase tracking-wider shadow-soft-xl hover:shadow-soft-2xl hover:scale-105 transition-all text-center">
            <i class="fa-solid fa-bolt mr-2"></i> Pilih Paket Internet
          </a>
          <a href="#coverage" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-sm tracking-wider backdrop-blur-md transition-all text-center">
            <i class="fa-solid fa-map-pin mr-2 text-pink-400"></i> Cek Area Coverage
          </a>
        </div>

        <!-- Metric highlights -->
        <div class="grid grid-cols-3 gap-4 pt-6 border-t border-white/10 text-center sm:text-left">
          <div>
            <span class="text-2xl sm:text-3xl font-extrabold text-white block">99.5%</span>
            <span class="text-2xs sm:text-xs text-slate-400">SLA Uptime Jaringan</span>
          </div>
          <div>
            <span class="text-2xl sm:text-3xl font-extrabold text-white block">&lt; 5 ms</span>
            <span class="text-2xs sm:text-xs text-slate-400">Latensi Gaming</span>
          </div>
          <div>
            <span class="text-2xl sm:text-3xl font-extrabold text-white block">24/7</span>
            <span class="text-2xs sm:text-xs text-slate-400">Dukungan NOC Cepat</span>
          </div>
        </div>

      </div>

      <!-- Hero Card Visual -->
      <div class="lg:col-span-5">
        <div class="relative mx-auto max-w-md">
          <div class="p-6 sm:p-8 rounded-2xl bg-white/10 border border-white/20 shadow-2xl backdrop-blur-xl space-y-6">
            <div class="flex items-center justify-between border-b border-white/10 pb-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-500/20 border border-green-500/40 flex items-center justify-center text-green-400 text-lg">
                  <i class="fa-solid fa-signal"></i>
                </div>
                <div>
                  <h4 class="text-sm font-bold text-white leading-tight">Live Network Status</h4>
                  <span class="text-2xs text-green-400 flex items-center gap-1 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Semua Server Normal
                  </span>
                </div>
              </div>
              <span class="text-xs px-2.5 py-1 rounded-lg bg-white/10 text-white font-mono">POP GPON-01</span>
            </div>

            <!-- Ping Meter -->
            <div class="space-y-3">
              <div class="flex justify-between text-xs text-slate-300">
                <span>Download Speed</span>
                <span class="font-bold text-white">Up to 200 Mbps</span>
              </div>
              <div class="w-full bg-white/10 rounded-full h-2 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-400 to-pink-500 h-2 rounded-full w-4/5"></div>
              </div>

              <div class="flex justify-between text-xs text-slate-300 pt-2">
                <span>Upload Speed (Simetris)</span>
                <span class="font-bold text-white">Up to 200 Mbps</span>
              </div>
              <div class="w-full bg-white/10 rounded-full h-2 overflow-hidden">
                <div class="bg-gradient-to-r from-pink-400 to-indigo-400 h-2 rounded-full w-4/5"></div>
              </div>
            </div>

            <!-- Portal Admin Quick Access Banner -->
            <div class="pt-4 border-t border-white/10">
              <div class="p-4 rounded-xl bg-gradient-to-br from-purple-900/60 to-pink-900/60 border border-purple-400/30 flex items-center justify-between gap-3">
                <div>
                  <p class="text-2xs uppercase tracking-wider font-bold text-pink-300">Portal Pengelola ISP</p>
                  <p class="text-xs font-semibold text-white">Akses Billing & Manajemen</p>
                </div>
                <a href="<?php echo Helper::url('login'); ?>" class="px-3.5 py-2 rounded-lg bg-white text-purple-900 font-bold text-xs hover:bg-slate-100 transition-colors whitespace-nowrap shadow-soft-sm">
                  Masuk <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- Internet Packages Section -->
<section id="paket" class="py-20 bg-slate-50 relative">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
      <span class="text-xs font-bold uppercase tracking-wider text-pink-600 bg-pink-50 px-3 py-1 rounded-full">Paket Berlangganan</span>
      <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Pilih Paket Internet Sesuai Kebutuhan</h2>
      <p class="text-xs sm:text-sm text-slate-500">
        Semua paket sudah termasuk instalasi modem ONT WiFi fiber optik, tanpa batasan kuota (Truly Unlimited FUP), dan dukungan teknis 24/7.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <?php foreach ($packages as $idx => $pkg): 
        $isPopular = ($idx === 1);
        $borderClass = $isPopular ? 'border-2 border-purple-500 shadow-soft-2xl relative -translate-y-2' : 'border border-slate-200 shadow-soft-md hover:shadow-soft-xl transition-all';
      ?>
      <div class="bg-white rounded-2xl p-6 flex flex-col justify-between <?php echo $borderClass; ?>">
        <?php if ($isPopular): ?>
          <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-gradient-to-tl from-purple-700 to-pink-500 text-white text-2xs font-bold uppercase tracking-wider shadow-soft-sm">
            Paling Populer
          </div>
        <?php endif; ?>

        <div class="space-y-4">
          <div>
            <h3 class="font-bold text-base text-slate-900"><?php echo Helper::e($pkg['name']); ?></h3>
            <p class="text-xs text-slate-400 mt-1 min-h-[32px]"><?php echo Helper::e($pkg['description']); ?></p>
          </div>

          <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
            <div>
              <span class="text-2xs text-slate-400 block font-medium">Download</span>
              <span class="text-sm font-bold text-purple-700"><?php echo Helper::e($pkg['download_speed']); ?></span>
            </div>
            <div class="h-6 w-px bg-slate-200"></div>
            <div>
              <span class="text-2xs text-slate-400 block font-medium">Upload</span>
              <span class="text-sm font-bold text-pink-600"><?php echo Helper::e($pkg['upload_speed']); ?></span>
            </div>
          </div>

          <div class="pt-2">
            <span class="text-2xs text-slate-400 font-semibold block uppercase tracking-wider">Mulai Dari</span>
            <div class="flex items-baseline gap-1">
              <span class="text-2xl font-black text-slate-900"><?php echo Helper::formatRupiah($pkg['price']); ?></span>
              <span class="text-xs text-slate-500 font-medium">/bulan</span>
            </div>
            <span class="text-2xs text-slate-400 block mt-0.5">+ PPN <?php echo $pkg['tax_percent']; ?>% &bull; Pasang <?php echo Helper::formatRupiah($pkg['installation_fee']); ?></span>
          </div>

          <ul class="space-y-2.5 text-xs text-slate-600 pt-3 border-t border-slate-100">
            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-green-500 text-xs"></i> 100% Fiber Optic FTTH</li>
            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-green-500 text-xs"></i> Unlimited Kuota / No FUP</li>
            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-green-500 text-xs"></i> Termasuk Sewa ONT Modem</li>
            <li class="flex items-center gap-2"><i class="fa-solid fa-check text-green-500 text-xs"></i> Support 24/7 SLA Responsif</li>
          </ul>
        </div>

        <div class="pt-6">
          <a href="https://wa.me/6281234567890?text=Halo%20NusantaraNet,%20saya%20tertarik%20berlangganan%20paket%20<?php echo urlencode($pkg['name']); ?>" target="_blank" class="w-full inline-block py-2.5 px-4 text-center rounded-xl bg-slate-900 hover:bg-gradient-to-tl hover:from-purple-700 hover:to-pink-500 text-white font-bold text-xs uppercase tracking-wider transition-all shadow-soft-sm">
            <i class="fa-brands fa-whatsapp mr-1.5 text-green-400"></i> Pesan Sekarang
          </a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- Features / Keunggulan Section -->
<section id="keunggulan" class="py-20 bg-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
      <span class="text-xs font-bold uppercase tracking-wider text-purple-600 bg-purple-50 px-3 py-1 rounded-full">Mengapa NusantaraNet?</span>
      <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Koneksi Terbaik untuk Aktivitas Digital Anda</h2>
      <p class="text-xs sm:text-sm text-slate-500">
        Infrastruktur modern dengan teknologi GPON terbaru memastikan kestabilan koneksi tanpa gangguan.
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      
      <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 space-y-4 hover:shadow-soft-lg transition-shadow">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-xl shadow-soft-md">
          <i class="fa-solid fa-gauge-high"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-900">Kecepatan Simetris & Stabil</h3>
        <p class="text-xs text-slate-500 leading-relaxed">
          Rasio unduh dan unggah yang seimbang memastikan upload video, live streaming, dan video conference berjalan mulus tanpa buffering.
        </p>
      </div>

      <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 space-y-4 hover:shadow-soft-lg transition-shadow">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-tl from-blue-600 to-cyan-400 text-white flex items-center justify-center text-xl shadow-soft-md">
          <i class="fa-solid fa-infinity"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-900">Bebas FUP & Kuota Tersembunyi</h3>
        <p class="text-xs text-slate-500 leading-relaxed">
          Gunakan internet sepuasnya untuk seluruh keluarga dan bisnis Anda. Kecepatan tidak akan pernah diturunkan di tengah bulan.
        </p>
      </div>

      <div class="p-8 rounded-2xl bg-slate-50 border border-slate-100 space-y-4 hover:shadow-soft-lg transition-shadow">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-tl from-green-600 to-lime-400 text-white flex items-center justify-center text-xl shadow-soft-md">
          <i class="fa-solid fa-headset"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-900">Teknisi Cepat Tanggap (SLA &lt; 4 Jam)</h3>
        <p class="text-xs text-slate-500 leading-relaxed">
          Tim Network Operations Center (NOC) dan teknisi lapangan siap siaga memperbaiki kabel putus atau kendala router dalam hitungan jam.
        </p>
      </div>

    </div>
  </div>
</section>

<!-- Coverage Area Section -->
<section id="coverage" class="py-20 bg-slate-50">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
      
      <div class="lg:col-span-5 space-y-6">
        <span class="text-xs font-bold uppercase tracking-wider text-pink-600 bg-pink-50 px-3 py-1 rounded-full">Jangkauan Wilayah</span>
        <h2 class="text-2xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Telah Terhubung di Berbagai Kawasan</h2>
        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
          Kami terus memperluas jaringan kabel fiber optik ODP dan POP untuk menjangkau perumahan, perkampungan RT/RW Net, hingga area perkantoran.
        </p>

        <div class="space-y-3">
          <?php foreach ($locations as $loc): ?>
          <div class="p-3.5 bg-white rounded-xl border border-slate-200 shadow-soft-xs flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                <i class="fa-solid fa-location-dot"></i>
              </div>
              <div>
                <span class="text-xs font-bold text-slate-800 block"><?php echo Helper::e($loc['area_name']); ?></span>
                <span class="text-2xs text-slate-400"><?php echo Helper::e($loc['district'] . ', ' . $loc['city']); ?> &bull; ODP: <?php echo Helper::e($loc['odp_name']); ?></span>
              </div>
            </div>
            <span class="text-2xs font-bold px-2 py-0.5 rounded-full bg-green-50 text-green-600 border border-green-200">Tercover</span>
          </div>
          <?php endforeach; ?>
        </div>

        <div class="pt-2">
          <a href="https://wa.me/6281234567890?text=Halo%20NusantaraNet,%20saya%20mau%20tanya%20coverage%20di%20wilayah%20saya" target="_blank" class="text-xs font-bold text-purple-700 hover:text-purple-900 flex items-center gap-1">
            <span>Area Anda belum terdaftar? Ajukan ekspansi ODP</span>
            <i class="fa-solid fa-arrow-right text-2xs"></i>
          </a>
        </div>
      </div>

      <!-- Interactive Map / Location Showcase Box -->
      <div class="lg:col-span-7">
        <div class="p-6 sm:p-8 rounded-3xl bg-white border border-slate-200 shadow-soft-xl space-y-6">
          <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div>
              <h4 class="text-sm font-bold text-slate-900">Peta Topologi & Titik Distribusi ODP</h4>
              <span class="text-2xs text-slate-400">Ringkasan titik jangkauan fiber optik</span>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-lg bg-purple-50 text-purple-700 font-semibold font-mono">FTTH Ready</span>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
              <span class="text-2xs text-slate-400 uppercase font-semibold">Total Titik ODP Aktif</span>
              <p class="text-xl font-extrabold text-slate-800">48+ Titik ODP</p>
              <span class="text-2xs text-green-600 font-medium"><i class="fa-solid fa-circle-check"></i> Redaman Rata-rata -19 dBm</span>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
              <span class="text-2xs text-slate-400 uppercase font-semibold">Kapasitas Port Tersedia</span>
              <p class="text-xl font-extrabold text-purple-700">384 Port Pelanggan</p>
              <span class="text-2xs text-slate-500 font-medium">Siap sambung pasang baru H+1</span>
            </div>
          </div>

          <!-- Bottom Management CTA -->
          <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="space-y-1 text-center sm:text-left">
              <h5 class="text-xs font-bold text-white">Khusus Administrator & Staff ISP</h5>
              <p class="text-2xs text-slate-300">Masuk ke sistem untuk manajemen tagihan, inventaris, dan pelanggan.</p>
            </div>
            <a href="<?php echo Helper::url('login'); ?>" class="px-4 py-2 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase shadow-soft-sm hover:scale-105 transition-all whitespace-nowrap">
              Login Portal ERP
            </a>
          </div>

        </div>
      </div>

    </div>

  </div>
</section>
