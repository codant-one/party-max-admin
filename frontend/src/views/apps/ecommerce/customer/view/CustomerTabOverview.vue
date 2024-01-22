<script setup>
import CustomerOrderTable from './CustomerOrderTable.vue'

import { useOrdersStores } from '@/stores/useOrders'

const ordersStores = useOrdersStores()

const orders = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalOrders = ref(0)

const paginationData = computed(() => {
  const firstIndex = orders.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = orders.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalOrders.value } registros`
})

watchEffect(fetchData)

async function fetchData() {

  let data = {
    search: searchQuery.value,
    orderByField: 'id',
    orderBy: 'desc',
    limit: rowPerPage.value,
    page: currentPage.value
  }

  await ordersStores.fetchOrders(data)

  orders.value = ordersStores.getOrders
  totalPages.value = ordersStores.last_page
  totalOrders.value = ordersStores.ordersTotalCount


}

</script>

<template>
  <VRow>
    <VCol
      cols="12"
      md="6"
    >
      <VCard>
        <VCardText class="d-flex gap-y-2 flex-column">
          <VAvatar
            variant="tonal"
            color="primary"
            icon="tabler-currency-dollar"
            rounded
          />
          <h4 class="text-h4">
            Saldo de la Cuenta
          </h4>
          <div>
            <span class="text-primary text-h4 me-2">$2345</span>
            <span class="text-body-1">Crédito restante</span>
            <p class="mb-0 text-base text-disabled">
              Saldo de cuenta para la próxima compra
            </p>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VCard>
        <VCardText class="d-flex gap-y-2 flex-column">
          <VAvatar
            variant="tonal"
            color="success"
            icon="tabler-gift-card"
            rounded
          />
          <h4 class="text-h4">
            Plan
          </h4>
          <div>
            <VChip
              color="success"
              class="mb-2"
              label
            >
              Platinum Miembro
            </VChip>
            <p class="mb-0 text-base text-disabled">
              3000 puntos para el siguiente nivel
            </p>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VCard>
        <VCardText class="d-flex gap-y-2 flex-column">
          <VAvatar
            variant="tonal"
            color="warning"
            icon="tabler-star"
            rounded
          />
          <h4 class="text-h4">
            Lista de deseos
          </h4>
          <div>
            <p class="mb-1">
              <span class="text-warning text-h5 me-2">15</span>
              <span class="text-body-1">artículos en la lista de deseos</span>
            </p>
            <p class="mb-0 text-disabled">
              Recibir notificación de los artículos.
            </p>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol
      cols="12"
      md="6"
    >
      <VCard>
        <VCardText class="d-flex gap-y-1 flex-column">
          <VAvatar
            variant="tonal"
            color="info"
            icon="tabler-discount-2"
            rounded
          />
          <h4 class="text-h4 mb-2">
            Cupones
          </h4>
          <div>
            <p class="mb-1">
              <span class="text-info text-h5 me-2">21</span>
              <span class="text-body-1">Cupones que ganas</span>
            </p>
            <p class="mb-0 text-disabled">
              Usar cupón en la próxima compra
            </p>
          </div>
        </VCardText>
      </VCard>
    </VCol>

    <VCol v-if="false">
      
      <v-card title="Pedidos realizados">
          <v-card-text class="d-flex flex-wrap py-4 gap-4">
            <div
              class="me-3"
              style="width: 80px;">
              
              <VSelect
                v-model="rowPerPage"
                density="compact"
                variant="outlined"
                :items="[10, 20, 30, 50]"/>
            </div>

            <v-spacer />

            <div class="d-flex align-center flex-wrap gap-4">
              <!-- 👉 Search  -->
              <div style="width: 10rem;">
                <v-text-field
                  v-model="searchQuery"
                  placeholder="Buscar"
                  density="compact"/>
              </div>
            </div>
          </v-card-text>

          <v-divider />

          <v-table class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col"> #ID </th>
                <th scope="col"> NOMBRE </th>
                <th scope="col"> DESCRIPCIÓN </th>
                <th scope="col"> ICONO </th>
                <th scope="col" v-if="$can('editar', 'categorías-faqs') || $can('eliminar', 'categorías-faqs')">
                  ACCIONES
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
              <tr 
                v-for="category in orders"
                :key="category.id"
                style="height: 3.75rem;">

                <td> {{ category.id }} </td>
                <td> {{ category.id }} </td>
                <td> {{ category.id }} </td>
                <td> 
                  {{ category.id }} 
                </td>
                <!-- 👉 Acciones -->
                <td class="text-center" style="width: 5rem;" v-if="$can('editar', 'categorías-faqs') || $can('eliminar', 'categorías-faqs')">      
                 
                </td>
              </tr>
            </tbody>
            <!-- 👉 table footer  -->
            <tfoot v-show="!orders.length">
              <tr>
                <td
                  colspan="5"
                  class="text-center">
                  Datos no disponibles
                </td>
              </tr>
            </tfoot>
          </v-table>
        
          <v-divider />

          <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3 px-5">
            <span class="text-sm text-disabled">
              {{ paginationData }}
            </span>

            <VPagination
              v-model="currentPage"
              size="small"
              :total-visible="5"
              :length="totalPages"/>
          
          </VCardText>
        </v-card>
      <!-- <CustomerOrderTable /> -->
    </VCol>
  </VRow>
</template>
