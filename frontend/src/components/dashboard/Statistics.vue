<script setup>

import { formatNumber } from '@/@core/utils/formatters'

const props = defineProps({
  data: {
    type: Object,
    required: true
  }
})

const statistics = [
  {
    title: 'Ventas al detal',
    stats: '$ ' + formatNumber(props.data.supplier.account.retail_sales_amount) ?? '0.00',
    icon: 'tabler-chart-pie-2',
    color: 'primary',
  },
  {
    title: 'Ventas mayoristas',
    stats: '$ ' + formatNumber(props.data.supplier.account.wholesale_sales_amount) ?? '0.00',
    icon: 'tabler-chart-pie-filled',
    color: 'info',
  },
  {
    title: 'Productos',
    stats: props.data.productsCount,
    icon: 'tabler-shopping-cart',
    color: 'error',
  },
  {
    title: 'Servicios',
    stats: props.data.serviceCount,
    icon: 'mdi-hand-heart-outline',
    color: 'success',
  }
]
</script>

<template>
  <VCard title="Estadísticas">
    <!-- <template #append>
      <span class="text-sm text-disabled">En el mes</span>
    </template> -->

    <VCardText class="pt-6">
      <VRow>
        <VCol
          v-for="item in statistics"
          :key="item.title"
          cols="6"
          md="3"
        >
          <div class="d-flex align-center gap-2">
            <VAvatar
              :color="item.color"
              variant="tonal"
              size="42"
            >
              <VIcon :icon="item.icon" />
            </VAvatar>

            <div class="d-flex flex-column">
              <span class="text-h5 font-weight-medium">{{ item.stats }}</span>
              <span class="text-sm">
                {{ item.title }}
              </span>
            </div>
          </div>
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
