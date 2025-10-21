<script setup>

import { formatNumber } from '@/@core/utils/formatters'
import { VIcon } from 'vuetify/components/VIcon'
import sliderBar1 from '@images/festin/002@2x.png'
import sliderBar2 from '@images/festin/03@2x.png'
import sliderBar3 from '@images/festin/04@2x.png'
import banner from '@images/logos/banner4.png'

const props = defineProps({
  productCount: {
    type: Object,
    required: false
  },
  orderCount: {
    type: Object,
    required: false
  },
  sales: {
    type: Object,
    required: false
  }
})

const userDataJ = ref('')
const name = ref('')
const product_count = ref(props.productCount)
const order_count = ref(props.orderCount)
const salesTotal = ref(props.sales)

const websiteAnalytics = [
  {
    name: 'PRODUCTOS',
    slideImg: sliderBar1,
    data: [
      {
        number: product_count.value.published ?? '0',
        text: 'Publicados'
      },
      {
        number: product_count.value.pending ?? '0',
        text: 'Pendientes'
      },
      {
        number: product_count.value.rejeted ?? '0',
        text: 'Rechazados'
      },
      {
        number: product_count.value.deleted ?? '0',
        text: 'Eliminados'
      },
    ],
  },
  {
    name: 'PEDIDOS',
    slideImg: sliderBar2,
    data: [
      {
        number: order_count.value.payment ?? '0',
        text: 'Pagados'
      },
      {
        number: order_count.value.pending ?? '0',
        text: 'Pendientes'
      },
      {
        number: order_count.value.failed ?? '0',
        text: 'Fallidos'
      },
      {
        number: order_count.value.canceled ?? '0',
        text: 'Cancelados'
      },
    ],
  },
  {
    name: 'VENTAS',
    slideImg: sliderBar3,
    data: [
      {
        number: '$ ' + formatNumber(salesTotal.value.today) ?? '0.00',
        text: 'Hoy'
      },
      {
        number: '$ ' + formatNumber(salesTotal.value.last_7_days) ?? '0.00',
        text: 'Semana'
      },
      {
        number: '$ ' + formatNumber(salesTotal.value.last_30_days) ?? '0.00',
        text: 'Mes'
      },
      {
        number: '$ ' + formatNumber(salesTotal.value.year) ?? '0.00',
        text: 'Año'
      },
    ],
  },
]

watchEffect(fetchData)

async function fetchData() {

    const userData = localStorage.getItem('user_data')
    
    userDataJ.value = JSON.parse(userData)
    name.value = userDataJ.value.name + " " + userDataJ.value.last_name
}

</script>

<template>
    <VCard :style="{ backgroundImage: `url(${banner})`, backgroundSize: `cover`}">
        <VCarousel
            cycle
            :continuous="false"
            :show-arrows="false"
            hide-delimiter-background
            :delimiter-icon="() => h(VIcon, { icon: 'fa-circle', size: '10' })"
            height="auto"
            class="carousel-delimiter-top-end web-analytics-carousel"
            >
            <VCarouselItem
                v-for="item in websiteAnalytics"
                :key="item.name"
            >
                <VCardText>
                    <VRow>
                        <VCol cols="12">
                        <h4 class="text-h4 text-magenta mb-1">
                            Análisis de Estadísticas
                        </h4>
                        <p class="text-sm mb-0 text-blue">
                            {{ name }}
                        </p>
                        </VCol>

                        <VCol
                            cols="12"
                            sm="6"
                            order="2"
                            order-sm="1"
                            >
                            <VRow>
                                <VCol cols="12" class="pb-0 pt-1">
                                    <p class="font-weight-bold mb-1 text-blue">
                                        {{ item.name }}
                                    </p>
                                </VCol>

                                <VCol
                                    v-for="d in item.data"
                                    :key="d.number"
                                    cols="6"
                                    class="text-no-wrap pb-2"
                                    >
                                    <VChip
                                        label
                                        variant="flat"
                                        size="default"
                                        color="rgba(var(--v-theme-primary), 1)"
                                        class="font-weight-medium text-white rounded me-2"
                                        :style="{ minWidth: '50px'}"
                                    >
                                        {{ d.number }}
                                    </VChip>
                                    <span class="text-blue">{{ d.text }}</span>
                                </VCol>
                            </VRow>
                        </VCol>

                        <VCol
                            cols="12"
                            sm="6"
                            order="1"
                            order-sm="2"
                            class="position-relative text-center"
                            >
                            <img
                                :src="item.slideImg"
                                class="card-website-analytics-img"
                                style="filter: drop-shadow(0 4px 40px rgba(0, 0, 0, 40%));"
                            >
                        </VCol>
                    </VRow>
                </VCardText>
            </VCarouselItem>
        </VCarousel>
    </VCard>
</template>

<style lang="scss">
    .p-0 {
        padding: 0 !important;
    }

    .card-website-analytics-img {
        block-size: 160px;
    }

    .text-blue {
      color: #0A1B33 !important;
    }
    
    .text-magenta {
      color: #FF0090 !important;
    }

    @media screen and (min-width: 600px) {
        .card-website-analytics-img {
            position: absolute;
            margin: auto;
            inset-block-end: 40px;
            inset-block-start: -1rem;
            inset-inline-end: 1rem;
        }
    }
</style>