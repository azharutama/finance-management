<template>
  <NuxtLayout>
    <div class="space-y-6">
      <!-- Header -->
      <PageHeader
        title="Master Chart of Account (COA)"
        description="Kelola akun COA (401 - Gaji Karyawan, 602 - Bensin, dsb.) beserta relasi kategorinya"
      >
        <template #actions>
          <button
            @click="openModal()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold rounded-xl shadow-sm shadow-sky-500/20 transition-all cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah COA
          </button>
        </template>
      </PageHeader>

      <!-- Filter Card Component -->
      <FilterCard>
        <FilterSearch
          v-model="search"
          @input="debounceSearch"
          placeholder="Cari kode atau nama akun..."
        />
        <FilterSelect
          v-model="filterCategory"
          @change="fetchCoa(1)"
          width-class="w-56"
        >
          <option value="">Semua Kategori</option>
          <option v-for="cat in allCategories" :key="cat.id" :value="cat.id">
            {{ cat.name }}
          </option>
        </FilterSelect>
      </FilterCard>

      <!-- Table Card -->
      <DataTableCard>
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200/60 font-bold">
            <tr>
              <th class="px-6 py-3">kode</th>
              <th class="px-6 py-3">nama</th>
              <th class="px-6 py-3">kategori</th>
              <th class="px-6 py-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <tr v-if="loading">
              <td colspan="4" class="px-6 py-8 text-center text-slate-400 text-xs">Memuat data COA...</td>
            </tr>
            <tr v-else-if="!coaList.length">
              <td colspan="4" class="px-6 py-8 text-center text-slate-400 text-xs">Tidak ada akun COA ditemukan</td>
            </tr>
            <tr v-for="coa in coaList" :key="coa.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="px-6 py-3.5 text-xs font-mono font-bold text-slate-900">{{ coa.code }}</td>
              <td class="px-6 py-3.5 text-xs font-bold text-slate-800">{{ coa.name }}</td>
              <td class="px-6 py-3.5 text-xs text-slate-700 font-medium">
                {{ coa.category?.name || '-' }}
              </td>
              <td class="px-6 py-3.5 text-xs text-right space-x-2">
                <button
                  @click="openModal(coa)"
                  class="text-sky-600 hover:text-sky-800 font-bold cursor-pointer"
                >
                  Ubah
                </button>
                <button
                  @click="handleDelete(coa)"
                  class="text-rose-500 hover:text-rose-700 font-bold cursor-pointer"
                >
                  Hapus
                </button>
              </td>
            </tr>
          </tbody>
        </table>

        <template #footer>
          <AppPagination :meta="meta" @change="fetchCoa" />
        </template>
      </DataTableCard>

      <!-- Add/Edit Modal Dialog -->
      <AppModal
        :show="showModal"
        :title="editingCoa ? 'Ubah Akun COA' : 'Tambah Akun COA Baru'"
        max-width-class="max-w-md"
        @close="showModal = false"
      >
        <form @submit.prevent="saveCoa" class="space-y-4">
          <div v-if="formError" class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-xs">
            {{ formError }}
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Kode Akun (Code) *</label>
            <input
              v-model="form.code"
              required
              type="text"
              placeholder="Contoh: 602"
              class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-hidden focus:border-sky-500 font-mono font-bold"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Akun (Name) *</label>
            <input
              v-model="form.name"
              required
              type="text"
              placeholder="Contoh: Bensin"
              class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-hidden focus:border-sky-500 font-medium"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 mb-1">Kategori COA *</label>
            <select
              v-model="form.category_id"
              required
              class="w-full px-3.5 py-2 text-xs border border-slate-200 rounded-xl focus:outline-hidden focus:border-sky-500 font-medium"
            >
              <option value="" disabled>Pilih Kategori</option>
              <option v-for="cat in allCategories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
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
              {{ saving ? 'Menyimpan...' : 'Simpan COA' }}
            </button>
          </div>
        </form>
      </AppModal>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { Category, ChartOfAccount, PaginationMeta } from '~/types';

const api = useApi();

const coaList = ref<ChartOfAccount[]>([]);
const allCategories = ref<Category[]>([]);
const meta = ref<PaginationMeta | null>(null);
const loading = ref(true);
const search = ref('');
const filterCategory = ref('');

const showModal = ref(false);
const editingCoa = ref<ChartOfAccount | null>(null);
const saving = ref(false);
const formError = ref('');
const form = reactive({
  code: '',
  name: '',
  category_id: '' as any,
});

let searchTimeout: any = null;
/**
 * Menunda pencarian akun COA dengan debounce 350ms.
 */
const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchCoa(1);
  }, 350);
};

/**
 * Mengambil daftar COA dari backend dengan filter kode/nama dan kategori.
 */
const fetchCoa = async (page = 1) => {
  loading.value = true;
  try {
    const res = await api.get<ChartOfAccount[]>('/coa', {
      page,
      search: search.value || undefined,
      category_id: filterCategory.value || undefined,
    });
    if (res.success && res.data) {
      coaList.value = res.data;
      meta.value = res.meta;
    }
  } catch (err) {
    console.error('Failed to fetch COA', err);
  } finally {
    loading.value = false;
  }
};

/**
 * Mengambil seluruh daftar kategori untuk opsi pilihan dropdown form modal.
 */
const fetchCategoriesList = async () => {
  try {
    const res = await api.get<Category[]>('/categories', { all: true });
    if (res.success && res.data) {
      allCategories.value = res.data;
    }
  } catch (e) {
    console.error(e);
  }
};

/**
 * Membuka modal dialog untuk tambah baru atau ubah akun COA.
 */
const openModal = (coa?: ChartOfAccount) => {
  formError.value = '';
  if (coa) {
    editingCoa.value = coa;
    form.code = coa.code;
    form.name = coa.name;
    form.category_id = coa.category_id;
  } else {
    editingCoa.value = null;
    form.code = '';
    form.name = '';
    form.category_id = allCategories.value[0]?.id || '';
  }
  showModal.value = true;
};

/**
 * Menyimpan data COA baru (POST) atau memperbarui COA yang ada (PUT).
 */
const saveCoa = async () => {
  saving.value = true;
  formError.value = '';
  try {
    if (editingCoa.value) {
      await api.put(`/coa/${editingCoa.value.id}`, form);
    } else {
      await api.post('/coa', form);
    }
    showModal.value = false;
    fetchCoa(meta.value?.current_page || 1);
  } catch (err: any) {
    formError.value = err.message || (err.errors ? Object.values(err.errors)[0] as string : 'Gagal menyimpan COA');
  } finally {
    saving.value = false;
  }
};

/**
 * Menghapus akun COA jika belum pernah dipakai di transaksi riwayat.
 */
const handleDelete = async (coa: ChartOfAccount) => {
  if (!confirm(`Apakah Anda yakin ingin menghapus COA "${coa.code} - ${coa.name}"?`)) return;
  try {
    await api.delete(`/coa/${coa.id}`);
    fetchCoa(meta.value?.current_page || 1);
  } catch (err: any) {
    alert(err.message || 'Gagal menghapus COA. COA mungkin sudah digunakan pada transaksi.');
  }
};

onMounted(() => {
  fetchCategoriesList();
  fetchCoa();
});
</script>
