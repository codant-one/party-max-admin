<script setup>

import { ref } from 'vue'
import { useFavoritesStores } from '@/stores/favorites'
import Loader from '@/components/common/Loader.vue'
import Product7 from '@/components/product/Product7.vue'
import Service7 from '@/components/service/Service7.vue'
import arrow_right from '@assets/icons/arrow_right.svg?inline';
import arrow_left from '@assets/icons/Arrow_left.svg?inline';

const favoritesStores = useFavoritesStores()
const route = useRoute();

const items = ref([])
const user_id = ref(null)
const isLoading = ref(true)
const isMobile = /Mobi/i.test(navigator.userAgent);

const rowPerPage = ref(5);
const currentPage = ref(1);
const totalPages = ref(1);
const totalFavorites = ref(null);

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

  isLoading.value = true

   if(localStorage.getItem('user_data')) {
        const userData = localStorage.getItem('user_data')
        const userDataJ = JSON.parse(userData)

        user_id.value = userDataJ.id  
    }

    let info = {
        user_id: user_id.value,
        orderByField: 'created_at',
        orderBy: 'desc',
        limit: rowPerPage.value,
        page: currentPage.value
    }

    var aux = await favoritesStores.fetchFavorites(info)
    items.value = favoritesStores.getData
    totalPages.value = aux.favorites.last_page;
    totalFavorites.value = aux.favoritesTotalCount;

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


const isLastItem = (index) => {
    return index === items.value.length - 1;
}

const deleteFavorite = async (data) => {

    if(data.is_product === 1)
        await favoritesStores.delete({
            user_id: user_id.value, 
            product_id: data.product_id
        })
    else
        await favoritesStores.delete({
            user_id: user_id.value, 
            service_id: data.service_id
        })

    fetchData()
 
}

</script>

<template>
    <Loader :isLoading="isLoading"/>
    <VContainer class="my-1 my-md-10 container-dashboard d-flex flex-column" v-if="items">
        <h2 class="data-title mt-5 pt-md-7">Favoritos</h2>
        <VCard class="card-profile px-0 py-0" v-if="items.length > 0">
            <template  v-for="(item, i) in items" :key="i">
                <Product7
                    v-if="item.is_product === 1"
                    :product="item"
                    :readonly="true"
                    :isLastItem="isLastItem(i)"
                    @delete="deleteFavorite"/>
                <Service7
                    v-else
                    :service="item"
                    :readonly="true"
                    :isLastItem="isLastItem(i)"
                    @delete="deleteFavorite"/>
            </template>
        </VCard>

          <!-- pagination -->
          <div class="mt-auto">
            <VCardText v-if="totalFavorites === 0" class="d-flex align-center justify-content-center py-3 px-5">
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
        padding: 16px 32px;
        margin-top: 24px;
        border-radius: 16px;
        box-shadow: none;
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

    .text-price {
        font-size: 24px;
        font-style: normal;
        font-weight: 600;
        line-height: 16px;
    }

    @media only screen and (max-width: 767px) {
        .container-dashboard {
            padding: 0 5%;
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