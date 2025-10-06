<script setup>

import { ref } from 'vue'
import { useCouponsStores } from '@/stores/coupons'
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import { useClipboard } from '@vueuse/core'
import { formatNumber } from '@formatters'
import router from '@/router'
import Loader from '@/components/common/Loader.vue'
import arrow_right from '@assets/icons/arrow_right.svg?inline';
import arrow_left from '@assets/icons/Arrow_left.svg?inline';
import serpentina from '@assets/images/serpentina.png';

const client_id = ref(null)
const isLoading = ref(true)
const isMobile = /Mobi/i.test(navigator.userAgent);

const cp = useClipboard()
const couponsStores = useCouponsStores()

const coupons = ref(null)
const route = useRoute();

const rowPerPage = ref(5);
const currentPage = ref(1);
const totalPages = ref(1);
const totalCoupons = ref(null);

const baseURL = ref(import.meta.env.VITE_APP_DOMAIN_API_URL + '/storage/')
const backgroundStyle = ref({})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value;
});

watch(() => 
  route.query,(newPath, oldPath) => {
    fetchData()
  }
);

watchEffect(fetchData)

async function fetchData() {
    
    backgroundStyle.value = {
        backgroundImage: `url(${serpentina})`,
        backgroundSize: 'cover',
        backgroundRepeat: 'no-repeat'
    }
    
    if(localStorage.getItem('user_data')){
        const userData = localStorage.getItem('user_data')
        const userDataJ = JSON.parse(userData)

        client_id.value = userDataJ.client.id
    }

    isLoading.value = true

    let info = {
        orderByField: 'created_at',
        orderBy: 'desc',
        limit: rowPerPage.value,
        page: currentPage.value
    }

    var aux = await couponsStores.show_by_client(info, client_id.value)

    coupons.value = aux.coupons.data;
    totalPages.value = aux.coupons.last_page;
    totalCoupons.value = aux.couponsTotalCount;

    isLoading.value = false

}

const changePage = (value) => {
  if(value === 'prev' && currentPage.value !== 1) {
    currentPage.value--
    fetchData()
  }  else if (value === 'next' && currentPage.value !== totalPages.value) {
    currentPage.value++
    fetchData()
  }
}

const chancePagination = () => {
  fetchData();
};

const redirect = (name) => {
    router.push({ name : name})
}

const copy = (data) => {
  cp.copy(data)
}

</script>

<template>
    <Loader :isLoading="isLoading"/>
    <VContainer class="my-1 my-md-10 container-dashboard d-flex flex-column" v-if="coupons">
        <h2 class="data-title mt-5 mt-md-7">Mis Cupones</h2>

        <div class="card-profile coupon-ticket" v-for="(coupon, i) in coupons">
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
                        'error' :
                       (coupon.is_used ? 'primary' : 'secondary') "
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
                            name: 'detail_pusher',
                            params: {
                                id: coupon.order_id
                            }
                        }"
                        class="tw-no-underline">
                        <VBtn class="btn-order tw-text-tertiary">
                            Ver pedido
                        </VBtn>       
                    </router-link>
                    <span v-else>
                        válido hasta {{ coupon.expiration_date }}
                    </span>
                    
                </span>
            </div>
        </div>

        <!-- pagination -->
        <div class="mt-auto">
            <VCardText v-if="totalCoupons === 0" class="d-flex align-center justify-content-center py-3 px-5">
              Datos no disponibles
            </VCardText>
            <VCardText class="d-flex align-center justify-content-center py-3 px-5 pb-0">
                <VPagination
                    v-model="currentPage"
                    :total-visible="isMobile ? 4 : 5"
                    :length="totalPages"
                    rounded="circle"
                    active-color="#FF0090"
                    class="pagination-custom"
                    size="small"
                    @update:modelValue="chancePagination">
                    <template v-slot:prev="{ attrs }">
                        <VBtn variant="plain" icon v-bind="attrs" class="icon-left" @click="changePage('prev')">
                            <arrow_left class="me-2"/>
                            <span class="d-none d-md-block">Anterior</span>
                        </VBtn>
                    </template>
                    <template v-slot:next="{ attrs }">
                        <VBtn variant="plain" icon v-bind="attrs" class="icon-right" @click="changePage('next')">
                            <span class="d-none d-md-block">Siguiente</span>
                            <arrow_right class="ms-1"/>
                        </VBtn>
                    </template>
                </VPagination>
            </VCardText>
        </div>
    </VContainer>
</template>


<style scoped>
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
</style>

<style scoped>

    .v-pagination::v-deep(.v-pagination__item--is-active .v-btn__overlay), .v-pagination::v-deep(.v-pagination__item--is-active .v-btn) {
        opacity: 1 !important;
    }

    .v-pagination::v-deep(.v-btn__content) {
        color: #0A1B33;
        caret-color: #0A1B33;
        z-index: 999;
        text-transform: capitalize;
        z-index: 0;
    }

    .v-pagination::v-deep(.v-pagination__prev button) {
        padding: 0 !important;
    }

    .v-pagination::v-deep(.v-pagination__next button) {
        padding-left: 10px !important;
        padding-right: 0 !important;
    }

    .v-pagination::v-deep(.v-pagination__item) {
        margin-top: 8px;
    }

    .v-pagination::v-deep(.v-pagination__prev:hover .v-btn__content), .v-pagination::v-deep(.v-pagination__next:hover .v-btn__content) {
        color: #FF0090 !important;
        caret-color: #FF0090 !important;
    }

    .icon-left::v-deep(path), .icon-right::v-deep(path) {
        fill: #0A1B33;
    }

    .icon-left:hover::v-deep(path), .icon-right:hover::v-deep(path) {
        fill: #FF0090;
    }

    .v-pagination::v-deep(.v-pagination__prev .v-btn__content), .v-pagination::v-deep(.v-pagination__prev button),
    .v-pagination::v-deep(.v-pagination__next .v-btn__content), .v-pagination::v-deep(.v-pagination__next button) {
        background-color: #E2F8FC; 
        width: 130px;
        color: #0A1B33;
        caret-color: #0A1B33;
        opacity: 1;
        z-index: 0;
    }

    .v-pagination::v-deep(.v-pagination__item--is-active .v-btn__content) {
        color: white;
        caret-color: white;
        z-index: 999;
    }

    .pagination-custom {
        background-color: #E2F8FC;
        border-radius: 16px;
    }

    .container-dashboard {
        padding: 0 15%;
        min-height: 90%;
    }

    .data-title {
        color: #0A1B33;
        font-size: 24px;
        font-style: normal;
        font-weight: 600;
        line-height: normal;
        text-align: left;
    }

    .card-profile {
        margin-top: 24px;
        border-radius: 16px;
        box-shadow: none;
    }

    .text-date {
        font-size: 16px;
        font-style: normal;
        font-weight: 600;
        line-height: 16px; /* 100% */
    }

    .image-product {
        width: 173.96px;
        height: 173.96px;
        max-width: 173.96px;
        border-radius: 16px;
        border: 1px solid var(--Light-Cyan-1, #E2F8FC);
    }

    .row-summary {
        padding: 24px;
        justify-content: space-between;
        align-items: center;
    }

    .text-status {
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 16px;
    }

    .name-product {
        font-size: 16px;
        font-style: normal;
        font-weight: 400;
        line-height: 16px;
    }

    .btn-order {
        border-radius: 32px;
        border: 1px solid var(--Maastricht-Blue, #0A1B33);
        width: 177px;
        font-size: 14px;
        font-style: normal;
        font-weight: 700;
        line-height: 14px;
        box-shadow: none;
    }

    .btn-buy {
        border-radius: 32px;
        border: none;
        font-size: 14px;
        font-style: normal;
        font-weight: 700;
        line-height: 14px;
        box-shadow: none;
        margin-top: 16px;
    }

    .btn-buy:hover {
        background: var(--Magenta-Party-500, #FF27B3);
        box-shadow: 0px 0px 24px 0px #FF27B3;
    }

    .btn-order:hover {
        border: 1px solid var(--Maastricht-Blue, #0A1B33);
        background: var(--Maastricht-Blue, #0A1B33);
        color: #FFFFFF!important;
    }

    @media only screen and (max-width: 767px) {
        .container-dashboard {
            padding: 0 5%;
        }

        .btn-order {
            width: 100%;
            height: 20px;
        }

        .btn-buy {
            width: 100%;
            height: 40px;
        }

        .v-pagination::v-deep(.v-pagination__next button) {
            padding-left: 0 !important;
        }

        .v-pagination::v-deep(.v-pagination__prev .v-btn__content), .v-pagination::v-deep(.v-pagination__prev button),
        .v-pagination::v-deep(.v-pagination__next .v-btn__content), .v-pagination::v-deep(.v-pagination__next button) {
            width: 30px !important;
        }

    }

</style>