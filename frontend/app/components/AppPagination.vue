<template>
  <div
    v-if="meta && meta.last_page > 1"
    class="px-6 py-3.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 bg-slate-50/50"
  >
    <span>Halaman {{ meta.current_page }} dari {{ meta.last_page }} (Total {{ meta.total }} data)</span>
    <div class="flex items-center gap-1">
      <button
        :disabled="meta.current_page <= 1"
        @click="$emit('change', meta.current_page - 1)"
        type="button"
        class="w-8 h-8 flex items-center justify-center border border-slate-200 rounded-lg hover:bg-white disabled:opacity-40 cursor-pointer font-bold text-slate-600 transition-colors"
        title="Halaman Sebelumnya"
      >
        &lt;
      </button>

      <span class="w-8 h-8 flex items-center justify-center bg-sky-500 text-white font-bold rounded-lg shadow-xs">
        {{ meta.current_page }}
      </span>

      <button
        :disabled="meta.current_page >= meta.last_page"
        @click="$emit('change', meta.current_page + 1)"
        type="button"
        class="w-8 h-8 flex items-center justify-center border border-slate-200 rounded-lg hover:bg-white disabled:opacity-40 cursor-pointer font-bold text-slate-600 transition-colors"
        title="Halaman Berikutnya"
      >
        &gt;
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { PaginationMeta } from '~/types';

defineProps<{
  meta: PaginationMeta | null;
}>();

defineEmits<{
  (e: 'change', page: number): void;
}>();
</script>

