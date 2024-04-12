<script setup>

import { themeConfig } from '@themeConfig'
import { useProductsStores } from '@/stores/useProducts'
import { useCategoriesStores } from '@/stores/useCategories'
import draggable from 'vuedraggable'

const productsStores = useProductsStores()
const categoriesStores = useCategoriesStores()

const myProductsList = ref([])
const products = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)
const totalPages = ref(1)
const totalProducts = ref(0)
const isRequestOngoing = ref(true)


const selectedCategory = ref('')
const categories = ref([])

const resolveStatus = statusMsg => {
  if (statusMsg === 3)
    return {
      text: 'Publicado',
      color: 'success',
    }
  if (statusMsg === 5)
    return {
      text: 'Eliminado',
      color: 'warning',
    }
  if (statusMsg === 4)
    return {
      text: 'Pendiente',
      color: 'error',
    }

  if (statusMsg === 6)
    return {
      text: 'Rechazado',
      color: 'info',
    }
}

const enabled = ref(true)

// 👉 Computing pagination data
const paginationData = computed(() => {
  const firstIndex = (rowPerPage.value === 'Todos') ? 1 : products.value.length ? (currentPage.value - 1) * rowPerPage.value + 1 : 0
  const lastIndex = (rowPerPage.value === 'Todos') ? totalProducts.value : products.value.length + (currentPage.value - 1) * rowPerPage.value

  return `Mostrando ${ firstIndex } hasta ${ lastIndex } de ${ totalProducts.value } registros`
})

// 👉 watching current page
watchEffect(() => {
  if (currentPage.value > totalPages.value)
    currentPage.value = totalPages.value
})

watchEffect(fetchData)

async function fetchData() {

  products.value =  []

  if(selectedCategory.value !== '' && selectedCategory.value !== null) {
    enabled.value = false
  } else {
    enabled.value = true
  }

  let data = {
    search: searchQuery.value,
    state_id: 3,
    category_id: selectedCategory.value,
    orderByField: 'order_id',
    orderBy: 'asc',
    limit: rowPerPage.value,
    page: currentPage.value
  }

  isRequestOngoing.value = true
  
  await categoriesStores.fetchCategoriesOrder({ limit: -1 })

  categories.value = categoriesStores.getCategories

  await productsStores.fetchProducts(data)

  myProductsList.value = productsStores.getProducts

  myProductsList.value.forEach(element =>
    products.value.push({
      id: element.id,
      name: element.name,
      favourite: element.favourite,
      discarded: element.discarded,
      user: element.user,
      state: element.state,
      state_id: element.state_id,
      in_stock: element.in_stock,
      stock: element.stock,
      archived: element.archived,            
      title: element.name,
      image: element.image,
      price: element.price_for_sale,
      sku: element.colors[0].sku,
      originalLink: themeConfig.settings.urlDomain + 'products/' + element.slug,
      categories: [...new Set(element.colors.flatMap(color => color.categories.map(item => item.category.name)))],// Utiliza map para extraer los nombres de las categorías
      categories_id: [...new Set(element.colors.flatMap(color => color.categories.map(item => item.category.id)))],// Utiliza map para extraer los id de las categorías
      rating: element.rating,//agregar mas adelante informacion
      comments: 0,//agregar mas adelante informacion
      sales: element.sales,//agregar mas adelante informacion
      selling_price: 0,//agregar mas adelante informacion,
      likes: element.likes,
      order_id: element.order_id,
      category_id: selectedCategory.value
    })
  );

  totalPages.value = productsStores.last_page
  totalProducts.value = productsStores.productsTotalCount

  isRequestOngoing.value = false
}

const closeDropdown = () => { 
  document.getElementById("selectCategory").blur()
}

const onStart = async (e) => {
  // console.log('oldIndex',e.oldIndex)
}

const onEnd = async (e) => {
  productsStores.updateOrder(products.value)
  fetchData()
}

</script>

<template>
  <section>
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
            class="mb-0"
          />
        </VCardText>
      </VCard>
    </VDialog>
    <VCard v-if="products">
      <VCardText class="d-flex align-center flex-wrap gap-4">
        <div
          class="d-flex align-center"
          style="width: 135px;">
          <span class="text-no-wrap me-3">Ver:</span>
          <VSelect
            v-model="rowPerPage"
            density="compact"
            variant="outlined"
            :items="[10, 20, 30, 50, 'Todos']"
          />
        </div>

        <VSpacer />

        <VAutocomplete
          id="selectCategory"
          v-model="selectedCategory"
          label="Categoría"
          :items="categories"
          :item-title="item => item.name"
          :item-value="item => item.id"
          autocomplete="off"
          clearable
          :menu-props="{ maxHeight: '300px' }">
          <template v-slot:item="{ props, item }">
            <v-list-item
              v-bind="props"
              :title="item?.raw?.name"
              :style="{ 
              paddingLeft: `${(item?.raw?.level) * 20}px !important`,
              paddingTop: `0 !important`,
              height: `10px !important`
              }"
            >
              <template v-slot:prepend="{ isActive }">
                <v-list-item-action start>
                  <v-checkbox-btn :model-value="isActive"></v-checkbox-btn>
                </v-list-item-action>
              </template>
            </v-list-item>
          </template>
          <template v-slot:append-item>
            <v-divider class="mt-2"></v-divider>
            <v-list-item title="Cerrar Opciones" class="text-right">
              <template v-slot:append>
                <VBtn
                  size="small"
                  variant="plain"
                  icon="tabler-x"
                  color="black"
                  :ripple="false"
                  @click="closeDropdown"/>
              </template>
            </v-list-item>
          </template>
        </VAutocomplete>
      </VCardText>
            
      <VCardText class="px-0">   
        <v-table class="text-no-wrap">
          <thead>
            <tr class="text-no-wrap">
              <th> #ORDEN ID </th>
              <th> PRODUCTO </th>
              <th class="pe-4"> STOCK </th>
              <th class="pe-4"> SKU </th>
              <th class="pe-4"> CATEGORÍAS </th>
              <th class="pe-4"> PRECIO </th>
              <th class="pe-4"> QTY </th>
              <th class="pe-4 text-end"> STATUS </th>
            </tr>
          </thead>

          <draggable 
            v-model="products" 
            tag="tbody"
            item-key="id"
            :disabled="enabled"
            @start="onStart"
            @end="onEnd">
            <template #item="{ element }">
              <tr 
                style="height: 3.75rem;"
                :class="!enabled ? 'draggable-item' : ''">
                <td> {{ !enabled ? element.order_id : '' }} </td>
                <td> 
                  <div class="d-flex align-center gap-x-2">
                    <VAvatar
                      v-if="element.image"
                      size="38"
                      variant="tonal"
                      rounded
                      :image="themeConfig.settings.urlStorage + element.image"
                    />
                    <div class="d-flex flex-column">
                      <span class="text-body-1 font-weight-medium text-uppercase">{{ element.name }}</span>
                      <span class="text-sm text-disabled">Tienda: {{ element.user.user_detail.store_name ?? (element.user.supplier?.company_name ?? (element.user.name + ' ' + (element.user.last_name ?? ''))) }}</span>
                    </div>
                  </div>
                </td>
                <td>   
                  <VSwitch 
                    :model-value="element.in_stock === 1 ? true : false"
                    readonly
                  /> 
                </td>
                <td> {{ element.sku }} </td>
                <td style="width: 10px;">
                  <span 
                    v-for="(category, index) in element.categories" 
                    :key="index"
                    class="d-block">
                      <VChip
                        class="my-1 d-flex align-center"
                        label
                        size="small"
                        color="secondary"
                        v-if="selectedCategory === null || selectedCategory === '' || (selectedCategory === element.categories_id[index])"
                      >
                        {{ category }}
                      </VChip>
                  </span>
                </td>  
                <td> {{ element.price }} </td>
                <td> {{ element.stock }} </td>
                <td class="text-end"> 
                  <VChip
                    v-bind="resolveStatus(element.state_id)"
                    density="default"
                    label
                  />
                </td>
              </tr>
            </template>
          </draggable>
          <!-- 👉 table footer  -->
          <tfoot v-show="!products.length">
            <tr>
              <td
                colspan="8"
                class="text-center">
                Datos no disponibles
              </td>
            </tr>
          </tfoot>
        </v-table>
        
        <v-divider />

        <VCardText class="d-flex align-center flex-wrap justify-space-between gap-4 py-3 px-5">
          <span class="text-sm text-disabled">
            {{ paginationData }}
          </span>

          <VPagination
            v-model="currentPage"
            size="small"
            :total-visible="5"
            :length="totalPages"/>
        </VCardText>
      </VCardText>
    </VCard>
  </section>
</template>

<style scope>
    .align-right {
      text-align: right !important;
    }

    .justify-content-end {
        justify-content: end !important;
    }

    .search {
        width: 100%;
    }

    .draggable-item:hover {
      background-color: #e9ecef; /* Color de fondo al hacer hover */
      cursor: move; /* Cambia el cursor para indicar que el elemento es interactivo */
    }

    @media(min-width: 991px){
        .search {
            width: 30rem;
        }
    }

</style>
<route lang="yaml">
  meta:
    action: ver
    subject: productos
</route>