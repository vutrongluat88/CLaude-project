<script setup lang="ts">
const { user, logout } = useAuth()
const route = useRoute()
const sidebarOpen = ref(false)

const nav = [
  { label: 'Dashboard', to: '/', icon: 'dashboard' },
  { label: 'Users', to: '/users', icon: 'users' },
]

watch(() => route.fullPath, () => { sidebarOpen.value = false })
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-800">
    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-slate-900 text-slate-300 transition-transform lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="flex h-16 items-center gap-2.5 px-6">
        <span class="grid size-9 place-items-center rounded-lg bg-indigo-500 text-white">
          <AppIcon name="users" class="size-5" />
        </span>
        <span class="font-semibold text-white">User Admin</span>
      </div>

      <nav class="flex-1 space-y-1 px-3 py-4">
        <NuxtLink
          v-for="item in nav"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
          :class="route.path === item.to
            ? 'bg-slate-800 text-white'
            : 'text-slate-400 hover:bg-slate-800/60 hover:text-white'"
        >
          <AppIcon :name="item.icon" />
          {{ item.label }}
        </NuxtLink>
      </nav>

      <div class="border-t border-slate-800 p-3">
        <div class="flex items-center gap-3 rounded-lg px-3 py-2">
          <UserAvatar v-if="user" :name="user.name" size="sm" />
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-white">{{ user?.name }}</p>
            <p class="truncate text-xs text-slate-500">{{ user?.email }}</p>
          </div>
        </div>
        <button
          type="button"
          class="mt-1 flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-slate-400 transition hover:bg-slate-800/60 hover:text-white"
          @click="logout()"
        >
          <AppIcon name="logout" />
          Sign out
        </button>
      </div>
    </aside>

    <!-- Backdrop (mobile) -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-30 bg-slate-900/50 lg:hidden"
      @click="sidebarOpen = false"
    />

    <div class="lg:pl-64">
      <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-slate-200 bg-white/90 px-4 backdrop-blur sm:px-6">
        <button
          type="button"
          class="-ml-1 rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden"
          aria-label="Open navigation"
          @click="sidebarOpen = true"
        >
          <AppIcon name="menu" />
        </button>
        <h1 class="text-base font-semibold text-slate-900">
          {{ nav.find(item => item.to === route.path)?.label ?? 'User Admin' }}
        </h1>
        <div class="ml-auto hidden items-center gap-3 sm:flex">
          <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
            {{ user?.role }}
          </span>
          <UserAvatar v-if="user" :name="user.name" size="sm" />
        </div>
      </header>

      <main class="px-4 py-6 sm:px-6 lg:px-8">
        <slot />
      </main>
    </div>
  </div>
</template>
