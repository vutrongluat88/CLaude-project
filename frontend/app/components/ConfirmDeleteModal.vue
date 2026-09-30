<script setup lang="ts">
const props = defineProps<{ name: string, busy?: boolean, error?: string }>()
const emit = defineEmits<{ close: [], confirm: [] }>()
</script>

<template>
  <ModalShell title="Delete user" :subtitle="`This permanently removes ${props.name}.`" @close="emit('close')">
    <div class="px-5 py-5">
      <p v-if="props.error" class="rounded-lg bg-rose-50 px-3.5 py-2.5 text-sm text-rose-700">{{ props.error }}</p>
      <p v-else class="text-sm text-slate-600">This action cannot be undone.</p>

      <div class="mt-5 flex justify-end gap-2.5 border-t border-slate-200 pt-4">
        <button
          type="button"
          class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
          @click="emit('close')"
        >
          Cancel
        </button>
        <button
          type="button"
          :disabled="props.busy"
          class="rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700 disabled:opacity-60"
          @click="emit('confirm')"
        >
          {{ props.busy ? 'Deleting…' : 'Delete' }}
        </button>
      </div>
    </div>
  </ModalShell>
</template>
