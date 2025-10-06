<script setup>

import { useOrdersStores } from '@/stores/orders'
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import Loader from '@/components/common/Loader.vue'
import router from '@/router'

const ordersStores = useOrdersStores()
const route = useRoute()

const orders = ref(null)
const products = ref(null)
const services = ref(null)
const isLoading = ref(true)
const subtotal = ref(null)
const coupon_id = ref(null)
const coupon = ref(null)
const discount = ref(null)
const shipping_cost = ref(null)
const total = ref(null)

const baseURL = ref(import.meta.env.VITE_APP_DOMAIN_API_URL + '/storage/')
const isMobile = /Mobi/i.test(navigator.userAgent);

const redirect = (name) => {
    router.push({ name : name})
}

watchEffect(fetchData)

async function fetchData() {

    isLoading.value = true
      
    await ordersStores.show_by_id(route.params.id)
      
    orders.value = ordersStores.getData[0]
    products.value = orders.value.products
    services.value = orders.value.services
    subtotal.value = orders.value.subtotal
    coupon_id.value = orders.value.coupon_id
    coupon.value = orders.value.coupon
    shipping_cost.value = orders.value.shipping_cost
    total.value = orders.value.total

    if(coupon_id.value !== null) {//aplico cupón

        if(coupon.value.is_percentage)// es porcentaje
            discount.value = ((subtotal.value * coupon.value.amount) / 100).toFixed(2)
        else
            discount.value = coupon.value.amount.toFixed(2)

    }

    isLoading.value = false
    
}

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

</script>

<template>
    <Loader :isLoading="isLoading"/>
    <VContainer class="my-1 my-md-10 container-dashboard" v-if="orders">
        <div v-for="(product, i) in products">
            <VCard class="card-profile mt-5 pt-5 px-0 pb-0">
                <VRow no-gutters class="px-10 pb-5">
                    <VCol cols="12" md="3" class="d-flex justify-center">
                        <VImg :src="baseURL + product.product_image" class="image-product"/>
                    </VCol>
                    <VCol cols="12" md="6" class="d-flex justify-content-center align-center">
                        <VCardText class="pl-md-0">
                            <VRating
                                v-if="orders.shipping.id === 3"
                                half-increments
                                :length="5"
                                :size="isMobile ? 50 : 40"
                                v-model="product.rating"
                                hover
                                color="yellow-darken-2"
                                active-color="yellow-darken-2"
                                readonly
                            />
                            <span class="d-block name-product tw-text-tertiary ml-3">{{ product.product_name}}</span>
                            <span class="text-status tw-text-gray ml-3">
                                Color: {{ product.color }}
                            </span> <br>
                            <span class="d-block text-status tw-text-gray ml-3">{{ product.quantity }} {{ Number(product.quantity) === 1 ? 'Unidad' : 'Unidades' }}</span>
                        </VCardText>
                    </VCol>
                    <VCol cols="12" md="3" class="d-flex flex-column justify-content-center align-center">
                        <router-link
                            :to="{
                                name: 'rating_products',
                                params: {
                                    id: product.product_id,
                                    orderId: route.params.id
                                }
                            }"
                            class="tw-no-underline btn-opinion-100">
                            <VBtn class="btn-opinion tw-text-tertiary" v-if="orders.shipping.id === 3">
                                Editar opinión
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
        </div>           

        <div v-for="(service, i) in services">
            <VCard class="card-profile mt-5 pt-5 px-0 pb-0">
                <VRow no-gutters class="px-10 pb-5">
                    <VCol cols="12" md="3" class="d-flex justify-center">
                        <VImg :src="baseURL + service.service_image" class="image-product"/>
                    </VCol>
                    <VCol cols="12" md="6" class="d-flex justify-content-center align-center">
                        <VCardText class="pl-md-0">
                            <VRating
                                v-if="orders.shipping.id === 3"
                                half-increments
                                :length="5"
                                :size="isMobile ? 50 : 40"
                                v-model="service.rating"
                                hover
                                color="yellow-darken-2"
                                active-color="yellow-darken-2"
                                readonly
                            />
                            <span class="d-block name-product tw-text-tertiary ml-3">{{ service.service_name}}</span>
                            <span class="text-status tw-text-gray ml-3" v-if="service.flavor && service.is_full">
                                Sabor: {{ service.flavor }}
                            </span> <br v-if="service.flavor">
                            <span class="text-status tw-text-gray ml-3" v-if="service.filling && service.is_full">
                                Relleno: {{ service.filling }}
                            </span> <br v-if="service.filling">
                            <span class="text-status tw-text-gray ml-3" v-if="service.cake_size">
                                Tamaño: {{ service.cake_size }}
                            </span> <br v-if="service.cake_size">
                            <span class="d-block text-status tw-text-gray ml-3">{{ service.quantity }} {{ Number(service.quantity) === 1 ? 'Unidad' : 'Unidades' }}</span>
                        </VCardText>
                    </VCol>
                    <VCol cols="12" md="3" class="d-flex flex-column justify-content-center align-center">
                        <router-link
                            :to="{
                                name: 'rating_products',
                                params: {
                                    id: service.service_id,
                                    orderId: route.params.id
                                }
                            }"
                            class="tw-no-underline btn-opinion-100">
                            <VBtn class="btn-opinion tw-text-tertiary" v-if="orders.shipping.id === 3">
                                Editar opinión
                            </VBtn>
                        </router-link> 
                        <VBtn
                            class="btn-buy tw-bg-primary tw-text-white"
                            @click="redirect('services')">
                                Volver a comprar
                        </VBtn>
                    </VCol>
                </VRow>
            </VCard>
        </div>

        <!--DETALLES DE LA COMPRA-->
        <VCard class="card-profile p-0 pb-3">
            <VCardTitle class="col-detalle px-10 py-5">
                <span class="tw-text-tertiary">Detalle de la compra</span>
            </VCardTitle>
            <VCardText class="d-flex px-10 py-3">
                <span class="text-editar tw-text-tertiary">Productos</span>
                <VSpacer />
                <span class="text-editar tw-text-tertiary">${{subtotal}}</span>
            </VCardText>
            <VCardText class="d-flex px-10 py-3" v-if="coupon_id">
                <span class="text-editar tw-text-yellow">Descuento</span>
                <VSpacer />
                <span class="text-editar tw-text-yellow">-${{discount}}</span>
            </VCardText>
            <VCardText class="d-flex px-10 py-3">
                <span class="text-editar tw-text-tertiary">Envío</span>
                <VSpacer />
                <span class="text-editar tw-text-tertiary">${{shipping_cost}}</span>
            </VCardText>
            <VCardText class="d-flex px-10 pt-3 pb-5">
                <span class="text-editar tw-text-tertiary tw-font-bold">Total</span>
                <VSpacer />
                <span class="text-editar tw-text-tertiary tw-font-bold">${{total}}</span>
            </VCardText>
        </VCard>

        <!--DATOS DE ENTREGA-->
        <VCard class="card-profile px-0 pt-0 pb-3 mb-7 mb-md-0">
            <VCardTitle class="px-10 pt-5 pb-0">
                <span v-if="orders.payment.id === 4" class="text-editar" :class="'tw-text-'+resolveStatusShipping(orders.shipping.id)?.color">
                    {{ orders.shipping.name }}
                </span>
                <span v-else class="text-editar" :class="'tw-text-'+resolveStatusPayment(orders.payment.id)?.color">
                    {{ orders.payment.name }}
                </span> 
            </VCardTitle>
            <VCardText class="d-flex px-10 py-3 pb-0" v-if="orders.payment.id === 4">
                <span v-if="orders.payment.id === 4 && orders.shipping.id === 1" class="text-date tw-text-tertiary">
                    {{ orders.type === 1 ? 'El pedido fue realizado con exito.' : 'El pedido está en el almacén, listo para enviar.' }}
                </span>
                <span v-if="orders.payment.id === 4 && orders.shipping.id === 2" class="text-date tw-text-tertiary">
                    {{ orders.type === 1 ? 'No se pudo realizar el pedido.' : 'No se pudo enviar el paquete.' }}
                </span>
                <span v-if="orders.payment.id === 4 && orders.shipping.id === 3" class="text-date tw-text-tertiary">
                    {{ orders.type === 1 ? 'Se entrego el' : 'Llegó el' }} {{ format(orders.updated_at, 'd').concat(' de ') }} {{ format(orders.updated_at, 'MMMM, y', { locale: es }).replace(/(^|\s)\S/g, (char) => char.toUpperCase()) }}.
                </span>
                <span v-if="orders.payment.id === 4 && orders.shipping.id === 4" class="text-date tw-text-tertiary">
                    {{ orders.type === 1 ? 'El pedido fue aceptado por el proveedor' : 'El paquete llegará de 3 a 5 días hábiles.' }}
                </span>
            </VCardText>
            <VCardText class="d-flex px-10 py-3">
                <span v-if="orders.payment.id === 4 && orders.shipping.id !== 2" class="text-editar tw-text-tertiary">
                    {{ orders.payment.id === 4 && orders.shipping.id === 3 ? 'Entregamos' : 'Entregaremos' }} 
                    {{ orders.type === 1 ? 'tu pedido en'  : 'tu paquete en' }}
                    {{ orders.address.address }} ,
                    {{ orders.address.street }} <span v-if="orders.address.street !== null">,</span>
                    {{ orders.address.city }} ,
                    {{ orders.address.province.name }}.<br>
                    <span v-if="orders.address.postal_code">
                        Código Postal: {{ orders.address.postal_code }}. 
                    </span>
                    <span v-if="orders.billing.note !== null">
                        ({{ orders.billing.note }}).
                    </span>
                </span>
                <span v-if="orders.payment.id !== 4" class="text-editar tw-text-tertiary">
                    No se pudo procesar el pago.
                </span>
                <span v-if="orders.shipping.id === 2" class="text-editar tw-text-tertiary">
                    Razón: {{ orders.histories[1].reason }}
                </span>
            </VCardText>
        </VCard>
    </VContainer>
</template>

<style scoped>

    .v-rating::v-deep(.v-icon--size-default) {
        font-size: 35px;
    }

    .btn-opinion {
        border-radius: 32px;
        border: 1px solid var(--Maastricht-Blue, #0A1B33);
        width: 177px;
        font-size: 14px;
        font-style: normal;
        font-weight: 700;
        line-height: 14px;
        box-shadow: none;
    }

    .btn-opinion:hover   {
        border: 1px solid var(--Maastricht-Blue, #0A1B33);
        background: var(--Maastricht-Blue, #0A1B33);
        color: #FFFFFF!important;
    }

    .container-dashboard {
        padding: 2% 15%;
    }

    .card-profile {
        padding: 16px 32px;
        margin-top: 24px;
        border-radius: 16px;
        box-shadow: none;
    }

    .text-editar {
        font-size: 16px;
        font-style: normal;
        font-weight: 400;
        line-height: 20px;
    }

    .image-product {
        width: 173.96px;
        height: 173.96px;
        max-width: 173.96px;
        border-radius: 16px;
        border: 1px solid var(--Light-Cyan-1, #E2F8FC);
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

    .col-detalle {
        border-bottom: 1px solid var(--Light-Cyan-3, #D9EEF2);
    }

    .col-detalle span {
        font-size: 16px;
        font-style: normal;
        font-weight: 600;
        line-height: normal;
    }

    .text-date {
        font-size: 24px;
        font-style: normal;
        font-weight: 400;
        line-height: 24px; 
    }

    @media only screen and (max-width: 767px)  {
        .container-dashboard {
            padding: 0 5%;
        }

        .btn-buy {
            width: 100%;
            height: 40px;
        }

        .btn-opinion {
            width: 100%;
            height: 40px;
        }

        .btn-opinion-100 {
            width: 100%;
        }

        .col-editar {
            text-align: center;
        }
    }
</style>