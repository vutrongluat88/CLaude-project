<script setup lang="ts">
import type { Paginated, Stats, User } from '~/types'

const api = useApi()

const { data: stats } = await useAsyncData('stats', () =>
  api<{ data: Stats }>('/stats').then(response => response.data))

const { data: recent } = await useAsyncData('recent-users', () =>
  api<Paginated<User>>('/users', { query: { per_page: 5 } }).then(response => response.data))

const dateFormatter = new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium' })
const formatDate = (value: string) => dateFormatter.format(new Date(value))
</script>

<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-xl font-semibold text-slate-900">Overview</h2>
      <p class="mt-1 text-sm text-slate-500">A snapshot of the accounts in your system.</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <StatCard label="Total users" :value="stats?.total_users ?? 0" icon="users" tone="indigo" />
      <StatCard label="Administrators" :value="stats?.admins ?? 0" icon="shield" tone="emerald" />
      <StatCard label="New this month" :value="stats?.new_this_month ?? 0" icon="spark" tone="amber" />
      <StatCard label="Verified emails" :value="stats?.verified ?? 0" icon="check" tone="sky" />
    </div>

    <section class="rounded-xl border border-slate-200 bg-white shadow-xs">
      <header class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
        <div>
          <h3 class="text-sm font-semibold text-slate-900">Recently added</h3>
          <p class="mt-0.5 text-xs text-slate-500">The five newest accounts.</p>
        </div>
        <NuxtLink to="/users" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
          View all
        </NuxtLink>
      </header>

      <ul class="divide-y divide-slate-100">
        <li v-for="user in recent" :key="user.id" class="flex items-center gap-4 px-5 py-3.5">
          <UserAvatar :name="user.name" />
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-medium text-slate-900">{{ user.name }}</p>
            <p class="truncate text-sm text-slate-500">{{ user.email }}</p>
          </div>
          <span
            class="rounded-full px-2.5 py-1 text-xs font-medium"
            :class="user.role === 'admin' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-600'"
          >
            {{ user.role }}
          </span>
          <span class="hidden text-sm text-slate-400 sm:block">{{ formatDate(user.created_at) }}</span>
        </li>
        <li v-if="!recent?.length" class="px-5 py-10 text-center text-sm text-slate-500">
          No users yet.
        </li>
      </ul>
    </section>
  </div>
</template>
