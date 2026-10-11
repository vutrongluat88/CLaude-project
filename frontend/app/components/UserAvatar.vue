<script setup lang="ts">
const props = defineProps<{ name: string, size?: 'sm' | 'md' }>()

// Stable colour per name so a row keeps its avatar across re-renders.
const palettes = [
  'bg-indigo-100 text-indigo-700',
  'bg-emerald-100 text-emerald-700',
  'bg-amber-100 text-amber-700',
  'bg-rose-100 text-rose-700',
  'bg-sky-100 text-sky-700',
  'bg-violet-100 text-violet-700',
]

const initials = computed(() =>
  props.name
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map(part => part[0]!.toUpperCase())
    .join(''),
)

const palette = computed(() => {
  const sum = [...props.name].reduce((total, char) => total + char.charCodeAt(0), 0)

  return palettes[sum % palettes.length]
})
</script>

<template>
  <span
    class="inline-flex shrink-0 items-center justify-center rounded-full font-semibold"
    :class="[palette, size === 'sm' ? 'size-8 text-xs' : 'size-10 text-sm']"
  >
    {{ initials }}
  </span>
</template>
