<script setup lang="ts">
const props = defineProps<{ title: string, subtitle?: string }>()
const emit = defineEmits<{ close: [] }>()

// Close on Escape, and stop the page behind from scrolling while open.
onMounted(() => {
  const onKey = (event: KeyboardEvent) => event.key === 'Escape' && emit('close')

  document.addEventListener('keydown', onKey)
  document.body.style.overflow = 'hidden'

  onUnmounted(() => {
    document.removeEventListener('keydown', onKey)
    document.body.style.overflow = ''
  })
})
</script>

<template>
  <div class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="emit('close')" />

    <div
      role="dialog"
      aria-modal="true"
      class="relative w-full max-w-md rounded-t-2xl bg-white shadow-xl sm:rounded-2xl"
    >
      <header class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
        <div>
          <h3 class="text-base font-semibold text-slate-900">{{ props.title }}</h3>
          <p v-if="props.subtitle" class="mt-0.5 text-sm text-slate-500">{{ props.subtitle }}</p>
        </div>
        <button
          type="button"
          class="-mr-1 rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-600"
          aria-label="Close"
          @click="emit('close')"
        >
          <AppIcon name="close" />
        </button>
      </header>

      <slot />
    </div>
  </div>
</template>
