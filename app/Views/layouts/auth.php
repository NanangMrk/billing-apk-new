<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo Helper::e($pageTitle ?? 'Masuk'); ?> - NusantaraNet ISP Management</title>
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
      background-color: #f8fafc;
    }
  </style>
</head>

<body class="bg-slate-50 min-h-screen flex flex-col justify-between text-slate-600 antialiased">
  
  <!-- Header / Nav link -->
  <div class="max-w-7xl mx-auto w-full px-6 py-6 flex justify-between items-center">
    <a href="<?php echo Helper::url('landing'); ?>" class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-base shadow-soft-md">
        <i class="fa-solid fa-tower-broadcast"></i>
      </div>
      <div>
        <span class="font-black text-lg text-slate-900 leading-tight block tracking-tight">Nusantara<span class="text-pink-600">Net</span></span>
      </div>
    </a>

    <a href="<?php echo Helper::url('landing'); ?>" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 hover:text-purple-700 font-bold text-xs shadow-soft-xs transition-colors flex items-center gap-1.5">
      <i class="fa-solid fa-arrow-left text-2xs"></i>
      <span>Halaman Utama</span>
    </a>
  </div>

  <!-- Centered Login Box -->
  <main class="my-auto px-4 py-8">
    <div class="max-w-md mx-auto">
      <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-soft-2xl space-y-6">
        
        <!-- Flash Alert -->
        <?php require __DIR__ . '/../partials/alerts.php'; ?>

        <?php echo $content ?? ''; ?>

      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="py-6 text-center text-xs text-slate-400">
    <p>&copy; <?php echo date('Y'); ?> PT Nusantara Net Mandiri &bull; All rights reserved.</p>
  </footer>

</body>
</html>
