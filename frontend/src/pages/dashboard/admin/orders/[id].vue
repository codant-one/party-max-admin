<script setup>

import { useOrdersStores } from '@/stores/useOrders'
import { formatNumber } from '@/@core/utils/formatters'
import { format } from 'date-fns';
import { avatarText } from '@/@core/utils/formatters'
import { themeConfig } from '@themeConfig'
import { es } from 'date-fns/locale';
import html2pdf from 'html2pdf.js';

const route = useRoute()
const ordersStores = useOrdersStores()

const order = ref(null)
const date = ref(null)
const total = ref(0)

const isRequestOngoing = ref(true)

watchEffect(fetchData)

async function fetchData() {

  isRequestOngoing.value = true

  if(Number(route.params.id)) {
    const response = await ordersStores.showOrder(Number(route.params.id))

    order.value = response.order
    total.value = response.ordersTotalCount

    date.value = order.value.created_at
  }

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

const resolveStatusPayment = shipping_state_id => {
  if (shipping_state_id === 1)
    return { color: 'error' }
  if (shipping_state_id === 2)
    return { color: 'default' }
  if (shipping_state_id === 3)
    return { color: 'warning' }
  if (shipping_state_id === 4)
    return { color: 'info' }
}

const download = () => {

  const element = document.getElementById('invoice-detail');

  const options = {
    margin: 0,
    filename: 'order-' + Number(route.params.id) + '.pdf',
    image: { type: 'jpeg', quality: 0.95 },
    html2canvas: { scale: 4, allowTaint: true },
    jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' },
    pagebreak: { mode: 'css' } 
  };

  html2pdf().set(options).from(element).save();
};

const printInvoice = () => {
  window.print()
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
                    Pedido ID #{{ route.params.id }}
                </h4>
                <div class="d-flex gap-x-2">
                    <VChip
                        variant="tonal"
                        :color="resolveStatusShipping(order.shipping.id)?.color"
                        label
                    >
                        {{ order.shipping.name }}
                    </VChip>
                    <VChip
                        variant="tonal"
                        :color="resolveStatusPayment(order.payment.id)?.color"
                        label
                    >
                        {{ order.payment.name }}
                    </VChip>
                </div>
            </div>
            <div>
                <span class="text-body-1" v-if="date">
                    {{  format(date, 'MMMM d, yyyy, H:mm', { locale: es }).replace(/(^|\s)\S/g, (char) => char.toUpperCase()) }}
                    <span class="text-xs">
                        (Fecha del pedido)
                    </span>
                </span>
            </div>
        </div>
        <div class="d-flex gap-4">
           
            <VBtn
                variant="tonal"
                color="error"
            >
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
          <VCol cols="12" md="8">
            <!-- 👉 Order Details -->
            <VCard class="mb-6 print-row">
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
                    <th class="font-weight-semibold"> PRODUCTO </th>
                    <th class="font-weight-semibold"> PRECIO </th>
                    <th class="font-weight-semibold"> CANTIDAD </th>
                    <th class="font-weight-semibold"> TOTAL </th>   
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="order in order.details"
                    :key="order.id"
                    style="height: 3.75rem;">
                    <td class="text-wrap">
                      <div class="d-flex gap-x-3 align-center">
                        <VAvatar
                          v-if="order.product_color.images.length > 0"
                          size="38"
                          :image="themeConfig.settings.urlStorage + order.product_color.images[0].image"
                          :rounded="0"
                        />
                        <VAvatar
                          v-else
                          size="38"
                          :image="themeConfig.settings.urlStorage + order.product_color.product.image"
                          :rounded="0"
                        />


                        <div class="d-flex flex-column align-start">
                          <span class="text-body-1 font-weight-medium">
                            {{ order.product_color.product.name }}
                          </span>

                          <span class="text-sm text-disabled">
                            Color: {{ order.product_color.color.name }}
                          </span>
                        </div>
                      </div>
                    </td>
                    <td><span>${{ formatNumber(order.price) }}</span></td>
                    <td><span class="text-high-emphasis font-weight-medium">{{ order.quantity }}</span></td>
                    <td><span class="text-h6">${{ formatNumber(order.total) }}</span></td>
                  </tr>
                </tbody>
              </v-table>

              <VDivider />

              <VCardText>
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
            <VCard title="Actividad de envío" class="d-print-none">
              <VCardText>
                <VTimeline
                  truncate-line="both"
                  align="start"
                  side="end"
                  line-color="primary"
                  density="compact"
                  class="v-timeline-density-compact"
                >
                  <VTimelineItem
                    dot-color="primary"
                    size="x-small"
                  >
                    <div class="d-flex justify-space-between align-center">
                      <div class="app-timeline-title">
                        Se realizo el pedido (Pedido ID: #{{ route.params.id }})
                      </div>
                      <div class="app-timeline-meta">
                        Tuesday 10:20 AM
                      </div>
                    </div>
                    <p class="app-timeline-text mb-0">
                      Su pedido ha sido realizado con éxito
                    </p>
                  </VTimelineItem>

                  <VTimelineItem
                    dot-color="primary"
                    size="x-small"
                  >
                    <div class="d-flex justify-space-between align-center">
                      <span class="app-timeline-title">Pick-up</span>
                      <span class="app-timeline-meta">Wednesday 11:29 AM</span>
                    </div>
                    <p class="app-timeline-text mb-0">
                      Pick-up scheduled with courier
                    </p>
                  </VTimelineItem>

                  <VTimelineItem
                    dot-color="primary"
                    size="x-small"
                  >
                    <div class="d-flex justify-space-between align-center">
                      <span class="app-timeline-title">Dispatched</span>
                      <span class="app-timeline-meta">Thursday 8:15 AM</span>
                    </div>
                    <p class="app-timeline-text mb-0">
                      Item has been picked up by courier.
                    </p>
                  </VTimelineItem>

                  <VTimelineItem
                    dot-color="primary"
                    size="x-small"
                  >
                    <div class="d-flex justify-space-between align-center">
                      <span class="app-timeline-title">Package arrived</span>
                      <span class="app-timeline-meta">Saturday 15:20 AM</span>
                    </div>
                    <p class="app-timeline-text mb-0">
                      Package arrived at an Amazon facility, NY
                    </p>
                  </VTimelineItem>

                  <VTimelineItem
                    dot-color="primary"
                    size="x-small"
                  >
                    <div class="d-flex justify-space-between align-center">
                      <span class="app-timeline-title">Dispatched for delivery</span>
                      <span class="app-timeline-meta">Today 14:12 PM</span>
                    </div>
                    <p class="app-timeline-text mb-0">
                      Package has left an Amazon facility , NY
                    </p>
                  </VTimelineItem>

                  <VTimelineItem
                    dot-color="secondary"
                    size="x-small"
                  >
                    <div class="d-flex justify-space-between align-center">
                      <span class="app-timeline-title">Delivery</span>
                    </div>
                    <p class="app-timeline-text mb-0">
                      Package will be delivered by tomorrow
                    </p>
                  </VTimelineItem>
                </VTimeline>
              </VCardText>
            </VCard>
          </VCol>

          <VCol cols="12" md="4" class="print-row">
            <!-- 👉 Customer Details  -->
            <VCard class="mb-6">
              <VCardText class="d-flex flex-column gap-y-6">
                <div class="text-body-1 text-high-emphasis font-weight-medium">
                    Detalles del cliente
                </div>

                <div class="d-flex align-center">
                  <VAvatar
                    class="me-3"
                    variant="tonal"
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
                    <span class="text-sm text-disabled">Cliente ID: #{{ order.client_id }}</span>
                  </div>
                </div>

                <div>
                  <VAvatar
                    variant="tonal"
                    color="success"
                    class="me-3"
                  >
                    <VIcon icon="tabler-shopping-cart" />
                  </VAvatar>
                  <span class="text-body-1 font-weight-medium text-high-emphasis">{{ total }} {{ total > 1 ? 'Pedidos' : 'Pedido' }}</span>
                </div>

                <div class="d-flex flex-column gap-y-1">
                  <div class="d-flex justify-space-between align-center text-body-2">
                    <span class="text-body-1 text-high-emphasis font-weight-medium">Datos de contacto</span>
                  </div>
                  <span>Email: {{ order.client.user.email }} </span>
                  <span>Teléfono: {{ order.client.user.user_detail.phone }}</span>
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
                </div>
                <div>
                  <h6 class="text-h6 me-2 mt-4">
                    {{ order.address.type.name }}
                  </h6>
                  {{ order.address.address }} <br> 
                  {{ order.address.street }} <br> 
                  {{ order.address.city }} <br> 
                  {{ order.address.postal_code }}
                </div>
              </VCardText>
            </VCard>

            <!-- 👉 Billing Address -->
            <VCard>
              <VCardText>
                <div class="d-flex align-center justify-space-between">
                  <div class="text-body-1 text-high-emphasis font-weight-medium">
                    Dirección de Facturación
                  </div>
                </div>
                <h6 class="text-h6 me-2 mt-4" v-if="order.billing.company">
                  {{ order.billing.company }}
                </h6>
                <div>
                  {{ order.billing.address }} <br> 
                  {{ order.billing.street }} <br> 
                  {{ order.billing.city }} <br> 
                  {{ order.billing.postal_code }}
                </div>

                <div class="mt-6" v-if="order.billing.pse === 0">
                  <div class="text-body-1 text-body-1 text-high-emphasis font-weight-medium">
                    {{ order.billing.payment_method_name }}
                  </div>
                  <div class="text-body-1">
                    Número de tarjeta: {{ order.billing.card_number }}
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
            <!-- 👉 Send Invoice Trigger button -->
            <VBtn
              block
              prepend-icon="mdi-cloud-download-outline"
              class="mb-2"
              @click="download"
            >
              DESCARGAR
            </VBtn>

            <VBtn
              block
              color="success"
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
              :to="{ name: 'dashboard-admin-orders' }"
              >
              REGRESAR
            </VBtn>

          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
<style lang="scss">
  .order-preview-table {
    --v-table-row-height: 44px !important;
  }

  @media print {
    .v-theme--dark {
      --v-theme-surface: 255, 255, 255;
      --v-theme-on-surface: 94, 86, 105;
    }

    body {
      background: none !important;
    }

    @page { margin: 0; size: auto; }

    .layout-page-content,
    .v-row,
    .v-col-md-9 {
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