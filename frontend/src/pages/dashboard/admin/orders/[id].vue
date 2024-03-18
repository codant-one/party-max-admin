<script setup>

import { useAddressesStores } from '@/stores/useAddresses'
import { useOrdersStores } from '@/stores/useOrders'
import { formatNumber } from '@/@core/utils/formatters'
import { format } from 'date-fns';
import { avatarText } from '@/@core/utils/formatters'
import { themeConfig } from '@themeConfig'
import { es } from 'date-fns/locale';

const route = useRoute()
const ordersStores = useOrdersStores()

const order = ref(null)
const date = ref(null)

const isRequestOngoing = ref(true)

watchEffect(fetchData)

async function fetchData() {

  isRequestOngoing.value = true

  if(Number(route.params.id)) {
    order.value = await ordersStores.showOrder(Number(route.params.id))

    console.log('order', order.value)
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
    <div v-if="order" class="d-flex justify-space-between align-center flex-wrap gap-y-4 mb-6">
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
                color="secondary"
                class="mb-2"
                :to="{ name: 'dashboard-admin-orders' }"
                >
                Regresar
            </VBtn>

            <VBtn
                variant="tonal"
                color="error"
            >
                Eliminar Pedido
            </VBtn>
        </div>
    </div>

    <VRow v-if="order">
      <VCol
        cols="12"
        md="8"
      >
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
          
          tabla
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
        <VCard title="Actividad de envío">
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
                    Order was placed (Order ID: #32543)
                  </div>
                  <div class="app-timeline-meta">
                    Tuesday 10:20 AM
                  </div>
                </div>
                <p class="app-timeline-text mb-0">
                  Your order has been placed successfully
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

      <VCol
        cols="12"
        md="4"
      >
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
              <span class="text-body-1 font-weight-medium text-high-emphasis">12 Orders</span>
            </div>

            <div class="d-flex flex-column gap-y-1">
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

            <div class="mt-6">
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
  </div>
</template>

<route lang="yaml">
    meta:
      action: ver
      subject: pedidos
</route>