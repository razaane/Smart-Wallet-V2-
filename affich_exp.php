<?php
require_once('config.php');
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Expenses Management — Your Smart Wallet</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: { display: ['Manrope', 'sans-serif'] },
          colors: { primary: '#2563eb' },
          borderRadius: { 'md-xl': '0.75rem', '2xl': '1rem' },
          boxShadow: {
            subtle: '0 6px 18px -8px rgba(16,24,40,0.32), 0 2px 6px rgba(2,6,23,0.06)',
            light: '0 4px 10px rgba(2,6,23,0.04)'
          }
        }
      }
    }
  </script>

  <style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    :root { --card-bg: #ffffff; --bg: #f6f8f7; --muted: #6b7280; --accent-light: #2563eb; }
    .dark { --card-bg: #0f1720; --bg: #0b1210; --muted: #9db8a8; --accent-dark-from: #7c3aed; --accent-dark-to: #ec4899; }
    :focus { outline: 2px solid transparent; outline-offset: 2px; }
    .focus-ring:focus { box-shadow: 0 0 0 4px rgba(37,99,235,0.12); border-color: rgba(37,99,235,0.6); }
    body { min-height: 100vh; }
  </style>
</head>
<body class="font-display text-gray-800 bg-[color:var(--bg)] dark:bg-[color:var(--bg)]">

  <script>
    window.uiTheme = {
      toggle: function() {
        const root = document.documentElement;
        const isDark = root.classList.toggle('dark');
        root.classList.toggle('light', !isDark);
        localStorage.setItem('ui-theme', isDark ? 'dark' : 'light');
        const icon = document.querySelector("[data-action='toggle-theme'] span");
        if(icon) icon.textContent = isDark ? 'dark_mode' : 'light_mode';
      }
    };

    (function(){
      const saved = localStorage.getItem('ui-theme') ||
            (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      if(saved === 'dark') document.documentElement.classList.add('dark'); 
      else document.documentElement.classList.add('light');
    })();

    document.addEventListener("click", function(e){
      const el = e.target.closest("[data-action]");
      if(!el) return;
      const action = el.dataset.action;
      if(action==='toggle-theme') window.uiTheme.toggle();
      if(action==='toggle-sidebar') {
        document.getElementById('mobile-sidebar').classList.toggle('hidden');
      }
    });
  </script>

  <div class="flex min-h-screen">
    
    <!-- Sidebar - Desktop (20% width) -->
    <aside class="hidden lg:flex lg:flex-col lg:w-[20%] bg-[color:var(--card-bg)] border-r border-gray-200 dark:border-gray-800 fixed h-full">
      <!-- Logo/Brand -->
      <div class="p-6 border-b border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[color:var(--accent-light)] to-indigo-600 dark:from-[color:var(--accent-dark-from)] dark:to-[color:var(--accent-dark-to)] flex items-center justify-center">
            <span class="material-symbols-outlined text-white text-xl">account_balance_wallet</span>
          </div>
          <span class="font-bold text-lg text-gray-900 dark:text-white">Smart Wallet</span>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 p-4 space-y-2">
        <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-all">
          <span class="material-symbols-outlined">dashboard</span>
          <span class="font-medium">Dashboard</span>
        </a>
        
        <a href="affich_inc.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-all">
          <span class="material-symbols-outlined">trending_up</span>
          <span class="font-medium">View Incomes</span>
        </a>
        
        <a href="affich_exp.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-[color:var(--accent-light)] text-white transition-all hover:opacity-90">
          <span class="material-symbols-outlined">trending_down</span>
          <span class="font-medium">View Expenses</span>
        </a>
      </nav>

      <!-- Theme Toggle at Bottom -->
      <div class="p-4 border-t border-gray-200 dark:border-gray-800">
        <button data-action="toggle-theme" class="flex items-center gap-3 px-4 py-3 rounded-xl w-full text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-all">
          <span class="material-symbols-outlined">light_mode</span>
          <span class="font-medium">Toggle Theme</span>
        </button>
      </div>
    </aside>

    <!-- Mobile Sidebar (Overlay) -->
    <div id="mobile-sidebar" class="hidden fixed inset-0 z-50 lg:hidden">
      <div class="absolute inset-0 bg-black/50" data-action="toggle-sidebar"></div>
      <aside class="absolute left-0 top-0 h-full w-64 bg-[color:var(--card-bg)] border-r border-gray-200 dark:border-gray-800 flex flex-col">
        <!-- Logo/Brand -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[color:var(--accent-light)] to-indigo-600 flex items-center justify-center">
              <span class="material-symbols-outlined text-white text-xl">account_balance_wallet</span>
            </div>
            <span class="font-bold text-lg text-gray-900 dark:text-white">Smart Wallet</span>
          </div>
          <button data-action="toggle-sidebar" class="lg:hidden">
            <span class="material-symbols-outlined text-gray-500">close</span>
          </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 p-4 space-y-2">
          <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-all">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="font-medium">Dashboard</span>
          </a>
          
          <a href="affich_inc.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-all">
            <span class="material-symbols-outlined">trending_up</span>
            <span class="font-medium">View Incomes</span>
          </a>
          
          <a href="affich_exp.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-[color:var(--accent-light)] text-white transition-all">
            <span class="material-symbols-outlined">trending_down</span>
            <span class="font-medium">View Expenses</span>
          </a>
        </nav>

        <!-- Theme Toggle -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-800">
          <button data-action="toggle-theme" class="flex items-center gap-3 px-4 py-3 rounded-xl w-full text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-all">
            <span class="material-symbols-outlined">light_mode</span>
            <span class="font-medium">Toggle Theme</span>
          </button>
        </div>
      </aside>
    </div>

    <!-- Main Content Area (80% on desktop) -->
    <div class="flex-1 lg:ml-[20%]">
      
      <!-- Top Header -->
      <header class="sticky top-0 z-30 backdrop-blur-sm bg-white/60 dark:bg-black/40 border-b border-gray-200 dark:border-gray-800">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-3">
              <button data-action="toggle-sidebar" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/6">
                <span class="material-symbols-outlined">menu</span>
              </button>
              <span class="font-bold text-xl text-gray-900 dark:text-white">Expenses Management</span>
            </div>
            
            <button data-action="toggle-theme" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/6 focus-ring">
              <span class="material-symbols-outlined">light_mode</span>
            </button>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="p-4 sm:p-6 lg:p-8">
        
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Expenses Management</h1>
          <p class="mt-2 text-sm text-gray-500 dark:text-[color:var(--muted)]">View and manage all your expense transactions</p>
        </div>

        <section class="rounded-2xl p-6 bg-[color:var(--card-bg)] border border-gray-100 dark:border-gray-800 shadow-subtle">
          
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">All Expenses</h2>
          </div>

          <div class="overflow-x-auto -mx-6 px-6">
            <table class="w-full text-left">
              <thead class="sticky top-0 bg-white dark:bg-[color:var(--card-bg)]">
                <tr class="border-b border-gray-200 dark:border-gray-800">
                  <th class="px-4 py-4 text-sm font-semibold text-gray-600 dark:text-[color:var(--muted)]">ID</th>
                  <th class="px-4 py-4 text-sm font-semibold text-gray-600 dark:text-[color:var(--muted)]">Amount</th>
                  <th class="px-4 py-4 text-sm font-semibold text-gray-600 dark:text-[color:var(--muted)]">Description</th>
                  <th class="px-4 py-4 text-sm font-semibold text-gray-600 dark:text-[color:var(--muted)]">Date</th>
                  <th class="px-4 py-4 text-sm font-semibold text-gray-600 dark:text-[color:var(--muted)] text-right">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($result_expenses as $row): ?>
                  <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <td class="px-4 py-4 text-gray-700 dark:text-gray-300">
                      <span class="font-medium">#<?= $row['id'] ?></span>
                    </td>
                    <td class="px-4 py-4">
                      <span class="font-bold text-red-600 dark:text-red-400">
                        -$<?= number_format($row['montant'], 2) ?>
                      </span>
                    </td>
                    <td class="px-4 py-4 text-gray-700 dark:text-gray-300">
                      <p class="max-w-xs truncate"><?= htmlspecialchars($row['descreption']) ?></p>
                    </td>
                    <td class="px-4 py-4 text-sm text-gray-500 dark:text-[color:var(--muted)]">
                      <?= date('M d, Y', strtotime($row['la_date'])) ?>
                    </td>
                    <td class="px-4 py-4">
                      <div class="flex justify-end gap-2">
                        <a href="edit_exp.php?id=<?= $row['id'] ?>">
                          <button class="flex items-center gap-1 px-3 py-2 rounded-lg text-white bg-[color:var(--accent-light)] hover:opacity-90 transition shadow-sm">
                            <span class="material-symbols-outlined text-sm">edit</span>
                            <span class="hidden sm:inline text-sm">Edit</span>
                          </button>
                        </a>
                        <a href="delete_exp.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this expense record?');">
                          <button class="flex items-center gap-1 px-3 py-2 rounded-lg text-white bg-red-600 hover:bg-red-700 transition shadow-sm">
                            <span class="material-symbols-outlined text-sm">delete</span>
                            <span class="hidden sm:inline text-sm">Delete</span>
                          </button>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <?php if(empty($result_expenses)): ?>
            <div class="py-12 text-center">
              <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-gray-700">inbox</span>
              <p class="mt-4 text-gray-500 dark:text-[color:var(--muted)]">No expense records found</p>
              <a href="index.php">
                <button class="mt-4 px-6 py-2 rounded-xl text-white" style="background: linear-gradient(90deg,var(--accent-light),#4f46e5);">
                  Add Your First Expense
                </button>
              </a>
            </div>
          <?php endif; ?>

        </section>

      </main>

      <!-- Footer -->
      <footer class="border-t border-gray-100 dark:border-gray-800 bg-white/60 dark:bg-black/40 mt-auto">
        <div class="px-4 sm:px-6 lg:px-8 py-4 text-center text-sm text-gray-500 dark:text-[color:var(--muted)]">
          © 2025 FinanceApp. All Rights Reserved.
        </div>
      </footer>
    </div>

  </div>

</body>
</html>