<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4 relative overflow-hidden">
    <!-- Modern Clean Header brand bar on top -->
    <header class="absolute top-0 inset-x-0 h-16 bg-white border-b border-slate-200/90 px-8 flex items-center justify-between">
      <div class="flex items-center gap-1.5 text-lg font-black tracking-tight">
        <span class="text-sky-500">Finance</span><span class="text-orange-500">Management</span>
      </div>
      <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
        <span class="w-3.5 h-2 bg-red-600 rounded-2xs inline-block"></span>
        <span>ID | Bantuan</span>
      </div>
    </header>

    <!-- Card Login Clean Modern Style -->
    <div class="max-w-md w-full bg-white border border-slate-200/90 rounded-2xl shadow-xl shadow-slate-200/60 p-8 relative z-10 mt-12">
      <div class="text-center mb-8">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-500 to-blue-600 mx-auto flex items-center justify-center text-white font-black text-xl shadow-md shadow-sky-500/30 mb-3">
          FM
        </div>
        <h1 class="text-xl font-black text-slate-900 tracking-tight">Masuk ke Akun Anda</h1>
        <p class="text-xs text-slate-400 mt-1">Sistem Pengelolaan Keuangan & Profit Loss</p>
      </div>

      <div v-if="errorMessage" class="mb-5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ errorMessage }}</span>
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Email</label>
          <input
            v-model="email"
            type="email"
            required
            placeholder="admin@example.com"
            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs focus:outline-hidden focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all font-medium"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi (Password)</label>
          <input
            v-model="password"
            type="password"
            required
            placeholder="••••••••"
            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-xs focus:outline-hidden focus:bg-white focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20 transition-all font-medium"
          />
        </div>

        <button
          type="submit"
          :disabled="submitting"
          class="w-full py-2.5 px-4 bg-sky-500 hover:bg-sky-600 disabled:opacity-50 text-white font-bold text-xs rounded-xl shadow-md shadow-sky-500/25 transition-all flex items-center justify-center gap-2 mt-6 cursor-pointer"
        >
          <span v-if="submitting">Memverifikasi...</span>
          <span v-else>Masuk Sekarang</span>
        </button>
      </form>

      <div class="mt-6 pt-5 border-t border-slate-100 text-center">
        <p class="text-xs text-slate-400">
          Akun Demo: <span class="text-slate-800 font-mono font-bold">admin@example.com</span> / <span class="text-slate-800 font-mono font-bold">password</span>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ApiResponse, User } from '~/types';

definePageMeta({
  layout: false,
});

const config = useRuntimeConfig();
const { setAuth } = useAuth();

const email = ref('admin@example.com');
const password = ref('password');
const submitting = ref(false);
const errorMessage = ref('');

const handleLogin = async () => {
  submitting.value = true;
  errorMessage.value = '';
  try {
    const res = await $fetch<ApiResponse<{ user: User; token: string }>>(`${config.public.apiBaseUrl}/auth/login`, {
      method: 'POST',
      body: {
        email: email.value,
        password: password.value,
      },
      headers: {
        Accept: 'application/json',
      },
    });

    if (res.success && res.data) {
      setAuth(res.data.user, res.data.token);
      navigateTo('/');
    }
  } catch (err: any) {
    errorMessage.value = err.response?._data?.message || err.message || 'Gagal login. Periksa email dan password.';
  } finally {
    submitting.value = false;
  }
};
</script>
