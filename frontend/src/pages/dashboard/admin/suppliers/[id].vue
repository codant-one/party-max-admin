<script setup>

import { useSuppliersStores } from '@/stores/useSuppliers'
import { format } from 'date-fns';
import { es } from 'date-fns/locale';

import CustomerBioPanel from '@/views/apps/ecommerce/customer/view/CustomerBioPanel.vue'
import CustomerTabAddressAndBilling from '@/views/apps/ecommerce/customer/view/CustomerTabAddressAndBilling.vue'
import CustomerTabOverview from '@/views/apps/ecommerce/customer/view/CustomerTabOverview.vue'
import CustomerTabSecurity from '@/views/apps/ecommerce/customer/view/CustomerTabSecurity.vue'

const route = useRoute()
const suppliersStores = useSuppliersStores()

const userTab = ref(null)

const supplier = ref(null)
const online = ref(null)

const isRequestOngoing = ref(true)

const tabs = [
  { title: 'Descripción general' },
  { title: 'Seguridad' },
  { title: 'Envíos' }
]

const advisor = ref({
  type: '',
  message: '',
  show: false
})

const showAlert = function(alert) {
  advisor.value.show = alert.value.show
  advisor.value.type = alert.value.type
  advisor.value.message = alert.value.message
}

const isAddCustomerDrawerOpen = ref(false)

watchEffect(fetchData)

async function fetchData() {

  isRequestOngoing.value = true

  if(Number(route.params.id)) {
    supplier.value = await suppliersStores.showSupplier(Number(route.params.id))
    console.log('proveedor', supplier.value)
    online.value = supplier.value.user.online

  }

  isRequestOngoing.value = false
}
</script>

<template>
  <div>
    <VDialog
        v-model="isRequestOngoing"
        width="300"
        persistent>
                    
        <VCard
            color="primary"
            width="300">
                        
            <VCardText class="pt-3">
                Cargando

                <VProgressLinear
                    indeterminate
                    color="white"
                    class="mb-0"/>
            </VCardText>
        </VCard>
    </VDialog>

    <v-col cols="12">
        <v-alert
          v-if="advisor.show"
          :type="advisor.type"
          class="mb-6">
            {{ advisor.message }}
        </v-alert>
    </v-col>

    <!-- 👉 Header  -->
    <div v-if="supplier" class="d-flex justify-space-between align-center flex-wrap gap-y-4 mb-6">
        <div>
            <div class="d-flex gap-2 align-center mb-2 flex-wrap">
            <h4 class="text-h4 font-weight-medium">
                Proveedor ID #{{ route.params.id }}
            </h4>
            </div>
            <div>
            <span class="text-body-1" v-if="online">
                {{  format(online, 'MMMM d, yyyy, H:mm', { locale: es }).replace(/(^|\s)\S/g, (char) => char.toUpperCase()) }}
                <span class="text-xs">
                    (Última Conexión)
                </span>
            </span>
            </div>
        </div>
        <div class="d-flex gap-4">
            <VBtn
                variant="tonal"
                color="secondary"
                class="mb-2"
                :to="{ name: 'dashboard-admin-suppliers' }"
                >
                Regresar
            </VBtn>
        </div>
    </div>
    <!-- 👉 Customer Profile  -->
    <VRow v-if="supplier">
        <VCol
            cols="12"
            md="5"
            lg="4"
        >
            <CustomerBioPanel :customer-data="supplier" />
        </VCol>
        <VCol
            cols="12"
            md="7"
            lg="8">
            <VTabs
                v-model="userTab"
                class="v-tabs-pill mb-3 disable-tab-transition">
                <VTab
                    v-for="tab in tabs"
                    :key="tab.title">
                    <span>{{ tab.title }}</span>
                </VTab>
            </VTabs>
            <VWindow
                v-model="userTab"
                class="disable-tab-transition"
                :touch="false"
            >
                <VWindowItem>
                    <CustomerTabOverview />
                </VWindowItem>
                <VWindowItem>
                    <CustomerTabSecurity 
                        :user_id="supplier.user_id"
                        @alert="showAlert" />
                </VWindowItem>
                <VWindowItem>
                    <CustomerTabAddressAndBilling :addresses="supplier.addresses"/>
                </VWindowItem>
            </VWindow>
        </VCol>
    </VRow>
  </div>
</template>

<route lang="yaml">
    meta:
      action: ver
      subject: proveedores
</route>