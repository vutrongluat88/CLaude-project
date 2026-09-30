<template>
  <div class="grid min-h-screen bg-slate-50 lg:grid-cols-2">
    <!-- Brand panel -->
    <div class="relative hidden flex-col justify-between overflow-hidden bg-slate-900 p-12 text-white lg:flex">
      <div class="flex items-center gap-2.5">
        <span class="grid size-9 place-items-center rounded-lg bg-indigo-500">
          <AppIcon name="users" class="size-5" />
        </span>
        <span class="font-semibold">User Admin</span>
      </div>

      <div class="relative z-10 max-w-md">
        <h2 class="text-3xl font-semibold leading-tight">
          Manage your team from one place.
        </h2>
        <p class="mt-4 text-slate-400">
          Create accounts, assign roles and keep track of who has access — all from a single dashboard.
        </p>
      </div>

      <p class="text-sm text-slate-500">Laravel 13 · Nuxt 4 · Sanctum</p>

      <div class="absolute -right-24 -top-24 size-96 rounded-full bg-indigo-600/20 blur-3xl" />
      <div class="absolute -bottom-32 -left-16 size-96 rounded-full bg-sky-500/10 blur-3xl" />
    </div>

    <div class="flex items-center justify-center p-6 sm:p-12">
      <slot />
    </div>
  </div>
</template>
