<script setup>

import { useReferralsStores } from '@/stores/useReferrals'
import { themeConfig } from '@themeConfig'
import { excelParser } from '@/plugins/csv/excelParser'
import router from '@/router'

const route = useRoute()
const referralsStores = useReferralsStores()
const emitter = inject("emitter")

const products = ref([])
const isDialogVisible = ref(false)

const isRequestOngoing = ref(true)

const total = computed(() => {
    return products.value.details.reduce((sum, item) => {
        return sum + (item.quantity ? parseInt(item.quantity) : 0);
    }, 0);
})

watchEffect(fetchData)

async function fetchData() {

  isRequestOngoing.value = true

  if(Number(route.params.id)) {
    const response = await referralsStores.userDetails({id: Number(route.params.id)})

    products.value = response.products
  }

  isRequestOngoing.value = false
}

const back = () => {
    router.push({ name : 'dashboard-referrals'})
}

const showDialog = referralData => {
  isDialogVisible.value = true
}

const submit = () => {

    let data = {
        id: Number(route.params.id),
        products: JSON.stringify(products.value.details)
    }

    isRequestOngoing.value = true
    isDialogVisible.value = false
        
    referralsStores.upload(data)
        .then(response => {

            let data = {
                message: 'Productos cargados!',
                error: false
            }

            router.push({ name : 'dashboard-referrals'})
            emitter.emit('toast', data)

        }).catch(error => {
            let data = {
                message: 'ERROR',
                error: true
            }

            router.push({ name : 'dashboard-referrals'})
            emitter.emit('toast', data)
        })
}

const downloadCSV = async () => {

  isRequestOngoing.value = true
      
  let dataArray = [];

  products.value.details.forEach(element => {

    let data = {
        PRODUCTO: element.color.product.name,
        SKU: element.color.sku,
        CANTIDAD_RECIBIDA: element.delivered
    }
            
    dataArray.push(data)
  })

  excelParser()
    .exportDataFromJSON(dataArray, "remision-" + products.value.date, "csv");

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

    <!-- 👉 Header  -->
    <div v-if="!isRequestOngoing" class="d-flex justify-space-between align-center flex-wrap gap-y-4 mb-6">
      <div>
        <div class="d-flex gap-2 align-center mb-2 flex-wrap">
          <h4 class="text-h4 font-weight-medium">
            {{ products.user.supplier ?  products.user.supplier.company_name : 'ADMINISTRADOR' }} 
          </h4>
        </div>
        <div>
          <span class="text-body-1">
            {{ products.user.name }} {{ products.user.last_name }}
            <span class="text-xs"> ({{products.user.email}}) </span>
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
          v-if="$can('editar', 'remisiones') && !products.delivered"
          variant="tonal"
          color="primary"
          @click="showDialog"
        >
          RECIBIR
        </VBtn>
        <VBtn
          v-if="$can('editar', 'remisiones') && products.delivered"
          variant="tonal"
          color="primary"
          prepend-icon="tabler-file-export"
          @click="downloadCSV"
        >
          EXPORTAR
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
                    Detalles de la remisión
                  </h5>
                </template>
              </VCardItem>

              <VDivider />
              
              <v-table class="text-no-wrap product-preview-table">
                <thead>
                  <tr class="text-no-wrap">
                    <th class="font-weight-semibold"> PRODUCTO </th>
                    <th class="font-weight-semibold" v-if="!products.delivered"> STOCK</th>
                    <th class="font-weight-semibold text-end"> CANTIDAD RECIBIDA</th>
                  </tr>
                </thead>
                <tbody>
                    <tr v-for="item in products.details" style="height: 3.75rem;">
                        <td class="text-wrap w-75">
                            <div class="d-flex gap-x-3 align-center my-1">
                                <VAvatar
                                    v-if="item.color.images.length > 0"
                                    size="150"
                                    variant="outlined"
                                    :image="themeConfig.settings.urlStorage + item.color.images[0].image"
                                    rounded="lg"
                                />
                                <VAvatar
                                    v-else
                                    size="150"
                                    variant="outlined"
                                    :image="themeConfig.settings.urlStorage + item.color.product.image"
                                    rounded="lg"
                                />
                                <div class="d-flex flex-column align-start">
                                    <span class="text-body-1 font-weight-medium">
                                        {{ item.color.product.name }}
                                    </span>
                                    <span class="text-sm text-disabled">
                                        Color: {{ item.color.color.name }}
                                    </span>
                                    <span class="text-sm text-disabled">
                                        Tienda: {{ item.color.product.user.user_detail.store_name ?? (item.color.product.user.supplier?.company_name ?? (item.color.product.user.name + ' ' + (item.color.product.user.last_name ?? ''))) }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="text-start justify-content-center" v-if="!products.delivered">
                            <span class="text-high-emphasis font-weight-medium">
                                {{ item.color.product.stock }}
                            </span>
                        </td>
                        <td class="text-end justify-content-center">
                            <span class="text-high-emphasis font-weight-medium">
                                <VTextField
                                    v-if="!products.delivered"
                                    v-model="item.quantity"
                                    type="number"
                                    label="Stock"
                                    :readonly="products.delivered === 0 ? false : true"
                                />
                                <span v-else> {{ item.quantity }}</span>
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
                          {{ total }} productos
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

    <!-- 👉 Confirm Delete -->
    <VDialog
      v-model="isDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isDialogVisible = !isDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Confirmar remisión">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de cargar los stock a todos los productos?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="submit">
              Cargar
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </div>
</template>
<style lang="scss">
  .product-preview-table {
    --v-table-row-height: 44px !important;
  }
</style>
<route lang="yaml">
    meta:
      action: ver
      subject: remisiones
</route>