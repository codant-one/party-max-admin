<script setup>

import { useCouponsStores } from '@/stores/useCoupons'
import { formatNumber } from '@/@core/utils/formatters'
import { useClipboard } from '@vueuse/core';
import serpentina from '@images/serpentina.png';
import router from '@/router'

const route = useRoute()
const couponsStores = useCouponsStores()
const coupon = ref([])
const backgroundStyle = ref({})
const cp = useClipboard()

const advisor = ref({
  type: '',
  message: '',
  show: false
})

const isRequestOngoing = ref(true)

watchEffect(fetchData)

async function fetchData() {

  isRequestOngoing.value = true

  if(Number(route.params.id) && route.name === 'dashboard-admin-coupons-id') {
    const response = await couponsStores.showCoupon(Number(route.params.id))

    coupon.value = response

    backgroundStyle.value = {
        backgroundImage: `url(${serpentina})`,
        backgroundSize: 'cover',
        backgroundRepeat: 'no-repeat'
    }

  }

  isRequestOngoing.value = false
}

const copy = (data) => {
  
  cp.copy(data)

  advisor.value.type = 'success'
  advisor.value.show = true
  advisor.value.message = 'Código copiado!'

  setTimeout(() => {
      advisor.value.show = false
      advisor.value.type = ''
      advisor.value.message = ''
  }, 5000)
}

const back = () => {
    router.push({ name : 'dashboard-admin-coupons'})
}

</script>

<template>
  <div>
    <VAlert
        v-if="advisor.show"
        :type="advisor.type"
        class="mb-6">  
        {{ advisor.message }}
    </VAlert>
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
    <div v-if="!isRequestOngoing" class="d-flex justify-space-between align-center flex-wrap gap-y-4 mb-6">
        <div>
            <div class="d-flex gap-2 align-center mb-2 flex-wrap">
                <h4 class="text-h4 font-weight-medium">
                    Código: {{ coupon.code }}
                </h4>
            </div>
        <div>
        </div>
      </div>
      <div class="d-flex gap-4">
        <VBtn
            variant="tonal"
            color="secondary"
            @click="back"
        >
          REGRESAR
        </VBtn>
      </div>
    </div>

    <VRow v-if="!isRequestOngoing">
      <VCol
        cols="12"
        md="12"
        id="invoice-detail"
      >
        <VRow>
          <VCol cols="12" md="12">
            <div class="card-profile coupon-ticket">
                <div class="coupon-left tw-text-white" :style="backgroundStyle">
                    <h2 class="coupon-title">
                        <span class="font-weight-bold">Cupón</span> <br />
                        <span>DESCUENTO</span> <br />
                        <span class="font-weight-bold" v-if="coupon.is_percentage">{{coupon.amount}}%</span>
                        <span class="font-weight-bold" v-else>${{ formatNumber(coupon.amount) }}</span>
                    </h2>
                </div>

                <div class="coupon-right">
                    <VChip
                        label
                        size="small"
                        class="ms-auto"
                        variant="outlined"
                        :color="(new Date(coupon.expiration_date) < new Date() && coupon.purchase_date === null)   ? 
                            'warning' :
                        (coupon.is_used ? 'success' : 'error') "
                        >
                        {{ 
                            (new Date(coupon.expiration_date) < new Date() && coupon.purchase_date === null )? 
                            'EXPIRADO' :
                        (coupon.is_used ? 'APLICADO' : 'PENDIENTE') 
                        }}
                    </VChip>
                    <br>
                    <span class="coupon-instructions">
                        Introduce el código <span class="font-weight-bold" @click="copy(coupon.code)">{{ coupon.code }}</span> <br />
                        en tu carrito de compra
                    </span>
                    <span class="coupon-validity">
                        {{ coupon.description }}
                    </span>
                    <span class="coupon-validity tw-text-yellow" v-if="new Date(coupon.expiration_date) < new Date() && coupon.purchase_date === null">
                        Expirado el {{ coupon.expiration_date }}
                    </span>
                    <span class="coupon-validity" :class="coupon.is_used ? 'mt-3' : ''" v-else>
                        <router-link
                            v-if="coupon.is_used"
                            :to="{
                                name: 'dashboard-admin-orders-id',
                                params: {
                                    id: coupon.order_id
                                }
                            }"
                            class="tw-no-underline">
                            <VBtn size="x-small" class="btn-xs p-1 tw-text-tertiary">
                                Ver pedido
                            </VBtn>       
                        </router-link>
                        <span v-else>
                            válido hasta {{ coupon.expiration_date }}
                        </span>
                        
                    </span>
                </div>
            </div>
          </VCol>
        </VRow>
      </VCol>
    </VRow>
  </div>
</template>
<style lang="scss">

    .card-profile {
        margin-top: 24px;
        border-radius: 16px;
        box-shadow: none;
    }

    .coupon-ticket {
        display: flex;
        height: 200px;
        overflow: hidden; /* Para que se corte bien el zigzag */
        background-color: #ffffff;
        font-family: "Segoe UI", Tahoma, sans-serif;
    }

    .coupon-left {
        flex: 0 0 25%; 
        background: #FFC549; /* Azul de fondo */
        position: relative;
        clip-path: polygon(
            0 0,      /* Esquina superior izq */
            90% 0,    /* Justo antes del zigzag */
            100% 10%, /* Zigzag 1 */
            90% 20%,  /* Zigzag 2 */
            100% 30%, /* Zigzag 3 */
            90% 40%,  /* Zigzag 4 */
            100% 50%, /* Zigzag 5 */
            90% 60%,  /* Zigzag 6 */
            100% 70%, /* Zigzag 7 */
            90% 80%,  /* Zigzag 8 */
            100% 90%, /* Zigzag 9 */
            90% 100%, /* Zigzag 10 */
            0 100%    /* Esquina inferior izq */
        );
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
    }

    .coupon-title {
        text-align: center;
        line-height: 1.2;
        margin: 0;
    }

    .coupon-title span {
        font-size: 1.1rem;
        font-weight: normal;
    }

    .coupon-right {
        padding: 20px;
        display: flex;
        flex-direction: column;
        color: #0A1B33;
        width: 100%;
    }

    .coupon-instructions {
        font-size: 1.1rem;
        margin: 0 0 12px 0;
        line-height: 1.4;
    }

    .coupon-validity {
        font-size: 0.9rem;
        color: #666;
        margin: 0;
        line-height: 1.2;
    }

    @media only screen and (max-width: 767px) { 

        .coupon-instructions, .coupon-validity {
            font-size: 12px;
        }

        .coupon-title, .coupon-title span {
            font-size: 16px;
        }
    }

    .product-preview-table {
        --v-table-row-height: 44px !important;
    }

    .is_blocked {
        text-decoration: line-through;
    }
</style>
<route lang="yaml">
    meta:
      action: ver
      subject: cupones
</route>