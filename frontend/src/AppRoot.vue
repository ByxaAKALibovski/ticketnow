<!-- AppRoot.vue -->
<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const layout = computed(() => {
  return route.meta.layout || 'default'
})
</script>

<template>
  <component :is="`${layout}Layout`" />
</template>