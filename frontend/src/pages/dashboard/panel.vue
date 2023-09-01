<script setup>

</script>

<template>
  <VRow>
    <VCol
      cols="12"
      style="text-align: center;"
    >
      <h1>Bienvenido al panel de control</h1>
    </VCol>  
  </VRow>
</template>

<style lang="scss" scoped>
  .card-list {
    --v-card-list-gap: 30px;
  }
</style>

<route lang="yaml">
  meta:
    action: ver
    subject: dashboard
</route>
