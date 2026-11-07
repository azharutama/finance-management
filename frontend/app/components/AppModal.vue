<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        @click.self="$emit('close')"
      >
        <div
          :class="[
            'bg-white rounded-2xl shadow-xl border border-slate-200 w-full overflow-hidden transition-all',
            maxWidthClass,
          ]"
        >
          <!-- Modal Header -->
          <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/70">
            <h3 class="font-bold text-slate-900 text-sm">
              {{ title }}
            </h3>
            <button
              @click="$emit('close')"
              type="button"
              class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 text-lg cursor-pointer transition-colors"
              title="Tutup"
            >
              &times;
            </button>
          </div>

          <!-- Modal Body Slot -->
          <div class="p-6">
            <slot />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    show: boolean;
    title: string;
    maxWidthClass?: string;
  }>(),
  {
    show: false,
    maxWidthClass: 'max-w-md',
  }
);

defineEmits<{
  (e: 'close'): void;
}>();
</script>

