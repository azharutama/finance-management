<template>
  <NuxtLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-xl font-black text-slate-900 tracking-tight">Master Kategori</h1>
          <p class="text-xs text-slate-400 mt-0.5">Kelola kategori kelompok transaksi (Salary, Other Income, Family Expense, dsb.)</p>
        </div>
        <button
          v-if="user?.role === 'admin'"
          @click="openModal()"
          class="inline-flex items-center gap-2 px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold rounded-xl shadow-sm shadow-sky-500/20 transition-all cursor-pointer self-start sm:self-auto"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Tambah Kategori
        </button>
      </div>

      <!-- Filters & Search Pill -->
      <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm flex flex-wrap items-center gap-3">
        <div class="flex-1 min-w-[220px]">
          <input
            v-model="search"
            @input="debounceSearch"
            type="text"
            placeholder="Cari nama kategori..."
            class="w-full px-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-hidden focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 transition-all font-medium"
          />
        </div>
        <div class="w-44">
          <select
            v-model="filterType"
            @change="fetchCategories(1)"
            class="w-full px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-hidden focus:bg-white focus:border-sky-500 transition-colors font-medium text-slate-700"
          >
            <option value="">Semua Tipe</option>
            <option value="income">Income (Pemasukan)</option>
            <option value="expense">Expense (Pengeluaran)</option>
          </select>
        </div>
      </div>

      <!-- Table Card (Clean Modern Card) -->
      <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200/60 font-bold">
              <tr>
                <th class="px-6 py-3">Nama Kategori</th>
                <th class="px-6 py-3">Tipe Klasifikasi</th>
                <th class="px-6 py-3 text-center">Jumlah Akun COA</th>
                <th class="px-6 py-3 text-center">Status</th>
                <th class="px-6 py-3 text-right" v-if="user?.role === 'admin'">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
              <tr v-if="loading">
                <td colspan="5" class="px-6 py-8 text-center text-slate-400 text-xs">Memuat data kategori...</td>
              </tr>
              <tr v-else-if="!categories.length">
                <td colspan="5" class="px-6 py-8 text-center text-slate-400 text-xs">Tidak ada kategori ditemukan</td>
              </tr>
              <tr v-for="cat in categories" :key="cat.id" class="hover:bg-slate-50/70 transition-colors">
                <td class="px-6 py-3.5 text-xs font-bold text-slate-900">{{ cat.name }}</td>
                <td class="px-6 py-3.5 text-xs">
                  <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase tracking-wider"
                    :class="cat.type === 'income' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
                  >
                    {{ cat.type === 'income' ? 'Income' : 'Expense' }}
                  </span>
                </td>
                <td class="px-6 py-3.5 text-xs text-center font-mono font-bold text-slate-700">
                  {{ cat.chart_of_accounts_count ?? 0 }}
                </td>
                <td class="px-6 py-3.5 text-xs text-center">
                  <span
                    class="inline-block w-2 h-2 rounded-full mr-1.5"
                    :class="cat.is_active ? 'bg-emerald-500' : 'bg-slate-300'"
                  ></span>
                  <span :class="cat.is_active ? 'text-slate-700 font-semibold' : 'text-slate-400'">
                    {{ cat.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-6 py-3.5 text-xs text-right space-x-2" v-if="user?.role === 'admin'">
                  <button
                    @click="openModal(cat)"
                    class="text-sky-600 hover:text-sky-800 font-bold cursor-pointer"
                  >
                    Ubah
                  </button>
                  <button
                    @click="handleDelete(cat)"
                    class="text-rose-500 hover:text-rose-700 font-bold cursor-pointer"
                  >
                    Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Styled like Modern Pill Page Buttons -->
        <div v-if="meta && meta.last_page > 1" class="px-6 py-3.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 bg-slate-50/50">
          <span>Halaman {{ meta.current_page }} dari {{ meta.last_page }} (Total {{ meta.total }} data)</span>
          <div class="flex items-center gap-1">
            <button
              :disabled="meta.current_page <= 1"
              @click="fetchCategories(meta.current_page - 1)"
              class="w-8 h-8 flex items-center justify-center border border-slate-200 rounded-lg hover:bg-white disabled:opacity-40 cursor-pointer"
            >
              &lt;
            </button>
            <span class="w-8 h-8 flex items-center justify-center bg-sky-500 text-white font-bold rounded-lg shadow-xs">
              {{ meta.current_page }}
            </span>
            <button
              :disabled="meta.current_page >= meta.last_page"
              @click="fetchCategories(meta.current_page + 1)"
              class="w-8 h-8 flex items-center justify-center border border-slate-200 rounded-lg hover:bg-white disabled:opacity-40 cursor-pointer"
            >
              &gt;
            </button>
          </div>
        </div>
      </div>

      <!-- Add/Edit Modal Dialog -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-md w-full overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 class="font-bold text-slate-900 text-sm">
              {{ editingCategory ? 'Ubah Kategori' : 'Tambah Kategori Baru' }}
            </h3>
            <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
          </div>

          <form @submit.prevent="saveCategory" class="p-6 space-y-4">
            <div v-if="formError" class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs">
              {{ formError }}
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kategori *</label>
              <input
                v-model="form.name"
                required
                type="text"
                placeholder="Contoh: Transport Expense"
                class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-hidden focus:border-sky-500 font-medium"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Transaksi *</label>
              <select
                v-model="form.type"
                required
                class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-hidden focus:border-sky-500 font-medium"
              >
                <option value="expense">Expense (Pengeluaran)</option>
                <option value="income">Income (Pemasukan)</option>
              </select>
            </div>

            <div class="flex items-center gap-2 pt-1">
              <input
                v-model="form.is_active"
                id="is_active"
                type="checkbox"
                class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500"
              />
              <label for="is_active" class="text-xs text-slate-700 select-none font-medium">Status Aktif</label>
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
                {{ saving ? 'Menyimpan...' : 'Simpan Kategori' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { Category, PaginationMeta } from '~/types';

const { user } = useAuth();
const api = useApi();

const categories = ref<Category[]>([]);
const meta = ref<PaginationMeta | null>(null);
const loading = ref(true);
const search = ref('');
const filterType = ref('');

const showModal = ref(false);
const editingCategory = ref<Category | null>(null);
const saving = ref(false);
const formError = ref('');
const form = reactive({
  name: '',
  type: 'expense' as 'income' | 'expense',
  is_active: true,
});

let searchTimeout: any = null;
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchCategories(1);
  }, 350);
};

const fetchCategories = async (page = 1) => {
  loading.value = true;
  try {
    const res = await api.get<Category[]>('/categories', {
      page,
      search: search.value || undefined,
      type: filterType.value || undefined,
    });
    if (res.success && res.data) {
      categories.value = res.data;
      meta.value = res.meta;
    }
  } catch (err) {
    console.error('Failed to fetch categories', err);
  } finally {
    loading.value = false;
  }
};

const openModal = (cat?: Category) => {
  formError.value = '';
  if (cat) {
    editingCategory.value = cat;
    form.name = cat.name;
    form.type = cat.type;
    form.is_active = cat.is_active;
  } else {
    editingCategory.value = null;
    form.name = '';
    form.type = 'expense';
    form.is_active = true;
  }
  showModal.value = true;
};

const saveCategory = async () => {
  saving.value = true;
  formError.value = '';
  try {
    if (editingCategory.value) {
      await api.put(`/categories/${editingCategory.value.id}`, form);
    } else {
      await api.post('/categories', form);
    }
    showModal.value = false;
    fetchCategories(meta.value?.current_page || 1);
  } catch (err: any) {
    formError.value = err.message || (err.errors ? Object.values(err.errors)[0] as string : 'Gagal menyimpan kategori');
  } finally {
    saving.value = false;
  }
};

const handleDelete = async (cat: Category) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus kategori "${cat.name}"?`)) return;
  try {
    await api.delete(`/categories/${cat.id}`);
    fetchCategories(meta.value?.current_page || 1);
  } catch (err: any) {
    alert(err.message || 'Gagal menghapus kategori. Kategori mungkin masih memiliki akun COA.');
  }
};

onMounted(() => {
  fetchCategories();
});
</script>
