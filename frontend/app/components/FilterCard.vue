<template>
  <div class="bg-white p-3.5 sm:p-4 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
    <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-2.5 sm:gap-3 flex-1 min-w-0">
      <slot />
    </div>
    <div v-if="$slots.actions" class="flex items-center gap-2 shrink-0 self-end md:self-auto">
      <slot name="actions" />
    </div>
  </div>
</template>

