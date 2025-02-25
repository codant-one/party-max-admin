<script setup>

import { useIpsStores } from '@/stores/useIps'
import { excelParser } from '@/plugins/csv/excelParser'
import router from '@/router'

const route = useRoute()
const ipsStores = useIpsStores()
const ip = ref([])

const advisor = ref({
  type: '',
  message: '',
  show: false
})

const isRequestOngoing = ref(true)

watchEffect(fetchData)

async function fetchData() {

  isRequestOngoing.value = true

  if(Number(route.params.id)) {
    const response = await ipsStores.showIp(Number(route.params.id))

    ip.value = response
  }

  isRequestOngoing.value = false
}

const back = () => {
    router.push({ name : 'dashboard-admin-ips'})
}

const openLink = function (data) {
  window.open(`https://www.google.com/maps?q=${data.coordinates}`)
}

const stateIp = async () => {
    isRequestOngoing.value = true

    let data = {
        is_blocked: ip.value.is_blocked === 0 ? 1 : 0
    }

    let res = await ipsStores.updateState(data, ip.value.id)

    isRequestOngoing.value = false 

    advisor.value = {
        type: res.data.success ? 'success' : 'error',
        message: res.data.success ? 'Ip actualizada!' : res.data.message,
        show: true
    }

    await fetchData()

    setTimeout(() => {
        advisor.value = {
            type: '',
            message: '',
            show: false
        }
    }, 3000)

    return true

}

const downloadCSV = async () => {

  isRequestOngoing.value = true
      
  let dataArray = [];

  ip.value.registrations.forEach(element => {

    let data = {
        FECHA: element.date,
        CÓDIGO_PAYU: element.response_code_pol,
        MENSAJE: element.message
    }
            
    dataArray.push(data)
  })

  excelParser()
    .exportDataFromJSON(dataArray, "ip-" + ip.value.ip, "csv");

  isRequestOngoing.value = false

}
</script>

<template>
  <div>
    <VAlert
        v-if="advisor.show"
        :type="advisor.type"
        class="mb-6">  
        {{ advisor.message }}
    </VAlert>
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

    <!-- 👉 Header  -->
    <div v-if="!isRequestOngoing" class="d-flex justify-space-between align-center flex-wrap gap-y-4 mb-6">
        <div>
            <div class="d-flex gap-2 align-center mb-2 flex-wrap">
                <h4 class="text-h4 font-weight-medium">
                        <span :class="ip.is_blocked === 1 ? 'is_blocked me-1' : ''">
                            {{ ip.ip }}
                        </span>
                    <span class="text-xs" v-if="ip.is_blocked === 1">(bloqueada)</span>
                </h4>
            </div>
        <div>
          <span class="text-body-1 d-flex flex-column">
            <span class="cursor-pointer text-primary" @click="openLink(ip)">{{ ip.location }}</span>
            <span class="text-xs"> Código Postal: {{ ip.postal_code }} </span>
            <span class="text-xs"> Timezone: {{ ip.timezone }} </span>
          </span>
        </div>
      </div>
      <div class="d-flex gap-4">
        <VBtn
          variant="tonal"
          color="secondary"
          @click="back"
         >
          REGRESAR
        </VBtn>
        <VBtn
          variant="tonal"
          color="error"
          prepend-icon="tabler-file-export"
          @click="downloadCSV"
        >
          EXPORTAR
        </VBtn>

        <VBtn
          variant="tonal"
          :color="ip.is_blocked === 0 ? 'warning' : 'success'"
          :prepend-icon="ip.is_blocked === 0 ? 'tabler-device-imac-off': 'tabler-device-imac'"
          @click="stateIp"
        >
            {{ip.is_blocked === 0 ? 'BLOQUEAR' : 'HABILITAR' }}
        </VBtn>
      </div>
    </div>

    <VRow v-if="!isRequestOngoing">
      <VCol
        cols="12"
        md="12"
        id="invoice-detail"
      >
        <VRow>
          <VCol cols="12" md="12">
            <!-- 👉 Product Details -->
            <VCard class="mb-6w">
              <VCardItem>
                <template #title>
                  <h5 class="text-h5">
                    Detalles de los registros de la ip
                  </h5>
                </template>
              </VCardItem>

              <VDivider />
              
              <v-table class="text-no-wrap product-preview-table">
                <thead>
                  <tr class="text-no-wrap">
                    <th class="font-weight-semibold"> FECHA </th>
                    <th class="font-weight-semibold"> CÓDIGO PAYU</th>
                    <th class="font-weight-semibold text-end"> MENSAJE</th>
                  </tr>
                </thead>
                <tbody>
                    <tr v-for="item in ip.registrations" style="height: 3.75rem;">
                        <td class="text-start justify-content-center">
                            <span class="text-high-emphasis font-weight-medium">
                                {{ item.date }}
                            </span>
                        </td>
                        <td class="text-start justify-content-center">
                            <span class="text-high-emphasis font-weight-medium">
                                {{ item.response_code_pol }}
                            </span>
                        </td>
                        <td class="text-end justify-content-center">
                            <span class="text-high-emphasis font-weight-medium">
                                <span> {{ item.message }}</span>
                            </span>
                        </td>
                    </tr>
                </tbody>
              </v-table>

              <VDivider />

              <VCardText>
                <div class="d-flex align-end flex-column">
                  <table class="text-high-emphasis">
                    <tbody>
                      <tr>
                        <td class="text-high-emphasis font-weight-medium">
                          Total:
                        </td>
                        <td class="font-weight-medium">
                          {{ ip.registrations.length }} registros
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VCol>
    </VRow>
  </div>
</template>
<style lang="scss">
  .product-preview-table {
    --v-table-row-height: 44px !important;
  }
  .is_blocked {
    text-decoration: line-through;
  }
</style>
<route lang="yaml">
    meta:
      action: ver
      subject: ips
</route>