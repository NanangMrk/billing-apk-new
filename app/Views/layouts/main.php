<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <link rel="icon" type="image/png" href="<?php echo Helper::asset('img/favicon.png'); ?>" />
  <title><?php echo Helper::e($pageTitle ?? 'Dashboard'); ?> - NusantaraNet ISP Management</title>
  
  <!-- Modern Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <!-- Nucleo Icons -->
  <link href="<?php echo Helper::asset('css/nucleo-icons.css'); ?>" rel="stylesheet" />
  <link href="<?php echo Helper::asset('css/nucleo-svg.css'); ?>" rel="stylesheet" />
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <!-- Tailwind CSS CDN with Full Soft UI Configuration -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: {
            sans: ['"Plus Jakarta Sans"', 'Inter', 'system-ui', 'sans-serif'],
          },
          colors: {
            slate: {
              50: "#f8fafc",
              100: "#f1f5f9",
              200: "#e2e8f0",
              300: "#cbd5e1",
              400: "#94a3b8",
              500: "#64748b",
              600: "#475569",
              700: "#334155",
              800: "#1e293b",
              900: "#0f172a",
            },
          },
          boxShadow: {
            'soft-xs': '0 2px 9px -5px rgba(0, 0, 0, 0.15)',
            'soft-sm': '0 5px 10px -5px rgba(0, 0, 0, 0.15)',
            'soft-md': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
            'soft-lg': '0 8px 26px -4px rgba(20, 20, 20, 0.15), 0 8px 9px -5px rgba(20, 20, 20, 0.06)',
            'soft-xl': '0 20px 27px 0 rgba(0, 0, 0, 0.05)',
            'soft-2xl': '0 20px 35px 0 rgba(0, 0, 0, 0.1)',
          },
          borderRadius: {
            'xl': '0.75rem',
            '2xl': '1rem',
            '3xl': '1.5rem',
          }
        }
      }
    }
  </script>

  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background-color: #f8fafc;
      -webkit-tap-highlight-color: transparent;
    }
    .bg-gradient-primary {
      background-image: linear-gradient(310deg, #7928ca 0%, #ff0080 100%);
    }
    .bg-gradient-dark {
      background-image: linear-gradient(310deg, #141727 0%, #3a416f 100%);
    }
    .table-responsive {
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
    }
    /* Scrollbar styling */
    ::-webkit-scrollbar {
      width: 5px;
      height: 5px;
    }
    ::-webkit-scrollbar-track {
      background: transparent;
    }
    ::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 9999px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }
  </style>
</head>

<body class="m-0 font-sans antialiased font-normal text-sm leading-normal bg-slate-50 text-slate-600 min-h-screen">
  
  <!-- Sidebar Partial (Floating Fixed on Desktop) -->
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <!-- Main Container -->
  <div class="xl:ml-72 min-h-screen flex flex-col justify-between transition-all duration-300">
    
    <div>
      <!-- Top Navbar Partial -->
      <?php require __DIR__ . '/../partials/navbar.php'; ?>

      <!-- Main Content Body (With safe bottom spacing for mobile app dock) -->
      <main class="w-full px-4 sm:px-6 py-4 mx-auto max-w-[1600px] pb-24 xl:pb-6">
        
        <!-- Flash Message Alerts -->
        <?php require __DIR__ . '/../partials/alerts.php'; ?>

        <!-- Dynamic Page Content -->
        <?php echo $content ?? ''; ?>

      </main>
    </div>

    <!-- Footer Partial (Hidden on mobile to keep app vibe clean) -->
    <div class="hidden xl:block">
      <?php require __DIR__ . '/../partials/footer.php'; ?>
    </div>
  </div>

  <!-- App-like Mobile Bottom Navigation Dock & Feature Drawer -->
  <?php require __DIR__ . '/../partials/mobile_nav.php'; ?>

  <!-- Interactive Scripts -->
  <script>
    // Desktop / Tablet sidebar toggle handler
    document.addEventListener("DOMContentLoaded", function() {
      const trigger = document.querySelector("[sidenav-trigger]");
      const close = document.querySelector("[sidenav-close]");
      const sidebar = document.querySelector("aside");

      if (trigger && sidebar) {
        trigger.addEventListener("click", function() {
          sidebar.classList.toggle("-translate-x-full");
        });
      }
      if (close && sidebar) {
        close.addEventListener("click", function() {
          sidebar.classList.add("-translate-x-full");
        });
      }
    });
  </script>
</body>
</html>
