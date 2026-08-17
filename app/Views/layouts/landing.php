<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NusantaraNet - Internet Cepat, Stabil & Tanpa Batas untuk Rumah & Bisnis</title>
  <link rel="icon" type="image/png" href="<?php echo Helper::asset('img/favicon.png'); ?>" />

  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />

  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
          },
          boxShadow: {
            'soft-xs': '0 2px 9px -5px rgba(0, 0, 0, 0.15)',
            'soft-sm': '0 5px 10px -5px rgba(0, 0, 0, 0.15)',
            'soft-md': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
            'soft-lg': '0 8px 26px -4px rgba(20, 20, 20, 0.15), 0 8px 9px -5px rgba(20, 20, 20, 0.06)',
            'soft-xl': '0 20px 27px 0 rgba(0, 0, 0, 0.05)',
            'soft-2xl': '0 20px 35px 0 rgba(0, 0, 0, 0.1)',
          }
        }
      }
    }
  </script>

  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .gradient-hero {
      background: linear-gradient(135deg, #090d16 0%, #17143a 50%, #290d38 100%);
    }
    .text-gradient {
      background: linear-gradient(310deg, #7928ca 0%, #ff0080 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }
    .glass-nav {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(16px);
    }
  </style>
</head>

<body class="bg-slate-50 text-slate-700 antialiased">
  
  <!-- Navigation Header -->
  <header class="sticky top-0 z-50 glass-nav border-b border-slate-200/80 transition-all duration-300 shadow-soft-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">
        
        <!-- Brand Logo -->
        <a href="<?php echo Helper::url('landing'); ?>" class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 flex items-center justify-center text-white shadow-soft-md text-lg">
            <i class="fa-solid fa-tower-broadcast"></i>
          </div>
          <div>
            <span class="font-extrabold text-xl text-slate-900 tracking-tight block leading-tight">Nusantara<span class="text-pink-600">Net</span></span>
            <span class="text-2xs text-slate-500 font-semibold tracking-wider uppercase block">Fiber Broadband Provider</span>
          </div>
        </a>

        <!-- Desktop Navigation Links -->
        <nav class="hidden md:flex items-center gap-8">
          <a href="#paket" class="text-xs font-bold uppercase tracking-wider text-slate-600 hover:text-purple-600 transition-colors">Paket Internet</a>
          <a href="#keunggulan" class="text-xs font-bold uppercase tracking-wider text-slate-600 hover:text-purple-600 transition-colors">Keunggulan</a>
          <a href="#coverage" class="text-xs font-bold uppercase tracking-wider text-slate-600 hover:text-purple-600 transition-colors">Area Coverage</a>
          <a href="#kontak" class="text-xs font-bold uppercase tracking-wider text-slate-600 hover:text-purple-600 transition-colors">Bantuan NOC</a>
        </nav>

        <!-- Action / Portal Access -->
        <div class="flex items-center gap-3">
          <?php if (AuthService::check()): ?>
            <a href="<?php echo Helper::url('dashboard'); ?>" class="px-5 py-2.5 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
              <i class="fa-solid fa-gauge-high"></i>
              <span>Ke Dashboard ISP</span>
            </a>
          <?php else: ?>
            <a href="<?php echo Helper::url('login'); ?>" class="px-5 py-2.5 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs uppercase shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
              <i class="fa-solid fa-lock"></i>
              <span>Masuk Aplikasi ISP</span>
            </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>

  <!-- Page Content -->
  <?php echo $content ?? ''; ?>

  <!-- Landing Footer -->
  <footer class="bg-slate-900 text-slate-400 pt-16 pb-12 border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-slate-800">
        
        <div class="space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 flex items-center justify-center text-white text-base">
              <i class="fa-solid fa-tower-broadcast"></i>
            </div>
            <span class="font-bold text-lg text-white">NusantaraNet</span>
          </div>
          <p class="text-xs leading-relaxed text-slate-400">
            Penyedia layanan internet fiber optic berkecepatan tinggi, stabil, dan terjangkau untuk kebutuhan rumah tangga, RT/RW Net, hingga korporat.
          </p>
        </div>

        <div>
          <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Paket Pilihan</h4>
          <ul class="space-y-2 text-xs">
            <li><a href="#paket" class="hover:text-white transition-colors">Home Fiber 20 Mbps</a></li>
            <li><a href="#paket" class="hover:text-white transition-colors">Home Fiber 50 Mbps</a></li>
            <li><a href="#paket" class="hover:text-white transition-colors">Office Fast 100 Mbps</a></li>
            <li><a href="#paket" class="hover:text-white transition-colors">Dedicated Pro 200 Mbps</a></li>
          </ul>
        </div>

        <div>
          <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Coverage Wilayah</h4>
          <ul class="space-y-2 text-xs">
            <li><a href="#coverage" class="hover:text-white transition-colors">Bekasi & Sekitarnya</a></li>
            <li><a href="#coverage" class="hover:text-white transition-colors">Depok & Cibubur</a></li>
            <li><a href="#coverage" class="hover:text-white transition-colors">Bogor & Cibinong</a></li>
            <li><a href="#coverage" class="hover:text-white transition-colors">Jakarta Selatan Hub</a></li>
          </ul>
        </div>

        <div id="kontak">
          <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Kontak & NOC</h4>
          <ul class="space-y-2 text-xs">
            <li class="flex items-center gap-2"><i class="fa-solid fa-location-dot text-pink-500"></i> Jl. Fiber Optik No. 88, Cyber City</li>
            <li class="flex items-center gap-2"><i class="fa-solid fa-phone text-pink-500"></i> 021-88997700</li>
            <li class="flex items-center gap-2"><i class="fa-brands fa-whatsapp text-green-500"></i> 0812-3456-7890 (24/7)</li>
            <li class="pt-2">
              <a href="<?php echo Helper::url('login'); ?>" class="text-purple-400 hover:text-purple-300 font-semibold underline">
                <i class="fa-solid fa-lock text-xs mr-1"></i> Login Staff & Administrator
              </a>
            </li>
          </ul>
        </div>

      </div>

      <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
        <p>&copy; <?php echo date('Y'); ?> PT Nusantara Net Mandiri. All rights reserved.</p>
        <p class="flex items-center gap-1">
          Ditenagai oleh <span class="text-slate-300 font-semibold">NusantaraNet Management System</span>
        </p>
      </div>
    </div>
  </footer>

</body>
</html>
