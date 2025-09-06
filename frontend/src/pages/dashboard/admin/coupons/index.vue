<script setup>

import { useCouponsStores } from '@/stores/useCoupons'
import { useClientsStores } from '@/stores/useClients'
import { excelParser } from '@/plugins/csv/excelParser'
import { themeConfig } from '@themeConfig'
import { avatarText, formatNumber } from '@/@core/utils/formatters'
import AddNewCouponDrawer from './AddNewCouponDrawer.vue'
import router from '@/router'

const couponsStores = useCouponsStores()
const clientsStores = useClientsStores()

const coupons = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalCoupons = ref(0)
const isRequestOngoing = ref(true)
const isConfirmDeleteDialogVisible = ref(false)
const isAddNewCouponDrawerVisible = ref(false)
const selectedCoupon = ref({})

const listClients = ref([])

const advisor = ref({
  type: '',
  message: '',
  show: false
})

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = coupons.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = coupons.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalCoupons.value } registros`
})

const loadClients = () => {
  listClients.value = clientsStores.getClients
}

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value

  if (!isAddNewCouponDrawerVisible.value)
    selectedCoupon.value = {}
})

onMounted(async () => {

  await clientsStores.fetchClients()

  loadClients()
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

  isRequestOngoing.value = true

  await couponsStores.fetchCoupons(data)

  coupons.value = couponsStores.getCoupons
  totalPages.value = couponsStores.last_page
  totalCoupons.value = couponsStores.couponsTotalCount

  isRequestOngoing.value = false
}

const showDeleteDialog = couponData => {
  isConfirmDeleteDialogVisible.value = true
  selectedCoupon.value = { ...couponData }
}

const removeCoupon = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await couponsStores.deleteCoupon(selectedCoupon.value.id)
  selectedCoupon.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Cupón eliminado!' : res.data.message,
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

const seeClient = clientData => {
  router.push({ name : 'dashboard-admin-clients-id', params: { id: clientData.id } })
}

const showCoupon = id => {
    router.push({ name : 'dashboard-admin-coupons-id', params: { id: id } })
}

const seeOrder = orderData => {
  router.push({ name : 'dashboard-admin-orders-id', params: { id: orderData.id } })
}

const resolveStatus = coupon => {

    return {
      text: 
        (new Date(coupon.expiration_date) < new Date() && coupon.purchase_date === null ) ? 
            'EXPIRADO' :
            (coupon.is_used ? 'APLICADO' : 'PENDIENTE'),
      color: 
        (new Date(coupon.expiration_date) < new Date() && coupon.purchase_date === null) ? 
            'warning' :
            (coupon.is_used ? 'success' : 'error')
    }

}

const resolveOrders = data => {
  if (data.type === 0)
    return data.wholesale === 0 ? 'text-success': 'text-primary'
  if (data.type === 1)
    return  'text-error' 
  if (data.type === 2)
    return  'text-secondary' 
}

const submitForm = async (client, method) => {
  isRequestOngoing.value = true

  if (method === 'update') {
    client.data.append('_method', 'PUT')
    submitUpdate(client)
    return
  }

  submitCreate(client.data)
}

const submitCreate = couponData => {
  couponsStores.addCoupon(couponData)
    .then(async res => {

      advisor.value = {
        type: res.data.success ? 'success' : 'error',
        message: res.data.success ? 'Cupón creado!' : res.data.message,
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

      isRequestOngoing.value = false

      return true
    })
    .catch(err => {
      console.error(err)
      isRequestOngoing.value = false
    })
}

const downloadCSV = async () => {

  isRequestOngoing.value = true

  let data = { limit: -1 }

  await couponsStores.fetchCoupons(data)

  let dataArray = [];
      
  couponsStores.getCoupons.forEach(element => {
    let data = {
        ID: element.id,
        NÚMERO_REGISTROS: element.registration_number,
        DISPOSITIVO: element.device,
        PLATAFORMA: element.plataform,
        NAVEGADOR: element.browser,
        LOCALIZACIÓN: element.location,
        CÓDIGO_POSTAL: element.postal_code,
        TIMEZONE: element.timezone,
        COORDENADAS: element.coordinates,
        GOOGLE_MAPS: `https://www.google.com/maps?q=${element.coordinates}`,
        ES_ROBOT: element.is_bot === 0 ? 'NO' : 'SI',
        ESTADO: element.is_blocked === 0 ? 'ACTIVO' : 'BLOQUEADO'
    }
          
    dataArray.push(data)
  })

  excelParser()
    .exportDataFromJSON(dataArray, "coupons", "csv");

  isRequestOngoing.value = false

}

</script>

<template>
  <section>
    <v-row>
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

        <v-card title="">
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

            <div class="d-flex align-center">
              <VBtn
                variant="tonal"
                color="secondary"
                prepend-icon="tabler-file-export"
                @click="downloadCSV">
                Exportar
              </VBtn>
            </div>

            <div class="d-flex align-center">
              <v-btn
                prepend-icon="tabler-plus"
                @click="isAddNewCouponDrawerVisible = true">
                  Agregar Cupon
              </v-btn>
            </div>

            <v-spacer />

            <div class="d-flex align-center flex-wrap gap-4">
              <!-- 👉 Search  -->
              <div class="search">
                <VTextField
                  v-model="searchQuery"
                  placeholder="Buscar"
                  density="compact"
                  clearable
                />
              </div>
            </div>
          </v-card-text>

          <v-divider />

          <v-table class="text-no-wrap">
            <!-- 👉 table head -->
            <thead>
              <tr>
                <th scope="col"> #ID </th>
                <th scope="col"> CLIENTE </th>
                <th scope="col"> REFERENCIA </th>
                <th scope="col"> CÓDIGO </th>
                <th scope="col"> STATUS </th>
                <th scope="col" v-if="$can('ver', 'cupones') || $can('eliminar', 'cupones')">
                  ACCIONES
                </th>
              </tr>
            </thead>
            <!-- 👉 table body -->
            <tbody>
                <tr 
                    v-for="coupon in coupons"
                    :key="coupon.id"
                    style="height: 3.75rem;">
                    <td> {{ coupon.id }} </td>
                    <td class="text-wrap">
                        <div class="d-flex align-center gap-x-3">
                            <VAvatar
                            :variant="coupon.client.user.avatar ? 'outlined' : 'tonal'"
                            size="38"
                            >
                            <VImg
                                v-if="coupon.client.user.avatar"
                                style="border-radius: 50%;"
                                :src="themeConfig.settings.urlStorage + coupon.client.user.avatar"
                            />
                                <span v-else>{{ avatarText(coupon.client.user.name) }}</span>
                            </VAvatar>
                            <div class="d-flex flex-column">
                            <span class="font-weight-medium cursor-pointer text-primary" @click="seeClient(coupon.client)">
                                {{ coupon.client.user.name }} {{ coupon.client.user.last_name }} 
                            </span>
                            <span class="text-sm text-disabled">{{ coupon.client.user.email }}</span>
                            </div>
                        </div>
                        </td>
                    <td class="text-wrap"> 
                        <span v-if="coupon.order"
                            class="font-weight-medium cursor-pointer" 
                            :class="resolveOrders(coupon.order)" 
                            @click="seeOrder(coupon.order)">
                            {{ coupon.order.reference_code }} 
                        </span>
                    </td>
                    <td class="text-wrap"> {{ coupon.code }} </td>
                    <td class="text-wrap">
                        <VChip
                            v-bind="resolveStatus(coupon)"
                            density="default"
                            label
                        />    
                    </td>
                    <!-- 👉 Acciones -->
                    <td class="text-center" style="width: 5rem;" v-if="$can('ver', 'cupones') || $can('eliminar', 'cupones')">      
                        <VBtn
                            v-if="$can('ver', 'cupones')"
                            @click="showCoupon(coupon.id)"
                            icon
                            variant="text"
                            color="default"
                            size="x-small">
                            <VTooltip
                            open-on-focus
                            location="top"
                            activator="parent">
                            Ver
                            </VTooltip>
                            <VIcon
                            size="28"
                            icon="tabler-eye"
                            class="me-1"
                            />
                        </VBtn>
                        <VBtn
                            v-if="$can('eliminar','cupones')"
                            icon
                            size="x-small"
                            color="default"
                            variant="text"
                            @click="showDeleteDialog(coupon)">
                            <VTooltip
                            open-on-focus
                            location="top"
                            activator="parent">
                            Eliminar
                            </VTooltip>          
                            <VIcon
                            size="22"
                            icon="tabler-trash" />
                        </VBtn>
                    </td>
                </tr>
            </tbody>
            <!-- 👉 table footer  -->
            <tfoot v-show="!coupons.length">
              <tr>
                <td
                  colspan="6"
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
      </v-col>
    </v-row>

    <!-- 👉 Add New Coupon -->
    <AddNewCouponDrawer
      v-model:isDrawerOpen="isAddNewCouponDrawerVisible"
      :coupon="selectedCoupon"
      :clients="listClients"
      @coupon-data="submitForm"
    />

    <!-- 👉 Confirm Delete -->
    <VDialog
      v-model="isConfirmDeleteDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Eliminar Cupón">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de eliminar el cupón <strong>{{ selectedCoupon.code }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeCoupon">
              Aceptar
          </VBtn>
        </VCardText>
      </VCard>
    </VDialog>
  </section>
</template>

<style scope>
    .search {
        width: 100%;
    }

    .draggable-item:hover {
      background-color: #e9ecef; /* Color de fondo al hacer hover */
      cursor: move; /* Cambia el cursor para indicar que el elemento es interactivo */
    }

    @media(min-width: 991px){
        .search {
            width: 30rem;
        }
    }
</style>
<route lang="yaml">
  meta:
    action: ver
    subject: cupones
</route>