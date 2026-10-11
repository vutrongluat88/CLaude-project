<script setup lang="ts">
// Inline 24px stroke icons; keeps the bundle free of an icon dependency.
const paths: Record<string, string> = {
  dashboard: 'M3 12l9-9 9 9M5 10v10h4v-6h6v6h4V10',
  users: 'M17 20v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 6a4 4 0 100 8 4 4 0 000-8zM23 20v-2a4 4 0 00-3-3.87M16 2.13A4 4 0 0119 6a4 4 0 01-3 3.87',
  search: 'M11 19a8 8 0 100-16 8 8 0 000 16zM21 21l-4.35-4.35',
  plus: 'M12 5v14M5 12h14',
  pencil: 'M12 20h9M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4 12.5-12.5z',
  trash: 'M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6',
  logout: 'M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9',
  shield: 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z',
  check: 'M20 6L9 17l-5-5',
  spark: 'M13 2L3 14h7l-1 8 10-12h-7l1-8z',
  chevronLeft: 'M15 18l-6-6 6-6',
  chevronRight: 'M9 18l6-6-6-6',
  close: 'M18 6L6 18M6 6l12 12',
  menu: 'M3 6h18M3 12h18M3 18h18',
}

const props = defineProps<{ name: keyof typeof paths | string, class?: string }>()
</script>

<template>
  <svg
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.8"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    :class="props.class ?? 'size-5'"
  >
    <path :d="paths[props.name] ?? ''" />
  </svg>
</template>
