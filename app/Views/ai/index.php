<?php
// app/Views/ai/index.php - AI Assistant & Multi-Provider Settings Interface
$activeTab = $activeTab ?? ($_GET['tab'] ?? 'chat');
$currentProvider = $aiSettings['provider'] ?? 'local';
?>

<div class="space-y-6">

  <!-- Header & Tab Navigation -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-5 md:p-6 rounded-3xl border border-slate-200/80 shadow-soft-sm">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-xl font-black shadow-soft-md shrink-0">
        <i class="fa-solid fa-brain"></i>
      </div>
      <div>
        <div class="flex items-center gap-2">
          <h5 class="font-black text-slate-900 text-lg md:text-xl tracking-tight">AI Business & Financial Advisor</h5>
          <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-3xs font-extrabold uppercase bg-purple-50 text-purple-700 border border-purple-200">
            <span class="w-1.5 h-1.5 rounded-full bg-purple-600 animate-pulse"></span>
            <span><?php echo strtoupper($currentProvider); ?></span>
          </span>
        </div>
        <p class="text-slate-500 text-xs mt-0.5">Analisis kelayakan capex, RAB proyek, piutang tertunggak, stok gudang, dan konfigurasi API model cerdas.</p>
      </div>
    </div>

    <!-- Tab Buttons -->
    <div class="flex items-center bg-slate-100/80 p-1.5 rounded-2xl border border-slate-200/80 self-start md:self-auto shrink-0">
      <button type="button" onclick="switchAiTab('chat')" id="tabBtnChat" class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 <?php echo $activeTab === 'chat' ? 'bg-white text-purple-700 shadow-soft-xs' : 'text-slate-600 hover:text-slate-900'; ?>">
        <i class="fa-solid fa-comments"></i>
        <span>Chat & Konsultasi</span>
      </button>
      <button type="button" onclick="switchAiTab('settings')" id="tabBtnSettings" class="px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 <?php echo $activeTab === 'settings' ? 'bg-white text-purple-700 shadow-soft-xs' : 'text-slate-600 hover:text-slate-900'; ?>">
        <i class="fa-solid fa-sliders"></i>
        <span>Pengaturan AI & API</span>
        <?php if ($currentProvider !== 'local'): ?>
          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <?php endif; ?>
      </button>
    </div>
  </div>

  <!-- TAB 1: CHAT & CONSULTATION -->
  <div id="tabContentChat" class="<?php echo $activeTab === 'chat' ? 'block' : 'hidden'; ?> space-y-6">
    
    <!-- Live Operational KPI Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-soft-xs flex items-center justify-between hover:shadow-soft-md transition-all">
        <div>
          <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400 block">Pelanggan Aktif</span>
          <h6 class="text-lg font-black text-slate-900 mt-0.5"><?php echo number_format($stats['active_customers'] ?? 0); ?> <span class="text-xs font-normal text-slate-400">User</span></h6>
        </div>
        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm shadow-soft-xs">
          <i class="fa-solid fa-users"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-soft-xs flex items-center justify-between hover:shadow-soft-md transition-all">
        <div>
          <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400 block">Total Kas & Bank</span>
          <h6 class="text-lg font-black text-purple-700 mt-0.5"><?php echo Helper::formatRupiah($stats['cash_balance'] ?? 0); ?></h6>
        </div>
        <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center text-sm shadow-soft-xs">
          <i class="fa-solid fa-vault"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-soft-xs flex items-center justify-between hover:shadow-soft-md transition-all">
        <div>
          <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400 block">Piutang Berjalan</span>
          <h6 class="text-lg font-black text-rose-600 mt-0.5"><?php echo Helper::formatRupiah($stats['unpaid_invoices_total'] ?? 0); ?></h6>
        </div>
        <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm shadow-soft-xs">
          <i class="fa-solid fa-file-invoice-dollar"></i>
        </div>
      </div>

      <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-soft-xs flex items-center justify-between hover:shadow-soft-md transition-all">
        <div>
          <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400 block">Stok Gudang Menipis</span>
          <h6 class="text-lg font-black <?php echo ($stats['low_stock_count'] ?? 0) > 0 ? 'text-amber-600' : 'text-slate-900'; ?> mt-0.5"><?php echo $stats['low_stock_count'] ?? 0; ?> <span class="text-xs font-normal text-slate-400">Item</span></h6>
        </div>
        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm shadow-soft-xs">
          <i class="fa-solid fa-boxes-stacked"></i>
        </div>
      </div>
    </div>

    <!-- Quick Prompt Category Chips -->
    <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-soft-xs space-y-2">
      <div class="flex items-center justify-between">
        <span class="text-3xs font-extrabold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
          <i class="fa-solid fa-wand-magic-sparkles text-purple-600"></i>
          <span>Rekomendasi Analisis Cepat:</span>
        </span>
        <button type="button" onclick="clearChat()" class="text-3xs font-bold text-slate-400 hover:text-rose-600 transition-colors flex items-center gap-1">
          <i class="fa-solid fa-trash-can"></i>
          <span>Bersihkan Percakapan</span>
        </button>
      </div>

      <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none text-2xs">
        <button type="button" onclick="sendQuickPrompt('Apakah saldo kas aman untuk membeli 1 unit OLT GPON seharga Rp 35.000.000?')" class="px-3 py-1.5 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 font-bold border border-purple-100 transition-all shrink-0 flex items-center gap-1.5">
          <i class="fa-solid fa-server"></i>
          <span>Kelayakan Beli OLT (Capex)</span>
        </button>
        <button type="button" onclick="sendQuickPrompt('Bagaimana rekapitulasi status pengajuan RAB proyek yang masih berstatus submitted dan draft?')" class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 font-bold border border-amber-100 transition-all shrink-0 flex items-center gap-1.5">
          <i class="fa-solid fa-folder-tree"></i>
          <span>Cek RAB & Anggaran Proyek</span>
        </button>
        <button type="button" onclick="sendQuickPrompt('Tampilkan daftar pelanggan yang menunggak dan urutkan berdasarkan jatuh tempo')" class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold border border-rose-100 transition-all shrink-0 flex items-center gap-1.5">
          <i class="fa-solid fa-clock-rotate-left"></i>
          <span>Tagihan Overdue & Piutang</span>
        </button>
        <button type="button" onclick="sendQuickPrompt('Bagaimana performa pembagian pelanggan dan koordinator PIC per wilayah?')" class="px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold border border-blue-100 transition-all shrink-0 flex items-center gap-1.5">
          <i class="fa-solid fa-users-gear"></i>
          <span>Kinerja PIC / RT-RW</span>
        </button>
        <button type="button" onclick="sendQuickPrompt('Barang apa saja di gudang yang sisa stoknya sudah di bawah batas minimum?')" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold border border-emerald-100 transition-all shrink-0 flex items-center gap-1.5">
          <i class="fa-solid fa-box-open"></i>
          <span>Peringatan Stok Gudang</span>
        </button>
        <button type="button" onclick="sendQuickPrompt('Berikan laporan laba rugi dan arus kas masuk keluar untuk bulan ini')" class="px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-bold border border-indigo-100 transition-all shrink-0 flex items-center gap-1.5">
          <i class="fa-solid fa-chart-line"></i>
          <span>Laba Rugi & Cashflow</span>
        </button>
      </div>
    </div>

    <!-- Interactive Chat Window -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft-sm overflow-hidden flex flex-col h-[580px]">
      
      <!-- Chat Message Feed -->
      <div id="chatFeed" class="flex-1 p-4 md:p-6 overflow-y-auto space-y-4 bg-slate-50/40">
        
        <!-- Welcome Message from AI -->
        <div class="flex items-start gap-3 chat-message-ai">
          <div class="w-8 h-8 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-xs font-black shrink-0 shadow-soft-sm mt-0.5">
            <i class="fa-solid fa-robot"></i>
          </div>
          <div class="flex-1 bg-white border border-slate-200/80 rounded-3xl rounded-tl-sm p-4 md:p-5 shadow-soft-xs text-xs space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
              <span class="font-extrabold text-purple-700 text-2xs uppercase tracking-wider flex items-center gap-1.5">
                <i class="fa-solid fa-brain"></i>
                <span>Asisten Cerdas ISP & Finansial</span>
              </span>
              <span class="text-3xs text-slate-400">Hari ini <?php echo date('H:i'); ?></span>
            </div>
            
            <div class="ai-bubble-content leading-relaxed">
              <p class="text-slate-700">Halo! Saya <strong>AI Advisor NusantaraNet</strong>. Saya terhubung langsung ke seluruh data operasional, RAB proyek, stok gudang, dan transaksi kas bank Anda.</p>
              <div class="mt-3 p-3 bg-purple-50/60 rounded-2xl border border-purple-100 text-2xs text-purple-900">
                <span class="font-bold block mb-1">Provider Aktif Saat Ini: <?php echo strtoupper($currentProvider); ?> (<?php echo Helper::e($aiSettings['model'] ?? 'local-engine'); ?>)</span>
                <p class="text-3xs opacity-80">Anda dapat mengubah kecerdasan AI ke OpenAI (GPT-4o), Google Gemini, DeepSeek, Claude, atau Ollama melalui tab <strong>Pengaturan AI & API</strong> di bagian atas.</p>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Typing Indicator -->
      <div id="aiLoadingIndicator" class="hidden px-6 py-2 bg-slate-50/80 border-t border-slate-100 items-center gap-2 text-2xs text-purple-700 font-bold">
        <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
        <span>AI sedang menganalisis data riil sistem & menghitung simulasi...</span>
      </div>

      <!-- Chat Input Area -->
      <div class="p-3 md:p-4 bg-white border-t border-slate-200/80">
        <form method="POST" action="<?php echo Helper::url('ai'); ?>" id="aiForm" onsubmit="handleChatSubmit(event)" class="space-y-2">
          <?php echo Helper::csrfField(); ?>
          <input type="hidden" name="ajax" value="1">
          
          <div class="relative">
            <textarea name="prompt" id="promptInput" rows="2" required placeholder="Tanyakan kelayakan capex, status RAB proyek, piutang tertunggak, persediaan gudang, atau data PIC..." class="w-full text-xs p-3.5 pr-28 rounded-2xl border border-slate-200 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-200 bg-slate-50/50 hover:bg-white focus:bg-white transition-all leading-relaxed resize-none"></textarea>

            <button type="submit" id="submitBtn" class="absolute right-2.5 bottom-2.5 px-4 py-2 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs rounded-xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-1.5">
              <span>Kirim</span>
              <i class="fa-solid fa-paper-plane text-2xs"></i>
            </button>
          </div>

          <div class="flex items-center justify-between text-3xs text-slate-400 px-1">
            <span>Tekan <kbd class="px-1.5 py-0.5 rounded bg-slate-100 font-mono text-slate-600">Enter</kbd> untuk mengirim, <kbd class="px-1.5 py-0.5 rounded bg-slate-100 font-mono text-slate-600">Shift+Enter</kbd> untuk baris baru.</span>
            <span>Aman & Terenkripsi &bull; <?php echo strtoupper($currentProvider); ?></span>
          </div>
        </form>
      </div>

    </div>
  </div>

  <!-- TAB 2: AI & API SETTINGS -->
  <div id="tabContentSettings" class="<?php echo $activeTab === 'settings' ? 'block' : 'hidden'; ?> space-y-6">
    <div class="bg-white p-6 md:p-8 rounded-3xl border border-slate-200/80 shadow-soft-sm space-y-8">
      
      <div>
        <h6 class="font-black text-slate-900 text-lg">Konfigurasi Model AI & Kunci API</h6>
        <p class="text-slate-500 text-xs mt-1">Pilih penyedia kecerdasan buatan (*AI Provider*) yang ingin Anda gunakan. Data operasional NusantaraNet akan otomatis disertakan sebagai konteks real-time saat berkonsultasi.</p>
      </div>

      <form id="aiSettingsForm" method="POST" action="<?php echo Helper::url('ai'); ?>" onsubmit="handleSaveSettings(event)" class="space-y-6">
        <?php echo Helper::csrfField(); ?>
        <input type="hidden" name="action" value="save_settings">

        <!-- 1. Provider Selection Cards -->
        <div class="space-y-3">
          <label class="text-xs font-bold text-slate-700 uppercase tracking-wider block">Pilih Provider AI:</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            
            <!-- Local Engine -->
            <label class="provider-card relative p-4 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between <?php echo $currentProvider === 'local' ? 'border-purple-600 bg-purple-50/40' : 'border-slate-200 hover:border-purple-300 bg-white'; ?>">
              <input type="radio" name="provider" value="local" class="hidden" <?php echo $currentProvider === 'local' ? 'checked' : ''; ?> onchange="updateProviderOptions('local')">
              <div class="flex items-start justify-between">
                <div class="w-10 h-10 rounded-xl bg-slate-800 text-white flex items-center justify-center text-base">
                  <i class="fa-solid fa-server"></i>
                </div>
                <span class="px-2 py-0.5 rounded-full text-3xs font-extrabold uppercase bg-emerald-100 text-emerald-800">Built-in (Gratis)</span>
              </div>
              <div class="mt-3">
                <span class="font-black text-slate-900 text-xs block">Local Database Engine</span>
                <span class="text-3xs text-slate-500 mt-0.5 block">Kalkulasi rule-based lokal cepat tanpa koneksi API eksternal.</span>
              </div>
            </label>

            <!-- Google Gemini -->
            <label class="provider-card relative p-4 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between <?php echo $currentProvider === 'gemini' ? 'border-purple-600 bg-purple-50/40' : 'border-slate-200 hover:border-purple-300 bg-white'; ?>">
              <input type="radio" name="provider" value="gemini" class="hidden" <?php echo $currentProvider === 'gemini' ? 'checked' : ''; ?> onchange="updateProviderOptions('gemini')">
              <div class="flex items-start justify-between">
                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-base">
                  <i class="fa-brands fa-google"></i>
                </div>
                <span class="px-2 py-0.5 rounded-full text-3xs font-extrabold uppercase bg-blue-100 text-blue-800">Direkomendasikan</span>
              </div>
              <div class="mt-3">
                <span class="font-black text-slate-900 text-xs block">Google Gemini</span>
                <span class="text-3xs text-slate-500 mt-0.5 block">Gemini 1.5 Flash / Pro (Cepat, pintar & kuota gratis melimpah).</span>
              </div>
            </label>

            <!-- OpenAI -->
            <label class="provider-card relative p-4 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between <?php echo $currentProvider === 'openai' ? 'border-purple-600 bg-purple-50/40' : 'border-slate-200 hover:border-purple-300 bg-white'; ?>">
              <input type="radio" name="provider" value="openai" class="hidden" <?php echo $currentProvider === 'openai' ? 'checked' : ''; ?> onchange="updateProviderOptions('openai')">
              <div class="flex items-start justify-between">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center text-base">
                  <i class="fa-solid fa-cube"></i>
                </div>
                <span class="px-2 py-0.5 rounded-full text-3xs font-extrabold uppercase bg-slate-100 text-slate-700">OpenAI API</span>
              </div>
              <div class="mt-3">
                <span class="font-black text-slate-900 text-xs block">OpenAI (ChatGPT)</span>
                <span class="text-3xs text-slate-500 mt-0.5 block">GPT-4o, GPT-4o-mini & GPT-3.5-turbo resmi dari OpenAI.</span>
              </div>
            </label>

            <!-- DeepSeek -->
            <label class="provider-card relative p-4 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between <?php echo $currentProvider === 'deepseek' ? 'border-purple-600 bg-purple-50/40' : 'border-slate-200 hover:border-purple-300 bg-white'; ?>">
              <input type="radio" name="provider" value="deepseek" class="hidden" <?php echo $currentProvider === 'deepseek' ? 'checked' : ''; ?> onchange="updateProviderOptions('deepseek')">
              <div class="flex items-start justify-between">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-base">
                  <i class="fa-solid fa-magnifying-glass-chart"></i>
                </div>
                <span class="px-2 py-0.5 rounded-full text-3xs font-extrabold uppercase bg-indigo-100 text-indigo-800">Reasoning</span>
              </div>
              <div class="mt-3">
                <span class="font-black text-slate-900 text-xs block">DeepSeek</span>
                <span class="text-3xs text-slate-500 mt-0.5 block">DeepSeek-V3 & DeepSeek-R1 (Penalaran logika tingkat tinggi).</span>
              </div>
            </label>

            <!-- Anthropic Claude -->
            <label class="provider-card relative p-4 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between <?php echo $currentProvider === 'claude' ? 'border-purple-600 bg-purple-50/40' : 'border-slate-200 hover:border-purple-300 bg-white'; ?>">
              <input type="radio" name="provider" value="claude" class="hidden" <?php echo $currentProvider === 'claude' ? 'checked' : ''; ?> onchange="updateProviderOptions('claude')">
              <div class="flex items-start justify-between">
                <div class="w-10 h-10 rounded-xl bg-amber-600 text-white flex items-center justify-center text-base">
                  <i class="fa-solid fa-sparkles"></i>
                </div>
                <span class="px-2 py-0.5 rounded-full text-3xs font-extrabold uppercase bg-amber-100 text-amber-800">Claude</span>
              </div>
              <div class="mt-3">
                <span class="font-black text-slate-900 text-xs block">Anthropic Claude</span>
                <span class="text-3xs text-slate-500 mt-0.5 block">Claude 3.5 Sonnet & Claude 3.5 Haiku.</span>
              </div>
            </label>

            <!-- Ollama / Self-hosted -->
            <label class="provider-card relative p-4 rounded-2xl border-2 cursor-pointer transition-all flex flex-col justify-between <?php echo $currentProvider === 'ollama' ? 'border-purple-600 bg-purple-50/40' : 'border-slate-200 hover:border-purple-300 bg-white'; ?>">
              <input type="radio" name="provider" value="ollama" class="hidden" <?php echo $currentProvider === 'ollama' ? 'checked' : ''; ?> onchange="updateProviderOptions('ollama')">
              <div class="flex items-start justify-between">
                <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center text-base">
                  <i class="fa-solid fa-network-wired"></i>
                </div>
                <span class="px-2 py-0.5 rounded-full text-3xs font-extrabold uppercase bg-purple-100 text-purple-800">Self-Hosted</span>
              </div>
              <div class="mt-3">
                <span class="font-black text-slate-900 text-xs block">Ollama / Custom API</span>
                <span class="text-3xs text-slate-500 mt-0.5 block">Jalankan LLM di server lokal sendiri (Llama 3.2, Qwen, Mistral).</span>
              </div>
            </label>

          </div>
        </div>

        <!-- 2. Provider Specific Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-slate-200">
          
          <!-- Model Selection -->
          <div class="space-y-2">
            <label class="text-xs font-bold text-slate-700 flex items-center justify-between">
              <span>Model AI:</span>
              <span id="modelHint" class="text-3xs text-slate-400 font-normal">Pilih model yang tersedia</span>
            </label>
            <div class="relative">
              <select name="model" id="modelSelect" class="w-full text-xs p-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-purple-500 font-mono transition-all">
                <!-- Dynamically populated via JS -->
              </select>
            </div>
            <p class="text-3xs text-slate-400">Model menentukan kecepatan respon dan kedalaman analisis data.</p>
          </div>

          <!-- API Key -->
          <div id="apiKeyContainer" class="space-y-2 <?php echo $currentProvider === 'local' ? 'opacity-40 pointer-events-none' : ''; ?>">
            <label class="text-xs font-bold text-slate-700 flex items-center justify-between">
              <span>API Key:</span>
              <a href="#" id="apiKeyHelpLink" target="_blank" class="text-3xs text-purple-600 font-bold hover:underline">Dapatkan API Key &rarr;</a>
            </label>
            <div class="relative">
              <input type="password" name="api_key" id="apiKeyInput" value="<?php echo Helper::e($aiSettings['api_key'] ?? ''); ?>" placeholder="Masukkan API Key (contoh: AIzaSy..., sk-proj-...)" class="w-full text-xs p-3 pr-10 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-purple-500 font-mono transition-all">
              <button type="button" onclick="toggleApiKeyVisibility()" class="absolute right-3 top-3 text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-eye" id="apiKeyEyeIcon"></i>
              </button>
            </div>
            <p class="text-3xs text-slate-400">Kunci API tersimpan dengan aman di database lokal server Anda.</p>
          </div>

          <!-- Custom Base URL (For Ollama/Proxy) -->
          <div id="baseUrlContainer" class="space-y-2">
            <label class="text-xs font-bold text-slate-700 flex items-center justify-between">
              <span>Custom Base URL / Endpoint (Opsional):</span>
              <span class="text-3xs text-slate-400 font-normal">Biarkan kosong untuk default</span>
            </label>
            <input type="text" name="base_url" id="baseUrlInput" value="<?php echo Helper::e($aiSettings['base_url'] ?? ''); ?>" placeholder="https://api.openai.com/v1 atau http://localhost:11434/v1" class="w-full text-xs p-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-purple-500 font-mono transition-all">
            <p class="text-3xs text-slate-400">Gunakan jika Anda memakai Ollama lokal atau endpoint proxy custom.</p>
          </div>

          <!-- Temperature & Max Tokens -->
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="text-xs font-bold text-slate-700 flex items-center justify-between">
                <span>Temperature:</span>
                <span id="tempValue" class="font-mono text-purple-700 font-bold"><?php echo $aiSettings['temperature'] ?? '0.7'; ?></span>
              </label>
              <input type="range" name="temperature" id="tempSlider" min="0.0" max="1.5" step="0.1" value="<?php echo $aiSettings['temperature'] ?? '0.7'; ?>" oninput="document.getElementById('tempValue').innerText = this.value" class="w-full accent-purple-600">
              <div class="flex justify-between text-3xs text-slate-400">
                <span>Presisi (0.1)</span>
                <span>Kreatif (1.5)</span>
              </div>
            </div>

            <div class="space-y-2">
              <label class="text-xs font-bold text-slate-700">Max Tokens:</label>
              <input type="number" name="max_tokens" value="<?php echo $aiSettings['max_tokens'] ?? 2048; ?>" min="256" max="8192" step="256" class="w-full text-xs p-3 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-purple-500 font-mono transition-all">
              <p class="text-3xs text-slate-400">Batas panjang karakter respon.</p>
            </div>
          </div>

        </div>

        <!-- 3. System Prompt / Custom Persona -->
        <div class="space-y-2 pt-4 border-t border-slate-200">
          <div class="flex items-center justify-between">
            <label class="text-xs font-bold text-slate-700">Instruksi Sistem & Persona AI (System Prompt):</label>
            <button type="button" onclick="resetSystemPrompt()" class="text-3xs text-purple-600 hover:underline font-bold">Reset ke Default</button>
          </div>
          <textarea name="system_prompt" id="systemPromptInput" rows="3" class="w-full text-xs p-3.5 rounded-2xl border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-purple-500 leading-relaxed"><?php echo Helper::e($aiSettings['system_prompt'] ?? ''); ?></textarea>
          <p class="text-3xs text-slate-400">Sistem akan secara otomatis menyisipkan data real-time terkini (saldo kas, RAB, tunggakan, stok) ke dalam prompt ini setiap kali Anda berkonsultasi.</p>
        </div>

        <!-- Test Connection Feedback Box -->
        <div id="testFeedbackBox" class="hidden p-4 rounded-2xl text-xs space-y-1"></div>

        <!-- Submit & Test Buttons -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-slate-200">
          <button type="button" id="testConnBtn" onclick="handleTestConnection()" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-50 font-bold text-xs transition-all flex items-center gap-2">
            <i class="fa-solid fa-plug"></i>
            <span>Uji Koneksi API</span>
          </button>

          <div class="flex items-center gap-3">
            <button type="button" onclick="switchAiTab('chat')" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold text-xs transition-all">
              Batal
            </button>
            <button type="submit" id="saveSettingsBtn" class="px-6 py-2.5 bg-gradient-to-tl from-purple-700 to-pink-500 text-white font-bold text-xs rounded-xl shadow-soft-md hover:scale-105 transition-all flex items-center gap-2">
              <i class="fa-solid fa-floppy-disk"></i>
              <span>Simpan Pengaturan</span>
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>

</div>

<!-- AI Dynamic Script -->
<script>
const modelsByProvider = {
  local: [
    { value: "local-engine", label: "Local Database & Financial Rule Engine (Offline)" }
  ],
  gemini: [
    { value: "gemini-1.5-flash", label: "Gemini 1.5 Flash (Sangat Cepat & Efisien - Direkomendasikan)" },
    { value: "gemini-1.5-pro", label: "Gemini 1.5 Pro (Penalaran Kompleks & Konteks Panjang)" },
    { value: "gemini-2.0-flash-exp", label: "Gemini 2.0 Flash Experimental" }
  ],
  openai: [
    { value: "gpt-4o-mini", label: "GPT-4o mini (Cepat, Cerdas & Hemat Biaya)" },
    { value: "gpt-4o", label: "GPT-4o (Kecerdasan Maksimal)" },
    { value: "gpt-3.5-turbo", label: "GPT-3.5 Turbo (Standar)" }
  ],
  deepseek: [
    { value: "deepseek-chat", label: "DeepSeek-V3 (DeepSeek Chat)" },
    { value: "deepseek-reasoner", label: "DeepSeek-R1 (DeepSeek Reasoner)" }
  ],
  claude: [
    { value: "claude-3-5-sonnet-20241022", label: "Claude 3.5 Sonnet (State-of-the-Art)" },
    { value: "claude-3-5-haiku-20241022", label: "Claude 3.5 Haiku (Ringan & Cepat)" }
  ],
  ollama: [
    { value: "llama3.2", label: "Llama 3.2 (Meta)" },
    { value: "qwen2.5", label: "Qwen 2.5 (Alibaba)" },
    { value: "mistral", label: "Mistral 7B" },
    { value: "deepseek-r1:8b", label: "DeepSeek-R1 8B (Local)" }
  ]
};

const apiKeyHelpLinks = {
  local: "#",
  gemini: "https://aistudio.google.com/app/apikey",
  openai: "https://platform.openai.com/api-keys",
  deepseek: "https://platform.deepseek.com/api_keys",
  claude: "https://console.anthropic.com/settings/keys",
  ollama: "https://ollama.com/library"
};

const savedModel = "<?php echo Helper::e($aiSettings['model'] ?? 'local-engine'); ?>";
const savedProvider = "<?php echo Helper::e($aiSettings['provider'] ?? 'local'); ?>";

function switchAiTab(tab) {
  const chatContent = document.getElementById("tabContentChat");
  const settingsContent = document.getElementById("tabContentSettings");
  const btnChat = document.getElementById("tabBtnChat");
  const btnSettings = document.getElementById("tabBtnSettings");

  if (tab === "settings") {
    chatContent.classList.add("hidden");
    settingsContent.classList.remove("hidden");
    btnSettings.className = "px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 bg-white text-purple-700 shadow-soft-xs";
    btnChat.className = "px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 text-slate-600 hover:text-slate-900";
    history.replaceState(null, null, "?page=ai&tab=settings");
  } else {
    settingsContent.classList.add("hidden");
    chatContent.classList.remove("hidden");
    btnChat.className = "px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 bg-white text-purple-700 shadow-soft-xs";
    btnSettings.className = "px-4 py-2 rounded-xl text-xs font-bold transition-all flex items-center gap-2 text-slate-600 hover:text-slate-900";
    history.replaceState(null, null, "?page=ai&tab=chat");
    scrollToBottom();
  }
}

function updateProviderOptions(provider) {
  // Update Radio Card Styles
  document.querySelectorAll(".provider-card").forEach(card => {
    const radio = card.querySelector('input[type="radio"]');
    if (radio.value === provider) {
      card.classList.add("border-purple-600", "bg-purple-50/40");
      card.classList.remove("border-slate-200", "bg-white");
    } else {
      card.classList.remove("border-purple-600", "bg-purple-50/40");
      card.classList.add("border-slate-200", "bg-white");
    }
  });

  // Populate Model Select
  const modelSelect = document.getElementById("modelSelect");
  modelSelect.innerHTML = "";
  const models = modelsByProvider[provider] || modelsByProvider.local;
  models.forEach(m => {
    const opt = document.createElement("option");
    opt.value = m.value;
    opt.textContent = m.label;
    if (m.value === savedModel) opt.selected = true;
    modelSelect.appendChild(opt);
  });

  // API Key & Help Link
  const apiKeyContainer = document.getElementById("apiKeyContainer");
  const apiKeyHelpLink = document.getElementById("apiKeyHelpLink");
  const baseUrlInput = document.getElementById("baseUrlInput");

  if (provider === "local") {
    apiKeyContainer.classList.add("opacity-40", "pointer-events-none");
    apiKeyHelpLink.classList.add("hidden");
  } else {
    apiKeyContainer.classList.remove("opacity-40", "pointer-events-none");
    apiKeyHelpLink.classList.remove("hidden");
    apiKeyHelpLink.href = apiKeyHelpLinks[provider] || "#";
  }

  // Suggest Base URLs
  if (provider === "ollama" && !baseUrlInput.value) {
    baseUrlInput.placeholder = "http://localhost:11434/v1";
  } else if (provider === "deepseek" && !baseUrlInput.value) {
    baseUrlInput.placeholder = "https://api.deepseek.com";
  }
}

function toggleApiKeyVisibility() {
  const input = document.getElementById("apiKeyInput");
  const icon = document.getElementById("apiKeyEyeIcon");
  if (input.type === "password") {
    input.type = "text";
    icon.className = "fa-solid fa-eye-slash";
  } else {
    input.type = "password";
    icon.className = "fa-solid fa-eye";
  }
}

function resetSystemPrompt() {
  document.getElementById("systemPromptInput").value = "Anda adalah AI Business & Financial Advisor untuk ISP (Internet Service Provider) NusantaraNet. Analisis data riil tagihan, kas bank, RAB proyek, stok gudang, dan PIC koordinator wilayah dengan objektif, akurat, dan berikan rekomendasi operasional yang taktis dalam bahasa Indonesia.";
}

// Test Connection Handler
async function handleTestConnection() {
  const form = document.getElementById("aiSettingsForm");
  const formData = new FormData(form);
  formData.set("action", "test_connection");
  formData.set("ajax", "1");

  const testBtn = document.getElementById("testConnBtn");
  const feedbackBox = document.getElementById("testFeedbackBox");
  
  testBtn.disabled = true;
  testBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i><span>Menguji Koneksi...</span>';
  feedbackBox.className = "hidden";

  try {
    const targetUrl = window.location.pathname + '?page=ai';
    const response = await fetch(targetUrl, {
      method: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
      body: formData
    });

    const data = await response.json();
    feedbackBox.classList.remove("hidden");

    if (data.status === "success") {
      feedbackBox.className = "p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs";
      feedbackBox.innerHTML = `<div class="flex items-center gap-2 font-bold"><i class="fa-solid fa-circle-check text-emerald-600"></i><span>${escapeHtml(data.message)}</span></div>`;
    } else {
      feedbackBox.className = "p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs";
      feedbackBox.innerHTML = `<div class="flex items-center gap-2 font-bold"><i class="fa-solid fa-triangle-exclamation text-rose-600"></i><span>${escapeHtml(data.message)}</span></div>`;
    }

    if (data.csrf_token) {
      document.querySelectorAll('input[name="_token"]').forEach(el => el.value = data.csrf_token);
    }
  } catch (e) {
    feedbackBox.classList.remove("hidden");
    feedbackBox.className = "p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs";
    feedbackBox.innerHTML = `<div class="flex items-center gap-2 font-bold"><i class="fa-solid fa-triangle-exclamation text-rose-600"></i><span>Gagal menghubungi server untuk pengujian.</span></div>`;
  } finally {
    testBtn.disabled = false;
    testBtn.innerHTML = '<i class="fa-solid fa-plug"></i><span>Uji Koneksi API</span>';
  }
}

// Save Settings Handler via AJAX
async function handleSaveSettings(e) {
  e.preventDefault();
  const form = document.getElementById("aiSettingsForm");
  const formData = new FormData(form);
  formData.set("action", "save_settings");
  formData.set("ajax", "1");

  const saveBtn = document.getElementById("saveSettingsBtn");
  saveBtn.disabled = true;
  saveBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i><span>Menyimpan...</span>';

  try {
    const targetUrl = window.location.pathname + '?page=ai';
    const response = await fetch(targetUrl, {
      method: "POST",
      headers: { "X-Requested-With": "XMLHttpRequest", "Accept": "application/json" },
      body: formData
    });

    const data = await response.json();
    if (data.status === "success") {
      alert("Pengaturan AI & API berhasil disimpan!");
      if (data.csrf_token) {
        document.querySelectorAll('input[name="_token"]').forEach(el => el.value = data.csrf_token);
      }
      switchAiTab("chat");
    } else {
      alert("Gagal: " + (data.message || "Terjadi kesalahan"));
    }
  } catch (err) {
    // Fallback normal submission if AJAX has issue
    form.submit();
  } finally {
    saveBtn.disabled = false;
    saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i><span>Simpan Pengaturan</span>';
  }
}

// Interactive Chat Script
const promptInput = document.getElementById("promptInput");
const chatFeed = document.getElementById("chatFeed");
const aiLoadingIndicator = document.getElementById("aiLoadingIndicator");
const submitBtn = document.getElementById("submitBtn");

promptInput?.addEventListener("keydown", function(e) {
  if (e.key === "Enter" && !e.shiftKey) {
    e.preventDefault();
    document.getElementById("aiForm").dispatchEvent(new Event("submit", { cancelable: true }));
  }
});

function scrollToBottom() {
  if (chatFeed) chatFeed.scrollTop = chatFeed.scrollHeight;
}

function sendQuickPrompt(text) {
  if (promptInput) {
    promptInput.value = text;
    document.getElementById("aiForm").dispatchEvent(new Event("submit", { cancelable: true }));
  }
}

function clearChat() {
  if (confirm("Reset percakapan dan kembali ke pesan pembuka?")) {
    const messages = chatFeed.querySelectorAll(".chat-message-user, .chat-message-ai:not(:first-child)");
    messages.forEach(m => m.remove());
    if (promptInput) {
      promptInput.value = "";
      promptInput.focus();
    }
  }
}

async function handleChatSubmit(e) {
  e.preventDefault();
  const prompt = promptInput.value.trim();
  if (!prompt) return;

  const now = new Date();
  const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');

  const userBubble = document.createElement("div");
  userBubble.className = "flex items-start justify-end gap-3 chat-message-user";
  userBubble.innerHTML = `
    <div class="max-w-xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white rounded-3xl rounded-tr-sm p-4 shadow-soft-md text-xs leading-relaxed">
      ${escapeHtml(prompt)}
    </div>
    <div class="w-8 h-8 rounded-2xl bg-slate-800 text-white flex items-center justify-center text-xs font-black shrink-0 shadow-soft-sm mt-0.5">
      <i class="fa-solid fa-user"></i>
    </div>
  `;
  chatFeed.appendChild(userBubble);
  promptInput.value = "";
  promptInput.disabled = true;
  submitBtn.disabled = true;
  submitBtn.classList.add("opacity-50", "cursor-not-allowed");

  aiLoadingIndicator.classList.remove("hidden");
  aiLoadingIndicator.classList.add("flex");
  scrollToBottom();

  try {
    const aiForm = document.getElementById("aiForm");
    const formData = new FormData(aiForm);
    formData.set("prompt", prompt);
    formData.set("ajax", "1");

    const targetUrl = window.location.pathname + '?page=ai';

    const response = await fetch(targetUrl, {
      method: "POST",
      headers: {
        "X-Requested-With": "XMLHttpRequest",
        "Accept": "application/json"
      },
      body: formData
    });

    const resText = await response.text();
    let data;
    try {
      data = JSON.parse(resText);
    } catch (parseErr) {
      console.error("Server raw response:", resText);
      throw new Error("Respon server bukan format JSON yang valid.");
    }

    aiLoadingIndicator.classList.add("hidden");
    aiLoadingIndicator.classList.remove("flex");

    if (data.csrf_token) {
      document.querySelectorAll('input[name="_token"]').forEach(el => el.value = data.csrf_token);
    }

    if (data.status === "success") {
      const aiBubble = document.createElement("div");
      aiBubble.className = "flex items-start gap-3 chat-message-ai";
      aiBubble.innerHTML = `
        <div class="w-8 h-8 rounded-2xl bg-gradient-to-tl from-purple-700 to-pink-500 text-white flex items-center justify-center text-xs font-black shrink-0 shadow-soft-sm mt-0.5">
          <i class="fa-solid fa-robot"></i>
        </div>
        <div class="flex-1 bg-white border border-slate-200/80 rounded-3xl rounded-tl-sm p-4 md:p-5 shadow-soft-xs text-xs space-y-3">
          <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <span class="font-extrabold text-purple-700 text-2xs uppercase tracking-wider flex items-center gap-1.5">
              <i class="fa-solid fa-brain"></i>
              <span>${escapeHtml(data.title || "Tanggapan AI Advisor")}</span>
            </span>
            <span class="text-3xs text-slate-400">${data.timestamp || timeStr}</span>
          </div>
          <div class="ai-bubble-content leading-relaxed">
            ${data.content}
          </div>
        </div>
      `;
      chatFeed.appendChild(aiBubble);
    } else {
      const errBubble = document.createElement("div");
      errBubble.className = "flex items-start gap-3 chat-message-ai";
      errBubble.innerHTML = `
        <div class="w-8 h-8 rounded-2xl bg-rose-600 text-white flex items-center justify-center text-xs font-black shrink-0 shadow-soft-sm mt-0.5">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="flex-1 bg-rose-50 border border-rose-200 rounded-3xl rounded-tl-sm p-4 text-xs text-rose-800">
          <p class="font-bold">Gagal memproses pertanyaan</p>
          <p class="text-2xs mt-1">${escapeHtml(data.message || "Terjadi kendala saat memproses pertanyaan. Silakan coba kembali.")}</p>
        </div>
      `;
      chatFeed.appendChild(errBubble);
    }
  } catch (err) {
    aiLoadingIndicator.classList.add("hidden");
    aiLoadingIndicator.classList.remove("flex");

    const errBubble = document.createElement("div");
    errBubble.className = "flex items-start gap-3 chat-message-ai";
    errBubble.innerHTML = `
      <div class="w-8 h-8 rounded-2xl bg-rose-600 text-white flex items-center justify-center text-xs font-black shrink-0 shadow-soft-sm mt-0.5">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
      <div class="flex-1 bg-rose-50 border border-rose-200 rounded-3xl rounded-tl-sm p-4 text-xs text-rose-800">
        <p class="font-bold">Pemberitahuan</p>
        <p class="text-2xs mt-1">${escapeHtml(err.message || "Tidak dapat menghubungi server AI. Silakan muat ulang halaman.")}</p>
      </div>
    `;
    chatFeed.appendChild(errBubble);
  } finally {
    promptInput.disabled = false;
    submitBtn.disabled = false;
    submitBtn.classList.remove("opacity-50", "cursor-not-allowed");
    promptInput.focus();
    scrollToBottom();
  }
}

function escapeHtml(text) {
  if (!text) return "";
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Initial setup
document.addEventListener("DOMContentLoaded", function() {
  updateProviderOptions(savedProvider);
  scrollToBottom();
});
</script>
