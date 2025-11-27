<script setup>

import Pending from './pending.vue'
import ByPay from './bypay.vue'
import Paid from './paid.vue'
import Suppliers from './suppliers.vue'
import Toaster from "@/components/common/Toaster.vue";

const currentTab = ref(0)
const rol = ref(null)
const userData = ref(null)

onMounted(async () => {

    userData.value = JSON.parse(localStorage.getItem('user_data') || 'null')
    rol.value = userData.value.roles[0].name

})
</script>

<template>
  <section>
    <Toaster />
    <Suppliers v-if="rol === 'Proveedor'" />

    <template v-else>
        <VTabs
            v-model="currentTab"
            grow
            stacked
        >
        <VTab>
            <VIcon
                icon="mdi-file-document-alert"
                class="mb-2"
            />
            <span>Por procesar</span>
        </VTab>
        <VTab>
            <VIcon
                icon="mdi-file-document-edit"
                class="mb-2"
            />
            <span>Por pagar</span>
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
            <VWindowItem><Pending /></VWindowItem>
            <VWindowItem><ByPay /></VWindowItem>
            <VWindowItem><Paid /></VWindowItem>
        </VWindow>
    </template>
   
  </section>
</template>

<style scoped>
    .v-tabs .v-btn {
        text-transform: none !important;
    }
</style>

<route lang="yaml">
  meta:
    action: ver
    subject: facturas
</route>