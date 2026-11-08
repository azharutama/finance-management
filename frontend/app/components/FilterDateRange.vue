<template>
  <div class="flex items-center gap-2 bg-slate-50 px-3.5 py-2 border border-slate-200 rounded-xl shrink-0 focus-within:bg-white focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-500/10 transition-all">
    <div class="flex items-center gap-1.5 text-slate-400 select-none">
      <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
      </svg>
      <span class="text-xs font-semibold text-slate-500">Tanggal:</span>
    </div>

    <!-- Input Tanggal Mulai -->
    <input
      :value="startDate"
      @change="onStartChange"
      type="date"
      class="bg-transparent text-xs text-slate-700 font-medium focus:outline-hidden cursor-pointer"
      title="Tanggal Mulai"
    />

    <!-- Pemisah s/d -->
    <span class="text-xs text-slate-400 font-bold px-1 select-none">s/d</span>

    <!-- Input Tanggal Akhir -->
    <input
      :value="endDate"
      @change="onEndChange"
      type="date"
      class="bg-transparent text-xs text-slate-700 font-medium focus:outline-hidden cursor-pointer"
      title="Tanggal Selesai"
    />

    <!-- Tombol Reset / Clear Tanggal -->
    <button
      v-if="startDate || endDate"
      @click="onClear"
      type="button"
      class="text-slate-400 hover:text-slate-600 ml-1 p-0.5 rounded-md hover:bg-slate-200/60 cursor-pointer transition-colors"
      title="Hapus filter tanggal"
    >
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>
</template>

<script setup lang="ts">
/**
 * Komponen Filter Rentang Tanggal (Date Range) untuk transaksi.
 */
const props = withDefaults(
  defineProps<{
    startDate?: string;
    endDate?: string;
  }>(),
  {
    startDate: '',
    endDate: '',
  }
);

const emit = defineEmits<{
  (e: 'update:startDate', val: string): void;
  (e: 'update:endDate', val: string): void;
  (e: 'change'): void;
}>();

/**
 * Memancarkan event saat tanggal mulai berubah.
 */
const onStartChange = (event: Event) => {
  const val = (event.target as HTMLInputElement).value;
  emit('update:startDate', val);
  emit('change');
};

/**
 * Memancarkan event saat tanggal akhir berubah.
 */
const onEndChange = (event: Event) => {
  const val = (event.target as HTMLInputElement).value;
  emit('update:endDate', val);
  emit('change');
};

/**
 * Mengosongkan kedua filter tanggal.
 */
const onClear = () => {
  emit('update:startDate', '');
  emit('update:endDate', '');
  emit('change');
};
</script>

