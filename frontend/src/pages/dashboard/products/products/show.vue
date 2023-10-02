<script setup>

import { themeConfig } from '@themeConfig'
import { FreeMode, Navigation, Thumbs } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Carousel, Slide } from 'vue3-carousel'

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/free-mode';
import 'swiper/css/thumbs';
import 'vue3-carousel/dist/carousel.css'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true
  },
  product: {
    type: Object,
    required: true
  }

})

const emit = defineEmits([
  'update:isDrawerOpen',
  'close'
])

const productImages = ref([])
const currentSlide = ref(0)
const modules = ref([FreeMode, Navigation, Thumbs])
const thumbsSwiper = ref(null);

const title = ref('')
const description = ref(null)
const sku = ref('')
const selling_price = ref(null)
const sales =  ref(null)
const comments = ref(null)
const favourite = ref('')
const rating = ref(null)
const price = ref('')
const price_for_sale = ref('')
const stock = ref('')
const width = ref('')
const height = ref('')
const deep = ref('')

watchEffect(() => {
    if (props.isDrawerOpen) {
        if (!(Object.entries(props.product).length === 0) && props.product.constructor === Object) {
            productImages.value = props.product.images
            productImages.value.push({image: props.product.image})

            title.value = props.product.name
            description.value = props.product.description
            sku.value = props.product.sku ?? null
            selling_price.value = props.product.selling_price ?? 0
            sales.value = props.product.sales ?? 0
            comments.value = props.product.comments ?? 0
            favourite.value = props.product.favourite ?? 0
            rating.value = props.product.rating ?? 0
            price.value = props.product.price
            price_for_sale.value = props.product.price_for_sale
            stock.value = props.product.stock
            width.value = props.product.detail.width
            height.value = props.product.detail.height
            deep.value = props.product.detail.deep
        }
    }
})

const closeProductDetailDialog = function() {
    thumbsSwiper.value = null
    productImages.value = []
    
    emit('update:isDrawerOpen', false)
}

const slideTo = (val) => {
    currentSlide.value = val
}

const setThumbsSwiper = (swiper) => {
    thumbsSwiper.value = swiper;
}

</script>

<template>
    <!-- DIALOGO DE VER -->
    <VDialog
        :model-value="props.isDrawerOpen"
        max-width="1000"
        persistent>
        <DialogCloseBtn @click="closeProductDetailDialog" />

        <VCard title="Detalle producto">
            <VCardText>
                <VRow>
                    <VCol md="1" cols="12">
                        <div class="d-none d-md-block">
                            <swiper
                                :direction="'vertical'"
                                :pagination="{ clickable: true}"
                                :spaceBetween="5"
                                :slidesPerView="6"
                                :freeMode="true"
                                :watchSlidesProgress="true"
                                @swiper="setThumbsSwiper"
                                class="mySwiper"
                            >
                                <swiper-slide v-for="(picture, index) in productImages" :key="index">
                                    <img :src="themeConfig.settings.urlStorage + picture.image" />
                                </swiper-slide>
                            </swiper>
                        </div>
                        <div class="d-block d-md-none">
                            <Carousel id="gallery" :items-to-show="1" :wrap-around="false" v-model="currentSlide">
                                <Slide v-for="(picture, index) in productImages" :key="index">
                                    <div class="carousel__item">
                                        <img :src="themeConfig.settings.urlStorage + picture.image" />
                                    </div>
                                </Slide>
                            </Carousel>

                            <Carousel
                                id="thumbnails"
                                :items-to-show="4"
                                :wrap-around="true"
                                v-model="currentSlide"
                                ref="carousel"
                            >
                                <Slide v-for="(picture, index) in productImages" :key="index">
                                    <div class="carousel__item" @click="slideTo(index)">
                                        <img :src="themeConfig.settings.urlStorage + picture.image" />
                                    </div>
                                </Slide>
                            </Carousel>
                        </div>
                    </VCol>
                    <VCol md="4" cols="12">
                        <div class="d-none d-md-block">
                            <swiper
                                :style="{
                                    '--swiper-navigation-color': '#fff',
                                    '--swiper-pagination-color': '#fff',
                                }"
                                :spaceBetween="10"
                                :navigation="true"
                                :thumbs="{ swiper: thumbsSwiper }"
                                :modules="modules"
                                class="mySwiper2"
                            >
                                <swiper-slide v-for="(picture, index) in productImages" :key="index">
                                    <img :src="themeConfig.settings.urlStorage + picture.image" />
                                </swiper-slide>
                            </swiper>
                        </div>
                    </VCol>
                    <VCol md="7" cols="12">
                        <VCardTitle class="text-h6 title-truncate py-0"> {{ title }} </VCardTitle>
                        <VCardSubtitle v-if="description" class="subtitle-truncate mb-3"> {{ description }} </VCardSubtitle>
                        <div class="px-4">
                            <div class="font-weight-semibold">PRECIO: 
                                <span>{{ price }}</span>
                            </div>
                            <div class="font-weight-semibold">PRECIO A LA VENTA: 
                                <span>{{ price_for_sale }}</span>
                            </div>
                            <div class="font-weight-semibold">SKU: 
                                <span>{{ sku }}</span>
                            </div>
                            <div>Stock: 
                                <span>{{ stock }}</span>
                            </div>
                            <div>Ancho: 
                                <span>{{ width }}</span>
                            </div>
                            <div>Alto: 
                                <span>{{ height }}</span>
                            </div>
                            <div>Profundo: 
                                <span>{{ deep }}</span>
                            </div>
                            <div>Envios: 
                                <span v-if="selling_price >= 0">{{ selling_price }}</span>
                                <span v-else>
                                    <VIcon
                                        size="20"
                                        icon="tabler-alarm-filled"
                                        class="me-1"
                                    />
                                </span>
                            </div>
                            <div>Ventas:
                                <span v-if="sales >= 0">{{ sales }}</span>
                                <span v-else>
                                    <VIcon
                                        size="20"
                                        icon="tabler-alarm-filled"
                                        class="me-1"
                                    />
                                </span>
                            </div>
                            <div>Comentarios:
                                <span v-if="comments >= 0">{{ comments }}</span>
                                <span v-else>
                                    <VIcon
                                        size="20"
                                        icon="tabler-alarm-filled"
                                        class="me-1"
                                    />
                                </span>
                            </div>
                            <div>Favoritos:
                                <span v-if="favourite >= 0">{{ favourite }}</span>
                                <span v-else>
                                    <VIcon
                                        size="20"
                                        icon="tabler-alarm-filled"
                                        class="me-1"
                                    />
                                </span>
                            </div>  
                            <div>Valoracion:
                                <span>
                                    <VIcon
                                        v-for="index in 5"
                                        size="20"
                                        icon="tabler-star"
                                        class="me-1"
                                    />
                                </span>
                            </div>
                            <div>Categorías:
                                <span v-if="rating >= 0">{{ rating }}</span>
                                <span v-else>
                                    <VIcon
                                        size="20"
                                        icon="tabler-alarm-filled"
                                        class="me-1"
                                    />
                                </span>
                            </div> 
                        </div>               
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
    </VDialog>
</template>

<style scoped>

    .carousel__item img {
        width: 60%;
    }

    .swiper-vertical > .swiper-pagination-bullets .swiper-pagination-bullet, .swiper-pagination-vertical.swiper-pagination-bullets .swiper-pagination-bullet {
        display: none !important;
    }
    .swiper {
        width: 100%;
        height: 100%;
    }

    .swiper-slide {
        text-align: center;
        font-size: 18px;
        background: #fff;

        /* Center slide text vertically */
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .swiper {
        width: 100%;
        height: 350px;
        margin-left: auto;
        margin-right: auto;
    }

    .swiper-slide {
        background-size: cover;
        background-position: center;
    }

    .mySwiper2 {
        height: 350px;
        width: 100%;
    }

    .mySwiper {
        box-sizing: border-box;
        padding: 10px 5px;
    }

    .mySwiper .swiper-slide {
        opacity: 0.4;
        border-style: solid;
        border-width: 1px;
        border-radius: 8px;
    }

    .mySwiper .swiper-slide-thumb-active {
        opacity: 1;
    }

    .swiper-slide img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 8px;
    }

</style>
