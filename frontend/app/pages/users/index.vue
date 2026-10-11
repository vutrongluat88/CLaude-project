<script setup lang="ts">
import type { Paginated, User } from '~/types'

const api = useApi()
const { user: currentUser } = useAuth()

const search = ref('')
const role = ref('')
const page = ref(1)
const perPage = 10

const { data, pending, refresh } = await useAsyncData(
  'users',
  () => api<Paginated<User>>('/users', {
    query: { search: search.value || undefined, role: role.value || undefined, page: page.value, per_page: perPage },
  }),
  { watch: [page, role] },
)

// Debounce typing so a search does not fire a request per keystroke.
let searchTimer: ReturnType<typeof setTimeout>
watch(search, () => {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    page.value = 1
    refresh()
  }, 300)
})

const users = computed(() => data.value?.data ?? [])
const meta = computed(() => data.value?.meta)

const editing = ref<User | null>(null)
const showForm = ref(false)
const deleting = ref<User | null>(null)
const deleteBusy = ref(false)
const deleteError = ref('')
const toast = ref('')

function openCreate() {
  editing.value = null
  showForm.value = true
}

function openEdit(user: User) {
  editing.value = user
  showForm.value = true
}

async function onSaved(message: string) {
  showForm.value = false
  await refresh()
  flash(message)
}

async function confirmDelete() {
  deleteBusy.value = true
  deleteError.value = ''

  try {
    await api(`/users/${deleting.value!.id}`, { method: 'DELETE' })
    const name = deleting.value!.name
    deleting.value = null
    await refresh()
    flash(`${name} was deleted.`)
  }
  catch (error) {
    deleteError.value = apiErrorMessage(error, 'Could not delete this user.')
  }
  finally {
    deleteBusy.value = false
  }
}

function flash(message: string) {
  toast.value = message
  setTimeout(() => (toast.value = ''), 3500)
}

const dateFormatter = new Intl.DateTimeFormat('en-GB', { dateStyle: 'medium' })
const formatDate = (value: string) => dateFormatter.format(new Date(value))
</script>

<template>
  <div class="space-y-5">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <h2 class="text-xl font-semibold text-slate-900">Users</h2>
        <p class="mt-1 text-sm text-slate-500">
          {{ meta?.total ?? 0 }} account{{ meta?.total === 1 ? '' : 's' }} in total.
        </p>
      </div>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
        @click="openCreate"
      >
        <AppIcon name="plus" class="size-4" />
        New user
      </button>
    </div>

    <p v-if="toast" class="rounded-lg bg-emerald-50 px-3.5 py-2.5 text-sm text-emerald-700">{{ toast }}</p>

    <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs">
      <div class="flex flex-wrap gap-3 border-b border-slate-200 p-4">
        <div class="relative min-w-56 flex-1">
          <AppIcon name="search" class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400" />
          <input
            v-model="search"
            type="search"
            placeholder="Search by name or email…"
            class="w-full rounded-lg border border-slate-300 py-2.5 pl-9 pr-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
          >
        </div>
        <select
          v-model="role"
          class="rounded-lg border border-slate-300 px-3 py-2.5 text-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
        >
          <option value="">All roles</option>
          <option value="admin">Admin</option>
          <option value="user">User</option>
        </select>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-left">
          <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
              <th class="px-5 py-3">User</th>
              <th class="px-5 py-3">Role</th>
              <th class="hidden px-5 py-3 md:table-cell">Verified</th>
              <th class="hidden px-5 py-3 sm:table-cell">Created</th>
              <th class="px-5 py-3 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="pending && !users.length">
              <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">Loading…</td>
            </tr>
            <tr v-else-if="!users.length">
              <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">No users match this filter.</td>
            </tr>
            <tr v-for="user in users" :key="user.id" class="transition hover:bg-slate-50/70">
              <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                  <UserAvatar :name="user.name" size="sm" />
                  <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-slate-900">
                      {{ user.name }}
                      <span v-if="user.id === currentUser?.id" class="ml-1 text-xs font-normal text-slate-400">(you)</span>
                    </p>
                    <p class="truncate text-sm text-slate-500">{{ user.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-3.5">
                <span
                  class="rounded-full px-2.5 py-1 text-xs font-medium"
                  :class="user.role === 'admin' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-600'"
                >
                  {{ user.role }}
                </span>
              </td>
              <td class="hidden px-5 py-3.5 md:table-cell">
                <span v-if="user.email_verified_at" class="inline-flex items-center gap-1.5 text-sm text-emerald-600">
                  <AppIcon name="check" class="size-4" /> Yes
                </span>
                <span v-else class="text-sm text-slate-400">No</span>
              </td>
              <td class="hidden px-5 py-3.5 text-sm text-slate-500 sm:table-cell">{{ formatDate(user.created_at) }}</td>
              <td class="px-5 py-3.5">
                <div class="flex justify-end gap-1">
                  <button
                    type="button"
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-100 hover:text-indigo-600"
                    :aria-label="`Edit ${user.name}`"
                    @click="openEdit(user)"
                  >
                    <AppIcon name="pencil" class="size-4" />
                  </button>
                  <button
                    type="button"
                    :disabled="user.id === currentUser?.id"
                    class="rounded-lg p-2 text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent disabled:hover:text-slate-400"
                    :aria-label="`Delete ${user.name}`"
                    :title="user.id === currentUser?.id ? 'You cannot delete your own account' : undefined"
                    @click="deleting = user; deleteError = ''"
                  >
                    <AppIcon name="trash" class="size-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between border-t border-slate-200 px-5 py-3.5">
        <p class="text-sm text-slate-500">
          Showing {{ meta.from }}–{{ meta.to }} of {{ meta.total }}
        </p>
        <div class="flex items-center gap-1">
          <button
            type="button"
            :disabled="meta.current_page === 1"
            class="rounded-lg border border-slate-300 p-2 text-slate-600 transition hover:bg-slate-50 disabled:opacity-40"
            aria-label="Previous page"
            @click="page--"
          >
            <AppIcon name="chevronLeft" class="size-4" />
          </button>
          <span class="px-2 text-sm text-slate-600">{{ meta.current_page }} / {{ meta.last_page }}</span>
          <button
            type="button"
            :disabled="meta.current_page === meta.last_page"
            class="rounded-lg border border-slate-300 p-2 text-slate-600 transition hover:bg-slate-50 disabled:opacity-40"
            aria-label="Next page"
            @click="page++"
          >
            <AppIcon name="chevronRight" class="size-4" />
          </button>
        </div>
      </div>
    </section>

    <UserFormModal
      v-if="showForm"
      :user="editing"
      @close="showForm = false"
      @saved="onSaved"
    />

    <ConfirmDeleteModal
      v-if="deleting"
      :name="deleting.name"
      :busy="deleteBusy"
      :error="deleteError"
      @close="deleting = null"
      @confirm="confirmDelete"
    />
  </div>
</template>
