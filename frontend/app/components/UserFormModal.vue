<script setup lang="ts">
import type { User } from '~/types'

const props = defineProps<{ user?: User | null }>()
const emit = defineEmits<{ close: [], saved: [message: string] }>()

const api = useApi()
const isEditing = computed(() => Boolean(props.user))

const form = reactive({
  name: props.user?.name ?? '',
  email: props.user?.email ?? '',
  password: '',
  role: props.user?.role ?? 'user',
})

const errors = ref<Record<string, string[]>>({})
const message = ref('')
const submitting = ref(false)

const fieldClass = (field: string) => [
  'mt-1.5 block w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm shadow-xs outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20',
  errors.value[field] ? 'border-rose-400' : 'border-slate-300',
]

async function submit() {
  submitting.value = true
  errors.value = {}
  message.value = ''

  try {
    if (isEditing.value) {
      await api(`/users/${props.user!.id}`, { method: 'PUT', body: form })
      emit('saved', `${form.name} was updated.`)
    }
    else {
      await api('/users', { method: 'POST', body: form })
      emit('saved', `${form.name} was created.`)
    }
  }
  catch (error) {
    errors.value = validationErrors(error)
    message.value = Object.keys(errors.value).length ? '' : apiErrorMessage(error)
  }
  finally {
    submitting.value = false
  }
}
</script>

<template>
  <ModalShell
    :title="isEditing ? 'Edit user' : 'New user'"
    :subtitle="isEditing ? 'Update the account details.' : 'Add an account to the dashboard.'"
    @close="emit('close')"
  >
    <form class="space-y-4 px-5 py-5" novalidate @submit.prevent="submit">
      <p v-if="message" class="rounded-lg bg-rose-50 px-3.5 py-2.5 text-sm text-rose-700">{{ message }}</p>

      <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Name</label>
        <input id="name" v-model="form.name" type="text" :class="fieldClass('name')" placeholder="Nguyen Van A">
        <p v-if="errors.name" class="mt-1.5 text-sm text-rose-600">{{ errors.name[0] }}</p>
      </div>

      <div>
        <label for="user-email" class="block text-sm font-medium text-slate-700">Email</label>
        <input id="user-email" v-model="form.email" type="email" :class="fieldClass('email')" placeholder="user@example.com">
        <p v-if="errors.email" class="mt-1.5 text-sm text-rose-600">{{ errors.email[0] }}</p>
      </div>

      <div>
        <label for="user-password" class="block text-sm font-medium text-slate-700">
          Password
          <span v-if="isEditing" class="font-normal text-slate-400">— leave blank to keep the current one</span>
        </label>
        <input
          id="user-password"
          v-model="form.password"
          type="password"
          :class="fieldClass('password')"
          :placeholder="isEditing ? '••••••••' : 'At least 8 characters'"
        >
        <p v-if="errors.password" class="mt-1.5 text-sm text-rose-600">{{ errors.password[0] }}</p>
      </div>

      <div>
        <label for="role" class="block text-sm font-medium text-slate-700">Role</label>
        <select id="role" v-model="form.role" :class="fieldClass('role')">
          <option value="user">User</option>
          <option value="admin">Admin</option>
        </select>
        <p v-if="errors.role" class="mt-1.5 text-sm text-rose-600">{{ errors.role[0] }}</p>
      </div>

      <div class="flex justify-end gap-2.5 border-t border-slate-200 pt-4">
        <button
          type="button"
          class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
          @click="emit('close')"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="submitting"
          class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-60"
        >
          {{ submitting ? 'Saving…' : isEditing ? 'Save changes' : 'Create user' }}
        </button>
      </div>
    </form>
  </ModalShell>
</template>
