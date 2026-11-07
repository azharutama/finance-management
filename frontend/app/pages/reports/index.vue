<template>
  <NuxtLayout>
    <div class="space-y-6">
      <!-- Header -->
      <PageHeader
        title="Laporan Profit & Loss"
        description="Ringkasan pemasukan, pengeluaran, dan laba bersih bulanan dengan style visual spreadsheet"
      >
        <template #actions>
          <button
            @click="handleExportExcel"
            :disabled="exporting"
            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-sm shadow-emerald-600/20 transition-all cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            {{ exporting ? 'Mengunduh...' : 'Export Excel (.xlsx)' }}
          </button>
        </template>
      </PageHeader>

      <!-- Filter Periode Bar (Component) -->
      <FilterCard>
        <FilterPeriod
          v-model:start-month="startMonth"
          v-model:end-month="endMonth"
          :loading="loading"
          @submit="fetchReport"
        />
        <div v-if="filterError" class="w-full text-xs text-rose-600 font-medium mt-1">
          {{ filterError }}
        </div>
      </FilterCard>

      <!-- Report Table Matrix (Spreadsheet Style) -->
      <DataTableCard>
        <div v-if="loading" class="p-12 text-center text-slate-400 text-xs font-medium">
          Menghitung dan memuat laporan Profit & Loss...
        </div>

        <table v-else-if="report" class="w-full text-left text-xs border-collapse">
          <!-- Table Header Row 1 & Row 2 (Clean Modern Soft Muted Style) -->
          <thead>
            <tr class="bg-slate-100/80 text-slate-700 border-b border-slate-200 font-bold">
              <th rowspan="2" class="px-5 py-3 border-r border-slate-200 text-left align-middle text-xs font-bold uppercase tracking-wider text-slate-700 min-w-[220px]">
                Category
              </th>
              <th
                v-for="month in report.months"
                :key="month"
                class="px-5 py-2.5 border-r border-slate-200 text-center min-w-[150px] font-bold text-slate-800"
              >
                {{ month }}
              </th>
            </tr>
            <tr class="bg-slate-50 text-slate-500 border-b border-slate-200 font-semibold">
              <th
                v-for="month in report.months"
                :key="month + '-amt'"
                class="px-5 py-1.5 border-r border-slate-200 text-center text-[10px] font-semibold text-slate-500 uppercase tracking-wider"
              >
                Amount
              </th>
            </tr>
          </thead>

          <tbody class="divide-y divide-slate-100 font-mono text-xs">
            <!-- Income Categories (Clean White with Soft Emerald Dot) -->
            <tr
              v-for="cat in report.income_categories"
              :key="cat.category_id"
              class="bg-white hover:bg-slate-50/70 transition-colors"
            >
              <td class="px-5 py-3 font-sans font-medium text-slate-800 border-r border-slate-100">
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-emerald-500/80 shrink-0"></span>
                  <span>{{ cat.category_name }}</span>
                </div>
              </td>
              <td
                v-for="month in report.months"
                :key="month"
                class="px-5 py-3 text-right border-r border-slate-100 text-slate-700 font-semibold"
              >
                {{ formatNumber(cat.monthly[month]) }}
              </td>
            </tr>

            <!-- Total Income Row (Soft Pastel Emerald) -->
            <tr class="bg-emerald-50/60 font-bold border-t border-b border-emerald-100/90 text-emerald-950">
              <td class="px-5 py-3 font-sans border-r border-emerald-100/80 text-emerald-900 font-bold">
                Total Income
              </td>
              <td
                v-for="month in report.months"
                :key="month + '-tot-inc'"
                class="px-5 py-3 text-right border-r border-emerald-100/80 text-emerald-700 font-bold"
              >
                {{ formatNumber(report.totals_by_month[month]?.total_income) }}
              </td>
            </tr>

            <!-- Expense Categories (Clean White with Soft Orange Dot) -->
            <tr
              v-for="cat in report.expense_categories"
              :key="cat.category_id"
              class="bg-white hover:bg-slate-50/70 transition-colors"
            >
              <td class="px-5 py-3 font-sans font-medium text-slate-800 border-r border-slate-100">
                <div class="flex items-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-orange-500/80 shrink-0"></span>
                  <span>{{ cat.category_name }}</span>
                </div>
              </td>
              <td
                v-for="month in report.months"
                :key="month"
                class="px-5 py-3 text-right border-r border-slate-100 text-slate-700 font-semibold"
              >
                {{ formatNumber(cat.monthly[month]) }}
              </td>
            </tr>

            <!-- Total Expense Row (Soft Pastel Orange) -->
            <tr class="bg-orange-50/60 font-bold border-t border-b border-orange-100/90 text-orange-950">
              <td class="px-5 py-3 font-sans border-r border-orange-100/80 text-orange-900 font-bold">
                Total Expense
              </td>
              <td
                v-for="month in report.months"
                :key="month + '-tot-exp'"
                class="px-5 py-3 text-right border-r border-orange-100/80 text-orange-700 font-bold"
              >
                {{ formatNumber(report.totals_by_month[month]?.total_expense) }}
              </td>
            </tr>

            <!-- Net Income Row (Clean Bold Contrast) -->
            <tr class="bg-slate-50/90 font-black text-xs border-t-2 border-slate-200 text-slate-900">
              <td class="px-5 py-3.5 font-sans border-r border-slate-200/80 text-slate-900 font-extrabold uppercase tracking-wide text-[11px]">
                Net Income
              </td>
              <td
                v-for="month in report.months"
                :key="month + '-net'"
                class="px-5 py-3.5 text-right border-r border-slate-200/80"
                :class="(report.totals_by_month[month]?.net_income || 0) >= 0 ? 'text-sky-700 font-black' : 'text-rose-600 font-black'"
              >
                {{ formatNumber(report.totals_by_month[month]?.net_income) }}
              </td>
            </tr>
          </tbody>
        </table>
      </DataTableCard>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { ProfitLossReport } from '~/types';

const api = useApi();
const config = useRuntimeConfig();

const report = ref<ProfitLossReport | null>(null);
const loading = ref(true);
const exporting = ref(false);

const startMonth = ref('2022-01');
const endMonth = ref('2022-03');
const filterError = ref('');

const formatNumber = (val: number | null | undefined): string => {
  if (val === null || val === undefined || isNaN(val)) return '0';
  return new Intl.NumberFormat('id-ID').format(val);
};

const fetchReport = async () => {
  filterError.value = '';
  if (startMonth.value > endMonth.value) {
    filterError.value = 'Periode akhir tidak boleh lebih awal dari periode awal.';
    return;
  }
  loading.value = true;
  try {
    const res = await api.get<ProfitLossReport>('/reports/profit-loss', {
      start_month: startMonth.value,
      end_month: endMonth.value,
    });
    if (res.success && res.data) {
      report.value = res.data;
    }
  } catch (err: any) {
    filterError.value = err?.errors ? (Object.values(err.errors).flat()[0] as string) : (err?.message || 'Gagal memuat laporan.');
    console.error('Failed to fetch profit and loss report', err);
  } finally {
    loading.value = false;
  }
};

const handleExportExcel = async () => {
  exporting.value = true;
  try {
    const query = new URLSearchParams({
      start_month: startMonth.value || '',
      end_month: endMonth.value || '',
    }).toString();

    const response = await fetch(`${config.public.apiBaseUrl}/reports/profit-loss/export?${query}`);

    if (!response.ok) throw new Error('Export failed');

    const blob = await response.blob();
    const downloadUrl = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = downloadUrl;
    a.download = `profit-loss-report-${startMonth.value}-to-${endMonth.value}.xlsx`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    window.URL.revokeObjectURL(downloadUrl);
  } catch (e) {
    alert('Gagal mengunduh file Excel.');
  } finally {
    exporting.value = false;
  }
};

onMounted(() => {
  fetchReport();
});
</script>
