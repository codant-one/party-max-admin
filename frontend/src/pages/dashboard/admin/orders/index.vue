<script setup>

import { useOrdersStores } from '@/stores/useOrders'
import { excelParser } from '@/plugins/csv/excelParser'
import { format, parseISO } from 'date-fns';
import { es } from 'date-fns/locale';
import { themeConfig } from '@themeConfig'
import { avatarText, formatNumber } from '@/@core/utils/formatters'
import router from '@/router'
import Toaster from "@/components/common/Toaster.vue";

const ordersStores = useOrdersStores()

const orders = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalOrders = ref(0)
const isRequestOngoing = ref(true)
const isConfirmDeleteDialogVisible = ref(false)
const selectedOrder = ref({})
const wholesale = ref(null)
const shipping_state_id = ref(null)
const payment_state_id = ref(4)
const type = ref(null)

const rol = ref(null)
const userData = ref(null)

const references = ref([
  { title: 'Al mayor', value: 1 },
  { title: 'Al detal', value: 0 }
])

const types = ref([
  { title: 'Producto', value: 0 },
  { title: 'Servicio', value: 1 },
  { title: 'Mixto', value: 2 }
])

const shippingStates = ref([
  { title: 'Listo para enviar', value: 1 },
  { title: 'Fuera de entrega', value: 2 },
  { title: 'Entregado', value: 3 },
  { title: 'Enviado', value: 4 }
])

const paymentStates = ref([
  { title: 'Pendiente', value: 1 },
  { title: 'Cancelado', value: 2 },
  { title: 'Fallido', value: 3 },
  { title: 'Pagada', value: 4 }
])

const advisor = ref({
  type: '',
  message: '',
  show: false
})

const payments = ref(null)
const widgetData = ref([])

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = orders.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = orders.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalOrders.value } registros`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value
})

watchEffect(fetchData)

async function fetchData() {

    userData.value = JSON.parse(localStorage.getItem('user_data') || 'null')
    rol.value = userData.value.roles[0].name

    let data = {
        search: searchQuery.value,
        orderByField: 'id',
        orderBy: 'desc',
        limit: rowPerPage.value,
        page: currentPage.value,
        wholesale: wholesale.value,
        type: type.value,
        shipping_state_id: shipping_state_id.value,
        payment_state_id: payment_state_id.value
    }

    isRequestOngoing.value = true

    await ordersStores.fetchOrders(data)

    orders.value = ordersStores.getOrders
    totalPages.value = ordersStores.last_page
    totalOrders.value = ordersStores.ordersTotalCount
    payments.value = ordersStores.payments

    const pending = rol.value === 'Proveedor' ? payments.value.orderInfo.order_count_pending : payments.value.pendingPayments 
    const failed = rol.value === 'Proveedor' ? payments.value.orderInfo.order_count_failed : payments.value.failedPayments 
    const success = rol.value === 'Proveedor' ? payments.value.orderInfo.order_count_payment : payments.value.successPayments 
    const canceled = rol.value === 'Proveedor' ? payments.value.orderInfo.order_count_canceled : payments.value.canceledPayments 

    widgetData.value = [
        {
            title: 'Pagos Pendientes',
            value: pending,
            icon: 'tabler-calendar-stats'
        },
        {
            title: 'Pagos Fallidos',
            value: failed,
            icon: 'tabler-circle-x'
        },
        {
            title: 'Pagos Completados',
            value: success,
            icon: 'tabler-checks'
        },
        {
            title: 'Pagos Cancelados',
            value: canceled,
            icon: 'tabler-wallet'
        }
    ]

    isRequestOngoing.value = false
}

const showDeleteDialog = orderData => {
  isConfirmDeleteDialogVisible.value = true
  selectedOrder.value = { ...orderData }
}

const seeClient = clientData => {
  router.push({ name : 'dashboard-admin-clients-id', params: { id: clientData.id } })
}

const seeOrder = orderData => {
  router.push({ name : 'dashboard-admin-orders-id', params: { id: orderData.id } })
}

const removeOrder = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await ordersStores.deleteOrder(selectedOrder.value.id)
  selectedOrder.value = {}

  advisor.value = {
    type: res.data.success ? 'success' : 'error',
    message: res.data.success ? 'Pedido eliminado!' : res.data.message,
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

const resolveType = order => {

    let type = {}
    
    switch (order.type) {
        case 0:
            type.text = 'PRODUCTO'
            type.color = 'info'
        break;
        case 1:
            type.text = 'SERVICIO'
            type.color = 'error'
            break;
        case 2:
            type.text = 'MIXTO'
            type.color = 'secondary'
        break;
        default:
            type.text = 'PRODUCTO'
            type.color = 'info'
    }

    return type;

}

const resolveOrders = data => {
  if (data.type === 0)
    return data.wholesale === 0 ? 'text-success': 'text-primary'
  if (data.type === 1)
    return  'text-error' 
  if (data.type === 2)
    return  'text-secondary' 
}

const resolveStatusShipping = shipping_state_id => {
  if (shipping_state_id === 1)
    return { color: 'error' }
  if (shipping_state_id === 2)
    return { color: 'warning' }
  if (shipping_state_id === 3)
    return { color: 'info' }
  if (shipping_state_id === 4)
    return { color: 'success' }
}

const resolveStatusPayment = payment_state_id => {
  if (payment_state_id === 1)
    return { color: 'error' }
  if (payment_state_id === 2)
    return { color: 'default' }
  if (payment_state_id === 3)
    return { color: 'warning' }
  if (payment_state_id === 4)
    return { color: 'info' }
}

const downloadCSV = async () => {

  isRequestOngoing.value = true

  let data = { limit: -1 }

  await ordersStores.fetchOrders(data)

  let dataArray = [];
      
  ordersStores.getOrders.forEach(element => {

    let data = {
      REFERENCIA: element.reference_code ?? '',
      FECHA: format(parseISO(element.date), 'MMMM d, yyyy', { locale: es }).replace(/(^|\s)\S/g, (char) => char.toUpperCase()),
      TIPO: resolveType(element).text,
      CLIENTE: element.client ? element.client.user.name + ' ' + (element.client.user.last_name ?? '') : element.billing?.name + ' ' + (element.billing?.last_name ?? '') + ' (no registrado)',
      CORREO: element.client ? element.client.user.email : element.billing?.email,
      ESTADO_ENVIO: element.shipping.name,
      ESTADO_PAGO: element.payment.name,
      MONTO: formatNumber(element.sub_total)
    }
          
    dataArray.push(data)
  })

  excelParser()
    .exportDataFromJSON(dataArray, "orders", "csv");

  isRequestOngoing.value = false

}
</script>

<template>
  <section>
    <div>
        <v-alert
            v-if="advisor.show"
            :type="advisor.type"
            class="mb-6">
            
          {{ advisor.message }}
        </v-alert>
        <Toaster />
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

        <VCard class="mb-6">
            <!-- 👉 Widgets  -->
            <VCardText>
                <VRow>
                    <template
                        v-for="(data, id) in widgetData"
                        :key="id"
                    >
                        <VCol cols="12" sm="6" md="3" class="px-6">
                            <div
                                class="d-flex justify-space-between"
                                :class="$vuetify.display.xs
                                ? 'product-widget'
                                : $vuetify.display.sm
                                    ? id < 2 ? 'product-widget' : ''
                                    : ''"
                            >
                                <div class="d-flex flex-column gap-y-1">
                                    <h4 class="text-h4">
                                        {{ data.value }}
                                    </h4>

                                    <h6 class="text-h6">
                                        {{ data.title }}
                                    </h6>
                                </div>

                                <VAvatar
                                    variant="tonal"
                                    rounded
                                    size="38"
                                >
                                    <VIcon
                                        :icon="data.icon"
                                        size="28"
                                    />
                                </VAvatar>
                            </div>
                        </VCol>
                        <VDivider
                            v-if="$vuetify.display.mdAndUp ? id !== widgetData.length - 1
                                : $vuetify.display.smAndUp ? id % 2 === 0
                                : false"
                            vertical
                            inset
                            length="55"
                        />
                    </template>
                </VRow>
            </VCardText>
        </VCard>
      
        <!-- 👉 orders -->
        <VCard
            title="Filtros"
            class="mb-6" >

            <VCardText>
                <VRow>

                    <VCol
                        cols="12"
                        sm="3"
                    >
                        <AppSelect
                            v-model="wholesale"
                            placeholder="Referencias"
                            :items="references"
                            clearable
                            clear-icon="tabler-x"
                        />
                    </VCol>

                    <VCol
                        cols="12"
                        sm="3"
                    >
                        <AppSelect
                            v-model="shipping_state_id"
                            placeholder="Estados de envios"
                            :items="shippingStates"
                            clearable
                            clear-icon="tabler-x"
                        />
                    </VCol>

                    <VCol
                        cols="12"
                        sm="3"
                    >
                        <AppSelect
                            v-model="payment_state_id"
                            placeholder="Estados de pagos"
                            :items="paymentStates"
                            clearable
                            clear-icon="tabler-x"
                        />
                    </VCol>
                    <VCol
                        cols="12"
                        sm="3"
                    >
                        <AppSelect
                            v-model="type"
                            placeholder="Tipo"
                            :items="types"
                            clearable
                            clear-icon="tabler-x"
                        />
                    </VCol>
                </VRow>
            </VCardText>

            <VCardText class="d-flex flex-wrap gap-4">
                <div class="d-flex align-center">
                    <!-- 👉 Search  -->
                    <AppTextField
                        v-model="searchQuery"
                        placeholder="Buscar"
                        density="compact"
                        style="inline-size: 700px;"
                        class="me-3"
                        clearable
                    />
                </div>

                <VSpacer />

                <div class="d-flex gap-4 flex-wrap align-center">
                    <AppSelect
                        v-model="rowPerPage"
                        :items="[5, 10, 20, 25, 50]"
                    />
                    <!-- 👉 Export button -->
                    <VBtn
                        variant="tonal"
                        color="secondary"
                        prepend-icon="tabler-file-export"
                        @click="downloadCSV"
                    >
                        Exportar
                    </VBtn>
                </div>
            </VCardText>

            <VDivider class="mt-4" />

            <!-- 👉 Datatable  -->

            <v-table class="text-no-wrap">
                <thead>
                    <tr class="text-no-wrap">
                        <th> REFERENCIA </th>
                        <th class="pe-4" v-if="rol === 'SuperAdmin'"> IP </th>
                        <th> FECHA </th>
                        <th> TIPO </th>
                        <th class="pe-4" v-if="rol !== 'Proveedor'"> CLIENTE </th>
                        <th class="pe-4"> ESTADO DEL ENVÍO </th>
                        <th class="pe-4"> ESTADO DEL PAGO </th>
                        <th class="pe-4"> PRECIO </th>
                  
                        <th scope="pe-4" v-if="$can('ver', 'pedidos') || $can('eliminar', 'pedidos')">
                            ACCIONES
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="order in orders"
                        :key="order.id"
                        style="height: 3.75rem;">
                        <td class="name">
                            <span 
                                class="font-weight-medium cursor-pointer" 
                                :class="resolveOrders(order)" 
                                @click="seeOrder(order)">
                                {{ order.reference_code }} 
                            </span>
                        </td>
                        <td class="text-wrap" v-if="rol === 'SuperAdmin'">
                            {{ order.ip }}
                        </td>
                        <td> {{ format(parseISO(order.date), 'MMMM d, yyyy', { locale: es }).replace(/(^|\s)\S/g, (char) => char.toUpperCase()) }}</td>
                        <td>
                            <VChip
                                variant="outlined"
                                label
                                :color="resolveType(order).color"
                            >                            
                            {{  resolveType(order).text }} 
                            </VChip>
                        </td>
                        <td class="text-wrap" v-if="rol !== 'Proveedor'">
                            <div class="d-flex align-center gap-x-3" v-if="order.client">
                                <VAvatar
                                    :variant="order.client.user.avatar ? 'outlined' : 'tonal'"
                                    size="38"
                                >
                                    <VImg
                                        v-if="order.client.user.avatar"
                                        style="border-radius: 50%;"
                                        :src="themeConfig.settings.urlStorage + order.client.user.avatar"
                                    />
                                        <span v-else>{{ avatarText(order.client.user.name) }}</span>
                                </VAvatar>
                                <div class="d-flex flex-column">
                                    <span class="font-weight-medium cursor-pointer text-primary" @click="seeClient(order.client)">
                                        {{ order.client.user.name }} {{ order.client.user.last_name }} 
                                    </span>
                                    <span class="text-sm text-disabled">{{ order.client.user.email }}</span>
                                </div>
                            </div>
                            <div class="d-flex align-center gap-x-3" v-else>
                                <VAvatar
                                    variant="tonal"
                                    size="38"
                                >
                                    <span>{{ avatarText(order.billing?.name) }}</span>
                                </VAvatar>
                                <div class="d-flex flex-column">
                                    <span class="font-weight-medium text-success">
                                        {{ order.billing?.name }} {{ order.billing?.last_name }} 
                                    </span>
                                    <span class="text-sm text-disabled">{{ order.billing?.email }}</span>
                                    <span class="text-sm text-secondary">Cliente no registrado</span>
                                </div>
                            </div>
                        </td>
                        <td> 
                            <li
                                :class="`text-${resolveStatusShipping(order.shipping.id)?.color}`"
                                class="font-weight-medium"
                                v-if="order.type === 0"
                            >
                                {{ order.shipping.name }}
                            </li>
                            <span v-else>----------------------</span>
                        </td>
                        <td> 
                            <VChip
                                label
                                :color="resolveStatusPayment(order.payment.id)?.color"
                            >
                            {{ order.payment.name }}
                            </VChip>
                        </td>
                        <td>${{ formatNumber(order.sub_total) }}</td>
                        <td class="text-center" style="width: 5rem;" v-if="$can('ver', 'pedidos') || $can('eliminar', 'pedidos')">
                            <VBtn
                                v-if="$can('ver', 'pedidos')"
                                icon
                                size="x-small"
                                color="default"
                                variant="text"
                                @click="seeOrder(order)">
                                <VTooltip
                                    open-on-focus
                                    location="top"
                                    activator="parent">
                                    Ver
                                </VTooltip>      
                                <VIcon
                                    size="22"
                                    icon="tabler-eye" />
                            </VBtn>

                            <VBtn
                                v-if="$can('eliminar','pedidos')"
                                icon
                                size="x-small"
                                color="default"
                                variant="text"
                                @click="showDeleteDialog(order)">
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
                <tfoot v-show="!orders.length">
                    <tr>
                        <td
                        colspan="8"
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
        </VCard>
    </div>
    <!-- 👉 Confirm Delete -->
    <VDialog
      v-model="isConfirmDeleteDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmDeleteDialogVisible = !isConfirmDeleteDialogVisible" />

      <!-- Dialog Content -->
      <VCard title="Eliminar Pedido">
        <VDivider class="mt-4"/>
        <VCardText>
          Está seguro de eliminar el pedido <strong>{{ selectedOrder.reference_code }}</strong>?.
        </VCardText>

        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn
            color="secondary"
            variant="tonal"
            @click="isConfirmDeleteDialogVisible = false">
              Cancelar
          </VBtn>
          <VBtn @click="removeOrder">
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

    .name .text-success:hover {
        color: #FF0090 !important
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
    subject: pedidos
</route>