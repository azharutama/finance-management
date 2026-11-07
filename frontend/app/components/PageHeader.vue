<template>
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-xl font-black text-slate-900 tracking-tight">{{ title }}</h1>
      <p v-if="description" class="text-xs text-slate-400 mt-0.5">{{ description }}</p>
    </div>
    <div v-if="$slots.actions" class="self-start sm:self-auto flex items-center gap-2">
      <slot name="actions" />
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title: string;
  description?: string;
}>();
</script>

