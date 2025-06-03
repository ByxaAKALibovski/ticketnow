<!-- layouts/AdminLayout.vue -->
<script setup>
import { RouterView } from 'vue-router'
</script>

<template>
  <div class="admin-wrapper">
    <RouterView />
  </div>
</template>

<style scoped>
.admin-wrapper {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}
</style>