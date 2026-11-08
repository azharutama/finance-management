<template>
  <NuxtLayout>
    <div class="space-y-6">
      <!-- Modern Clean Carousel Hero -->
      <div class="relative bg-gradient-to-r from-sky-600 via-blue-600 to-sky-700 rounded-2xl p-6 text-white shadow-lg shadow-sky-600/10 overflow-hidden">
        <!-- Abstract gradient shapes -->
        <div class="absolute -right-8 -bottom-8 w-60 h-60 bg-orange-500/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute top-0 right-1/4 w-40 h-40 bg-white/10 rounded-full blur-xl pointer-events-none"></div>

        <div class="relative z-10 max-w-xl">
          <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/15 backdrop-blur-md text-white rounded-full text-[11px] font-bold uppercase tracking-wider mb-2.5 border border-white/20">
            <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
            Sistem Informasi Keuangan
          </div>
          <h1 class="text-2xl font-black tracking-tight">Selamat Datang di Financial Management!</h1>
          <p class="text-sky-100 text-xs mt-1 leading-relaxed">
            Kelola Kategori, Chart of Account (COA), pencatatan transaksi harian, serta Laporan Profit & Loss bulanan dengan export Excel otomatis.
          </p>
        </div>
      </div>

      <!-- Quick Metrics Cards (current month, calculated by backend) -->
      <div class="flex items-center justify-between">
        <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider">Ringkasan Bulan Ini</h2>
        <span class="text-xs font-bold text-slate-500">{{ currentMonthLabel }}</span>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Total Income = SUM(credit) -->
        <MetricCard
          title="Total Income (Credit)"
          :value="formatCurrency(summary?.total_income || 0)"
          :sublabel="`${summary?.credit_count ?? 0} transaksi credit`"
          :loading="loading"
          badge-class="bg-emerald-50 text-emerald-700 border-emerald-200/80"
          icon-box-class="bg-emerald-500/10 text-emerald-600 border-emerald-500/20"
          icon-symbol="&darr;"
        />

        <!-- Total Expense = SUM(debit) -->
        <MetricCard
          title="Total Expense (Debit)"
          :value="formatCurrency(summary?.total_expense || 0)"
          :sublabel="`${summary?.debit_count ?? 0} transaksi debit`"
          :loading="loading"
          badge-class="bg-rose-50 text-rose-700 border-rose-200/80"
          icon-box-class="bg-rose-500/10 text-rose-600 border-rose-500/20"
          icon-symbol="&uarr;"
        />

        <!-- Net Income = credit - debit -->
        <MetricCard
          title="Net Income"
          :value="formatCurrency(summary?.net_income || 0)"
          sublabel="Credit &minus; Debit"
          :loading="loading"
          badge-class="bg-orange-50 text-orange-600 border-orange-200/80"
          icon-box-class="bg-orange-500/10 text-orange-600 border-orange-500/20"
          icon-symbol="&starf;"
          :value-color-class="(summary?.net_income || 0) >= 0 ? 'text-blue-700' : 'text-rose-700'"
        />
      </div>

      <!-- Feature Shortcut Cards (Modern Card Grid Look) -->
      <div>
        <h2 class="text-sm font-black text-slate-800 uppercase tracking-wider mb-3">Modul Utama Sistem</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Transaksi -->
          <NuxtLink to="/transactions" class="bg-white p-5 rounded-2xl border border-slate-200 hover:border-sky-500 hover:shadow-md transition-all group relative overflow-hidden flex flex-col justify-between">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-slate-900 text-sm group-hover:text-sky-600 transition-colors">Daftar Transaksi</h3>
              <p class="text-xs text-slate-500 mt-1">Input pemasukan & pengeluaran berdasarkan COA.</p>
            </div>
            <span class="text-xs text-sky-600 font-bold mt-4 inline-flex items-center gap-1">Buka Menu &rarr;</span>
          </NuxtLink>

          <!-- Laporan P&L -->
          <NuxtLink to="/reports" class="bg-white p-5 rounded-2xl border border-slate-200 hover:border-orange-500 hover:shadow-md transition-all group relative overflow-hidden flex flex-col justify-between">
            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-slate-900 text-sm group-hover:text-orange-600 transition-colors">Laporan Profit & Loss</h3>
              <p class="text-xs text-slate-500 mt-1">Matriks bulanan & unduh laporan Excel.</p>
            </div>
            <span class="text-xs text-orange-600 font-bold mt-4 inline-flex items-center gap-1">Lihat Laporan &rarr;</span>
          </NuxtLink>

          <!-- Kategori -->
          <NuxtLink to="/categories" class="bg-white p-5 rounded-2xl border border-slate-200 hover:border-sky-500 hover:shadow-md transition-all group relative overflow-hidden flex flex-col justify-between">
            <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-slate-900 text-sm group-hover:text-sky-600 transition-colors">Kategori</h3>
              <p class="text-xs text-slate-500 mt-1">Kelompok Salary, Expense, Meal, dsb.</p>
            </div>
            <span class="text-xs text-sky-600 font-bold mt-4 inline-flex items-center gap-1">Kelola Kategori &rarr;</span>
          </NuxtLink>

          <!-- Chart of Account (COA) -->
          <NuxtLink to="/coa" class="bg-white p-5 rounded-2xl border border-slate-200 hover:border-orange-500 hover:shadow-md transition-all group relative overflow-hidden flex flex-col justify-between">
            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-slate-900 text-sm group-hover:text-orange-600 transition-colors">Chart of Account (COA)</h3>
              <p class="text-xs text-slate-500 mt-1">Kode rekening 401, 602, dan akun keuangan.</p>
            </div>
            <span class="text-xs text-orange-600 font-bold mt-4 inline-flex items-center gap-1">Kelola COA &rarr;</span>
          </NuxtLink>
        </div>
      </div>

      <!-- Recent Transactions Table Card -->
      <DataTableCard>
        <template #header>
          <div>
            <h3 class="font-extrabold text-slate-900 text-sm">Transaksi Bulan Ini</h3>
            <p class="text-xs text-slate-400">Mutasi credit dan debit {{ currentMonthLabel }}</p>
          </div>
          <NuxtLink to="/transactions" class="text-xs font-bold text-sky-600 hover:text-sky-700">
            Lihat Semua &rarr;
          </NuxtLink>
        </template>

        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200/60 font-bold">
            <tr>
              <th class="px-6 py-3">Tanggal</th>
              <th class="px-6 py-3">COA Kode</th>
              <th class="px-6 py-3">Nama Akun & Kategori</th>
              <th class="px-6 py-3">Keterangan</th>
              <th class="px-6 py-3 text-right">Debit (Expense)</th>
              <th class="px-6 py-3 text-right">Credit (Income)</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <tr v-if="loading">
              <td colspan="6" class="px-6 py-8 text-center text-slate-400 text-xs">Memuat data transaksi...</td>
            </tr>
            <tr v-else-if="!recentTransactions.length">
              <td colspan="6" class="px-6 py-8 text-center text-slate-400 text-xs">Belum ada transaksi di bulan ini</td>
            </tr>
            <tr v-for="tx in recentTransactions" :key="tx.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="px-6 py-3.5 text-xs font-medium text-slate-600">{{ formatDate(tx.date) }}</td>
              <td class="px-6 py-3.5 text-xs font-mono font-bold text-slate-900">{{ tx.coa?.code || '-' }}</td>
              <td class="px-6 py-3.5 text-xs">
                <div class="font-bold text-slate-800">{{ tx.coa?.name || '-' }}</div>
                <div class="text-[10px] text-slate-400">{{ tx.coa?.category?.name || '-' }}</div>
              </td>
              <td class="px-6 py-3.5 text-xs text-slate-600">{{ tx.description }}</td>
              <td class="px-6 py-3.5 text-xs text-right font-bold text-rose-600">
                {{ tx.debit > 0 ? formatCurrency(tx.debit) : '-' }}
              </td>
              <td class="px-6 py-3.5 text-xs text-right font-bold text-emerald-600">
                {{ tx.credit > 0 ? formatCurrency(tx.credit) : '-' }}
              </td>
            </tr>
          </tbody>
        </table>
      </DataTableCard>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { DashboardSummary, Transaction } from '~/types';
import { formatCurrency, formatDate } from '~/utils/formatters';

const api = useApi();

const loading = ref(true);
const summary = ref<DashboardSummary | null>(null);
const recentTransactions = ref<Transaction[]>([]);

// Current month boundaries (local time) — only this month's data is shown.
const now = new Date();

/**
 * Menambahkan angka 0 di depan digit tunggal untuk pembentukan tanggal YYYY-MM-DD.
 */
const pad = (n: number) => String(n).padStart(2, '0');
const currentMonth = `${now.getFullYear()}-${pad(now.getMonth() + 1)}`;
const monthStart = `${currentMonth}-01`;
const monthEnd = `${currentMonth}-${pad(new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate())}`;
const currentMonthLabel = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(now);

/**
 * Memuat ringkasan KPI bulan berjalan dan 5 transaksi terkini saat halaman dimount.
 */
onMounted(async () => {
  try {
    const [summaryRes, txRes] = await Promise.all([
      api.get<DashboardSummary>('/reports/dashboard-summary', { month: currentMonth }),
      api.get<Transaction[]>('/transactions', { per_page: 5, start_date: monthStart, end_date: monthEnd }),
    ]);

    if (summaryRes.success && summaryRes.data) {
      summary.value = summaryRes.data;
    }
    if (txRes.success && txRes.data) {
      recentTransactions.value = txRes.data;
    }
  } catch (e) {
    console.error('Failed to fetch dashboard data', e);
  } finally {
    loading.value = false;
  }
});
</script>
