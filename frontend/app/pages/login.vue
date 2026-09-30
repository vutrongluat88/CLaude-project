<script setup lang="ts">
definePageMeta({ layout: 'auth' })

const { login } = useAuth()
const route = useRoute()

const form = reactive({ email: '', password: '', remember: false })
const errors = ref<Record<string, string[]>>({})
const message = ref('')
const submitting = ref(false)

async function submit() {
  submitting.value = true
  errors.value = {}
  message.value = ''

  try {
    await login(form)
    await navigateTo((route.query.redirect as string) || '/')
  }
  catch (error) {
    errors.value = validationErrors(error)
    message.value = Object.keys(errors.value).length ? '' : apiErrorMessage(error, 'Unable to sign in.')
  }
  finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="w-full max-w-sm">
    <div class="mb-8 flex items-center gap-2.5 lg:hidden">
      <span class="grid size-9 place-items-center rounded-lg bg-indigo-500 text-white">
        <AppIcon name="users" class="size-5" />
      </span>
      <span class="font-semibold text-slate-900">User Admin</span>
    </div>

    <h1 class="text-2xl font-semibold text-slate-900">Sign in</h1>
    <p class="mt-1.5 text-sm text-slate-500">Enter your credentials to access the dashboard.</p>

    <form class="mt-8 space-y-5" novalidate @submit.prevent="submit">
      <p v-if="message" class="rounded-lg bg-rose-50 px-3.5 py-2.5 text-sm text-rose-700">
        {{ message }}
      </p>

      <div>
        <label for="email" class="block text-sm font-medium text-slate-700">Email</label>
        <input
          id="email"
          v-model="form.email"
          type="email"
          autocomplete="email"
          autofocus
          class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-xs outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
          :class="errors.email && 'border-rose-400'"
          placeholder="admin@example.com"
        >
        <p v-if="errors.email" class="mt-1.5 text-sm text-rose-600">{{ errors.email[0] }}</p>
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
        <input
          id="password"
          v-model="form.password"
          type="password"
          autocomplete="current-password"
          class="mt-1.5 block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-xs outline-none transition placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
          :class="errors.password && 'border-rose-400'"
          placeholder="••••••••"
        >
        <p v-if="errors.password" class="mt-1.5 text-sm text-rose-600">{{ errors.password[0] }}</p>
      </div>

      <label class="flex items-center gap-2.5 text-sm text-slate-600">
        <input
          v-model="form.remember"
          type="checkbox"
          class="size-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/30"
        >
        Remember me
      </label>

      <button
        type="submit"
        :disabled="submitting"
        class="flex w-full items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500/40 disabled:opacity-60"
      >
        {{ submitting ? 'Signing in…' : 'Sign in' }}
      </button>
    </form>

    <p class="mt-6 rounded-lg bg-slate-100 px-3.5 py-3 text-xs text-slate-500">
      Seeded account — <span class="font-medium text-slate-700">admin@example.com</span> / <span class="font-medium text-slate-700">password</span>
    </p>
  </div>
</template>
