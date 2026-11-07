<template>
  <div class="bg-white rounded-2xl p-5 border border-slate-200/90 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
    <div class="space-y-1">
      <span
        class="inline-block px-2 py-0.5 rounded text-[10px] font-extrabold uppercase tracking-wide border"
        :class="badgeClass"
      >
        {{ title }}
      </span>
      <h3
        class="text-xl font-black tracking-tight"
        :class="valueColorClass"
      >
        {{ loading ? '...' : value }}
      </h3>
      <p v-if="sublabel" class="text-[11px] text-slate-400 font-medium">
        {{ sublabel }}
      </p>
    </div>
    <div
      class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-xl border shrink-0"
      :class="iconBoxClass"
    >
      <slot name="icon">
        <span v-html="iconSymbol"></span>
      </slot>
    </div>
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    title: string;
    value: string | number;
    sublabel?: string;
    loading?: boolean;
    badgeClass?: string;
    iconBoxClass?: string;
    iconSymbol?: string;
    valueColorClass?: string;
  }>(),
  {
    loading: false,
    badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
    iconBoxClass: 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
    iconSymbol: '&darr;',
    valueColorClass: 'text-slate-900',
  }
);
</script>

