<script setup>

import { ref } from 'vue'
import { useOrdersStores } from '@/stores/orders'
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import router from '@/router'
import Loader from '@/components/common/Loader.vue'
import arrow_right from '@assets/icons/arrow_right.svg?inline';
import arrow_left from '@assets/icons/Arrow_left.svg?inline';

const client_id = ref(null)
const isLoading = ref(true)
const isMobile = /Mobi/i.test(navigator.userAgent);

const ordersStores = useOrdersStores()

const orders = ref(null)
const route = useRoute();

const rowPerPage = ref(5);
const currentPage = ref(1);
const totalPages = ref(1);
const totalOrders = ref(null);

const baseURL = ref(import.meta.env.VITE_APP_DOMAIN_API_URL + '/storage/')

const resolveStatusShipping = shipping_state_id => {
  if (shipping_state_id === 1)
    return { color: 'info' }
  if (shipping_state_id === 2)
    return { color: 'cancel' }
  if (shipping_state_id === 3)
    return { color: 'positive' }
  if (shipping_state_id === 4)
    return { color: 'info' }
}

const resolveStatusPayment = payment_state_id => {
  if (payment_state_id === 1)
    return { color: 'cancel' }
  if (payment_state_id === 2)
    return { color: 'cancel' }
  if (payment_state_id === 3)
    return { color: 'cancel' }
  if (payment_state_id === 4)
    return { color: 'info' }
}

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

    var aux = await ordersStores.show_by_client(info, client_id.value)

    orders.value = aux.orders;
    totalPages.value = aux.ordersAll.last_page;
    totalOrders.value = aux.ordersTotalCount;

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

</script>

<template>
    <Loader :isLoading="isLoading"/>
    <VContainer class="my-1 my-md-10 container-dashboard d-flex flex-column" v-if="orders">
        <h2 class="data-title mt-5 mt-md-7">Mis Compras</h2>

        <VCard class="card-profile px-0" v-for="(order, i) in orders">
            <VCardTitle class="border-line ps-5">
                <span class="text-date tw-text-tertiary">{{  format(order.order_date, 'MMMM d, yyyy', { locale: es }).replace(/(^|\s)\S/g, (char) => char.toUpperCase()) }}</span>
            </VCardTitle>
            <VRow align="center" class="row-summary">
                <VCol cols="12" md="3" class="d-flex justify-center">
                    <VImg :src="baseURL + order.products[0].product_image" class="image-product" v-if="order.products.length > 0"/>
                    <VImg :src="baseURL + order.services[0].service_image" class="image-product" v-else/>
                </VCol>
                <VCol cols="12" md="6" class="d-block">
                    <span v-if="order.payment.id === 4" class="text-status" :class="'tw-text-'+resolveStatusShipping(order.shipping.id)?.color">
                        {{ order.shipping.name }}
                    </span>
                    <span v-else class="text-status" :class="'tw-text-'+resolveStatusPayment(order.payment.id)?.color">
                        {{ order.payment.name }}
                    </span> 
                    <br>
                    <span class="name-product tw-text-tertiary">
                        {{ order.products.length > 0 ? order.products[0].product_name : order.services[0].service_name}}
                    </span> <br>
                    <span class="text-status tw-text-gray" v-if="order.products.length > 0">
                        Color: {{ order.products[0].color }}
                    </span> <br>
                    <span class="text-status tw-text-gray" v-if="order.products.length === 0 && order.services.length > 0 && order.services[0].flavor && order.services[0].is_full">
                        Sabor: {{ order.services[0].flavor }}
                    </span> <br>
                    <span class="text-status tw-text-gray" v-if="order.products.length === 0 && order.services.length > 0 && order.services[0].filling && order.services[0].is_full">
                        Relleno: {{ order.services[0].filling }}
                    </span> <br>
                    <span class="text-status tw-text-gray" v-if="order.products.length === 0 && order.services.length > 0 && order.services[0].cake_size">
                        Tamaño: {{ order.services[0].cake_size }}
                    </span> <br>
                    <span class="text-status tw-text-gray" v-if="order.products.length">
                        {{ order.products[0].quantity }} {{ Number(order.products[0].quantity) === 1 ? 'Unidad' : 'Unidades' }}
                    </span>
                    <span class="text-status tw-text-gray" v-else>
                        {{ order.services[0].quantity }} {{ Number(order.services[0].quantity) === 1 ? 'Unidad' : 'Unidades' }}
                    </span>
                </VCol>
                <VCol cols="12" md="3" class="text-center">
                    <router-link
                        :to="{
                            name: 'detail_pusher',
                            params: {
                                id: order.order_id
                            }
                        }"
                        class="tw-no-underline">
                        <VBtn class="btn-order tw-text-tertiary">
                            Ver pedido
                        </VBtn>       
                    </router-link>
                    <VBtn
                        class="btn-buy tw-bg-primary tw-text-white"
                        @click="redirect('products')">
                            Volver a comprar
                    </VBtn>
                </VCol>
            </VRow>  
        </VCard>

        <!-- pagination -->
        <div class="mt-auto">
            <VCardText v-if="totalOrders === 0" class="d-flex align-center justify-content-center py-3 px-5">
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

    .border-line {
        border-bottom: 1px solid var(--Grey-2, #E1E1E1);
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
        font-weight: 700;
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
            height: 40px;
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