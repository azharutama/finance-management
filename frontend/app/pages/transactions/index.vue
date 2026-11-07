<template>
  <NuxtLayout>
    <div class="space-y-6">
      <!-- Header -->
      <PageHeader
        title="Daftar Transaksi"
        description="Catat transaksi pemasukan (Credit) dan pengeluaran (Debit) berdasarkan COA"
      >
        <template #actions>
          <button
            @click="openModal()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold rounded-xl shadow-sm shadow-sky-500/20 transition-all cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Catat Transaksi
          </button>
        </template>
      </PageHeader>

      <!-- Filter Card Component -->
      <FilterCard>
        <FilterSearch
          v-model="filters.search"
          @input="debounceSearch"
          placeholder="Cari deskripsi atau COA..."
        />
        <FilterSelect
          v-model="filters.coa_id"
          @change="fetchTransactions(1)"
          width-class="w-44"
        >
          <option value="">Semua COA</option>
          <option v-for="c in coaOptions" :key="c.id" :value="c.id">
            {{ c.code }} - {{ c.name }}
          </option>
        </FilterSelect>
        <FilterSelect
          v-model="filters.category_id"
          @change="fetchTransactions(1)"
          width-class="w-40"
        >
          <option value="">Semua Kategori</option>
          <option v-for="cat in categoryOptions" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </FilterSelect>
        <FilterDateRange
          v-model:start-date="filters.start_date"
          v-model:end-date="filters.end_date"
          @change="fetchTransactions(1)"
        />
      </FilterCard>

      <!-- Table Card -->
      <DataTableCard>
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200/60 font-bold">
            <tr>
              <th class="px-6 py-3">Tanggal</th>
              <th class="px-6 py-3">COA Kode</th>
              <th class="px-6 py-3">COA Nama</th>
              <th class="px-6 py-3">Desc</th>
              <th class="px-6 py-3 text-right">Debit</th>
              <th class="px-6 py-3 text-right">Credit</th>
              <th class="px-6 py-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <tr v-if="loading">
              <td colspan="7" class="px-6 py-8 text-center text-slate-400 text-xs">Memuat data transaksi...</td>
            </tr>
            <tr v-else-if="!transactions.length">
              <td colspan="7" class="px-6 py-8 text-center text-slate-400 text-xs">Tidak ada transaksi ditemukan</td>
            </tr>
            <tr v-for="tx in transactions" :key="tx.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="px-6 py-3.5 text-xs font-medium text-slate-700 whitespace-nowrap">{{ formatDate(tx.date) }}</td>
              <td class="px-6 py-3.5 text-xs font-mono font-bold text-slate-900">{{ tx.coa?.code || '-' }}</td>
              <td class="px-6 py-3.5 text-xs font-bold text-slate-800">{{ tx.coa?.name || '-' }}</td>
              <td class="px-6 py-3.5 text-xs text-slate-600">{{ tx.description }}</td>
              <td class="px-6 py-3.5 text-xs text-right font-mono font-bold text-slate-800">
                {{ tx.debit > 0 ? formatCurrency(tx.debit) : '0' }}
              </td>
              <td class="px-6 py-3.5 text-xs text-right font-mono font-bold text-slate-800">
                {{ tx.credit > 0 ? formatCurrency(tx.credit) : '' }}
              </td>
              <td class="px-6 py-3.5 text-xs text-right space-x-2 whitespace-nowrap">
                <button
                  @click="openModal(tx)"
                  class="text-sky-600 hover:text-sky-800 font-bold cursor-pointer"
                >
                  Ubah
                </button>
                <button
                  @click="handleDelete(tx)"
                  class="text-rose-500 hover:text-rose-700 font-bold cursor-pointer"
                >
                  Hapus
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <template #footer>
          <AppPagination :meta="meta" @change="fetchTransactions" />
        </template>
      </DataTableCard>

      <!-- Add/Edit Modal Dialog -->
      <AppModal
        :show="showModal"
        :title="editingTransaction ? 'Ubah Transaksi' : 'Catat Transaksi Baru'"
        max-width-class="max-w-lg"
        @close="showModal = false"
      >
        <form @submit.prevent="saveTransaction" class="space-y-4">
          <div v-if="formError" class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs">
            {{ formError }}
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal *</label>
              <input
                v-model="form.date"
                required
                type="date"
                class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-hidden focus:border-sky-500 font-medium"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Akun COA *</label>
              <select
                v-model="form.coa_id"
                required
                class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-hidden focus:border-sky-500 font-medium"
              >
                <option value="" disabled>Pilih COA</option>
                <option v-for="c in coaOptions" :key="c.id" :value="c.id">
                  {{ c.code }} - {{ c.name }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Transaksi *</label>
            <input
              v-model="form.description"
              required
              type="text"
              placeholder="Contoh: Bensin Anak / Gaji Karyawan"
              class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-hidden focus:border-sky-500 font-medium"
            />
          </div>

          <!-- Amount Input: user explicitly chooses Debit or Credit -->
          <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1.5">Tipe Transaksi *</label>
              <div class="grid grid-cols-2 gap-2">
                <label
                  class="flex items-center justify-center gap-2 px-3 py-2 rounded-xl border text-xs font-bold cursor-pointer transition-colors"
                  :class="entryType === 'debit' ? 'bg-orange-50 border-orange-400 text-orange-800' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                >
                  <input v-model="entryType" type="radio" value="debit" class="sr-only" />
                  Debit
                </label>
                <label
                  class="flex items-center justify-center gap-2 px-3 py-2 rounded-xl border text-xs font-bold cursor-pointer transition-colors"
                  :class="entryType === 'credit' ? 'bg-sky-50 border-sky-400 text-sky-800' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'"
                >
                  <input v-model="entryType" type="radio" value="credit" class="sr-only" />
                  Credit
                </label>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Nominal (IDR) *</label>
              <input
                v-model.number="nominalAmount"
                required
                min="1"
                type="number"
                placeholder="0"
                class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-hidden focus:border-sky-500 font-mono font-bold"
              />
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2 border border-slate-200 rounded-xl text-xs font-medium text-slate-600 hover:bg-slate-50 cursor-pointer"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white rounded-xl text-xs font-bold disabled:opacity-50 cursor-pointer"
            >
              {{ saving ? 'Menyimpan...' : 'Simpan Transaksi' }}
            </button>
          </div>
        </form>
      </AppModal>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { Category, ChartOfAccount, PaginationMeta, Transaction } from '~/types';
import { formatCurrency, formatDate } from '~/utils/formatters';

const api = useApi();

const transactions = ref<Transaction[]>([]);
const coaOptions = ref<ChartOfAccount[]>([]);
const categoryOptions = ref<Category[]>([]);
const meta = ref<PaginationMeta | null>(null);
const loading = ref(true);

const filters = reactive({
  search: '',
  coa_id: '',
  category_id: '',
  start_date: '',
  end_date: '',
});

const showModal = ref(false);
const editingTransaction = ref<Transaction | null>(null);
const saving = ref(false);
const formError = ref('');
const nominalAmount = ref<number | ''>('');

const form = reactive({
  date: new Date().toISOString().split('T')[0],
  coa_id: '' as any,
  description: '',
  debit: 0,
  credit: 0,
});

const entryType = ref<'debit' | 'credit'>('debit');

let searchTimeout: any = null;
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchTransactions(1);
  }, 350);
};

const fetchTransactions = async (page = 1) => {
  loading.value = true;
  try {
    const res = await api.get<Transaction[]>('/transactions', {
      page,
      search: filters.search || undefined,
      coa_id: filters.coa_id || undefined,
      category_id: filters.category_id || undefined,
      start_date: filters.start_date || undefined,
      end_date: filters.end_date || undefined,
    });
    if (res.success && res.data) {
      transactions.value = res.data;
      meta.value = res.meta;
    }
  } catch (err) {
    console.error('Failed to fetch transactions', err);
  } finally {
    loading.value = false;
  }
};

const fetchFilterOptions = async () => {
  try {
    const [coaRes, catRes] = await Promise.all([
      api.get<ChartOfAccount[]>('/coa', { all: true }),
      api.get<Category[]>('/categories', { all: true }),
    ]);
    if (coaRes.success && coaRes.data) coaOptions.value = coaRes.data;
    if (catRes.success && catRes.data) categoryOptions.value = catRes.data;
  } catch (e) {
    console.error(e);
  }
};

const openModal = (tx?: Transaction) => {
  formError.value = '';
  if (tx) {
    editingTransaction.value = tx;
    form.date = tx.date;
    form.coa_id = tx.coa_id;
    form.description = tx.description;
    form.debit = tx.debit;
    form.credit = tx.credit;
    nominalAmount.value = tx.debit > 0 ? tx.debit : tx.credit;
    entryType.value = tx.credit > 0 ? 'credit' : 'debit';
  } else {
    editingTransaction.value = null;
    form.date = new Date().toISOString().split('T')[0];
    form.coa_id = coaOptions.value[0]?.id || '';
    form.description = '';
    form.debit = 0;
    form.credit = 0;
    nominalAmount.value = '';
    entryType.value = 'debit';
  }
  showModal.value = true;
};

const saveTransaction = async () => {
  saving.value = true;
  formError.value = '';

  const amt = Number(nominalAmount.value) || 0;
  if (amt <= 0) {
    formError.value = 'Nominal harus lebih besar dari 0.';
    saving.value = false;
    return;
  }

  if (entryType.value === 'credit') {
    form.credit = amt;
    form.debit = 0;
  } else {
    form.debit = amt;
    form.credit = 0;
  }

  try {
    if (editingTransaction.value) {
      await api.put(`/transactions/${editingTransaction.value.id}`, form);
    } else {
      await api.post('/transactions', form);
    }
    showModal.value = false;
    fetchTransactions(meta.value?.current_page || 1);
  } catch (err: any) {
    formError.value = err.message || (err.errors ? Object.values(err.errors)[0] as string : 'Gagal menyimpan transaksi');
  } finally {
    saving.value = false;
  }
};

const handleDelete = async (tx: Transaction) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus transaksi "${tx.description}"?`)) return;
  try {
    await api.delete(`/transactions/${tx.id}`);
    fetchTransactions(meta.value?.current_page || 1);
  } catch (err: any) {
    alert(err.message || 'Gagal menghapus transaksi.');
  }
};

onMounted(() => {
  fetchFilterOptions();
  fetchTransactions();
});
</script>
