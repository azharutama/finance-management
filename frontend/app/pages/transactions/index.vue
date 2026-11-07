<template>
  <NuxtLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-xl font-black text-slate-900 tracking-tight">Daftar Transaksi</h1>
          <p class="text-xs text-slate-400 mt-0.5">Catat transaksi pemasukan (Credit) dan pengeluaran (Debit) berdasarkan COA</p>
        </div>
        <button
          v-if="user?.role === 'admin'"
          @click="openModal()"
          class="inline-flex items-center gap-2 px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold rounded-xl shadow-sm shadow-sky-500/20 transition-all cursor-pointer self-start sm:self-auto"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Catat Transaksi
        </button>
      </div>

      <!-- Filters & Search Pill -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[200px]">
          <input
            v-model="filters.search"
            @input="debounceSearch"
            type="text"
            placeholder="Cari deskripsi atau COA..."
            class="w-full px-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-hidden focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 transition-all font-medium"
          />
        </div>
        <div class="w-44">
          <select
            v-model="filters.coa_id"
            @change="fetchTransactions(1)"
            class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-hidden focus:bg-white focus:border-sky-500 transition-colors font-medium text-slate-700"
          >
            <option value="">Semua COA</option>
            <option v-for="c in coaOptions" :key="c.id" :value="c.id">
              {{ c.code }} - {{ c.name }}
            </option>
          </select>
        </div>
        <div class="w-40">
          <select
            v-model="filters.category_id"
            @change="fetchTransactions(1)"
            class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-hidden focus:bg-white focus:border-sky-500 transition-colors font-medium text-slate-700"
          >
            <option value="">Semua Kategori</option>
            <option v-for="cat in categoryOptions" :key="cat.id" :value="cat.id">
              {{ cat.name }}
            </option>
          </select>
        </div>
        <div class="flex items-center gap-2">
          <input
            v-model="filters.start_date"
            @change="fetchTransactions(1)"
            type="date"
            class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-hidden focus:border-sky-500 font-medium"
          />
          <span class="text-xs text-slate-400 font-semibold">s/d</span>
          <input
            v-model="filters.end_date"
            @change="fetchTransactions(1)"
            type="date"
            class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-hidden focus:border-sky-500 font-medium"
          />
        </div>
      </div>

      <!-- Table Card (Clean Modern Card) -->
      <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200/60 font-bold">
              <tr>
                <th class="px-6 py-3">Tanggal</th>
                <th class="px-6 py-3">COA Kode</th>
                <th class="px-6 py-3">COA Nama & Kategori</th>
                <th class="px-6 py-3">Deskripsi Transaksi</th>
                <th class="px-6 py-3 text-right">Debit (Expense)</th>
                <th class="px-6 py-3 text-right">Credit (Income)</th>
                <th class="px-6 py-3 text-right" v-if="user?.role === 'admin'">Aksi</th>
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
                <td class="px-6 py-3.5 text-xs text-right space-x-2 whitespace-nowrap" v-if="user?.role === 'admin'">
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
        </div>

        <!-- Pagination Styled like Modern Page Buttons -->
        <div v-if="meta && meta.last_page > 1" class="px-6 py-3.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 bg-slate-50/50">
          <span>Halaman {{ meta.current_page }} dari {{ meta.last_page }} (Total {{ meta.total }} data)</span>
          <div class="flex items-center gap-1">
            <button
              :disabled="meta.current_page <= 1"
              @click="fetchTransactions(meta.current_page - 1)"
              class="w-8 h-8 flex items-center justify-center border border-slate-200 rounded-lg hover:bg-white disabled:opacity-40 cursor-pointer"
            >
              &lt;
            </button>
            <span class="w-8 h-8 flex items-center justify-center bg-sky-500 text-white font-bold rounded-lg shadow-xs">
              {{ meta.current_page }}
            </span>
            <button
              :disabled="meta.current_page >= meta.last_page"
              @click="fetchTransactions(meta.current_page + 1)"
              class="w-8 h-8 flex items-center justify-center border border-slate-200 rounded-lg hover:bg-white disabled:opacity-40 cursor-pointer"
            >
              &gt;
            </button>
          </div>
        </div>
      </div>

      <!-- Add/Edit Modal Dialog -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 class="font-bold text-slate-900 text-sm">
              {{ editingTransaction ? 'Ubah Transaksi' : 'Catat Transaksi Baru' }}
            </h3>
            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
          </div>

          <form @submit.prevent="saveTransaction" class="p-6 space-y-4">
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
                    {{ c.code }} - {{ c.name }} ({{ c.category?.type }})
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

            <!-- Amount Input (Automatically assigned based on COA category type) -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl space-y-3">
              <div class="flex items-center justify-between text-xs font-bold">
                <span class="text-slate-700">Jenis Nominal:</span>
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider"
                  :class="selectedCoaType === 'income' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'"
                >
                  {{ selectedCoaType === 'income' ? 'Credit (Pemasukan)' : 'Debit (Pengeluaran)' }}
                </span>
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
              <p class="text-[11px] text-slate-400">
                Sesuai domain spreadsheet, transaksi COA Income dicatat di Credit, dan Expense di Debit.
              </p>
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
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { Category, ChartOfAccount, PaginationMeta, Transaction } from '~/types';
import { formatCurrency, formatDate } from '~/utils/formatters';

const { user } = useAuth();
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

const selectedCoaType = computed(() => {
  const coa = coaOptions.value.find((c) => c.id === Number(form.coa_id));
  return coa?.category?.type || 'expense';
});

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
  } else {
    editingTransaction.value = null;
    form.date = new Date().toISOString().split('T')[0];
    form.coa_id = coaOptions.value[0]?.id || '';
    form.description = '';
    form.debit = 0;
    form.credit = 0;
    nominalAmount.value = '';
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

  if (selectedCoaType.value === 'income') {
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
