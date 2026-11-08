<template>
  <div class="min-h-screen bg-slate-50/70 flex">
    <!-- Sidebar: Clean Light Style (White Background with Subtle Borders & Modern Blue/Orange Accents) -->
    <aside class="w-64 bg-white border-r border-slate-200 text-slate-700 flex flex-col fixed inset-y-0 z-30 shadow-xs">
      <!-- App Brand / Header -->
      <div class="h-16 flex items-center px-6 gap-3 border-b border-slate-100 bg-white">
        <!-- Logo with blue & orange dual tone -->
        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-500 to-orange-500 p-0.5 shadow-sm flex items-center justify-center">
          <div class="w-full h-full bg-white rounded-[10px] flex items-center justify-center">
            <span class="text-sky-600 font-extrabold text-xs">FM</span>
            <span class="text-orange-500 font-black text-xs">.</span>
          </div>
        </div>
        <div>
          <div class="flex items-center text-sm font-extrabold tracking-tight">
            <span class="text-sky-600">Finance</span>
            <span class="text-orange-500">Management</span>
          </div>
          <p class="text-[10px] text-slate-400 font-medium">Accounting System</p>
        </div>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-3.5 py-6 space-y-1.5 overflow-y-auto">
        <NuxtLink
          to="/"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all group"
          :class="$route.path === '/' ? 'bg-sky-50 text-sky-700 font-bold border border-sky-100 shadow-xs' : 'hover:bg-slate-50 text-slate-600 hover:text-slate-900'"
        >
          <svg
            class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"
            :class="$route.path === '/' ? 'text-sky-600' : 'text-slate-400 group-hover:text-slate-600'"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
          Dashboard
        </NuxtLink>

        <NuxtLink
          to="/transactions"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all group"
          :class="$route.path.startsWith('/transactions') ? 'bg-sky-50 text-sky-700 font-bold border border-sky-100 shadow-xs' : 'hover:bg-slate-50 text-slate-600 hover:text-slate-900'"
        >
          <svg
            class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"
            :class="$route.path.startsWith('/transactions') ? 'text-sky-600' : 'text-slate-400 group-hover:text-slate-600'"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
          </svg>
          Transaksi
        </NuxtLink>

        <NuxtLink
          to="/reports"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all group"
          :class="$route.path.startsWith('/reports') ? 'bg-sky-50 text-sky-700 font-bold border border-sky-100 shadow-xs' : 'hover:bg-slate-50 text-slate-600 hover:text-slate-900'"
        >
          <svg
            class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"
            :class="$route.path.startsWith('/reports') ? 'text-sky-600' : 'text-slate-400 group-hover:text-slate-600'"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          Laporan Profit & Loss
        </NuxtLink>

        <div class="pt-5 pb-2">
          <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Kategori & Akun</p>
        </div>

        <NuxtLink
          to="/categories"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all group"
          :class="$route.path.startsWith('/categories') ? 'bg-sky-50 text-sky-700 font-bold border border-sky-100 shadow-xs' : 'hover:bg-slate-50 text-slate-600 hover:text-slate-900'"
        >
          <svg
            class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"
            :class="$route.path.startsWith('/categories') ? 'text-sky-600' : 'text-slate-400 group-hover:text-slate-600'"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
          </svg>
          Kategori
        </NuxtLink>

        <NuxtLink
          to="/coa"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all group"
          :class="$route.path.startsWith('/coa') ? 'bg-sky-50 text-sky-700 font-bold border border-sky-100 shadow-xs' : 'hover:bg-slate-50 text-slate-600 hover:text-slate-900'"
        >
          <svg
            class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110"
            :class="$route.path.startsWith('/coa') ? 'text-sky-600' : 'text-slate-400 group-hover:text-slate-600'"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
          Chart of Account (COA)
        </NuxtLink>
      </nav>

      <!-- Bottom System Status Card (Light theme) -->
      <div class="p-3.5 m-3 rounded-xl border border-slate-200 bg-slate-50/80 flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-sky-500 to-blue-600 flex items-center justify-center text-white text-xs font-bold shrink-0 shadow-xs">
          FM
        </div>
        <div class="truncate">
          <p class="text-xs font-bold text-slate-800 truncate">Finance System</p>
          <p class="text-[10px] text-emerald-600 font-bold truncate">&bull; Online</p>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 ml-64 flex flex-col min-w-0">
      <!-- Clean White Topbar -->
      <header class="h-16 bg-white border-b border-slate-200/90 px-8 flex items-center justify-between sticky top-0 z-20 shadow-xs">
        <!-- Search bar pill -->
        <div class="flex items-center gap-3 w-80">
          <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </span>
            <input
              type="text"
              readonly
              placeholder="Cari menu, transaksi atau laporan..."
              class="w-full pl-9 pr-4 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-full text-slate-600 focus:outline-hidden cursor-default"
            />
          </div>
        </div>

        <!-- Topbar Right Items -->
        <div class="flex items-center gap-4">
          <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-700 border border-sky-200">
              Finance Admin
            </span>

            <div class="w-8 h-8 rounded-full bg-sky-500 text-white flex items-center justify-center font-bold text-xs shadow-xs">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-8">
        <slot />
      </main>

      <!-- Footer -->
      <footer class="bg-white border-t border-slate-200/80 px-8 py-5 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
        <div class="flex items-center gap-2 font-semibold">
          <span class="text-sky-600 font-extrabold">Finance</span><span class="text-orange-500 font-black">Management</span>
          <span class="text-slate-300">|</span>
          <span>Financial Management System</span>
        </div>
        <div>
          &copy; {{ new Date().getFullYear() }} All Rights Reserved.
        </div>
      </footer>
    </div>
  </div>
</template>

<script setup lang="ts">
// Direct access without login
</script>
