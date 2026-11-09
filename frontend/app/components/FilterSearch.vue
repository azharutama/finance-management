<template>
  <div class="relative flex-1 min-w-0 sm:min-w-[200px] w-full sm:w-auto">
    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
    </div>
    <input
      :value="modelValue"
      @input="onInput"
      type="text"
      :placeholder="placeholder"
      class="w-full pl-9 pr-8 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-hidden focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 transition-all font-medium text-slate-700 placeholder:text-slate-400"
    />
    <button
      v-if="modelValue"
      @click="onClear"
      type="button"
      class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer"
      title="Hapus pencarian"
    >
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    modelValue?: string;
    placeholder?: string;
  }>(),
  {
    modelValue: '',
    placeholder: 'Cari data...',
  }
);

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
  (e: 'input', value: string): void;
  (e: 'clear'): void;
}>();

const onInput = (event: Event) => {
  const value = (event.target as HTMLInputElement).value;
  emit('update:modelValue', value);
  emit('input', value);
};

const onClear = () => {
  emit('update:modelValue', '');
  emit('input', '');
  emit('clear');
};
</script>

