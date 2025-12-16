<?php
require_once "config.php";
require_once "auth.php";
require_once "traitement.php";

// Require authentication - redirect to login if not logged in
requireAuth();

// Get current user
$currentUser = getCurrentUser($pdo);
$userId = getCurrentUserId();

// Get user initials for avatar
$userInitials = getUserInitials($currentUser['fullname'], $currentUser['username']);

// Get user-specific totals
$stmt_rev = $pdo->prepare("SELECT SUM(montant) AS total_rev FROM incomes WHERE user_id = ?");
$stmt_rev->execute([$userId]);
$total_rev = $stmt_rev->fetch(PDO::FETCH_ASSOC)['total_rev'];
if(!$total_rev) $total_rev = 0; 

$stmt_exp = $pdo->prepare("SELECT SUM(montant) AS total_exp FROM expenses WHERE user_id = ?");
$stmt_exp->execute([$userId]);
$total_exp = $stmt_exp->fetch(PDO::FETCH_ASSOC)['total_exp'];
if(!$total_exp) $total_exp = 0; 

$balance = $total_rev - $total_exp;

// Get monthly data for chart (user-specific)
$current_year = date('Y');
$monthly_data = [];

for($month = 1; $month <= 12; $month++) {
  // Incomes for this user
  $stmt_inc = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) AS total FROM incomes WHERE user_id = ? AND YEAR(la_date) = ? AND MONTH(la_date) = ?");
  $stmt_inc->execute([$userId, $current_year, $month]);
  $month_income = $stmt_inc->fetch(PDO::FETCH_ASSOC)['total'];
  
  // Expenses for this user
  $stmt_exp = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) AS total FROM expenses WHERE user_id = ? AND YEAR(la_date) = ? AND MONTH(la_date) = ?");
  $stmt_exp->execute([$userId, $current_year, $month]);
  $month_expense = $stmt_exp->fetch(PDO::FETCH_ASSOC)['total'];
  
  $monthly_data[] = [
    'month' => date('M', mktime(0, 0, 0, $month, 1)),
    'income' => floatval($month_income),
    'expense' => floatval($month_expense)
  ];
}
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Dashboard — Your Smart Wallet</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    const monthlyData = <?= json_encode($monthly_data) ?>;
    
    window.uiTheme = {
      toggle: function() {
        const root = document.documentElement;
        const isDark = root.classList.toggle('dark');
        root.classList.toggle('light', !isDark);
        localStorage.setItem('ui-theme', isDark ? 'dark' : 'light');
        const icon = document.querySelector("[data-action='toggle-theme'] span");
        if(icon) icon.textContent = isDark ? 'dark_mode' : 'light_mode';
        
        if(window.financeChart) {
          updateChartTheme(isDark);
        }
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

    window.modal = {
      open(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove("hidden");
      },
      close(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.add("hidden");
      }
    };

    document.addEventListener("click", function(e) {
      const el = e.target.closest("[data-action]");
      if (!el) return;
      const action = el.dataset.action;
      const target = el.dataset.target;
      if (!action || !target) return;
      if (action === "open") window.modal.open(target);
      if (action === "close") window.modal.close(target);
    });

    function updateChartTheme(isDark) {
      const textColor = isDark ? '#9db8a8' : '#6b7280';
      const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
      
      window.financeChart.options.scales.x.ticks.color = textColor;
      window.financeChart.options.scales.y.ticks.color = textColor;
      window.financeChart.options.scales.x.grid.color = gridColor;
      window.financeChart.options.scales.y.grid.color = gridColor;
      window.financeChart.options.plugins.legend.labels.color = textColor;
      window.financeChart.update();
    }

    window.addEventListener('DOMContentLoaded', function() {
      const ctx = document.getElementById('financeChart');
      if(!ctx) return;
      
      const isDark = document.documentElement.classList.contains('dark');
      const textColor = isDark ? '#9db8a8' : '#6b7280';
      const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
      
      window.financeChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: monthlyData.map(d => d.month),
          datasets: [
            {
              label: 'Incomes',
              data: monthlyData.map(d => d.income),
              backgroundColor: 'rgba(34, 197, 94, 0.7)',
              borderColor: 'rgb(34, 197, 94)',
              borderWidth: 2,
              borderRadius: 8
            },
            {
              label: 'Expenses',
              data: monthlyData.map(d => d.expense),
              backgroundColor: 'rgba(239, 68, 68, 0.7)',
              borderColor: 'rgb(239, 68, 68)',
              borderWidth: 2,
              borderRadius: 8
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false,
          },
          plugins: {
            legend: {
              position: 'top',
              labels: {
                color: textColor,
                font: {
                  family: 'Manrope',
                  size: 12,
                  weight: '500'
                },
                padding: 15,
                usePointStyle: true,
                pointStyle: 'circle'
              }
            },
            tooltip: {
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              titleFont: {
                family: 'Manrope',
                size: 14,
                weight: '600'
              },
              bodyFont: {
                family: 'Manrope',
                size: 13
              },
              padding: 12,
              borderColor: 'rgba(255, 255, 255, 0.1)',
              borderWidth: 1,
              callbacks: {
                label: function(context) {
                  return context.dataset.label + ': $' + context.parsed.y.toFixed(2);
                }
              }
            }
          },
          scales: {
            x: {
              grid: {
                display: false
              },
              ticks: {
                color: textColor,
                font: {
                  family: 'Manrope',
                  size: 11
                }
              }
            },
            y: {
              beginAtZero: true,
              grid: {
                color: gridColor,
                drawBorder: false
              },
              ticks: {
                color: textColor,
                font: {
                  family: 'Manrope',
                  size: 11
                },
                callback: function(value) {
                  return '$' + value.toFixed(0);
                }
              }
            }
          }
        }
      });
    });
  </script>

  <div class="flex min-h-screen">
    
    <!-- Sidebar - Desktop (20% width) -->
    <aside class="hidden lg:flex lg:flex-col lg:w-[20%] bg-[color:var(--card-bg)] border-r border-gray-200 dark:border-gray-800 fixed h-full">
      <div class="p-6 border-b border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[color:var(--accent-light)] to-indigo-600 dark:from-[color:var(--accent-dark-from)] dark:to-[color:var(--accent-dark-to)] flex items-center justify-center">
            <span class="material-symbols-outlined text-white text-xl">account_balance_wallet</span>
          </div>
          <span class="font-bold text-lg text-gray-900 dark:text-white">Smart Wallet</span>
        </div>
      </div>

      <nav class="flex-1 p-4 space-y-2">
        <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-[color:var(--accent-light)] text-white transition-all hover:opacity-90">
          <span class="material-symbols-outlined">dashboard</span>
          <span class="font-medium">Dashboard</span>
        </a>
        
        <a href="affich_inc.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-all">
          <span class="material-symbols-outlined">trending_up</span>
          <span class="font-medium">View Incomes</span>
        </a>
        
        <a href="affich_exp.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-all">
          <span class="material-symbols-outlined">trending_down</span>
          <span class="font-medium">View Expenses</span>
        </a>
      </nav>

      <!-- User Profile Section -->
      <div class="p-4 border-t border-gray-200 dark:border-gray-800">
        <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-white/5 mb-3">
          <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[color:var(--accent-light)] to-indigo-600 flex items-center justify-center flex-shrink-0">
            <span class="text-white font-semibold text-sm"><?= $userInitials ?></span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
              <?= htmlspecialchars($currentUser['fullname'] ?: $currentUser['username']) ?>
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?= htmlspecialchars($currentUser['email']) ?></p>
          </div>
          <a href="logout.php" class="p-2 hover:bg-gray-200 dark:hover:bg-white/10 rounded-lg transition-colors" title="Logout">
            <span class="material-symbols-outlined text-gray-600 dark:text-gray-400 text-xl">logout</span>
          </a>
        </div>
      </div>

      <!-- Theme Toggle -->
      <div class="p-4 border-t border-gray-200 dark:border-gray-800">
        <button data-action="toggle-theme" class="flex items-center gap-3 px-4 py-3 rounded-xl w-full text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition-all">
          <span class="material-symbols-outlined">light_mode</span>
          <span class="font-medium">Toggle Theme</span>
        </button>
      </div>
    </aside>

    <!-- Mobile Sidebar -->
    <div id="mobile-sidebar" class="hidden fixed inset-0 z-50 lg:hidden">
      <div class="absolute inset-0 bg-black/50" data-action="toggle-sidebar"></div>
      <aside class="absolute left-0 top-0 h-full w-64 bg-[color:var(--card-bg)] border-r border-gray-200 dark:border-gray-800 flex flex-col">
        <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[color:var(--accent-light)] to-indigo-600 flex items-center justify-center">
              <span class="material-symbols-outlined text-white text-xl">account_balance_wallet</span>
            </div>
            <span class="font-bold text-lg text-gray-900 dark:text-white">Smart Wallet</span>
          </div>
          <button data-action="toggle-sidebar">
            <span class="material-symbols-outlined text-gray-500">close</span>
          </button>
        </div>

        <nav class="flex-1 p-4 space-y-2">
          <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-[color:var(--accent-light)] text-white">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="font-medium">Dashboard</span>
          </a>
          
          <a href="affich_inc.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5">
            <span class="material-symbols-outlined">trending_up</span>
            <span class="font-medium">View Incomes</span>
          </a>
          
          <a href="affich_exp.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5">
            <span class="material-symbols-outlined">trending_down</span>
            <span class="font-medium">View Expenses</span>
          </a>
        </nav>

        <!-- User Profile -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-800">
          <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-white/5 mb-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[color:var(--accent-light)] to-indigo-600 flex items-center justify-center">
              <span class="text-white font-semibold text-sm"><?= $userInitials ?></span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                <?= htmlspecialchars($currentUser['fullname'] ?: $currentUser['username']) ?>
              </p>
              <p class="text-xs text-gray-500 dark:text-gray-400 truncate"><?= htmlspecialchars($currentUser['email']) ?></p>
            </div>
            <a href="logout.php" class="p-2 hover:bg-gray-200 dark:hover:bg-white/10 rounded-lg" title="Logout">
              <span class="material-symbols-outlined text-gray-600 dark:text-gray-400 text-xl">logout</span>
            </a>
          </div>
        </div>

        <!-- Theme Toggle -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-800">
          <button data-action="toggle-theme" class="flex items-center gap-3 px-4 py-3 rounded-xl w-full text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5">
            <span class="material-symbols-outlined">light_mode</span>
            <span class="font-medium">Toggle Theme</span>
          </button>
        </div>
      </aside>
    </div>

    <!-- Main Content -->
    <div class="flex-1 lg:ml-[20%]">
      <header class="sticky top-0 z-30 backdrop-blur-sm bg-white/60 dark:bg-black/40 border-b border-gray-200 dark:border-gray-800">
        <div class="px-4 sm:px-6 lg:px-8">
          <div class="flex items-center justify-between h-16">
            <div class="flex items-center gap-3">
              <button data-action="toggle-sidebar" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/6">
                <span class="material-symbols-outlined">menu</span>
              </button>
              <span class="font-bold text-xl text-gray-900 dark:text-white">Dashboard</span>
            </div>
            
            <button data-action="toggle-theme" class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/6 focus-ring">
              <span class="material-symbols-outlined">light_mode</span>
            </button>
          </div>
        </div>
      </header>

      <main class="p-4 sm:p-6 lg:p-8">
        
        <!-- Stats Cards -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
          <div class="rounded-2xl bg-[color:var(--card-bg)] p-6 border border-gray-100 dark:border-gray-800 shadow-subtle transition hover:shadow-lg">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-sm text-gray-500 dark:text-[color:var(--muted)]">Total Revenues</p>
                <p class="mt-2 text-2xl font-bold text-green-700 dark:text-green-400">$<?= number_format($total_rev, 2) ?></p>
              </div>
              <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-green-50 dark:bg-green-900/30">
                <span class="material-symbols-outlined text-green-600 dark:text-green-400">payments</span>
              </div>
            </div>
          </div>

          <div class="rounded-2xl bg-[color:var(--card-bg)] p-6 border border-gray-100 dark:border-gray-800 shadow-subtle transition hover:shadow-lg">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-sm text-gray-500 dark:text-[color:var(--muted)]">Total Expenses</p>
                <p class="mt-2 text-2xl font-bold text-red-700 dark:text-red-400">$<?= number_format($total_exp, 2) ?></p>
              </div>
              <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-red-50 dark:bg-red-900/30">
                <span class="material-symbols-outlined text-red-600 dark:text-red-400">arrow_downward</span>
              </div>
            </div>
          </div>

          <div class="rounded-2xl bg-[color:var(--card-bg)] p-6 border border-gray-100 dark:border-gray-800 shadow-subtle transition hover:shadow-lg">
            <div class="flex items-start justify-between gap-4">
              <div>
                <p class="text-sm text-gray-500 dark:text-[color:var(--muted)]">Current Balance</p>
                <p class="mt-2 text-2xl font-bold text-[color:var(--accent-light)] dark:text-transparent bg-clip-text dark:bg-gradient-to-r dark:from-[color:var(--accent-dark-from)] dark:to-[color:var(--accent-dark-to)]">$<?= number_format($balance, 2) ?></p>
              </div>
              <div class="w-12 h-12 rounded-xl flex items-center justify-center bg-blue-50 dark:bg-blue-900/30">
                <span class="material-symbols-outlined text-blue-600 dark:text-blue-400">savings</span>
              </div>
            </div>
          </div>
        </section>

        <!-- Chart + Quick Actions -->
        <section class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
          <div class="xl:col-span-2 rounded-2xl p-6 bg-[color:var(--card-bg)] border border-gray-100 dark:border-gray-800 shadow-subtle">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Monthly Overview</h2>
              <span class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-white/6 text-sm text-gray-700 dark:text-[color:var(--muted)]">Year <?= $current_year ?></span>
            </div>
            <div class="w-full" style="height: 300px;">
              <canvas id="financeChart"></canvas>
            </div>
          </div>
          
          <aside class="rounded-2xl p-6 bg-[color:var(--card-bg)] border border-gray-100 dark:border-gray-800 shadow-subtle flex flex-col gap-4">
            <h3 class="text-xl text-center font-semibold text-gray-900 dark:text-white">Quick Actions</h3>
            <div class="flex flex-col justify-center gap-4 mt-4">
              <button data-action="open" data-target="modal-income" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl text-white hover:opacity-95 transition" style="background: linear-gradient(90deg,var(--accent-light),#4f46e5);">
                <span class="material-symbols-outlined">add</span> New Income
              </button>
              <button data-action="open" data-target="modal-expense" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-[color:var(--muted)] hover:bg-gray-50 dark:hover:bg-white/5 transition">
                <span class="material-symbols-outlined">remove</span> New Expense
              </button>
            </div>
          </aside>
        </section>

        <!-- Recent Transactions -->
        <section class="rounded-2xl p-6 bg-[color:var(--card-bg)] border border-gray-100 dark:border-gray-800 shadow-subtle mb-8">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Transactions</h3>
          <div class="overflow-x-auto">
            <table class="w-full text-left">
              <thead>
                <tr class="border-b border-gray-200 dark:border-gray-800">
                  <th class="px-4 py-3 text-sm font-medium text-gray-500 dark:text-[color:var(--muted)]">Description</th>
                  <th class="px-4 py-3 text-sm font-medium text-gray-500 dark:text-[color:var(--muted)] text-right">Amount</th>
                  <th class="px-4 py-3 text-sm font-medium text-gray-500 dark:text-[color:var(--muted)]">Date</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Get user's recent transactions
                $recent_query = "
                  (SELECT 'income' as type, montant, descreption, la_date FROM incomes WHERE user_id = ? ORDER BY la_date DESC LIMIT 5)
                  UNION ALL
                  (SELECT 'expense' as type, montant, descreption, la_date FROM expenses WHERE user_id = ? ORDER BY la_date DESC LIMIT 5)
                  ORDER BY la_date DESC LIMIT 10
                ";
                $recent_stmt = $pdo->prepare($recent_query);
                $recent_stmt->execute([$userId, $userId]);
                $recent_transactions = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if(empty($recent_transactions)):
                ?>
                  <tr>
                    <td colspan="3" class="px-4 py-8 text-center text-gray-500 dark:text-[color:var(--muted)]">
                      No transactions yet. Start by adding your first income or expense!
                    </td>
                  </tr>
                <?php else: ?>
                  <?php foreach($recent_transactions as $trans): ?>
                    <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                      <td class="px-4 py-4">
                        <p class="font-semibold text-gray-900 dark:text-white"><?= htmlspecialchars($trans['descreption']) ?></p>
                      </td>
                      <td class="px-4 py-4 text-right font-semibold <?= $trans['type'] === 'income' ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' ?>">
                        <?= $trans['type'] === 'income' ? '+' : '-' ?>$<?= number_format($trans['montant'], 2) ?>
                      </td>
                      <td class="px-4 py-4 text-sm text-gray-500 dark:text-[color:var(--muted)]">
                        <?= date('M d, Y', strtotime($trans['la_date'])) ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </section>

      </main>

      <!-- Footer -->
      <footer class="border-t border-gray-100 dark:border-gray-800 bg-white/60 dark:bg-black/40">
        <div class="px-4 sm:px-6 lg:px-8 py-4 text-center text-sm text-gray-500 dark:text-[color:var(--muted)]">
          © 2025 FinanceApp. All Rights Reserved.
        </div>
      </footer>
    </div>

    <!-- Modals -->
    <div id="modal-income" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" data-action="close" data-target="modal-income"></div>
      <div class="relative w-full max-w-lg rounded-2xl bg-[color:var(--card-bg)] p-6 shadow-lg border border-gray-100 dark:border-gray-800">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add Income</h3>
          <button class="p-2 rounded-md text-gray-500 dark:text-[color:var(--muted)] hover:bg-gray-100 dark:hover:bg-white/5" data-action="close" data-target="modal-income">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <form method="POST" action="traitement.php" class="grid grid-cols-1 gap-3">
          <div>
            <label class="text-sm text-gray-600 dark:text-[color:var(--muted)]">Date</label>
            <input type="date" name="date_inc" class="mt-1 w-full rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-2 focus-ring" required />
          </div>
          <div>
            <label class="text-sm text-gray-600 dark:text-[color:var(--muted)]">Amount</label>
            <input type="number" name="amount_inc" step="0.01" class="mt-1 w-full rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-2 focus-ring" required />
          </div>
          <div>
            <label class="text-sm text-gray-600 dark:text-[color:var(--muted)]">Description</label>
            <textarea name="descreption_inc" class="h-20 w-full rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-2 focus-ring" required></textarea>
          </div>
          <button type="submit" name="save_inc" class="px-4 py-2 rounded-xl text-white hover:opacity-90 transition" style="background: linear-gradient(90deg,var(--accent-light),#4f46e5);">Save</button>
        </form>
      </div>
    </div>

    <div id="modal-expense" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" data-action="close" data-target="modal-expense"></div>
      <div class="relative w-full max-w-lg rounded-2xl bg-[color:var(--card-bg)] p-6 shadow-lg border border-gray-100 dark:border-gray-800">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Add Expense</h3>
          <button class="p-2 rounded-md text-gray-500 dark:text-[color:var(--muted)] hover:bg-gray-100 dark:hover:bg-white/5" data-action="close" data-target="modal-expense">
            <span class="material-symbols-outlined">close</span>
          </button>
        </div>
        <form method="POST" action="traitement.php" class="grid grid-cols-1 gap-3">
          <div>
            <label class="text-sm text-gray-600 dark:text-[color:var(--muted)]">Date</label>
            <input type="date" name="date_exp" class="mt-1 w-full rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-2 focus-ring" required />
          </div>
          <div>
            <label class="text-sm text-gray-600 dark:text-[color:var(--muted)]">Amount</label>
            <input type="number" name="amount_exp" step="0.01" class="mt-1 w-full rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-2 focus-ring" required />
          </div>
          <div>
            <label class="text-sm text-gray-600 dark:text-[color:var(--muted)]">Description</label>
            <textarea name="descreption_exp" class="h-20 w-full rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white px-3 py-2 focus-ring" required></textarea>
          </div>
          <button type="submit" name="save_exp" class="px-4 py-2 rounded-xl text-white hover:opacity-90 transition" style="background: linear-gradient(90deg,var(--accent-light),#4f46e5);">Save</button>
        </form>
      </div>
    </div>

  </div>

</body>
</html>