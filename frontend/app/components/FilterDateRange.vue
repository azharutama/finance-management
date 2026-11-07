<template>
  <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 border border-slate-200 rounded-xl shrink-0 focus-within:bg-white focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-500/10 transition-all">
    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
    </svg>
    <input
      :value="startDate"
      @change="onStartChange"
      type="date"
      class="bg-transparent text-xs text-slate-700 font-medium focus:outline-hidden cursor-pointer"
      title="Tanggal Mulai"
    />
    <span class="text-xs text-slate-400 font-bold px-0.5 select-none">s/d</span>
    <input
      :value="endDate"
      @change="onEndChange"
      type="date"
      class="bg-transparent text-xs text-slate-700 font-medium focus:outline-hidden cursor-pointer"
      title="Tanggal Selesai"
    />
  </div>
</template>

<script setup lang="ts">
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

const onStartChange = (event: Event) => {
  const val = (event.target as HTMLInputElement).value;
  emit('update:startDate', val);
  emit('change');
};

const onEndChange = (event: Event) => {
  const val = (event.target as HTMLInputElement).value;
  emit('update:endDate', val);
  emit('change');
};
</script>

