<template>
  <RouterView />
  <Toast position="top-right" />
  <ConfirmDialog />
  <PwaPrompt />
</template>

<script setup>
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'
import PwaPrompt from '@/components/PwaPrompt.vue'
</script>
