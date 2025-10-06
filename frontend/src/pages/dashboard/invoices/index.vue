<script setup>

import All from './all.vue'
import Pending from './pending.vue'
import Finished from './finished.vue'
import Toaster from "@/components/common/Toaster.vue";

const currentTab = ref(0)

</script>

<template>
  <section>
    <Toaster />
    <VTabs
        v-model="currentTab"
        grow
        stacked
    >
      <VTab>
          <VIcon
              icon="mdi-file-document-multiple-outline"
              class="mb-2"
          />
          <span>Todas</span>
      </VTab>
      <VTab>
          <VIcon
              icon="mdi-file-document-alert"
              class="mb-2"
          />
          <span>Pendientes</span>
      </VTab>
      <VTab>
          <VIcon
              icon="mdi-file-document-check"
              class="mb-2"
          />
          <span>Pagadas</span>
      </VTab>
    </VTabs>
    <VDivider />

    <VWindow
        v-model="currentTab"
        class="mt-5"
    >
        <VWindowItem><All /></VWindowItem>
        <VWindowItem><Pending /></VWindowItem>
        <VWindowItem><Finished /></VWindowItem>
    </VWindow>
  </section>
</template>

<route lang="yaml">
  meta:
    action: ver
    subject: facturas
</route>