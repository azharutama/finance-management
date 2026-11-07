<template>
  <NuxtLayout>
    <div class="space-y-6">
      <!-- Header -->
      <PageHeader
        title="Master Kategori"
        description="Kelola kategori kelompok transaksi (Salary, Other Income, Family Expense, dsb.)"
      >
        <template #actions>
          <button
            @click="openModal()"
            class="inline-flex items-center gap-2 px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white text-xs font-bold rounded-xl shadow-sm shadow-sky-500/20 transition-all cursor-pointer"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Kategori
          </button>
        </template>
      </PageHeader>

      <!-- Filter Card Component -->
      <FilterCard>
        <FilterSearch
          v-model="search"
          @input="debounceSearch"
          placeholder="Cari nama kategori..."
        />
      </FilterCard>

      <!-- Table Card -->
      <DataTableCard>
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider border-b border-slate-200/60 font-bold">
            <tr>
              <th class="px-6 py-3">nama</th>
              <th class="px-6 py-3 text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 text-slate-700">
            <tr v-if="loading">
              <td colspan="2" class="px-6 py-8 text-center text-slate-400 text-xs">Memuat data kategori...</td>
            </tr>
            <tr v-else-if="!categories.length">
              <td colspan="2" class="px-6 py-8 text-center text-slate-400 text-xs">Tidak ada kategori ditemukan</td>
            </tr>
            <tr v-for="cat in categories" :key="cat.id" class="hover:bg-slate-50/70 transition-colors">
              <td class="px-6 py-3.5 text-xs font-bold text-slate-900">{{ cat.name }}</td>
              <td class="px-6 py-3.5 text-xs text-right space-x-2">
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

        <template #footer>
          <AppPagination :meta="meta" @change="fetchCategories" />
        </template>
      </DataTableCard>

      <!-- Modal Create / Edit -->
      <AppModal
        :show="showModal"
        :title="editingCategory ? 'Ubah Kategori' : 'Tambah Kategori Baru'"
        max-width-class="max-w-md"
        @close="showModal = false"
      >
        <form @submit.prevent="saveCategory" class="space-y-4">
          <div v-if="formError" class="p-3 text-xs bg-rose-50 text-rose-600 border border-rose-200 rounded-xl">
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
      </AppModal>
    </div>
  </NuxtLayout>
</template>

<script setup lang="ts">
import type { Category, PaginationMeta } from '~/types';

const api = useApi();

const categories = ref<Category[]>([]);
const meta = ref<PaginationMeta | null>(null);
const loading = ref(true);
const search = ref('');

const showModal = ref(false);
const editingCategory = ref<Category | null>(null);
const saving = ref(false);
const formError = ref('');
const form = reactive({
  name: '',
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
  } else {
    editingCategory.value = null;
    form.name = '';
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
    await fetchCategories(meta.value?.current_page || 1);
  } catch (err: any) {
    formError.value = err?.message || 'Gagal menyimpan kategori.';
  } finally {
    saving.value = false;
  }
};

const handleDelete = async (cat: Category) => {
  if (!confirm(`Hapus kategori "${cat.name}"?`)) return;
  try {
    await api.delete(`/categories/${cat.id}`);
    await fetchCategories(meta.value?.current_page || 1);
  } catch (err: any) {
    alert(err?.message || 'Gagal menghapus kategori. Kemungkinan terdapat relasi ke data lain.');
  }
};

onMounted(() => {
  fetchCategories();
});
</script>
