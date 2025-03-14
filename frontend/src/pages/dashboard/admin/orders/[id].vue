<script setup>

import { useOrdersStores } from '@/stores/useOrders'
import { formatNumber } from '@/@core/utils/formatters'
import { format, parseISO } from 'date-fns';
import { avatarText } from '@/@core/utils/formatters'
import { themeConfig } from '@themeConfig'
import { es } from 'date-fns/locale';
import mastercard from '@images/cards/mastercard.png'
import visa from '@images/cards/visa.png'
import pse from '@images/cards/pse.png'
import nequi from '@images/cards/nequi.png'
import router from '@/router'

const route = useRoute()
const ordersStores = useOrdersStores()
const emitter = inject("emitter")

const order = ref(null)
const date = ref(null)
const total = ref(0)
const isConfirmDeleteDialogVisible = ref(false)

const isRequestOngoing = ref(true)

const rol = ref(null)
const userData = ref(null)

watchEffect(fetchData)

async function fetchData() {

  isRequestOngoing.value = true

  if(Number(route.params.id)) {
    const response = await ordersStores.showOrder(Number(route.params.id))

    order.value = response.order
    total.value = response.ordersTotalCount

    date.value = order.value.created_at
  }

  userData.value = JSON.parse(localStorage.getItem('user_data') || 'null')
  rol.value = userData.value.roles[0].name

  isRequestOngoing.value = false
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

const printInvoice = () => {
  window.print()
}

const showDeleteDialog = orderData => {
  isConfirmDeleteDialogVisible.value = true
}

const back = () => {
  if(typeof route.query.route === 'undefined') {
    router.push({ name : 'dashboard-admin-orders'})
  } else {
    router.push({ name : 'dashboard-admin-shipping'})
  }
}

const download = async (img) => {

  try {
    const response = await fetch(themeConfig.settings.urlbase + 'proxy-image?url=' + themeConfig.settings.urlStorage + img);
    const blob = await response.blob();

    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;

    link.setAttribute('download', 'image.jpg');

    document.body.appendChild(link);
    link.click();

    window.URL.revokeObjectURL(url);

    let data = {
      message: 'Descarga Exitosa!',
      error: false 
    }

    emitter.emit('toast', data)

  } catch (error) {

    let data = {
      message: 'Error al descargar la imagen:' + error,
      error: true 
    }

    emitter.emit('toast', data)
  }
}

const removeOrder = async () => {
  isConfirmDeleteDialogVisible.value = false
  let res = await ordersStores.deleteOrder(route.params.id)

  let data = {
    message: res.data.success ? 'Pedido eliminado!' : res.data.message,
    error: res.data.success ? false : true
  }

  router.push({ name : 'dashboard-admin-orders'})
  emitter.emit('toast', data)
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
    <div v-if="order" class="d-flex justify-space-between align-center flex-wrap gap-y-4 mb-6 d-print-none">
      <div>
        <div class="d-flex gap-2 align-center mb-2 flex-wrap">
          <h4 class="text-h4 font-weight-medium">
            Pedido #{{ order.reference_code }} 
          </h4>
          <div class="d-flex gap-x-2">
            <VChip
              variant="tonal"
              :color="resolveStatusShipping(order.shipping.id)?.color"
              label>
              {{ order.shipping.name }}
            </VChip>
            <VChip
              variant="tonal"
              :color="resolveStatusPayment(order.payment.id)?.color"
              label>
              {{ order.payment.name }}
            </VChip>
          </div>
        </div>
        <div>
          <span class="text-body-1" v-if="date">
            {{  format(parseISO(date), 'MMMM d, yyyy, H:mm', { locale: es }).replace(/(^|\s)\S/g, (char) => char.toUpperCase()) }}
            <span class="text-xs"> (Fecha del pedido) </span>
            <span class="text-xs ms-2" v-if="rol === 'SuperAdmin'"> {{order.ip}}</span>
          </span>
        </div>
      </div>
      <div class="d-flex gap-4">
        <VBtn
          v-if="$can('eliminar', 'pedidos')"
          variant="tonal"
          color="error"
          @click="showDeleteDialog">
          ELIMINAR PEDIDO
        </VBtn>
      </div>
    </div>

    <VRow v-if="order">
      <VCol
        cols="12"
        md="10"
        id="invoice-detail"
      >
        <VRow>
          <VCol cols="12" :md="(rol !== 'Proveedor' || order.type === 1) ? '8' : '12'" class="print-row print-order-2">
            <!-- 👉 Order Details -->
            <VCard class="mb-6">
              <VCardItem>
                <template #title>
                  <h5 class="text-h5">
                    Detalles del pedido
                  </h5>
                </template>
              </VCardItem>

              <VDivider />
              
              <v-table class="text-no-wrap order-preview-table">
                <thead>
                  <tr class="text-no-wrap">
                    <th class="font-weight-semibold"> PRODUCTO</th>
                    <th class="font-weight-semibold"> PRECIO </th>
                    <th class="font-weight-semibold"> CANTIDAD </th>
                    <th class="font-weight-semibold"> TOTAL </th>   
                  </tr>
                </thead>
                <tbody>
                  <template v-for="item in order.details">
                    <template v-if="item.product_color_id !== null">
                      <tr style="height: 3.75rem;" v-if="(item.product_color.product.user.id === userData.id && rol === 'Proveedor') || rol === 'Administrador' || rol === 'SuperAdmin'">
                        <td class="text-wrap">
                          <div class="d-flex gap-x-3 align-center my-1">
                            <VAvatar
                              v-if="item.product_color.images.length > 0"
                              class="avatar-dynamic"
                              size="150"
                              variant="outlined"
                              :image="themeConfig.settings.urlStorage + item.product_color.images[0].image"
                              rounded="lg"
                            />
                            <VAvatar
                              v-else
                              class="avatar-dynamic"
                              size="150"
                              variant="outlined"
                              :image="themeConfig.settings.urlStorage + item.product_color.product.image"
                              rounded="lg"
                            />
                            <div class="d-flex flex-column align-start">
                              <span class="text-body-1 font-weight-medium">
                                {{ item.product_color.product.name }}
                              </span>

                              <span class="text-sm text-disabled">
                                Color: {{ item.product_color.color.name }}
                              </span>
                              <span class="text-sm text-disabled">
                                Tienda: {{ item.product_color.product.user.user_detail.store_name ?? (item.product_color.product.user.supplier?.company_name ?? (item.product_color.product.user.name + ' ' + (item.product_color.product.user.last_name ?? ''))) }}
                              </span>
                            </div>
                          </div>
                        </td>
                        <td><span>${{ formatNumber(item.price) }}</span></td>
                        <td class="text-center justify-content-center"><span class="text-high-emphasis font-weight-medium">{{ item.quantity }}</span></td>
                        <td><span class="text-h6">${{ formatNumber(item.total) }}</span></td>
                      </tr>
                    </template>
                    <template v-else>
                      <tr style="height: 3.75rem;" v-if="(item.service.user.id === userData.id && rol === 'Proveedor') || rol === 'Administrador' || rol === 'SuperAdmin'">
                        <td class="text-wrap">
                          <div class="d-flex gap-x-3 align-center my-1">
                            <VAvatar
                              v-if="item.service.images.length > 0"
                              class="avatar-dynamic"
                              size="150"
                              variant="outlined"
                              :image="themeConfig.settings.urlStorage + item.service.images[0].image"
                              rounded="lg"
                            />
                            <VAvatar
                              v-else
                              class="avatar-dynamic"
                              size="150"
                              variant="outlined"
                              :image="themeConfig.settings.urlStorage + item.service.image"
                              rounded="lg"
                            />
                            <div class="d-flex flex-column align-start">
                              <span class="text-body-1 font-weight-medium">
                                {{ item.service.name }}
                              </span>
                              <span class="text-sm text-disabled" v-if="item.service.cupcakes.length > 0">
                                Sabor: {{ item.flavor.name }}
                              </span>
                              <span class="text-sm text-disabled" v-if="item.service.cupcakes.length > 0">
                                Relleno: {{ item.filling.name }}
                              </span>
                              <span class="text-sm text-disabled" v-if="item.service.cupcakes.length > 0">
                                Tamaño: {{ item.cake_size.name }}
                              </span>
                              <span class="text-sm text-disabled">
                                Tienda: {{ item.service.user.user_detail.store_name ?? (item.service.user.supplier?.company_name ?? (item.service.user.name + ' ' + (item.service.user.last_name ?? ''))) }}
                              </span>
                              <span class="text-sm text-disabled" v-if="item.service.cupcakes.length > 0 && item.order_file !== null">
                                Archivo: 
                                <VBtn
                                  @click="download(item.order_file.image)"
                                  icon
                                  variant="text"
                                  color="default"
                                  size="x-small">
                                  <VTooltip
                                    open-on-focus
                                    location="top"
                                    activator="parent">
                                    Descargar imagen
                                  </VTooltip>
                                  <VIcon
                                    size="20"
                                    icon="tabler-photo"
                                    class="me-1"
                                  />
                              </VBtn>
                              </span>
                            </div>
                          </div>
                        </td>
                        <td><span>${{ formatNumber(item.price) }}</span></td>
                        <td class="text-center justify-content-center"><span class="text-high-emphasis font-weight-medium">{{ item.quantity }}</span></td>
                        <td><span class="text-h6">${{ formatNumber(item.total) }}</span></td>
                      </tr>
                    </template>
                    
                  </template>
                </tbody>
              </v-table>

              <VDivider />

              <VCardText v-if="rol !== 'Proveedor'">
                <div class="d-flex align-end flex-column">
                  <table class="text-high-emphasis">
                    <tbody>
                      <tr>
                        <td width="200px">
                          Subtotal:
                        </td>
                        <td>
                          $ {{ formatNumber(order.sub_total) }}
                        </td>
                      </tr>
                      <tr>
                        <td>Envío Total: </td>
                        <td>
                          $ {{ formatNumber(order.shipping_total) }}
                        </td>
                      </tr>
                      <tr>
                        <td>Tax: </td>
                        <td>
                          $ {{ formatNumber(order.tax) }}
                        </td>
                      </tr>
                      <tr>
                        <td class="text-high-emphasis font-weight-medium">
                          Total:
                        </td>
                        <td class="font-weight-medium">
                          $ {{ formatNumber(order.total) }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </VCardText>
            </VCard>

            <!-- 👉 Shipping Activity -->
            <VCard title="Actividad de envío" class="d-print-none" v-if="order.type !== 1">
              <VCardText>
                <VTimeline
                  v-if="order.payment.id !== 4"
                  truncate-line="both"
                  align="start"
                  side="end"
                  line-color="primary"
                  density="compact"
                  class="v-timeline-density-compact"
                >
                  <!-- estado 1 -->
                  <VTimelineItem
                    dot-color="secondary"
                    size="x-small"
                  >
                    <div class="d-flex justify-space-between align-center">
                      <div class="app-timeline-title">
                        Se realizo el pedido (Pedido #{{ order.reference_code }})
                      </div>
                      <div class="app-timeline-meta">
                        {{  format(parseISO(order.created_at), 'MMMM d, H:mm', { locale: es }).replace(/(^|\s)\S/g, (char) => char.toUpperCase()) }}
                      </div>
                    </div>
                    <p class="app-timeline-text mb-0">
                      <span v-if="order.payment.id === 1">
                        El pedido se encuentra pendiente por pagar.
                      </span>
                      <span v-if="order.payment.id === 2">
                        El pago del pedido ha sido cancelado
                      </span>
                      <span v-if="order.payment.id === 3">
                        El pago del pedido ha fallado
                      </span>
                    </p>
                  </VTimelineItem>
                  
                </VTimeline>
                <VTimeline
                  v-else
                  truncate-line="both"
                  align="start"
                  side="end"
                  line-color="primary"
                  density="compact"
                  class="v-timeline-density-compact"
                >
                  <!-- estado 1 -->
                  <VTimelineItem
                    dot-color="primary"
                    size="x-small"
                  >
                    <div class="d-flex justify-space-between align-center">
                      <div class="app-timeline-title">
                        Se realizo el pedido (Pedido #{{ order.reference_code }})
                      </div>
                      <div class="app-timeline-meta">
                        {{  format(parseISO(order.histories[0].created_at), 'MMMM d, H:mm', { locale: es }).replace(/(^|\s)\S/g, (char) => char.toUpperCase()) }}
                      </div>
                    </div>
                    <p class="app-timeline-text mb-0">
                      El pedido ha sido realizado con éxito
                    </p>
                  </VTimelineItem>
                  <VTimelineItem
                    v-if="order.shipping_state_id === 1"
                    dot-color="secondary"
                    size="x-small"
                  >
                    <div class="d-flex justify-space-between align-center">
                      <div class="app-timeline-title">
                          Enviado
                      </div>
                      <div class="app-timeline-meta">
                        
                      </div>
                    </div>
                    <p class="app-timeline-text mb-0">
                      El pedido aún no se ha enviado
                    </p>
                  </VTimelineItem>
                  <!-- estado 2 -->
                  <VTimelineItem
                    v-if="order.shipping_state_id === 2"
                    dot-color="primary"
                    size="x-small"
                  >
                    <div class="d-flex justify-space-between align-center">
                      <div class="app-timeline-title">
                          No se pudo enviar el paquete
                      </div>
                      <div class="app-timeline-meta">
                        {{  format(parseISO(order.histories[1].created_at), 'MMMM d, H:mm', { locale: es }).replace(/(^|\s)\S/g, (char) => char.toUpperCase()) }}
                      </div>
                    </div>
                    <p class="app-timeline-text mb-0">
                      El pedido esta fuera de entrega.<br>
                      Razón: {{ order.histories[1].reason }}
                    </p>
                  </VTimelineItem>
                  <!-- estado 3 -->
                  <VTimelineItem
                    v-if="order.shipping_state_id === 3"
                    dot-color="primary"
                    size="x-small"
                  >
                    <div class="d-flex justify-space-between align-center">
                      <div class="app-timeline-title">
                        Enviado
                      </div>
                      <div class="app-timeline-meta">
                        {{  format(parseISO(order.histories[1].created_at), 'MMMM d, H:mm', { locale: es }).replace(/(^|\s)\S/g, (char) => char.toUpperCase()) }}
                      </div>
                    </div>
                    <p class="app-timeline-text mb-0">
                      El pedido fue enviado
                    </p>
                  </VTimelineItem>
                  <VTimelineItem
                    v-if="order.shipping_state_id === 3"
                    dot-color="primary"
                    size="x-small"
                  >
                    <div class="d-flex justify-space-between align-center">
                      <div class="app-timeline-title">
                        Entregado
                      </div>
                      <div class="app-timeline-meta">
                        {{  format(parseISO(order.histories[2].created_at), 'MMMM d, H:mm', { locale: es }).replace(/(^|\s)\S/g, (char) => char.toUpperCase()) }}
                      </div>
                    </div>
                    <p class="app-timeline-text mb-0">
                      El pedido fue entregado
                    </p>
                  </VTimelineItem>
                  <!-- estado 4 -->
                  <VTimelineItem
                    v-if="order.shipping_state_id === 4"
                    dot-color="primary"
                    size="x-small"
                  >
                    <div class="d-flex justify-space-between align-center">
                      <div class="app-timeline-title">
                        Enviado
                      </div>
                      <div class="app-timeline-meta">
                        {{  format(parseISO(order.histories[1].created_at), 'MMMM d, H:mm', { locale: es }).replace(/(^|\s)\S/g, (char) => char.toUpperCase()) }}
                      </div>
                    </div>
                    <p class="app-timeline-text mb-0">
                      El pedido fue enviado
                    </p>
                  </VTimelineItem>
                  <VTimelineItem
                    v-if="order.shipping_state_id === 4"
                    dot-color="secondary"
                    size="x-small"
                  >
                    <div class="d-flex justify-space-between align-center">
                      <div class="app-timeline-title">
                        Entregado
                      </div>
                      <div class="app-timeline-meta">
                      </div>
                    </div>
                    <p class="app-timeline-text mb-0">
                      El pedido aún no se ha entregado
                    </p>
                  </VTimelineItem>
                </VTimeline>
              </VCardText>
            </VCard>
          </VCol>

          <VCol cols="12" md="4" class="print-row print-order-1" v-if="rol !== 'Proveedor' || order.type === 1">
            <!-- 👉 Customer Details  -->
            <VCard class="mb-6 client-details">
              <VCardText class="d-flex flex-column gap-y-6">
                <div class="text-body-1 text-high-emphasis font-weight-medium">
                    Detalles del cliente
                </div>

                <div class="d-flex align-center" v-if="order.client">
                  <VAvatar
                    class="me-3 d-print-none"
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
                  <div>
                    <div class="text-body-1 font-weight-medium">
                      {{ order.client.user.name }} {{ order.client.user.last_name }}
                    </div>
                    <span class="text-sm text-disabled d-print-none">Cliente ID: #{{ order.client_id }}</span>
                  </div>
                </div>

                <div class="d-flex align-center" v-else>
                  <VAvatar
                    class="me-3 d-print-none"
                    variant="tonal"
                    size="38"
                    >
                    <span>{{ avatarText(order.billing?.name) }}</span>
                  </VAvatar>
                  <div>
                    <div class="text-body-1 font-weight-medium">
                      {{ order.billing?.name }} {{ order.billing?.last_name }}
                    </div>
                    <span class="text-sm text-disabled d-print-none">Cliente no registrado</span>
                  </div>
                </div>

                <div class="d-print-none">
                  <VAvatar
                    variant="tonal"
                    color="success"
                    class="me-3"
                  >
                    <VIcon icon="tabler-shopping-cart" />
                  </VAvatar>
                  <span v-if="order.client" class="text-body-1 font-weight-medium text-high-emphasis">{{ total }} {{ total > 1 ? 'Pedidos' : 'Pedido' }}</span>
                  <span v-else class="text-body-1 font-weight-medium text-high-emphasis">1 Pedido</span>
                </div>

                <div class="d-flex flex-column gap-y-1">
                  <div class="d-flex justify-space-between align-center text-body-2 d-print-none">
                    <span class="text-body-1 text-high-emphasis font-weight-medium">Datos de contacto</span>
                  </div>
                  <span>Tipo de documento: {{ order.billing?.document_type?.name }} </span>
                  <span>Documento: {{ order.billing?.document }} </span>
                  <span>Email: {{ order.client ? order.client.user.email : order.billing?.email }} </span>
                  <span>Teléfono: {{ order.client ? order.client.user.user_detail.phone : order.billing?.phone}}</span>
                </div>
              </VCardText>
            </VCard>

            <!-- 👉 Shipping Address -->
            <VCard class="mb-6">
              <VCardText>
                <div class="d-flex align-center justify-space-between">
                  <div class="text-body-1 text-high-emphasis font-weight-medium">
                    Dirección de envío
                  </div>
                  <div class="text-body-1 text-high-emphasis font-weight-medium text-success">
                    Envío Express: {{ order.shipping_express === 0 ? 'NO' : 'SI'}}
                  </div>
                </div>
                <div>
                  <h6 class="text-h6 me-2 mt-4 d-print-none">
                    {{ order.address ? order.address.type.name : order.address_type.name }}
                  </h6>
                  <span v-if="order.address">
                    {{ order.address.address }} <br> 
                    {{ order.address.street }} <br> 
                    {{ order.address.city }} <br> 
                    {{ order.address.postal_code }}
                  </span>
                  <span v-else>
                    {{ order.shipping_address }} <br> 
                    {{ order.shipping_street }} <br> 
                    {{ order.shipping_city }} <br> 
                    {{ order.shipping_postal_code }}
                  </span>

                  <span v-if="order.billing?.note !== null">
                    <br>({{ order.billing?.note }}).
                  </span>
                </div>
              </VCardText>
            </VCard>

            <!-- 👉 Billing Address -->
            <VCard title="Dirección de Facturación" class="billing">
              <template #append>
                <div v-if="order.billing?.pse === 0 && order.billing?.card_number">
                  <VImg
                      :src="order.billing?.payment_method_name === 'MASTERCARD' ? mastercard : visa"
                      height="50"
                      max-width="50"
                      min-width="50"
                    />
                </div>
                <div v-if="order.billing?.nequi === 1">
                  <VImg
                      :src="nequi"
                      height="50"
                      max-width="50"
                      min-width="50"
                    />
                </div>
                <div v-if="order.billing?.pse === 1">
                  <VImg
                      :src="pse"
                      height="50"
                      max-width="50"
                      min-width="50"
                    />
                </div>
              </template>
              <VCardText>
                <h6 class="text-h6 me-2" v-if="order.billing?.company">
                  {{ order.billing?.company }}
                </h6>
                <span>
                  {{ order.billing?.address }} <br> 
                  {{ order.billing?.street }} <br> 
                  {{ order.billing?.city }} <br> 
                  {{ order.billing?.postal_code }}
                </span>
                <div class="mt-6" v-if="order.billing?.pse === 0 && order.billing?.card_number">
                  <div class="text-body-1 text-body-1 text-high-emphasis font-weight-medium">
                    {{ order.billing?.payment_method_name }}
                  </div>
                  <div class="text-body-1">
                    Número de tarjeta: {{ order.billing?.card_number }}
                  </div>
                </div>

                <div class="mt-6" v-if="order.billing?.pse === 1">
                  <div class="text-body-1 text-body-1 text-high-emphasis font-weight-medium">
                    PSE: {{ order.billing?.pse_bank }}
                  </div>
                  <div class="text-body-1">
                    Referencia de pago: {{ order.billing?.reference_pol }}
                  </div>
                </div>

                <div class="mt-6" v-if="order.billing?.nequi === 1">
                  <div class="text-body-1 text-body-1 text-high-emphasis font-weight-medium">
                    {{ order.billing?.payment_method_name }}
                  </div>
                  <div class="text-body-1">
                    Referencia de pago: {{ order.billing?.reference_pol }}
                  </div>
                </div>
              </VCardText>
            </VCard>
          </VCol>
        </VRow>
      </VCol>

      <VCol
        cols="12"
        md="2"
        class="d-print-none"
      >
        <VCard>
          <VCardText>
            <VBtn
              block
              prepend-icon="mdi-printer"
              class="mb-2"
              @click="printInvoice"
            >
              IMPRIMIR
            </VBtn>

            <VBtn
              block
              variant="tonal"
              color="secondary"
              class="mb-2"
              @click="back"
              >
              REGRESAR
            </VBtn>

          </VCardText>
        </VCard>
      </VCol>
    </VRow>

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
          Está seguro de eliminar el pedido <strong>{{ order.reference_code }}</strong>?.
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
  </div>
</template>
<style lang="scss">

  .v-card.billing .v-card-item {
    padding-bottom: 0 !important
  }

  .order-preview-table {
    --v-table-row-height: 44px !important;
  }

  @media print {
    .v-theme--dark {
      --v-theme-surface: 255, 255, 255;
      --v-theme-on-surface: 94, 86, 105;
    }

    .avatar-dynamic {
      width: 50px !important;
      height: 50px !important;
    }

    .print-order-1 {
      order: 1 !important;
    }

    .print-order-2 {
      order: 2 !important;
    }

    .print-row .v-card {
      border-top: 1px solid #999999;
      border-radius: 0 !important;
      margin: 0 !important;
    }
    .print-row .v-card.client-details {
      border: none !important;
    }

    body {
      background: none !important;
    }

    @page { margin: 0; size: auto; }

    .layout-page-content,
    .v-row,
    .v-col-md-2, .v-col-md-4, .v-col-md-8, .v-col-md-10 {
      padding: 0;
      margin: 0;
    }

    .product-buy-now {
      display: none;
    }

    .v-navigation-drawer,
    .layout-vertical-nav,
    .app-customizer-toggler,
    .layout-footer,
    .layout-navbar,
    .layout-navbar-and-nav-container {
      display: none;
    }

    .v-card {
      box-shadow: none !important;

      .print-row {
        flex-direction: row !important;
      }
    }

    .layout-content-wrapper {
      padding-inline-start: 0 !important;
    }

    .v-table__wrapper {
      overflow: hidden !important;
    }
}
</style>
<route lang="yaml">
    meta:
      action: ver
      subject: pedidos
</route>