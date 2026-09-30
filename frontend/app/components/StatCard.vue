<script setup lang="ts">
defineProps<{
  label: string
  value: number | string
  icon: string
  tone: 'indigo' | 'emerald' | 'amber' | 'sky'
  hint?: string
}>()

const tones = {
  indigo: 'bg-indigo-50 text-indigo-600',
  emerald: 'bg-emerald-50 text-emerald-600',
  amber: 'bg-amber-50 text-amber-600',
  sky: 'bg-sky-50 text-sky-600',
}
</script>

<template>
  <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-xs">
    <div class="flex items-start justify-between">
      <div>
        <p class="text-sm font-medium text-slate-500">{{ label }}</p>
        <p class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">{{ value }}</p>
      </div>
      <span class="grid size-10 place-items-center rounded-lg" :class="tones[tone]">
        <AppIcon :name="icon" />
      </span>
    </div>
    <p v-if="hint" class="mt-3 text-xs text-slate-500">{{ hint }}</p>
  </div>
</template>
