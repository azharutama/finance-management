<template>
  <div class="flex flex-wrap items-center gap-3">
    <!-- Period Label with Icon -->
    <div class="flex items-center gap-2">
      <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 border border-sky-100">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
      <span class="text-xs font-bold text-slate-700">Periode:</span>
    </div>

    <!-- Start Month & Year -->
    <div class="flex items-center gap-1.5 bg-slate-50 p-1 border border-slate-200 rounded-xl">
      <select
        v-model="startM"
        @change="emitUpdate"
        class="bg-transparent text-xs font-medium text-slate-700 px-2 py-1 focus:outline-hidden cursor-pointer"
      >
        <option v-for="m in monthOptions" :key="'sm-' + m.value" :value="m.value">
          {{ m.label }}
        </option>
      </select>
      <select
        v-model="startY"
        @change="emitUpdate"
        class="bg-transparent text-xs font-bold text-slate-800 px-2 py-1 focus:outline-hidden cursor-pointer border-l border-slate-200"
      >
        <option v-for="y in yearOptions" :key="'sy-' + y" :value="y">
          {{ y }}
        </option>
      </select>
    </div>

    <span class="text-xs text-slate-400 font-bold select-none">sampai</span>

    <!-- End Month & Year -->
    <div class="flex items-center gap-1.5 bg-slate-50 p-1 border border-slate-200 rounded-xl">
      <select
        v-model="endM"
        @change="emitUpdate"
        class="bg-transparent text-xs font-medium text-slate-700 px-2 py-1 focus:outline-hidden cursor-pointer"
      >
        <option v-for="m in monthOptions" :key="'em-' + m.value" :value="m.value">
          {{ m.label }}
        </option>
      </select>
      <select
        v-model="endY"
        @change="emitUpdate"
        class="bg-transparent text-xs font-bold text-slate-800 px-2 py-1 focus:outline-hidden cursor-pointer border-l border-slate-200"
      >
        <option v-for="y in yearOptions" :key="'ey-' + y" :value="y">
          {{ y }}
        </option>
      </select>
    </div>

    <!-- Submit Button -->
    <button
      @click="$emit('submit')"
      :disabled="loading"
      type="button"
      class="px-4 py-2 bg-sky-500 hover:bg-sky-600 disabled:opacity-50 text-white text-xs font-bold rounded-xl shadow-xs shadow-sky-500/20 transition-all cursor-pointer flex items-center gap-1.5"
    >
      <svg v-if="loading" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
      </svg>
      <span>{{ loading ? 'Memuat...' : 'Tampilkan' }}</span>
    </button>
  </div>
</template>

<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    startMonth?: string; // Format: YYYY-MM
    endMonth?: string;   // Format: YYYY-MM
    loading?: boolean;
  }>(),
  {
    startMonth: '2022-01',
    endMonth: '2022-03',
    loading: false,
  }
);

const emit = defineEmits<{
  (e: 'update:startMonth', val: string): void;
  (e: 'update:endMonth', val: string): void;
  (e: 'submit'): void;
}>();

const pad = (n: number) => String(n).padStart(2, '0');
const today = new Date();
const currentYear = today.getFullYear();

const monthOptions = Array.from({ length: 12 }, (_, i) => ({
  value: pad(i + 1),
  label: new Intl.DateTimeFormat('id-ID', { month: 'short' }).format(new Date(2000, i, 1)),
}));

// Rentang tahun dari 2015 s.d. tahun depan
const yearOptions = Array.from(
  { length: currentYear + 1 - 2015 + 1 },
  (_, i) => currentYear + 1 - i
);

const parseMonth = (ym?: string, defaultY?: number, defaultM?: string) => {
  if (ym && ym.includes('-')) {
    const [y, m] = ym.split('-');
    return { y: Number(y), m };
  }
  return { y: defaultY || currentYear, m: defaultM || '01' };
};

const initialStart = parseMonth(props.startMonth, 2022, '01');
const initialEnd = parseMonth(props.endMonth, 2022, '03');

const startM = ref(initialStart.m);
const startY = ref(initialStart.y);
const endM = ref(initialEnd.m);
const endY = ref(initialEnd.y);

watch(
  () => props.startMonth,
  (newVal) => {
    if (newVal) {
      const parsed = parseMonth(newVal);
      startM.value = parsed.m;
      startY.value = parsed.y;
    }
  }
);

watch(
  () => props.endMonth,
  (newVal) => {
    if (newVal) {
      const parsed = parseMonth(newVal);
      endM.value = parsed.m;
      endY.value = parsed.y;
    }
  }
);

const emitUpdate = () => {
  emit('update:startMonth', `${startY.value}-${startM.value}`);
  emit('update:endMonth', `${endY.value}-${endM.value}`);
};
</script>

