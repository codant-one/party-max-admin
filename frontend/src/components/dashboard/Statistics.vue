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
    comission: '(' + (props.data.supplier.commission ?? '0.00') + '%)',
    stats: '$ ' + ((props.data.supplier.retail_sales_not_invoices) ? formatNumber(props.data.supplier.retail_sales_not_invoices) : '0.00'),
    icon: 'tabler-chart-pie-2',
    color: 'primary',
  },
  {
    title: 'Ventas al mayor',
    comission: '(' + (props.data.supplier.wholesale_commission ?? '0.00') + '%)',
    stats: '$ ' + ((props.data.supplier.wholesale_sales_not_invoices) ? formatNumber(props.data.supplier.wholesale_sales_not_invoices) : '0.00'),
    icon: 'tabler-chart-pie-filled',
    color: 'success',
  },
  {
    title: 'Ventas servicios',
    comission: '(' + (props.data.supplier.service_commission ?? '0.00') + '%)',
    stats: '$ ' + ((props.data.supplier.services_not_invoices) ? formatNumber(props.data.supplier.services_not_invoices) : '0.00'),
    icon: 'tabler-chart-bubble',
    color: 'error',
  },
  {
    title: 'Total',
    comission: '(sin comisión)',
    stats: '$ ' + formatNumber(Number( (props.data.supplier.sales_not_invoices) ? props.data.supplier.sales_not_invoices : 0) + Number( (props.data.supplier.services) ? props.data.supplier.services : 0)) ?? '0.00',
    icon: 'mdi-poll',
    color: 'secondary',
  },
  // {
  //   title: 'Productos',
  //   comission: '',
  //   stats: props.data.productsCount,
  //   icon: 'tabler-shopping-cart',
  //   color: 'error',
  // },
  // {
  //   title: 'Servicios',
  //   comission: '',
  //   stats: props.data.serviceCount,
  //   icon: 'mdi-hand-heart-outline',
  //   color: 'info',
  // }
]
</script>

<template>
  <VCard title="Estadísticas">
    <!-- <template #append>
      <span class="text-sm text-disabled">En el mes</span>
    </template> -->

    <VCardText class="pt-6">
      <VRow class="px-0">
        <VCol
          v-for="item in statistics"
          :key="item.title"
          cols="12"
          md="3"
          class="pr-0"
        >
          <div class="d-flex align-center gap-1">
            <VAvatar
              :color="item.color"
              variant="tonal"
              size="42"
            >
              <VIcon :icon="item.icon" />
            </VAvatar>

            <div class="d-flex flex-column">
              <span class="text-h5 font-weight-medium">{{ item.stats }}</span>
              <span class="d-flex">
                <span class="text-sm me-1">
                  {{ item.title }}
                </span>    
                <span class="text-xs" :class="'text-'+item.color">
                  {{ item.comission }}
                </span>  
              </span>         
            </div>
          </div>
        </VCol>
      </VRow>
    </VCardText>
  </VCard>
</template>
