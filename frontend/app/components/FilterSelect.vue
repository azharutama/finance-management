<template>
  <div :class="['relative shrink-0 w-full sm:w-auto', widthClass]">
    <select
      :value="modelValue"
      @change="onChange"
      class="w-full appearance-none pl-3.5 pr-8 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-hidden focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-500/10 transition-all font-medium text-slate-700 cursor-pointer"
    >
      <slot />
    </select>
    <div class="absolute inset-y-0 right-0 pr-2.5 flex items-center pointer-events-none text-slate-400">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
      </svg>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    modelValue?: string | number;
    widthClass?: string;
  }>(),
  {
    modelValue: '',
    widthClass: 'min-w-[150px]',
  }
);

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
  (e: 'change', value: string): void;
}>();

const onChange = (event: Event) => {
  const value = (event.target as HTMLSelectElement).value;
  emit('update:modelValue', value);
  emit('change', value);
};
</script>

