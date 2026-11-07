<template>
  <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
    <div v-if="$slots.header" class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
      <slot name="header" />
    </div>
    <div class="overflow-x-auto">
      <slot />
    </div>
    <slot name="footer" />
  </div>
</template>

