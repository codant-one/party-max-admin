<script setup>

import { themeConfig } from '@themeConfig'
import { FreeMode, Navigation, Thumbs, Scrollbar } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Carousel, Slide } from 'vue3-carousel'

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/free-mode';
import 'swiper/css/thumbs';
import 'swiper/css/scrollbar';
import 'vue3-carousel/dist/carousel.css'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true
  },
  service: {
    type: Object,
    required: true
  }

})

const emit = defineEmits([
  'update:isDrawerOpen',
  'close'
])

const serviceImages = ref([])
const currentSlide = ref(0)
const modules = ref([FreeMode, Navigation, Thumbs, Scrollbar])
const thumbsSwiper = ref(null);

const title = ref('')
const description = ref(null)
const sku = ref('')
const videos = ref('')
const selling_price = ref(null)
const sales =  ref(null)
const comments = ref(null)
const favourite = ref('')
const rating = ref(null)
const price = ref('')
const categories = ref('')
const store = ref('')
const imageAux = ref('')

const tab = ref('1')

watchEffect(async () => {
    if (props.isDrawerOpen) {
        if (!(Object.entries(props.service).length === 0) && props.service.constructor === Object) {
            imageAux.value = [{ image : props.service.image }]

            categories.value = props.service.categories.map(item => item.category.name)
            serviceImages.value = props.service.images

            store.value = props.service.user.user_detail.store_name ?? (props.service.user.supplier?.company_name ?? (props.service.user.name + ' ' + (props.service.user.last_name ?? '')))
            title.value = props.service.name
            description.value = props.service.single_description
            sku.value = props.service.sku ?? null
            selling_price.value = props.service.selling_price ?? 0
            sales.value = props.service.sales ?? 0
            comments.value = props.service.comments ?? 0
            favourite.value = props.service.favourite ?? 0
            rating.value = props.service.rating ?? 2.5
            price.value = props.service.cupcakes.length > 0 ? props.service.cupcakes[0].price : props.service.price
            
            videos.value = props.service.videos.map(u => ({
                type: 'video',
                url: u.url,
                thumb: '/assets/video-placeholder.png',
            }))

            await Promise.all(
                videos.value.map(async slide => {
                if (slide.type === 'video') {
                    slide.thumb = await loadVideoThumbnail(slide.url);
                }
                })
            );
        }
    }
})

const mediaSlides = computed(() => {
    const imgs = serviceImages.value.map(i => ({
        type: 'image',
        url: themeConfig.settings.urlStorage + i.image,
        thumb: themeConfig.settings.urlStorage + i.image
    }));
    const vids = videos.value.map(u => ({
        type: 'video',
        url: u.url,
        thumb: u.thumb
    }));
    const main = {
        type: 'image',
        url: themeConfig.settings.urlStorage + imageAux.value[0].image,
        thumb: themeConfig.settings.urlStorage + imageAux.value[0].image
    }
    return [...vids, main, ...imgs];
});

const loadVideoThumbnail = async (url) => {
    const yt = url.match(
        /(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/
    );
    if (yt) {
        return `https://img.youtube.com/vi/${yt[1]}/mqdefault.jpg`;
    }

    const vm = url.match(/vimeo\.com\/(\d+)/);
    if (vm) {
        return `https://vumbnail.com/${vm[1]}.jpg`;
    }

    return '/assets/video-placeholder.png';
}

const closeServiceDetailDialog = function() {
    thumbsSwiper.value = null
    serviceImages.value = []
    
    emit('update:isDrawerOpen', false)
}

const slideTo = (val) => {
    currentSlide.value = val
}

const setThumbsSwiper = (swiper) => {
    thumbsSwiper.value = swiper;
}

const buildEmbedUrl = (url) => {
  const yt = url.match(
    /(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{11})/
  );
  if (yt) return `https://www.youtube.com/embed/${yt[1]}`;
  const vm = url.match(/vimeo\.com\/(\d+)/);
  if (vm) return `https://player.vimeo.com/video/${vm[1]}`;
  return url;
}
</script>

<template>
    <!-- DIALOGO DE VER -->
    <VDialog
        :model-value="props.isDrawerOpen"
        max-width="1200"
        persistent>
        <DialogCloseBtn @click="closeServiceDetailDialog" />

        <VCard title="Detalle servicio" min-height="500">
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
                                class="mySwiper pt-0"
                            >
                                <swiper-slide v-for="(slide, index) in mediaSlides" :key="index">
                                    <img 
                                        v-if="slide.type === 'image'"
                                        :src="slide.url" 
                                        :alt="'image-'+index"
                                        class="thumb-media"
                                    />
                                    <img
                                        v-else
                                        :src="slide.thumb"
                                        :alt="'thumbnail-'+index"
                                        class="thumb-media"
                                    />
                                </swiper-slide>
                            </swiper>
                        </div>
                        <div class="d-block d-md-none">
                            <Carousel
                                id="thumbnails"
                                :items-to-show="1"
                                :wrap-around="true"
                                v-model="currentSlide"
                                ref="carousel"
                            >
                                <Slide v-for="(slide, index) in mediaSlides" :key="index">
                                    <div class="carousel__item border-img" @click="slideTo(index)">
                                        <img 
                                            v-if="slide.type === 'image'"
                                            :src="slide.url" 
                                            :alt="'image-'+index"
                                            class="thumb-media-fill"
                                        />
                                        <iframe
                                            v-else
                                            :src="buildEmbedUrl(slide.url)"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                            allowfullscreen
                                        />
                                    </div>
                                </Slide>
                            </Carousel>
                        </div>
                    </VCol>
                    <VCol md="4" cols="12" class="d-none d-md-block">
                        <swiper
                            :scrollbar="{
                                hide: true,
                            }"
                            :spaceBetween="10"
                            :thumbs="{ swiper: thumbsSwiper }"
                            :modules="modules"
                            class="mySwiper2 border-img"
                        >
                            <swiper-slide v-for="(slide, index) in mediaSlides" :key="index">
                                <img 
                                    v-if="slide.type === 'image'"
                                    :src="slide.url" 
                                    :alt="'slide-'+index"
                                    class="thumb-media-fill"
                                />
                                <iframe
                                    v-else
                                    :src="buildEmbedUrl(slide.url)"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                />
                            </swiper-slide>
                        </swiper>
                    </VCol>
                    <VCol md="7" cols="12">
                        <VCardTitle class="text-h6 title-truncate py-0 text-uppercase"> {{ title }} </VCardTitle>
                        <VCardSubtitle v-if="store" class="subtitle-truncate mb-3"> 
                            <span><strong class="me-2">Tienda:</strong> {{ store }}</span>
                        </VCardSubtitle>
                        <VDivider />
                        <VCardText class="py-4">
                            <div>SKU: 
                                <span  class="font-weight-semibold">{{ sku }}</span>
                            </div>
                        </VCardText>
                        <VDivider />
                        <VCardText class="px-4 py-2">
                            <VTabs
                                v-model="tab"
                                color="deep-purple-accent-4"
                                align-tabs="center"
                            >
                                <VTab value="0">Descripción</VTab>
                                <VTab value="1">Detalles</VTab>
                            </VTabs>
                            <VWindow v-model="tab">
                                <VWindowItem value="0">
                                    <VContainer fluid>
                                        <VRow>
                                            <VCol cols="12">
                                                <span class="text-xs description" v-html="description"></span>
                                            </VCol>
                                        </VRow>
                                    </VContainer>
                                </VWindowItem>
                                <VWindowItem value="1">
                                    <VContainer fluid>
                                        <VRow>
                                            <VCol cols="12">
                                                <div>
                                                    <span class="font-weight-semibold"> Costo: </span>
                                                    <span>{{ (parseFloat(price)).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2, style: "currency", currency: 'COP' }) }}</span>
                                                </div>
                                                <div> 
                                                    <span class="font-weight-semibold"> Envios: </span>
                                                    <span v-if="selling_price >= 0">{{ selling_price }}</span>
                                                    <span v-else>
                                                        <VIcon
                                                            size="20"
                                                            icon="tabler-alarm-filled"
                                                            class="me-1"
                                                        />
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="font-weight-semibold"> Ventas: </span>
                                                    <span v-if="sales >= 0">{{ sales }}</span>
                                                    <span v-else>
                                                        <VIcon
                                                            size="20"
                                                            icon="tabler-alarm-filled"
                                                            class="me-1"
                                                        />
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="font-weight-semibold"> Comentarios: </span>
                                                    <span v-if="comments >= 0">{{ comments }}</span>
                                                    <span v-else>
                                                        <VIcon
                                                            size="20"
                                                            icon="tabler-alarm-filled"
                                                            class="me-1"
                                                        />
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="font-weight-semibold"> Favoritos: </span>
                                                    <span v-if="favourite >= 0">{{ favourite }}</span>
                                                    <span v-else>
                                                        <VIcon
                                                            size="20"
                                                            icon="tabler-alarm-filled"
                                                            class="me-1"
                                                        />
                                                    </span>
                                                </div>  
                                                <div class="d-flex">
                                                    <span class="font-weight-semibold"> Valoración: </span>
                                                    <VRating
                                                        class="ms-1"
                                                        v-model="rating"
                                                        half-increments
                                                        readonly
                                                        density="compact"
                                                        size="small"
                                                        color="error"
                                                        active-color="error"
                                                    />
                                                </div>
                                                <div>
                                                    <span class="font-weight-semibold"> Categorías: </span>
                                                    <span v-for="category in categories">
                                                        <VChip
                                                            class="me-4"
                                                            label
                                                            size="x-small"
                                                            color="secondary"
                                                            >
                                                            {{ category }}
                                                        </VChip>
                                                    </span>
                                                </div> 
                                            </VCol>
                                        </VRow>
                                    </VContainer>
                                </VWindowItem>
                            </VWindow>
                        </VCardText>              
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
    </VDialog>
</template>

<style lang="scss" scoped>

    .custom-input-setting {

        :deep(.custom-input) {
            padding: 10px !important;
        }
    }

    .thumb-media {
        width: 60px !important;
        height: 60px !important;
        object-fit: cover !important;
        border-radius: 8px !important;
    }

    .thumb-media-fill {
        width: 100% !important;
        object-fit: cover !important;
        border-radius: 8px !important;
    }

    .carousel__item  {
        width: 100%;
        margin: 0 2px;
    }

    .carousel__item iframe {
        height: 250px !important;
        width: 100% !important;
        border-radius: 16px !important;
    }
    
    .carousel__item img {
        height: 250px !important;
        border-radius: 16px !important;
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

    .border-img {
        border-radius: 16px !important;
        border: 1px solid #D9D9D9;
        background-color: white;
        text-align: center;
        align-items: center;
        display: flex;
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
        width: 60px !important;
        height: 60px !important;
    }

    .mySwiper .swiper-slide-thumb-active {
        opacity: 1;
    }

    .swiper-slide img, .swiper-slide iframe {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: contain;
        border-radius: 8px;
    }

    .description {
        line-height: 5px;

        :deep(ul) {
            list-style: disc;
            padding-inline-start: 1.5em;
        }
    }

</style>
