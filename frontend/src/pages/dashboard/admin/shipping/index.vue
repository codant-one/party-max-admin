<script setup>

import { useOrdersStores } from '@/stores/useOrders'
import { excelParser } from '@/plugins/csv/excelParser'
import { format, parseISO } from 'date-fns';
import { es } from 'date-fns/locale';
import { themeConfig } from '@themeConfig'
import { avatarText, formatNumber } from '@/@core/utils/formatters'
import { requiredValidator } from '@validators'
import router from '@/router'
import mastercard from '@images/cards/mastercard.png'
import visa from '@images/cards/visa.png'
import pse from '@images/cards/pse.png'
import nequi from '@images/cards/nequi.png'

const ordersStores = useOrdersStores()

const orders = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalOrders = ref(0)
const isRequestOngoing = ref(true)
const isConfirmSendDialogVisible = ref(false)
const selectedOrder = ref({})
const wholesale = ref(null)
const shipping_state_id = ref(null)
const id = ref(null)
const reason = ref(null)
const refVForm = ref()

const shippingStates = ref([
  { title: 'Listo para enviar', value: 1 },
  { title: 'Fuera de entrega', value: 2 },
  { title: 'Entregado', value: 3 },
  { title: 'Enviado', value: 4 }
])

const references = ref([
    { title: 'Al mayor', value: 1 },
    { title: 'Al detal', value: 0 }
])

const advisor = ref({
    type: '',
    message: '',
    show: false
})

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

    let data = {
        search: searchQuery.value,
        orderByField: 'id',
        orderBy: 'desc',
        limit: rowPerPage.value,
        page: currentPage.value,
        wholesale: wholesale.value,
        shipping_state_id: shipping_state_id.value,
        payment_state_id: 4
    }

    isRequestOngoing.value = true

    await ordersStores.fetchOrders(data)

    orders.value = ordersStores.getOrders
    totalPages.value = ordersStores.last_page
    totalOrders.value = ordersStores.ordersTotalCount
    payments.value = ordersStores.payments

    widgetData.value = [
        {
            title: 'Pedidos por enviar',
            value: payments.value.pendingShipping,
            icon: 'mdi-truck-fast-outline'
        },
        {
            title: 'Pedidos fuera de entrega',
            value: payments.value.outforDeliveryShipping,
            icon: 'mdi-truck-remove-outline'
        },
        {
            title: 'Pedidos en tránsito',
            value: payments.value.deliveredShipping,
            icon: 'mdi-truck-delivery'
        },
        {
            title: 'Pedidos Completados',
            value: payments.value.sentShipping,
            icon: 'mdi-truck-check-outline'
        }
    ]

    isRequestOngoing.value = false
}

const seeClient = clientData => {
    router.push({ name : 'dashboard-admin-clients-id', params: { id: clientData.id } })
}

const seeOrder = orderData => {
    router.push({ 
        name : 'dashboard-admin-orders-id', 
        params: { id: orderData.id },
        query: { route: 'shipping' } 
    })
}

const showSendDialog = (orderData, shipping_state_id) => {
    isConfirmSendDialogVisible.value = true
    id.value = shipping_state_id
    selectedOrder.value = { ...orderData }
}

const sendOrder = async () => {

    refVForm.value?.validate().then(async ({ valid: isValid }) => {
        if (isValid) {
            isConfirmSendDialogVisible.value = false
            let res = await ordersStores.sendOrder(selectedOrder.value.id, {shipping_state_id : id.value, reason: reason.value})
            selectedOrder.value = {}

            advisor.value = {
                type: res.data.success ? 'success' : 'error',
                message: res.data.success ? 'Pedido actualizado!' : res.data.message,
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
    })
}

const downloadCSV = async () => {

    isRequestOngoing.value = true

    let data = { 
        limit: -1,
        payment_state_id: 4
    }

    await ordersStores.fetchOrders(data)

    let dataArray = [];
      
    ordersStores.getOrders.forEach(element => {

        let data = {
        REFERENCIA: element.reference_code ?? '',
        FECHA: format(parseISO(element.date), 'MMMM d, yyyy', { locale: es }).replace(/(^|\s)\S/g, (char) => char.toUpperCase()),
        CLIENTE: element.client.user.name + ' ' + (element.client.user.last_name ?? ''),
        CORREO: element.client.user.email,
        ESTADO_ENVIO: element.shipping.name,
        ESTADO_PAGO: element.payment.name,
        MONTO: formatNumber(element.total)
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

        <!-- 👉 shipping -->
        <VCard
            title="Filtros"
            class="mb-6" >

            <VCardText>
                <VRow>

                    <VCol
                        cols="12"
                        sm="4"
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
                        sm="4"
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
                        sm="4">
                        <!-- 👉 Search  -->
                        <AppTextField
                            v-model="searchQuery"
                            placeholder="Buscar"
                            density="compact"
                            clearable
                        />
                    </VCol>
                    <VCol
                        cols="12"
                        sm="12"
                        class="d-flex flex-wrap gap-4">

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
                    </VCol>
                </VRow>
            </VCardText>

            <VDivider class="mt-4" />

            <!-- 👉 Datatable  -->

            <v-table class="text-no-wrap">
                <thead>
                    <tr class="text-no-wrap">
                        <th> REFERENCIA </th>
                        <th> FECHA </th>
                        <th class="pe-4"> CLIENTE </th>
                        <th class="pe-4"> ESTADO DEL ENVÍO </th>
                        <th class="pe-4"> ESTADO DEL PAGO </th>
                        <th class="pe-4"> MÉTODO </th>
                        <th scope="pe-4" v-if="$can('ver', 'envíos') || $can('editar', 'envíos')">
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
                                :class="order.wholesale === 0 ? 'text-success': 'text-primary'" 
                                @click="seeOrder(order)">
                                {{ order.reference_code }} 
                            </span>
                        </td>
                       
                        <td> {{ format(parseISO(order.date), 'MMMM d, yyyy', { locale: es }).replace(/(^|\s)\S/g, (char) => char.toUpperCase()) }}</td>    
                        
                        <td class="text-wrap">
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
                                    <span>{{ avatarText(order.billing.name) }}</span>
                                </VAvatar>
                                <div class="d-flex flex-column">
                                    <span class="font-weight-medium text-success">
                                        {{ order.billing.name }} {{ order.billing.last_name }} 
                                    </span>
                                    <span class="text-sm text-disabled">{{ order.billing.email }}</span>
                                    <span class="text-sm text-secondary">Cliente no registrado</span>
                                </div>
                            </div>
                        </td>
                        <td> 
                            <li
                                :class="`text-${resolveStatusShipping(order.shipping.id)?.color}`"
                                class="font-weight-medium"
                            >
                                {{ order.shipping.name }}
                            </li>
                        </td>
                        <td> 
                            <VChip label color="info">{{ order.payment.name }}</VChip>
                        </td>
                        <td :class="order.billing?.pse ? 'px-0' : ''">
                            <div class="d-flex align-start px-0" v-if="order.billing?.nequi === 1">
                                <VImg
                                    :src="nequi"
                                    height="65"
                                    max-width="65"
                                    min-width="65"
                                />
                            </div>
                            <div class="d-flex align-start gap-x-2" v-if="order.billing?.pse === 0 && order.billing?.card_number">
                                <VImg
                                    :src="order.billing.payment_method_name === 'MASTERCARD' ? mastercard : visa"
                                    height="40"
                                    max-width="40"
                                    min-width="40"
                                />
                                <!-- <div class="mt-2">
                                    <VIcon
                                        icon="tabler-dots"
                                        class="mt-1"
                                    />
                                    <span class="mt-2">
                                        {{ order.billing.card_number.replaceAll('*', '').trim() }}
                                    </span>
                                </div> -->
                            </div>
                            <div class="d-flex align-start px-0" v-if="order.billing?.pse === 1">
                                <VImg
                                    :src="pse"
                                    height="65"
                                    max-width="65"
                                    min-width="65"
                                />
                            </div>
                            
                        </td>
                        <td class="text-center" style="width: 5rem;" v-if="$can('ver', 'envíos') || $can('editar', 'envíos')">
                            <VBtn
                                v-if="$can('ver', 'envíos')"
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
                                v-if="$can('editar', 'envíos') && order.shipping_state_id === 1"
                                icon
                                size="x-small"
                                color="default"
                                variant="text"
                                @click="showSendDialog(order, 2)">
                                <VTooltip
                                    open-on-focus
                                    location="top"
                                    activator="parent">
                                    No enviar
                                </VTooltip>      
                                <VIcon
                                    size="22"
                                    icon="mdi-truck-remove-outline" />
                            </VBtn>
                            <VBtn
                                v-if="$can('editar', 'envíos') && order.shipping_state_id === 1"
                                icon
                                size="x-small"
                                color="default"
                                variant="text"
                                @click="showSendDialog(order, 4)">
                                <VTooltip
                                    open-on-focus
                                    location="top"
                                    activator="parent">
                                    Enviar
                                </VTooltip>      
                                <VIcon
                                    size="22"
                                    icon="mdi-truck-fast-outline" />
                            </VBtn>
                            <VBtn
                                v-if="$can('editar', 'envíos') && order.shipping_state_id === 4"
                                icon
                                size="x-small"
                                color="default"
                                variant="text"
                                @click="showSendDialog(order, 3)">
                                <VTooltip
                                    open-on-focus
                                    location="top"
                                    activator="parent">
                                    Entregar
                                </VTooltip>      
                                <VIcon
                                    size="22"
                                    icon="mdi-truck-check-outline" />
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
    <!-- 👉 Confirm Send -->
    <VDialog
      v-model="isConfirmSendDialogVisible"
      persistent
      class="v-dialog-sm" >
      <!-- Dialog close btn -->
        
      <DialogCloseBtn @click="isConfirmSendDialogVisible = !isConfirmSendDialogVisible" />

      <VForm
        ref="refVForm"
        @submit.prevent="sendOrder">
        <!-- Dialog Content -->
        <VCard title="Control del Pedido">
            <VDivider />
            <VCardText class="pb-0" v-if="id === 2">
                Cambiar estado del pedido <strong>{{ selectedOrder.reference_code }}</strong> a fuera de entrega?.
                <VTextField
                    class="my-2"
                    v-model="reason"
                    label="Razón"
                    :rules="[requiredValidator]"
                />
            </VCardText>
            <VCardText class="pb-0" v-if="id === 3">
                Cambiar estado del pedido <strong>{{ selectedOrder.reference_code }}</strong> a entregado?.
            </VCardText>
            <VCardText class="pb-0" v-if="id === 4">
                Enviar pedido <strong>{{ selectedOrder.reference_code }}</strong>?.
            </VCardText>

            <VCardText class="d-flex justify-end gap-3 flex-wrap">
                <VBtn
                    color="secondary"
                    variant="tonal"
                    @click="isConfirmSendDialogVisible = false">
                    Cancelar
                </VBtn>
                <VBtn type="submit">
                    Aceptar
                </VBtn>
            </VCardText>
        </VCard>
      </VForm>
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
    subject: envíos
</route>